<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeSecondaryBannerRequest;
use App\Models\HomeSecondaryBanner;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeSecondaryBannerController extends Controller
{
    public function index(): View
    {
        $banners = HomeSecondaryBanner::query()
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.home-secondary-banners.index', compact('banners'));
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.home-secondary-banners.create', compact('products'));
    }

    public function store(HomeSecondaryBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = $request->file('image_file')->store('home-secondary-banners', 'public');

        HomeSecondaryBanner::query()->create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'image_url' => Storage::url($imagePath),
            'link_url' => $validated['link_url'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.home-secondary-banners.index')->with('status', 'Banner secundario creado correctamente.');
    }

    public function edit(HomeSecondaryBanner $homeSecondaryBanner): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.home-secondary-banners.edit', [
            'banner' => $homeSecondaryBanner,
            'products' => $products,
        ]);
    }

    public function update(HomeSecondaryBannerRequest $request, HomeSecondaryBanner $homeSecondaryBanner): RedirectResponse
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
            $oldStoragePath = $this->storagePathFromPublicUrl((string) $homeSecondaryBanner->image_url);
            $imagePath = $request->file('image_file')->store('home-secondary-banners', 'public');
            $payload['image_url'] = Storage::url($imagePath);

            if ($oldStoragePath) {
                Storage::disk('public')->delete($oldStoragePath);
            }
        }

        $homeSecondaryBanner->update($payload);

        return redirect()->route('admin.home-secondary-banners.index')->with('status', 'Banner secundario actualizado correctamente.');
    }

    public function destroy(HomeSecondaryBanner $homeSecondaryBanner): RedirectResponse
    {
        $oldStoragePath = $this->storagePathFromPublicUrl((string) $homeSecondaryBanner->image_url);

        if ($oldStoragePath) {
            Storage::disk('public')->delete($oldStoragePath);
        }

        $homeSecondaryBanner->delete();

        return redirect()->route('admin.home-secondary-banners.index')->with('status', 'Banner secundario eliminado correctamente.');
    }

    private function storagePathFromPublicUrl(string $url): ?string
    {
        if (! Str::startsWith($url, '/storage/')) {
            return null;
        }

        return Str::after($url, '/storage/');
    }
}
