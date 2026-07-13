<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageRules;
use Illuminate\Foundation\Http\FormRequest;

class HomeProductCarouselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:99'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => ImageRules::bannerImage(! (bool) $this->route('homeProductCarousel')),
            'product_ids' => ['required', 'array', 'min:1', 'max:20'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return ImageRules::bannerImageMessages();
    }
}