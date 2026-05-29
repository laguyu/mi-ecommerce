<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $categories = Category::query()
            ->with('parent:id,name,parent_id')
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('COALESCE(parent_id, 0) ASC')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        $categories = $this->categoryTreeOptions();

        return view('admin.categories.create', compact('categories'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Category::query()->create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $this->flushCatalogCategoryCache();

        return redirect()->route('admin.categories.index')->with('status', 'Categoria creada correctamente.');
    }

    public function edit(Category $category): View
    {
        $categories = $this->categoryTreeOptions([$category->id, ...$this->descendantIds($category)]);

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['parent_id'] ?? null) === $category->id) {
            return back()->withErrors(['parent_id' => 'Una categoria no puede ser su propia categoria padre.'])->withInput();
        }

        $category->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $category->id),
            'description' => $validated['description'] ?? null,
        ]);

        $this->flushCatalogCategoryCache();

        return redirect()->route('admin.categories.index')->with('status', 'Categoria actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if (! request()->user()?->hasPermission('delete_categories')) {
            abort(403, 'No autorizado.');
        }

        if ($category->products()->exists()) {
            return redirect()->route('admin.categories.index')->with('status', 'No se puede eliminar una categoria con productos asociados.');
        }

        $category->delete();

        $this->flushCatalogCategoryCache();

        return redirect()->route('admin.categories.index')->with('status', 'Categoria eliminada correctamente.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Category::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function categoryTreeOptions(array $excludeIds = []): array
    {
        $categories = Category::query()
            ->when($excludeIds !== [], fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $grouped = $categories->groupBy('parent_id');
        $options = [];

        $build = function ($parentId = null, int $depth = 0) use (&$build, $grouped, &$options): void {
            foreach ($grouped->get($parentId, collect())->sortBy('name') as $category) {
                $options[] = [
                    'id' => $category->id,
                    'label' => str_repeat('— ', $depth).$category->name,
                ];

                $build($category->id, $depth + 1);
            }
        };

        $build();

        return $options;
    }

    private function flushCatalogCategoryCache(): void
    {
        Cache::forget('catalog.categories.flat.v1');
        Cache::forget('catalog.categories.parent-map.v1');
    }
}
