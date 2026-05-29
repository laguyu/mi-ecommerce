<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(private readonly OrderNotificationService $orderNotificationService)
    {
    }

    public function markOrderAsPaid(Order $order, string $method, string $reference): bool
    {
        $justPaid = false;
        $statusChangedFrom = null;
        $statusChangedTo = null;

        $result = DB::transaction(function () use ($order, $method, $reference, &$justPaid, &$statusChangedFrom, &$statusChangedTo) {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === 'paid') {
                return true;
            }

            $productIds = $lockedOrder->items->pluck('product_id')->values();

            $productsById = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lockedOrder->items as $item) {
                $product = $productsById->get($item->product_id);

                if (! $product || $product->stock < $item->quantity) {
                    $statusChangedFrom = (string) $lockedOrder->status;
                    $lockedOrder->update([
                        'status' => 'payment_failed',
                    ]);
                    $statusChangedTo = 'payment_failed';

                    return false;
                }
            }

            foreach ($lockedOrder->items as $item) {
                $product = $productsById->get($item->product_id);
                $product->decrement('stock', $item->quantity);
            }

            $lockedOrder->update([
                'status' => 'paid',
                'payment_method' => $method,
                'payment_reference' => $reference,
                'paid_at' => now(),
            ]);

            $justPaid = true;

            return true;
        });

        if ($result && $justPaid) {
            $this->orderNotificationService->sendForSuccessfulOrder($order->fresh('items'), true);

            return $result;
        }

        if (! $result && $statusChangedFrom !== null && $statusChangedTo !== null && $statusChangedFrom !== $statusChangedTo) {
            $this->orderNotificationService->sendStatusChangedToCustomer(
                $order->fresh('items'),
                $statusChangedFrom,
                $statusChangedTo
            );
        }

        return $result;
    }
}
