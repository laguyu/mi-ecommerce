<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $siteSetting = SiteSetting::current();

        return view('admin.site-settings.edit', compact('siteSetting'));
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $siteSetting = SiteSetting::current();

        $validated = $request->validated();

        $normalizedBankAccounts = collect($validated['bank_accounts'] ?? [])
            ->map(function (array $account): array {
                return [
                    'bank_name' => trim((string) ($account['bank_name'] ?? '')),
                    'account_holder' => trim((string) ($account['account_holder'] ?? '')),
                    'account_number' => trim((string) ($account['account_number'] ?? '')),
                    'account_type' => trim((string) ($account['account_type'] ?? '')),
                    'phones' => trim((string) ($account['phones'] ?? '')),
                    'reference_note' => trim((string) ($account['reference_note'] ?? '')),
                ];
            })
            ->filter(fn (array $account): bool => collect($account)->some(fn (string $value): bool => $value !== ''))
            ->values()
            ->all();

        $primaryBank = $normalizedBankAccounts[0] ?? null;

        $payload = [
            'site_name' => $validated['site_name'],
            'site_tagline' => $validated['site_tagline'],
            'site_eyebrow' => $validated['site_eyebrow'],
            'menu_background_color' => $validated['menu_background_color'],
            'menu_text_color' => $validated['menu_text_color'],
            'menu_active_background_color' => $validated['menu_active_background_color'],
            'menu_active_text_color' => $validated['menu_active_text_color'],
            'button_background_color' => $validated['button_background_color'],
            'button_text_color' => $validated['button_text_color'],
            'footer_background_color' => $validated['footer_background_color'],
            'footer_text_color' => $validated['footer_text_color'],
            'footer_address' => $validated['footer_address'] ?? null,
            'footer_phone' => $validated['footer_phone'] ?? null,
            'footer_email' => $validated['footer_email'] ?? null,
            'footer_facebook_url' => $validated['footer_facebook_url'] ?? null,
            'footer_instagram_url' => $validated['footer_instagram_url'] ?? null,
            'footer_x_url' => $validated['footer_x_url'] ?? null,
            'footer_whatsapp_url' => $validated['footer_whatsapp_url'] ?? null,
            'footer_note' => $validated['footer_note'] ?? null,
            'delivery_fee' => $validated['delivery_fee'],
            'free_shipping_threshold' => $validated['free_shipping_threshold'],
            'privacy_policy_content' => $validated['privacy_policy_content'] ?? null,
            'terms_of_service_content' => $validated['terms_of_service_content'] ?? null,
            'shipping_policy_content' => $validated['shipping_policy_content'] ?? null,
            'refund_policy_content' => $validated['refund_policy_content'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_type' => $validated['bank_account_type'] ?? null,
            'bank_phone' => $validated['bank_phone'] ?? null,
            'bank_reference_note' => $validated['bank_reference_note'] ?? null,
            'bank_accounts' => $normalizedBankAccounts !== [] ? $normalizedBankAccounts : null,
        ];

        if ($primaryBank) {
            $payload['bank_name'] = $primaryBank['bank_name'] ?: null;
            $payload['bank_account_holder'] = $primaryBank['account_holder'] ?: null;
            $payload['bank_account_number'] = $primaryBank['account_number'] ?: null;
            $payload['bank_account_type'] = $primaryBank['account_type'] ?: null;
            $payload['bank_phone'] = $primaryBank['phones'] ?: null;
            $payload['bank_reference_note'] = $primaryBank['reference_note'] ?: null;
        }

        if ($request->hasFile('logo_file')) {
            $oldStoragePath = $this->storagePathFromPublicUrl((string) $siteSetting->logo_path);
            $logoPath = $request->file('logo_file')->store('site', 'public');
            $payload['logo_path'] = $logoPath;

            if ($oldStoragePath) {
                Storage::disk('public')->delete($oldStoragePath);
            }
        }

        $siteSetting->update($payload);

        return redirect()->route('admin.site-settings.edit')->with('status', 'Configuracion del sitio actualizada.');
    }

    private function storagePathFromPublicUrl(string $url): ?string
    {
        if (! Str::startsWith($url, '/storage/')) {
            return null;
        }

        return Str::after($url, '/storage/');
    }
}
