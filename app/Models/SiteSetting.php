<?php

namespace App\Models;

use App\Support\ResolvesStoredMediaUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;
    use ResolvesStoredMediaUrls;

    protected $appends = [
        'logo_url',
    ];

    protected $fillable = [
        'site_name',
        'site_tagline',
        'site_eyebrow',
        'menu_background_color',
        'menu_text_color',
        'menu_active_background_color',
        'menu_active_text_color',
        'button_background_color',
        'button_text_color',
        'footer_background_color',
        'footer_text_color',
        'logo_path',
        'footer_address',
        'footer_phone',
        'footer_email',
        'footer_facebook_url',
        'footer_instagram_url',
        'footer_x_url',
        'footer_whatsapp_url',
        'footer_note',
        'delivery_fee',
        'free_shipping_threshold',
        'privacy_policy_content',
        'terms_of_service_content',
        'shipping_policy_content',
        'refund_policy_content',
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
        'bank_account_type',
        'bank_phone',
        'bank_reference_note',
        'bank_accounts',
    ];

    protected $casts = [
        'bank_accounts' => 'array',
        'delivery_fee' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'site_name' => 'Nova Shop',
            'site_tagline' => 'Home con carrusel, catalogo, ficha de producto y checkout con Stripe/PayPal.',
            'site_eyebrow' => 'Laravel + Vue Ecommerce',
            'menu_background_color' => '#ffffff',
            'menu_text_color' => '#111827',
            'menu_active_background_color' => '#111827',
            'menu_active_text_color' => '#ffffff',
            'button_background_color' => '#111827',
            'button_text_color' => '#ffffff',
            'footer_background_color' => '#111827',
            'footer_text_color' => '#e2e8f0',
            'delivery_fee' => 7.99,
            'free_shipping_threshold' => 120,
            'privacy_policy_content' => '',
            'terms_of_service_content' => '',
            'shipping_policy_content' => '',
            'refund_policy_content' => '',
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->resolveStoredMediaUrl($this->logo_path);
    }
}
