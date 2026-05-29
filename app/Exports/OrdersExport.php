<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Collection $orders,
        protected bool $includeUserColumn = false,
    ) {
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        $headings = [
            'Orden',
            'Cliente',
            'Email',
        ];

        if ($this->includeUserColumn) {
            $headings[] = 'Usuario';
        }

        return array_merge($headings, [
            'Estado',
            'Metodo',
            'Subtotal',
            'Descuento',
            'Envio',
            'Total',
            'Fecha',
            'Productos',
        ]);
    }

    public function map($row): array
    {
        /** @var Order $order */
        $order = $row;

        $base = [
            $order->order_number,
            $order->customer_full_name,
            $order->customer_email,
        ];

        if ($this->includeUserColumn) {
            $base[] = $order->user?->name ?? 'Invitado';
        }

        $products = $order->items
            ->map(fn ($item) => sprintf('%s x %s', $item->product_name, $item->quantity))
            ->join(' | ');

        return array_merge($base, [
            $order->status,
            $order->payment_method,
            (float) $order->subtotal,
            (float) $order->discount_amount,
            (float) $order->shipping_amount,
            (float) $order->total,
            optional($order->paid_at)->toDateTimeString() ?? $order->created_at?->toDateTimeString(),
            $products,
        ]);
    }
}
