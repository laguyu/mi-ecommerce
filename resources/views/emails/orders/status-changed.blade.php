<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Actualizacion de estado {{ $order->order_number }}</title>
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
                <h1 style="margin:0; font-size:21px;">Estado de pedido actualizado</h1>
                <p style="margin:6px 0 0; font-size:14px; color:#cbd5e1;">Orden {{ $order->order_number }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:22px;">
                <p style="margin:0 0 12px;">Hola {{ $order->customer_full_name }},</p>
                <p style="margin:0 0 14px; color:#334155;">Te informamos que el estado de tu pedido cambió.</p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-bottom:12px;">
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Estado anterior:</strong> {{ $previousStatusLabel }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Estado actual:</strong> {{ $newStatusLabel }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Total:</strong> ${{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Descuento promociones:</strong> ${{ number_format((float) ($order->promotion_discount_amount ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e2e8f0; padding:8px;"><strong>Descuento cupon:</strong> ${{ number_format((float) ($order->coupon_discount_amount ?? 0), 2) }}</td>
                    </tr>
                </table>

                <p style="margin:0; color:#334155;">Si tienes dudas, puedes responder este correo para soporte.</p>
            </td>
        </tr>
    </table>
</body>
</html>
