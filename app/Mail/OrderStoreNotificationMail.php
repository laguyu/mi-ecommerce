<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\MailBrandingData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStoreNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order, public bool $isPaid, public string $storeEmail)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isPaid
                ? 'Pedido pagado: ' . $this->order->order_number
                : 'Nuevo pedido recibido: ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        $branding = MailBrandingData::fromSettings();

        return new Content(
            view: 'emails.orders.store',
            with: [
                'order' => $this->order,
                'isPaid' => $this->isPaid,
                'storeEmail' => $this->storeEmail,
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
