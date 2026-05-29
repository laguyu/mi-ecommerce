<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;
        $type = (string) $this->input('type');

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => array_merge(['required', 'numeric', 'min:0.01'], $type === 'percentage' ? ['max:100'] : []),
            'status' => ['required', 'in:active,inactive'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ];
    }
}