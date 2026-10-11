<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva reserva</title>
</head>
{{-- Aviso interno para el negocio. Estilos en línea para los programas de correo. --}}
<body style="margin:0; padding:0; background:#f3f4f6; font-family: Helvetica, Arial, sans-serif; color:#333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#111111; padding:20px 28px; color:#ffffff;">
                            <p style="margin:0; font-size:13px; opacity:0.9;">{{ $negocio }}</p>
                            <p style="margin:4px 0 0; font-size:20px; font-weight:bold;">📅 Nueva reserva desde la página web</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            @php
                                $filas = [
                                    'Cliente' => $cita->cliente_nombre,
                                    'Teléfono' => $cita->telefono,
                                    'Correo' => $cita->email ?: 'No lo indicó',
                                    'Servicio' => $cita->servicio . ($cita->service ? ' ($' . number_format($cita->service->precio, 2) . ')' : ''),
                                    'Fecha' => ucfirst($cita->fecha->translatedFormat('l')) . ', ' . $cita->fecha->format('d/m/Y'),
                                    'Hora' => $cita->horaTexto(),
                                ];
                            @endphp
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;">
                                @foreach ($filas as $etiqueta => $valor)
                                    <tr>
                                        <td style="padding:8px 0; color:#6b7280; width:90px; vertical-align:top; border-bottom:1px solid #f3f4f6;">{{ $etiqueta }}</td>
                                        <td style="padding:8px 0; color:#111; font-weight:bold; border-bottom:1px solid #f3f4f6;">{{ $valor }}</td>
                                    </tr>
                                @endforeach
                                @if ($cita->notas)
                                    <tr>
                                        <td style="padding:8px 0; color:#6b7280; vertical-align:top;">Notas</td>
                                        <td style="padding:8px 0; color:#111;">{{ $cita->notas }}</td>
                                    </tr>
                                @endif
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:22px;">
                                <tr>
                                    <td style="background:#db2777; border-radius:8px;">
                                        <a href="{{ route('citas.index', ['vista' => 'proximas', 'estado' => 'pendiente']) }}" style="display:inline-block; padding:11px 20px; color:#ffffff; font-size:14px; font-weight:bold; text-decoration:none;">Ver y confirmar en el sistema</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 28px; background:#f9fafb; border-top:1px solid #f3f4f6; font-size:12px; color:#9ca3af;">
                            La cita quedó como <strong>Pendiente</strong>. Si respondes este correo, la respuesta le llega al cliente{{ $cita->email ? '' : ' (este cliente no dejó correo)' }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
