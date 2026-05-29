<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\MailBrandingData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatusLabel,
        public string $newStatusLabel
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualizacion de estado: pedido ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        $branding = MailBrandingData::fromSettings();

        return new Content(
            view: 'emails.orders.status-changed',
            with: [
                'order' => $this->order,
                'previousStatusLabel' => $this->previousStatusLabel,
                'newStatusLabel' => $this->newStatusLabel,
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
