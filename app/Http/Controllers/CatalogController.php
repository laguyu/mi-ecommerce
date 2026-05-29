<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\HomeBanner;
use App\Models\HomeProductCarousel;
use App\Models\HomeSecondaryBanner;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\ProductPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    public function __construct(private readonly ProductPricingService $productPricingService)
    {
    }

    public function mainBanner(): JsonResponse
    {
        $banner = HomeBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();

        if ($banner) {
            return response()->json([
                'data' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'image' => $banner->image_url,
                    'linkUrl' => $banner->link_url,
                    'productId' => $banner->product_id,
                ],
            ]);
        }

        $fallbackProduct = Product::query()
            ->where('status', 'active')
            ->with($this->catalogProductRelations())
            ->where('show_in_main_banner', true)
            ->orderByRaw('CASE WHEN main_banner_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('main_banner_order')
            ->orderByDesc('id')
            ->first();

        if (! $fallbackProduct) {
            $fallbackProduct = Product::query()
                ->where('status', 'active')
                ->with($this->catalogProductRelations())
                ->where('is_featured', true)
                ->orderByDesc('id')
                ->first();
        }

        if (! $fallbackProduct) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $fallbackProduct->id,
                'title' => $fallbackProduct->name,
                'subtitle' => 'Descubre lo nuevo en '.($fallbackProduct->category?->name ?? 'catalogo'),
                'image' => $fallbackProduct->primaryImage?->url,
                'linkUrl' => null,
                'productId' => $fallbackProduct->id,
            ],
        ]);
    }

    public function promotionBanner(): JsonResponse
    {
        $activePromotionBanner = Promotion::query()
            ->activeNow()
            ->whereNotNull('banner_image_url')
            ->whereHas('products', fn ($query) => $query->where('status', 'active'))
            ->orderByDesc('discount_percentage')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();

        if (! $activePromotionBanner) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $activePromotionBanner->id,
                'title' => $activePromotionBanner->banner_title ?: $activePromotionBanner->name,
                'subtitle' => $activePromotionBanner->banner_subtitle ?: 'Productos con descuento por tiempo limitado',
                'image' => $activePromotionBanner->banner_image_url,
                'promotionId' => $activePromotionBanner->id,
                'discountPercentage' => (int) $activePromotionBanner->discount_percentage,
                'startsAt' => optional($activePromotionBanner->starts_at)->toIso8601String(),
                'endsAt' => optional($activePromotionBanner->ends_at)->toIso8601String(),
            ],
        ]);
    }

    public function banners(): JsonResponse
    {
        $banners = Product::query()
            ->where('status', 'active')
            ->with($this->catalogProductRelations())
            ->where('show_in_main_banner', true)
            ->orderByRaw('CASE WHEN main_banner_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('main_banner_order')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        if ($banners->isEmpty()) {
            $banners = Product::query()
                ->where('status', 'active')
                ->with($this->catalogProductRelations())
                ->where('is_featured', true)
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        return response()->json([
            'data' => $banners
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'title' => $product->name,
                    'subtitle' => 'Descubre lo nuevo en '.($product->category?->name ?? 'catalogo'),
                    'image' => $product->primaryImage?->url,
                ])
                ->values(),
        ]);
    }

    public function secondaryBanners(): JsonResponse
    {
        $banners = HomeSecondaryBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return response()->json([
            'data' => $banners->map(fn (HomeSecondaryBanner $banner) => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image' => $banner->image_url,
                'linkUrl' => $banner->link_url,
                'productId' => $banner->product_id,
            ])->values(),
        ]);
    }

    public function homeProductCarousels(): JsonResponse
    {
        $carousels = HomeProductCarousel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(3)
            ->with([
                'products' => fn ($query) => $query
                    ->where('products.status', 'active')
                    ->with($this->catalogProductRelations()),
            ])
            ->get();

        return response()->json([
            'data' => $carousels->map(function (HomeProductCarousel $carousel) {
                return [
                    'id' => $carousel->id,
                    'title' => $carousel->title,
                    'subtitle' => $carousel->subtitle,
                    'image' => $carousel->image_url,
                    'products' => $carousel->products
                        ->map(fn (Product $product) => $this->mapProduct($product))
                        ->values(),
                ];
            })->values(),
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::query()
            ->where('status', 'active')
            ->with($this->catalogProductRelations())
            ->where('show_in_home_carousel', true)
            ->orderByRaw('CASE WHEN home_carousel_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('home_carousel_order')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::query()
                ->where('status', 'active')
                ->with($this->catalogProductRelations())
                ->where('is_featured', true)
                ->orderByDesc('id')
                ->limit(6)
                ->get();
        }

        return response()->json([
            'data' => $products
                ->map(fn (Product $product) => $this->mapProduct($product))
                ->values(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('status', 'active')
            ->with($this->catalogProductRelations());

        $promotionMeta = null;

        $promotionId = (int) $request->query('promotion_id', 0);
        if ($promotionId > 0) {
            $activePromotion = Promotion::query()
                ->activeNow()
                ->whereKey($promotionId)
                ->first();

            if (! $activePromotion) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('promotions', function ($promotionQuery) use ($promotionId) {
                    $promotionQuery
                        ->where('promotions.id', $promotionId)
                        ->where('promotions.status', 'active')
                        ->where('promotions.starts_at', '<=', now())
                        ->where('promotions.ends_at', '>=', now());
                });

                $promotionMeta = [
                    'id' => $activePromotion->id,
                    'name' => $activePromotion->name,
                    'startsAt' => optional($activePromotion->starts_at)->toIso8601String(),
                    'endsAt' => optional($activePromotion->ends_at)->toIso8601String(),
                ];
            }
        }

        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $like = $this->prefixLike($search);

            $query->where(function ($nested) use ($like) {
                $nested->where('sku', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', $like))
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', $like));
            });
        }

        $categoryIds = array_values(array_filter(array_map('intval', (array) $request->query('category_ids', []))));
        if ($categoryIds !== []) {
            $query->whereIn('category_id', $this->categoryAndDescendantIds($categoryIds));
        }

        $brandIds = array_values(array_filter(array_map('intval', (array) $request->query('brand_ids', []))));
        if ($brandIds !== []) {
            $query->whereIn('brand_id', $brandIds);
        }

        $sort = (string) $request->query('sort', 'nuevos');

        match ($sort) {
            'price-asc' => $query->orderBy('price')->orderByDesc('id'),
            'price-desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'name-asc' => $query->orderBy('name')->orderByDesc('id'),
            'name-desc' => $query->orderByDesc('name')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        $paginator = $query->paginate((int) $request->query('per_page', 24));

        $paginator->getCollection()->transform(fn (Product $product) => $this->mapProduct($product));

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'promotion' => $promotionMeta,
            ],
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Cache::remember('catalog.categories.flat.v1', now()->addHour(), function (): array {
            $tree = Category::query()
                ->with(['children' => function ($query) {
                    $query->with('children')->orderBy('name');
                }])
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();

            return $this->flattenCategories($tree);
        });

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function brands(): JsonResponse
    {
        $brands = Cache::remember('catalog.brands.v1', now()->addHour(), function (): array {
            return Brand::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Brand $brand) => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ])
                ->values()
                ->all();
        });

        return response()->json([
            'data' => $brands,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load(['category.parent:id,name,parent_id', 'brand:id,name', 'images:id,product_id,url,alt_text,is_primary,sort_order', 'promotions:id,name,discount_percentage,status,starts_at,ends_at']);

        return response()->json([
            'data' => [
                ...$this->mapProduct($product),
                'images' => $product->images
                    ->sortBy([
                        ['is_primary', 'desc'],
                        ['sort_order', 'asc'],
                    ])
                    ->map(fn ($image) => [
                        'url' => $image->url,
                        'alt' => $image->alt_text,
                        'isPrimary' => (bool) $image->is_primary,
                    ])
                    ->values(),
            ],
        ]);
    }

    private function mapProduct(Product $product): array
    {
        $pricing = $this->productPricingService->pricingForProduct($product);

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand_name' => $product->brand?->name,
            'category' => $product->category?->name,
            'category_path' => $product->category?->full_name,
            'price' => $pricing['final_price'],
            'original_price' => $pricing['original_price'],
            'discount_percentage' => $pricing['discount_percentage'],
            'has_discount' => $pricing['has_discount'],
            'promotion_name' => $pricing['promotion_name'],
            'stock' => (int) $product->stock,
            'image' => $product->primaryImage?->url,
            'description' => $product->description,
        ];
    }

    private function flattenCategories($categories, string $prefix = '', int $depth = 0): array
    {
        $items = [];

        foreach ($categories as $category) {
            $path = $prefix === '' ? $category->name : $prefix.' / '.$category->name;

            $items[] = [
                'id' => $category->id,
                'name' => $category->name,
                'path' => $path,
                'depth' => $depth,
                'hasChildren' => $category->relationLoaded('children') && $category->children->isNotEmpty(),
            ];

            if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
                $items = array_merge($items, $this->flattenCategories($category->children, $path, $depth + 1));
            }
        }

        return $items;
    }

    private function prefixLike(string $value): string
    {
        $escaped = addcslashes(trim($value), "%_");

        return $escaped.'%';
    }

    private function categoryAndDescendantIds(array $categoryIds): array
    {
        $childrenByParent = Cache::remember('catalog.categories.parent-map.v1', now()->addHour(), function (): Collection {
            return Category::query()
                ->get(['id', 'parent_id'])
                ->groupBy('parent_id');
        });

        $visited = [];
        $stack = $categoryIds;

        while ($stack !== []) {
            $currentId = array_pop($stack);

            if (in_array($currentId, $visited, true)) {
                continue;
            }

            $visited[] = $currentId;

            foreach ($childrenByParent->get($currentId, collect()) as $child) {
                $stack[] = (int) $child->id;
            }
        }

        return $visited;
    }

    private function catalogProductRelations(): array
    {
        return [
            'category.parent:id,name,parent_id',
            'brand:id,name',
            'primaryImage:id,product_id,url',
            'promotions' => function ($query): void {
                $query->activeNow()
                    ->select('promotions.id', 'promotions.name', 'promotions.discount_percentage', 'promotions.status', 'promotions.starts_at', 'promotions.ends_at')
                    ->orderByDesc('discount_percentage')
                    ->orderByDesc('starts_at')
                    ->orderByDesc('id');
            },
        ];
    }
}
