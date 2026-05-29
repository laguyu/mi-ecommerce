<?php

namespace App\Support;

use App\Models\SiteSetting;

class MailBrandingData
{
    public static function fromSettings(): array
    {
        $storeName = config('app.name', 'Mi Ecommerce');
        $storeLogoUrl = null;

        try {
            $settings = SiteSetting::current();

            $storeName = trim((string) ($settings->site_name ?: $storeName));

            $logoUrl = trim((string) ($settings->logo_url ?? ''));

            if ($logoUrl !== '') {
                $storeLogoUrl = preg_match('#^https?://#i', $logoUrl) ? $logoUrl : url($logoUrl);
            }
        } catch (\Throwable) {
            // Fallback a config si settings no esta disponible.
        }

        return [
            'storeName' => $storeName,
            'storeLogoUrl' => $storeLogoUrl,
        ];
    }
}
