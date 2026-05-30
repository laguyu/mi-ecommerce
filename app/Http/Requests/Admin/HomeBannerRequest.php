<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HomeBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch') || (bool) $this->route('home_banner');

        return [
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'product_id' => ['nullable', 'exists:products,id'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:99'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => [$isUpdate ? 'nullable' : 'required', 'image', 'max:5120'],
        ];
    }
}