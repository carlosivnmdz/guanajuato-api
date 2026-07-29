<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Código de verificación</title>
</head>

<body style="margin:0;padding:0;background:#F3F4F6;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

    <!-- Preheader: se ve en la vista previa del correo, oculto en el cuerpo -->
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        Tu código de verificación es {{ $code }}. Vence en 5 minutos.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
        <tr>
            <td align="center">

                <table role="presentation" width="480" cellpadding="0" cellspacing="0"
                    style="width:480px;max-width:100%;background:#FFFFFF;border-radius:16px;border:1px solid #E5E7EB;overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#FF8A00;padding:32px 24px;">
                            <div style="color:#FFFFFF;font-size:20px;font-weight:bold;letter-spacing:.3px;">
                                Carnicería Guanajuato
                            </div>
                            <div style="margin-top:6px;color:#FFE3C2;font-size:13px;">
                                Verificación de cuenta
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 32px;">

                            <p style="margin:0 0 4px;font-size:15px;color:#111827;">
                                Hola{{ $firstName ? ', ' . $firstName : '' }}.
                            </p>

                            <p style="margin:0 0 28px;font-size:15px;color:#6B7280;line-height:24px;">
                                Usa el siguiente código para verificar tu cuenta en la app de
                                Carnicería Guanajuato.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#FFF7ED;border-radius:12px;">
                                <tr>
                                    <td align="center" style="padding:24px;">
                                        <div style="font-size:12px;font-weight:600;letter-spacing:1px;color:#B45309;text-transform:uppercase;">
                                            Código de verificación
                                        </div>
                                        <div style="margin-top:10px;font-size:38px;font-weight:bold;letter-spacing:10px;color:#111827;">
                                            {{ $code }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:14px;color:#6B7280;line-height:22px;">
                                Este código vence en <strong style="color:#111827;">5 minutos</strong>.
                                Si no lo usas a tiempo, puedes solicitar uno nuevo desde la app.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="margin-top:24px;border-top:1px solid #E5E7EB;">
                                <tr>
                                    <td style="padding-top:20px;font-size:13px;color:#9CA3AF;line-height:20px;">
                                        Por seguridad, nunca compartas este código. Nadie de
                                        Carnicería Guanajuato te lo va a pedir por teléfono,
                                        correo o redes sociales.
                                        Si tú no solicitaste este código, puedes ignorar este
                                        correo.
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#111827;padding:20px 24px;">
                            <div style="color:#9CA3AF;font-size:12px;">
                                © {{ date('Y') }} Carnicería Guanajuato · Guanajuato, México
                            </div>
                            <div style="margin-top:4px;color:#6B7280;font-size:12px;">
                                Este es un correo automático, por favor no respondas.
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
