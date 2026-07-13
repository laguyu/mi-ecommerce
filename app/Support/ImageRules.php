<?php

namespace App\Support;

class ImageRules
{
    public static function productImage(): array
    {
        return [
            'image',
            'max:4096',
            'dimensions:max_width=1600,max_height=1600',
        ];
    }

    public static function bannerImage(bool $required = true): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable'],
            [
                'image',
                'max:3072',
                'dimensions:max_width=1920,max_height=1080',
            ]
        );
    }

    public static function productImageMessages(): array
    {
        return [
            'image_files.*.dimensions' => 'La imagen del producto debe medir como maximo 1600 x 1600 pixeles.',
        ];
    }

    public static function bannerImageMessages(string $field = 'image_file'): array
    {
        return [
            "{$field}.dimensions" => 'La imagen debe medir como maximo 1920 x 1080 pixeles.',
        ];
    }
}