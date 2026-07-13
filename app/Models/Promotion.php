<?php

namespace App\Models;

use App\Support\ResolvesStoredMediaUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Promotion extends Model
{
    use HasFactory;
    use ResolvesStoredMediaUrls;

    protected $fillable = [
        'name',
        'discount_percentage',
        'status',
        'banner_title',
        'banner_subtitle',
        'banner_image_url',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'discount_percentage' => 'integer',
        'status' => 'string',
        'banner_title' => 'string',
        'banner_subtitle' => 'string',
        'banner_image_url' => 'string',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_promotion')->withTimestamps();
    }

    public function scopeActiveNow(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function getBannerImageUrlAttribute(?string $value): ?string
    {
        return $this->resolveStoredMediaUrl($value);
    }
}
