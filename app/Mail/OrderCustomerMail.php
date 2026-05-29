<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\MailBrandingData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCustomerMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order, public bool $isPaid)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isPaid
                ? 'Pago confirmado: pedido ' . $this->order->order_number
                : 'Pedido recibido: ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        $branding = MailBrandingData::fromSettings();

        return new Content(
            view: 'emails.orders.customer',
            with: [
                'order' => $this->order,
                'isPaid' => $this->isPaid,
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
