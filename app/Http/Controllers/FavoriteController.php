<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function ids(Request $request): JsonResponse
    {
        $ids = $request->user()
            ->favoriteProducts()
            ->pluck('products.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        return response()->json([
            'data' => $ids,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('status', 'active')
            ->whereHas('favoredByUsers', fn ($query) => $query->where('users.id', $request->user()->id))
            ->with(['category.parent:id,name,parent_id', 'brand:id,name', 'primaryImage:id,product_id,url'])
            ->orderByDesc('favorites.created_at')
            ->join('favorites', function ($join) use ($request) {
                $join->on('favorites.product_id', '=', 'products.id')
                    ->where('favorites.user_id', '=', $request->user()->id);
            })
            ->select('products.*')
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'slug' => $product->slug,
                'name' => $product->name,
                'brand_name' => $product->brand?->name,
                'category' => $product->category?->name,
                'category_path' => $product->category?->full_name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'image' => $product->primaryImage?->url,
                'description' => $product->description,
                'is_favorite' => true,
            ])->values(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $productId = (int) $validated['product_id'];

        $existing = Favorite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorite = false;
        } else {
            Favorite::query()->create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            $isFavorite = true;
        }

        $ids = $user->favoriteProducts()
            ->pluck('products.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        return response()->json([
            'data' => [
                'product_id' => $productId,
                'is_favorite' => $isFavorite,
                'favorite_ids' => $ids,
                'favorites_count' => $ids->count(),
            ],
        ]);
    }
}
