<?php

namespace App\Mail;

use App\Models\SiteSetting;
use App\Support\MailBrandingData;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        $settingsName = null;

        try {
            $settingsName = trim((string) (SiteSetting::current()->site_name ?? ''));
        } catch (\Throwable) {
            $settingsName = null;
        }

        return new Envelope(
            subject: 'Bienvenido a ' . ($settingsName !== '' ? $settingsName : config('app.name', 'Mi Ecommerce')),
        );
    }

    public function content(): Content
    {
        $branding = MailBrandingData::fromSettings();

        return new Content(
            view: 'emails.welcome-user',
            with: [
                'userName' => $this->user->name,
                'storeName' => $branding['storeName'],
                'storeLogoUrl' => $branding['storeLogoUrl'],
                'storefrontUrl' => route('storefront.home'),
                'accountUrl' => route('account.profile.edit'),
                'ordersUrl' => route('account.orders.index'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
