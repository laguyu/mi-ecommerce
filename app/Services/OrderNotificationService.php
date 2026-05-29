<?php

namespace App\Services;

use App\Mail\OrderCustomerMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\OrderStoreNotificationMail;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderNotificationService
{
    private const STATUS_LABELS = [
        'pending_payment' => 'Pendiente de pago',
        'paid' => 'Pagado',
        'payment_failed' => 'Pago fallido',
    ];

    public function sendForSuccessfulOrder(Order $order, bool $isPaid): void
    {
        $order->loadMissing('items.product.primaryImage');

        try {
            Mail::to($order->customer_email)->send(new OrderCustomerMail($order, $isPaid));
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar correo al comprador.', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $storeEmail = trim((string) data_get(SiteSetting::current(), 'footer_email', ''));
        } catch (Throwable $exception) {
            Log::warning('No se pudo leer correo del ecommerce en settings.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            $storeEmail = '';
        }

        if ($storeEmail === '') {
            return;
        }

        try {
            Mail::to($storeEmail)->send(new OrderStoreNotificationMail($order, $isPaid, $storeEmail));
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar correo al ecommerce.', [
                'order_id' => $order->id,
                'store_email' => $storeEmail,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function sendStatusChangedToCustomer(Order $order, string $previousStatus, string $newStatus): void
    {
        if ($previousStatus === $newStatus) {
            return;
        }

        $previousStatusLabel = self::STATUS_LABELS[$previousStatus] ?? $previousStatus;
        $newStatusLabel = self::STATUS_LABELS[$newStatus] ?? $newStatus;

        try {
            Mail::to($order->customer_email)->send(new OrderStatusChangedMail($order, $previousStatusLabel, $newStatusLabel));
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar correo de cambio de estado al comprador.', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
