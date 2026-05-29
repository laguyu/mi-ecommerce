<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_full_name',
        'customer_email',
        'customer_address',
        'customer_city',
        'customer_postal_code',
        'coupon_code',
        'discount_rate',
        'subtotal',
        'discount_amount',
        'promotion_discount_amount',
        'coupon_discount_amount',
        'shipping_amount',
        'total',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
    ];

    protected $casts = [
        'discount_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'promotion_discount_amount' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
