<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageRules;
use Illuminate\Foundation\Http\FormRequest;

class HomeSecondaryBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = (bool) $this->route('homeSecondaryBanner');

        return [
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'product_id' => ['nullable', 'exists:products,id'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:99'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => ImageRules::bannerImage(! $isUpdate),
        ];
    }

    public function messages(): array
    {
        return ImageRules::bannerImageMessages();
    }
}