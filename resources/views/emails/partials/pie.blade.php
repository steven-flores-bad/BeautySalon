{{-- Pie de los correos al cliente: datos de contacto del negocio. --}}
@php
    $telefono = config('services.negocio.telefono');
    // Enlace de WhatsApp: código de El Salvador (503) + el número sin guiones.
    $soloDigitos = preg_replace('/\D/', '', (string) $telefono);
    $whatsapp = strlen($soloDigitos) === 8 ? '503' . $soloDigitos : $soloDigitos;
@endphp
<tr>
    <td style="padding:18px 28px; background:#f9fafb; border-top:1px solid #f3f4f6; text-align:center;">
        @if ($telefono)
            <p style="margin:0 0 6px; font-size:14px; color:#374151;">
                ¿Dudas o cambios? Llámanos o escríbenos:
                <a href="tel:+{{ $whatsapp }}" style="color:#db2777; font-weight:bold; text-decoration:none;">{{ $telefono }}</a>
            </p>
            <p style="margin:0 0 10px; font-size:13px;">
                <a href="https://wa.me/{{ $whatsapp }}" style="color:#16a34a; font-weight:bold; text-decoration:none;">💬 Escríbenos por WhatsApp</a>
            </p>
        @endif
        <p style="margin:0; font-size:12px; color:#9ca3af;">
            {{ config('services.negocio.nombre') }} · Este correo se envió automáticamente porque reservaste una cita en nuestra página.
        </p>
    </td>
</tr>
