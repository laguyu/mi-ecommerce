<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Support\MailBrandingData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name', 'Mi Ecommerce') . '] Contacto: ' . $this->contactMessage->subject,
            replyTo: [$this->contactMessage->email],
        );
    }

    public function content(): Content
    {
        $branding = MailBrandingData::fromSettings();

        return new Content(
            view: 'emails.contact-message',
            with: [
                'contactMessage' => $this->contactMessage,
                'storeName' => $branding['storeName'],
                'storeLogoUrl' => $branding['storeLogoUrl'],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}