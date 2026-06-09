<?php

namespace App\Models;

use App\Support\ResolvesStoredMediaUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HomeProductCarousel extends Model
{
    use HasFactory;
    use ResolvesStoredMediaUrls;

    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'home_product_carousel_product')
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderBy('home_product_carousel_product.sort_order')
            ->orderByDesc('products.id');
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        return $this->resolveStoredMediaUrl($value);
    }
}
