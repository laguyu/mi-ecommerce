<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, sans-serif; color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="padding:20px 22px; background:#0f172a; color:#fff;">
                @if(!empty($storeLogoUrl))
                    <p style="margin:0 0 10px;">
                        <img src="{{ $storeLogoUrl }}" alt="{{ $storeName }}" style="max-height:44px; width:auto; display:block;">
                    </p>
                @endif
                <h1 style="margin:0; font-size:22px;">{{ $storeName }}</h1>
                <p style="margin:6px 0 0; font-size:14px; color:#cbd5e1;">Tu cuenta ya está activa</p>
            </td>
        </tr>
        <tr>
            <td style="padding:22px;">
                <p style="margin:0 0 12px; font-size:15px;">Hola {{ $userName }},</p>
                <p style="margin:0 0 14px; font-size:15px; line-height:1.5;">
                    Gracias por registrarte. Ya puedes explorar productos, realizar pedidos y seguir su estado desde tu cuenta.
                </p>

                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:16px 0;">
                    <tr>
                        <td style="padding-right:8px;">
                            <a href="{{ $storefrontUrl }}" style="display:inline-block; text-decoration:none; background:#0f172a; color:#fff; padding:10px 14px; border-radius:8px; font-size:14px;">Ir a la tienda</a>
                        </td>
                        <td style="padding-right:8px;">
                            <a href="{{ $accountUrl }}" style="display:inline-block; text-decoration:none; background:#fff; color:#0f172a; border:1px solid #cbd5e1; padding:10px 14px; border-radius:8px; font-size:14px;">Mi cuenta</a>
                        </td>
                        <td>
                            <a href="{{ $ordersUrl }}" style="display:inline-block; text-decoration:none; background:#fff; color:#0f172a; border:1px solid #cbd5e1; padding:10px 14px; border-radius:8px; font-size:14px;">Mis pedidos</a>
                        </td>
                    </tr>
                </table>

                <p style="margin:0; font-size:13px; color:#64748b;">
                    Si no creaste esta cuenta, puedes ignorar este correo.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
