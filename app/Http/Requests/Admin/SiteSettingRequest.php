<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class SiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:160'],
            'site_tagline' => ['required', 'string', 'max:220'],
            'site_eyebrow' => ['required', 'string', 'max:120'],
            'menu_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'menu_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'menu_active_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'menu_active_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo_file' => ['nullable', 'image', 'max:5120'],
            'footer_address' => ['nullable', 'string'],
            'footer_phone' => ['nullable', 'string', 'max:80'],
            'footer_email' => ['nullable', 'email', 'max:160'],
            'footer_facebook_url' => ['nullable', 'url', 'max:500'],
            'footer_instagram_url' => ['nullable', 'url', 'max:500'],
            'footer_x_url' => ['nullable', 'url', 'max:500'],
            'footer_whatsapp_url' => ['nullable', 'url', 'max:500'],
            'footer_note' => ['nullable', 'string'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['required', 'numeric', 'min:0'],
            'privacy_policy_content' => ['nullable', 'string'],
            'terms_of_service_content' => ['nullable', 'string'],
            'shipping_policy_content' => ['nullable', 'string'],
            'refund_policy_content' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_holder' => ['nullable', 'string', 'max:160'],
            'bank_account_number' => ['nullable', 'string', 'max:80'],
            'bank_account_type' => ['nullable', 'string', 'max:80'],
            'bank_phone' => ['nullable', 'string', 'max:40'],
            'bank_reference_note' => ['nullable', 'string', 'max:500'],
            'bank_accounts' => ['nullable', 'array', 'max:20'],
            'bank_accounts.*.bank_name' => ['nullable', 'string', 'max:120'],
            'bank_accounts.*.account_holder' => ['nullable', 'string', 'max:160'],
            'bank_accounts.*.account_number' => ['nullable', 'string', 'max:80'],
            'bank_accounts.*.account_type' => ['nullable', 'string', 'max:80'],
            'bank_accounts.*.phones' => ['nullable', 'string', 'max:180'],
            'bank_accounts.*.reference_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ((array) $this->input('bank_accounts', []) as $index => $account) {
                $trimmed = array_map(
                    static fn ($value) => trim((string) $value),
                    Arr::only((array) $account, [
                        'bank_name',
                        'account_holder',
                        'account_number',
                        'account_type',
                        'phones',
                        'reference_note',
                    ])
                );

                $filledFields = array_filter($trimmed, static fn (string $value): bool => $value !== '');

                if ($filledFields !== [] && count($filledFields) < count($trimmed)) {
                    $validator->errors()->add("bank_accounts.{$index}", 'Cada cuenta bancaria debe completarse por completo o dejarse vacia.');
                }
            }
        });
    }
}