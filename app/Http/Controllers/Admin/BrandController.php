<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $brands = Brand::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.brands.index', compact('brands', 'search'));
    }

    public function create(): View
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Brand::query()->create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $this->flushCatalogBrandCache();

        return redirect()->route('admin.brands.index')->with('status', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validated();

        $brand->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $brand->id),
            'description' => $validated['description'] ?? null,
        ]);

        $this->flushCatalogBrandCache();

        return redirect()->route('admin.brands.index')->with('status', 'Marca actualizada correctamente.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if (! request()->user()?->hasPermission('delete_products')) {
            abort(403, 'No autorizado.');
        }

        $brand->products()->update(['brand_id' => null]);

        $brand->delete();

        $this->flushCatalogBrandCache();

        return redirect()->route('admin.brands.index')->with('status', 'Marca eliminada correctamente.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Brand::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function flushCatalogBrandCache(): void
    {
        Cache::forget('catalog.brands.v1');
    }
}
