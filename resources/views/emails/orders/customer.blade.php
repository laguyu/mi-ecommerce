<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido {{ $order->order_number }}</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, sans-serif; color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="padding:18px 22px; background:#0f172a; color:#fff;">
                @if(!empty($storeLogoUrl))
                    <p style="margin:0 0 10px;">
                        <img src="{{ $storeLogoUrl }}" alt="{{ $storeName }}" style="max-height:44px; width:auto; display:block;">
                    </p>
                @endif
                <p style="margin:0 0 8px; font-size:14px; color:#cbd5e1;">{{ $storeName }}</p>
                <h1 style="margin:0; font-size:21px;">{{ $isPaid ? 'Pago confirmado' : 'Pedido recibido' }}</h1>
                <p style="margin:6px 0 0; font-size:14px; color:#cbd5e1;">Orden {{ $order->order_number }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:22px;">
                <p style="margin:0 0 12px;">Hola {{ $order->customer_full_name }},</p>
                <p style="margin:0 0 14px; color:#334155;">
                    {{ $isPaid
                        ? 'Tu pedido fue pagado correctamente. Aquí tienes el resumen.'
                        : 'Recibimos tu pedido. Te compartimos el resumen para seguimiento.' }}
                </p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-bottom:12px;">
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Metodo:</strong> {{ strtoupper((string) $order->payment_method) }}</td>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Estado:</strong> {{ $isPaid ? 'Pagado' : 'Pendiente de pago' }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Fecha:</strong> {{ optional($order->paid_at)->toDateTimeString() ?? $order->created_at->toDateTimeString() }}</td>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Total:</strong> ${{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Descuento promociones:</strong> ${{ number_format((float) ($order->promotion_discount_amount ?? 0), 2) }}</td>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Descuento cupon:</strong> ${{ number_format((float) ($order->coupon_discount_amount ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Descuento total:</strong> ${{ number_format((float) (($order->promotion_discount_amount ?? 0) + ($order->coupon_discount_amount ?? 0)), 2) }}</td>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Cupon aplicado:</strong> {{ $order->coupon_code ?: 'Ninguno' }}</td>
                    </tr>
                </table>

                <h3 style="margin:0 0 8px;">Productos</h3>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left; border:1px solid #e2e8f0; background:#f8fafc; padding:8px;">Imagen</th>
                            <th style="text-align:left; border:1px solid #e2e8f0; background:#f8fafc; padding:8px;">Producto</th>
                            <th style="text-align:left; border:1px solid #e2e8f0; background:#f8fafc; padding:8px;">Cantidad</th>
                            <th style="text-align:left; border:1px solid #e2e8f0; background:#f8fafc; padding:8px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $imageUrl = $item->product?->primaryImage?->url;
                                $imageSrc = '';

                                if (!empty($imageUrl)) {
                                    $imageSrc = \Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://'])
                                        ? $imageUrl
                                        : url($imageUrl);
                                }
                            @endphp
                            <tr>
                                <td style="border:1px solid #e2e8f0; padding:8px; width:68px;">
                                    @if(!empty($imageSrc))
                                        <img src="{{ $imageSrc }}" alt="{{ $item->product_name }}" style="width:56px; height:56px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0; background:#fff; display:block;">
                                    @else
                                        <div style="width:56px; height:56px; border-radius:8px; border:1px dashed #cbd5e1; color:#94a3b8; font-size:11px; display:flex; align-items:center; justify-content:center;">Sin imagen</div>
                                    @endif
                                </td>
                                <td style="border:1px solid #e2e8f0; padding:8px;">{{ $item->product_name }}</td>
                                <td style="border:1px solid #e2e8f0; padding:8px;">{{ $item->quantity }}</td>
                                <td style="border:1px solid #e2e8f0; padding:8px;">${{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
