<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\HandlesMediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    use HandlesMediaStorage;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with(['category.parent:id,name,parent_id', 'brand:id,name', 'primaryImage:id,product_id,url'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"))
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = Category::query()->with('parent:id,name,parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $brands = Brand::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $showInMainBanner = $request->boolean('show_in_main_banner');
        $showInHomeCarousel = $request->boolean('show_in_home_carousel');

        $product = Product::query()->create([
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'status' => $validated['status'],
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_featured' => $request->boolean('is_featured'),
            'show_in_main_banner' => $showInMainBanner,
            'main_banner_order' => $showInMainBanner ? ($validated['main_banner_order'] ?? null) : null,
            'show_in_home_carousel' => $showInHomeCarousel,
            'home_carousel_order' => $showInHomeCarousel ? ($validated['home_carousel_order'] ?? null) : null,
        ]);

        $this->uploadProductImages($product, $request->file('image_files', []), $validated['name']);

        return redirect()->route('admin.products.index')->with('status', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->with('parent:id,name,parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $brands = Brand::query()->orderBy('name')->get(['id', 'name']);
        $product->load(['primaryImage:id,product_id,url', 'images:id,product_id,url,is_primary,sort_order', 'brand:id,name', 'category.parent:id,name,parent_id']);

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $showInMainBanner = $request->boolean('show_in_main_banner');
        $showInHomeCarousel = $request->boolean('show_in_home_carousel');

        $product->update([
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'status' => $validated['status'],
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $product->id),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_featured' => $request->boolean('is_featured'),
            'show_in_main_banner' => $showInMainBanner,
            'main_banner_order' => $showInMainBanner ? ($validated['main_banner_order'] ?? null) : null,
            'show_in_home_carousel' => $showInHomeCarousel,
            'home_carousel_order' => $showInHomeCarousel ? ($validated['home_carousel_order'] ?? null) : null,
        ]);

        if ($request->hasFile('image_files')) {
            $this->uploadProductImages($product, $request->file('image_files', []), $validated['name']);
        }

        return redirect()->route('admin.products.index')->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (! request()->user()?->hasPermission('delete_products')) {
            abort(403, 'No autorizado.');
        }

        foreach ($product->images as $image) {
            $this->deleteMediaByUrl((string) $image->url);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Producto eliminado correctamente.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Product::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function uploadProductImages(Product $product, array $uploadedFiles, string $altText): void
    {
        if ($uploadedFiles === []) {
            return;
        }

        $nextSortOrder = (int) ($product->images()->max('sort_order') ?? 0);
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($uploadedFiles as $index => $file) {
            $imagePath = $this->storeMediaFile($file, 'products');

            $product->images()->create([
                'url' => $imagePath,
                'alt_text' => $altText,
                'sort_order' => ++$nextSortOrder,
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }
    }
}
