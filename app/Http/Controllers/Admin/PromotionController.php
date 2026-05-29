<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $promotions = Promotion::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.promotions.index', compact('promotions', 'search'));
    }

    public function create(): View
    {
        return view('admin.promotions.create', [
            'selectedProducts' => collect(),
        ]);
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $promotion = Promotion::query()->create([
            'name' => $validated['name'],
            'discount_percentage' => $validated['discount_percentage'],
            'status' => $validated['status'],
            'banner_title' => $validated['banner_title'] ?? null,
            'banner_subtitle' => $validated['banner_subtitle'] ?? null,
            'banner_image_url' => null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
        ]);

        if ($request->hasFile('banner_image_file')) {
            $bannerPath = $request->file('banner_image_file')->store('promotions', 'public');
            $promotion->update([
                'banner_image_url' => Storage::url($bannerPath),
            ]);
        }

        $promotion->products()->sync($validated['product_ids']);

        return redirect()->route('admin.promotions.index')->with('status', 'Promocion creada correctamente.');
    }

    public function edit(Promotion $promotion): View
    {
        $promotion->load(['products' => fn ($query) => $query->select(['products.id', 'products.name', 'products.sku', 'products.status'])->orderBy('name')]);

        return view('admin.promotions.edit', [
            'promotion' => $promotion,
            'selectedProducts' => $promotion->products,
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

    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $validated = $request->validated();

        $promotion->update([
            'name' => $validated['name'],
            'discount_percentage' => $validated['discount_percentage'],
            'status' => $validated['status'],
            'banner_title' => $validated['banner_title'] ?? null,
            'banner_subtitle' => $validated['banner_subtitle'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
        ]);

        $removeBannerImage = $request->boolean('remove_banner_image');
        $oldStoragePath = $this->storagePathFromPublicUrl((string) $promotion->banner_image_url);

        if ($removeBannerImage && $oldStoragePath) {
            Storage::disk('public')->delete($oldStoragePath);
        }

        if ($removeBannerImage) {
            $promotion->update([
                'banner_image_url' => null,
            ]);
        }

        if ($request->hasFile('banner_image_file')) {
            if (! $removeBannerImage && $oldStoragePath) {
                Storage::disk('public')->delete($oldStoragePath);
            }

            $bannerPath = $request->file('banner_image_file')->store('promotions', 'public');
            $promotion->update([
                'banner_image_url' => Storage::url($bannerPath),
            ]);
        }

        $promotion->products()->sync($validated['product_ids']);

        return redirect()->route('admin.promotions.index')->with('status', 'Promocion actualizada correctamente.');
    }

    private function storagePathFromPublicUrl(string $url): ?string
    {
        if (! Str::startsWith($url, '/storage/')) {
            return null;
        }

        return Str::after($url, '/storage/');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        if (! request()->user()?->hasPermission('delete_products')) {
            abort(403, 'No autorizado.');
        }

        $bannerStoragePath = $this->storagePathFromPublicUrl((string) $promotion->banner_image_url);
        if ($bannerStoragePath) {
            Storage::disk('public')->delete($bannerStoragePath);
        }

        $promotion->products()->detach();
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('status', 'Promocion eliminada correctamente.');
    }
}
