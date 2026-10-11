<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu cita está confirmada</title>
</head>
{{-- Los correos usan estilos en línea: muchos programas de correo ignoran <style>. --}}
<body style="margin:0; padding:0; background:#fdf2f8; font-family: Helvetica, Arial, sans-serif; color:#333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fdf2f8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #fbcfe8;">
                    <tr>
                        <td style="background:#111111; padding:22px 28px; color:#ffffff;">
                            <p style="margin:0; font-size:22px; font-weight:bold;">{{ $negocio }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px; font-size:18px; font-weight:bold; color:#111;">¡Hola, {{ $cita->cliente_nombre }}!</p>

                            <!-- Aviso: confirmada -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ecfdf5; border-radius:10px; border:1px solid #6ee7b7; margin-bottom:16px;">
                                <tr>
                                    <td style="padding:12px 16px; font-size:15px; line-height:1.5; color:#065f46;">
                                        ✅ <strong>Tu cita está confirmada.</strong> ¡Te esperamos!
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fdf2f8; border-radius:12px; border:1px solid #fbcfe8;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 4px; font-size:12px; text-transform:uppercase; color:#9d174d; letter-spacing:0.5px;">Servicio</p>
                                        <p style="margin:0 0 14px; font-size:16px; font-weight:bold; color:#111;">{{ $cita->servicio }}</p>

                                        <p style="margin:0 0 4px; font-size:12px; text-transform:uppercase; color:#9d174d; letter-spacing:0.5px;">Fecha</p>
                                        <p style="margin:0 0 14px; font-size:16px; font-weight:bold; color:#111;">{{ ucfirst($cita->fecha->translatedFormat('l')) }}, {{ $cita->fecha->format('d/m/Y') }}</p>

                                        <p style="margin:0 0 4px; font-size:12px; text-transform:uppercase; color:#9d174d; letter-spacing:0.5px;">Hora</p>
                                        <p style="margin:0; font-size:16px; font-weight:bold; color:#111;">{{ $cita->horaTexto() }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0; font-size:15px; line-height:1.5; color:#444;">
                                Te recomendamos llegar unos minutos antes. Si no puedes asistir o necesitas cambiar tu cita, responde este correo o contáctanos con anticipación.
                            </p>
                        </td>
                    </tr>
                    @include('emails.partials.pie')
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
