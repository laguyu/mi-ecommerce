<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        $isUpdate = $productId !== null;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'status' => ['required', 'in:active,inactive'],
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'show_in_main_banner' => ['nullable', 'boolean'],
            'main_banner_order' => ['nullable', 'required_if:show_in_main_banner,1', 'integer', 'min:1', 'max:99'],
            'show_in_home_carousel' => ['nullable', 'boolean'],
            'home_carousel_order' => ['nullable', 'required_if:show_in_home_carousel,1', 'integer', 'min:1', 'max:99'],
            'image_files' => [$isUpdate ? 'nullable' : 'required', 'array', $isUpdate ? 'max:10' : 'min:1', 'max:10'],
            'image_files.*' => ['image', 'max:5120'],
        ];
    }
}