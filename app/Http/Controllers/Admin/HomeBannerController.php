<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeBannerRequest;
use App\Models\HomeBanner;
use App\Models\Product;
use App\Support\HandlesMediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeBannerController extends Controller
{
    use HandlesMediaStorage;

    public function index(): View
    {
        $banners = HomeBanner::query()
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.home-banners.index', compact('banners'));
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.home-banners.create', compact('products'));
    }

    public function store(HomeBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = $this->storeMediaFile($request->file('image_file'), 'home-banners');

        HomeBanner::query()->create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'image_url' => $imagePath,
            'link_url' => $validated['link_url'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.home-banners.index')->with('status', 'Banner principal creado correctamente.');
    }

    public function edit(HomeBanner $homeBanner): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.home-banners.edit', [
            'banner' => $homeBanner,
            'products' => $products,
        ]);
    }

    public function update(HomeBannerRequest $request, HomeBanner $homeBanner): RedirectResponse
    {
        $validated = $request->validated();

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image_file')) {
            $this->deleteMediaByUrl((string) $homeBanner->image_url);
            $imagePath = $this->storeMediaFile($request->file('image_file'), 'home-banners');
            $payload['image_url'] = $imagePath;
        }

        $homeBanner->update($payload);

        return redirect()->route('admin.home-banners.index')->with('status', 'Banner principal actualizado correctamente.');
    }

    public function destroy(HomeBanner $homeBanner): RedirectResponse
    {
        $this->deleteMediaByUrl((string) $homeBanner->image_url);

        $homeBanner->delete();

        return redirect()->route('admin.home-banners.index')->with('status', 'Banner principal eliminado correctamente.');
    }
}
