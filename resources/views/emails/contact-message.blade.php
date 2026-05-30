<!doctype html>
<html lang="es">
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6; background: #f8fafc; padding: 24px;">
    <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
        @if(!empty($storeLogoUrl))
            <img src="{{ $storeLogoUrl }}" alt="{{ $storeName }}" style="max-width: 160px; height: auto; margin-bottom: 18px;">
        @endif

        <h1 style="margin: 0 0 8px; font-size: 22px;">Nuevo mensaje de contacto</h1>
        <p style="margin: 0 0 20px; color: #475569;">Has recibido una consulta desde el formulario de contacto de {{ $storeName }}.</p>

        <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
            <tr>
                <td style="padding: 8px 0; width: 140px; font-weight: bold;">Nombre</td>
                <td style="padding: 8px 0;">{{ $contactMessage->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; width: 140px; font-weight: bold;">Correo</td>
                <td style="padding: 8px 0;">{{ $contactMessage->email }}</td>
            </tr>
            @if($contactMessage->phone)
                <tr>
                    <td style="padding: 8px 0; width: 140px; font-weight: bold;">Teléfono</td>
                    <td style="padding: 8px 0;">{{ $contactMessage->phone }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 8px 0; width: 140px; font-weight: bold;">Asunto</td>
                <td style="padding: 8px 0;">{{ $contactMessage->subject }}</td>
            </tr>
        </table>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 18px;">
            <p style="margin: 0 0 8px; font-weight: bold;">Mensaje</p>
            <div style="white-space: pre-wrap; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">{{ $contactMessage->message }}</div>
        </div>
    </div>
</body>
</html>