<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'status',
        'starts_at',
        'ends_at',
        'max_uses',
        'used_count',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_uses' => 'integer',
        'used_count' => 'integer',
    ];

    public function isActiveNow(?Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->starts_at && $at->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $at->gt($this->ends_at)) {
            return false;
        }

        if (!is_null($this->max_uses) && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function calculateDiscountAmount(float $subtotal): float
    {
        $safeSubtotal = max($subtotal, 0);

        if ($safeSubtotal <= 0) {
            return 0;
        }

        $amount = $this->type === 'percentage'
            ? $safeSubtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return min(max($amount, 0), $safeSubtotal);
    }
}
