<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeProductCarouselRequest;
use App\Models\HomeProductCarousel;
use App\Models\Product;
use App\Support\HandlesMediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeProductCarouselController extends Controller
{
    use HandlesMediaStorage;

    public function index(): View
    {
        $carousels = HomeProductCarousel::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.home-product-carousels.index', compact('carousels'));
    }

    public function create(): View
    {
        return view('admin.home-product-carousels.create', [
            'selectedProducts' => collect(),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json(['data' => []]);
        }

        $excludeIds = collect((array) $request->query('exclude_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $products = Product::query()
            ->select(['id', 'name', 'sku', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->where('status', 'active')
            ->when($excludeIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $excludeIds->all()))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'status' => $product->status,
            ])->values(),
        ]);
    }

    public function store(HomeProductCarouselRequest $request): RedirectResponse
    {
        if (HomeProductCarousel::query()->count() >= 3) {
            return back()->withErrors([
                'title' => 'Solo se permiten 3 carruseles en home.',
            ])->withInput();
        }

        $validated = $request->validated();

        $imagePath = $this->storeMediaFile($request->file('image_file'), 'home-carousels');

        $carousel = HomeProductCarousel::query()->create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'image_url' => $imagePath,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $syncPayload = [];
        foreach (array_values($validated['product_ids']) as $index => $productId) {
            $syncPayload[(int) $productId] = ['sort_order' => $index + 1];
        }
        $carousel->products()->sync($syncPayload);

        return redirect()->route('admin.home-product-carousels.index')->with('status', 'Carrusel de productos creado correctamente.');
    }

    public function edit(HomeProductCarousel $homeProductCarousel): View
    {
        $homeProductCarousel->load([
            'products' => fn ($query) => $query->select(['products.id', 'products.name', 'products.sku', 'products.status'])->orderBy('name'),
        ]);

        return view('admin.home-product-carousels.edit', [
            'carousel' => $homeProductCarousel,
            'selectedProducts' => $homeProductCarousel->products,
        ]);
    }

    public function update(HomeProductCarouselRequest $request, HomeProductCarousel $homeProductCarousel): RedirectResponse
    {
        $validated = $request->validated();

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image_file')) {
            $this->deleteMediaByUrl((string) $homeProductCarousel->image_url);
            $imagePath = $this->storeMediaFile($request->file('image_file'), 'home-carousels');
            $payload['image_url'] = $imagePath;
        }

        $homeProductCarousel->update($payload);

        $syncPayload = [];
        foreach (array_values($validated['product_ids']) as $index => $productId) {
            $syncPayload[(int) $productId] = ['sort_order' => $index + 1];
        }
        $homeProductCarousel->products()->sync($syncPayload);

        return redirect()->route('admin.home-product-carousels.index')->with('status', 'Carrusel de productos actualizado correctamente.');
    }

    public function destroy(HomeProductCarousel $homeProductCarousel): RedirectResponse
    {
        $this->deleteMediaByUrl((string) $homeProductCarousel->image_url);

        $homeProductCarousel->products()->detach();
        $homeProductCarousel->delete();

        return redirect()->route('admin.home-product-carousels.index')->with('status', 'Carrusel eliminado correctamente.');
    }
}
