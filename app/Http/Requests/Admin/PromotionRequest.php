<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageRules;
use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:90'],
            'status' => ['required', 'in:active,inactive'],
            'banner_title' => ['nullable', 'string', 'max:160'],
            'banner_subtitle' => ['nullable', 'string', 'max:220'],
            'banner_image_file' => ImageRules::bannerImage(false),
            'remove_banner_image' => ['nullable', 'boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'banner_image_file.dimensions' => 'La imagen del banner debe medir como maximo 1920 x 1080 pixeles.',
        ];
    }
}