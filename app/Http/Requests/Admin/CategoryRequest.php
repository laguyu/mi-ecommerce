<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category?->id;
        $excludedIds = $category ? array_merge([$categoryId], $this->descendantIds($category)) : [];

        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn($excludedIds)],
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
        ];
    }

    private function descendantIds(Category $category): array
    {
        $ids = [];

        foreach ($category->children()->get(['id']) as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->descendantIds($child));
        }

        return array_values(array_unique($ids));
    }
}