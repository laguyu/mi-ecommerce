<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pedido {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1, h2, h3 { margin: 0; }
        .header { margin-bottom: 14px; }
        .muted { color: #475569; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: middle; }
        .table th { background: #f1f5f9; text-align: left; }
        .img-cell { width: 58px; }
        .img-cell img { width: 52px; height: 52px; object-fit: cover; border: 1px solid #cbd5e1; border-radius: 6px; }
        .placeholder { width: 52px; height: 52px; border: 1px dashed #cbd5e1; border-radius: 6px; text-align: center; line-height: 52px; color: #64748b; font-size: 9px; }
        .totals { margin-top: 12px; }
        .totals td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        .right { text-align: right; }
        .section { margin-top: 12px; }
    </style>
</head>
<body>
    @php
        $statusLabels = [
            'pending_payment' => 'Pendiente de pago',
            'paid' => 'Pagado',
            'payment_failed' => 'Pago fallido',
        ];

        $paymentMethodLabels = [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'transferencia' => 'Transferencia',
            'efectivo' => 'Efectivo',
        ];
    @endphp

    <div class="header">
        <h1>Pedido {{ $order->order_number }}</h1>
        <p class="muted" style="margin:4px 0 0;">
            {{ $isAdmin ? 'Documento administrativo de pedido.' : 'Comprobante de compra.' }}
        </p>
    </div>

    <table class="summary">
        <tr>
            <td><strong>Cliente:</strong><br>{{ $order->customer_full_name }}</td>
            <td><strong>Email:</strong><br>{{ $order->customer_email }}</td>
            @if($isAdmin)
                <td><strong>Usuario:</strong><br>{{ $order->user?->name ?? 'Invitado' }}</td>
            @endif
        </tr>
        <tr>
            <td><strong>Estado:</strong><br>{{ $statusLabels[$order->status] ?? $order->status }}</td>
            <td><strong>Metodo:</strong><br>{{ $paymentMethodLabels[$order->payment_method] ?? strtoupper((string) $order->payment_method) }}</td>
            <td><strong>Fecha:</strong><br>{{ optional($order->paid_at)->toDateTimeString() ?? $order->created_at->toDateTimeString() }}</td>
        </tr>
    </table>

    <h3>Productos</h3>
    <table class="table">
        <thead>
            <tr>
                <th class="img-cell">Imagen</th>
                <th>Producto</th>
                @if($isAdmin)
                    <th>SKU</th>
                @endif
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $item)
                @php
                    $imageUrl = $item->product?->primaryImage?->url;
                    $imageAlt = $item->product?->primaryImage?->alt_text ?: $item->product_name;
                    $imageSrc = '';

                    if (!empty($imageUrl)) {
                        if (\Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://'])) {
                            $path = parse_url($imageUrl, PHP_URL_PATH) ?: '';
                            $candidate = public_path(ltrim($path, '/'));

                            if ($path !== '' && is_file($candidate)) {
                                $imageSrc = $candidate;
                            } else {
                                $imageSrc = $imageUrl;
                            }
                        } else {
                            $candidate = public_path(ltrim($imageUrl, '/'));

                            if (is_file($candidate)) {
                                $imageSrc = $candidate;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td class="img-cell">
                        @if(!empty($imageSrc))
                            <img src="{{ $imageSrc }}" alt="{{ $imageAlt }}">
                        @else
                            <div class="placeholder">N/A</div>
                        @endif
                    </td>
                    <td>{{ $item->product_name }}</td>
                    @if($isAdmin)
                        <td>{{ $item->sku }}</td>
                    @endif
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>${{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isAdmin ? 6 : 5 }}">No hay productos registrados para este pedido.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals" style="width:100%; border-collapse: collapse;">
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="right">${{ number_format((float) $order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Descuento</strong></td>
            <td class="right">-${{ number_format((float) $order->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Envio</strong></td>
            <td class="right">${{ number_format((float) $order->shipping_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="right"><strong>${{ number_format((float) $order->total, 2) }}</strong></td>
        </tr>
    </table>

    <div class="section">
        <h3>Entrega</h3>
        <p style="margin:6px 0 0;">
            <strong>Direccion:</strong> {{ $order->customer_address ?: 'No aplica (pickup)' }}<br>
            <strong>Ciudad:</strong> {{ $order->customer_city ?: '-' }}<br>
            <strong>Codigo postal:</strong> {{ $order->customer_postal_code ?: '-' }}
        </p>
    </div>
</body>
</html>
