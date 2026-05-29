<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ProductPricingService
{
    public function pricingForProduct(Product $product, ?CarbonInterface $at = null): array
    {
        $at = $at ?? now();
        $originalPrice = (float) $product->price;

        $activePromotion = $this->resolveActivePromotion($product, $at);

        if (! $activePromotion) {
            return [
                'original_price' => $originalPrice,
                'final_price' => $originalPrice,
                'discount_percentage' => 0,
                'has_discount' => false,
                'promotion_id' => null,
                'promotion_name' => null,
            ];
        }

        $discountPercentage = max(0, min(90, (int) $activePromotion->discount_percentage));
        $finalPrice = round($originalPrice * (1 - ($discountPercentage / 100)), 2);

        return [
            'original_price' => $originalPrice,
            'final_price' => $finalPrice,
            'discount_percentage' => $discountPercentage,
            'has_discount' => $discountPercentage > 0,
            'promotion_id' => $activePromotion->id,
            'promotion_name' => $activePromotion->name,
        ];
    }

    private function resolveActivePromotion(Product $product, CarbonInterface $at): ?Promotion
    {
        if ($product->relationLoaded('promotions') && $product->promotions->isNotEmpty()) {
            return $product->promotions->first();
        }

        return $product->promotions()
            ->activeNow()
            ->orderByDesc('discount_percentage')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();
    }
}
