<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 14px; }
        h1 { color: #db2777; font-size: 24px; margin-bottom: 0; }
        .subtitle { color: #999; font-size: 13px; margin-top: 2px; margin-bottom: 20px; }

        .resumen { width: 100%; margin-bottom: 20px; }
        .resumen td { padding: 12px 14px; border: 1px solid #e5e7eb; }
        .resumen .label { font-size: 13px; text-transform: uppercase; color: #999; display: block; margin-bottom: 4px; }
        .resumen .valor { font-size: 26px; font-weight: bold; color: #333; }
        .resumen .valor.total { color: #db2777; }

        table.detalle { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detalle th { background: #f3f4f6; text-transform: uppercase; font-size: 11px; text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
        table.detalle td { padding: 8px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        table.detalle .num { text-align: right; }
        table.detalle .center { text-align: center; }
        table.totales { width: 100%; border-collapse: collapse; border-top: 2px solid #333; background: #f9fafb; }
        table.totales td { padding: 12px 10px; text-align: right; }
        table.totales .titulo { text-align: left; font-size: 18px; font-weight: bold; color: #333; }
        table.totales .t-label { display: block; font-size: 11px; text-transform: uppercase; color: #999; }
        table.totales .t-valor { font-size: 20px; font-weight: bold; }

        table.metodos { width: 100%; margin-bottom: 15px; }
        table.metodos td { width: 33%; padding: 10px 12px; border: 1px solid #e5e7eb; background: #f9fafb; }
        table.metodos .m-label { display: block; font-size: 13px; text-transform: capitalize; color: #666; font-weight: bold; margin-bottom: 4px; }
        table.metodos .m-valor { display: block; font-size: 20px; font-weight: bold; color: #333; }
        table.metodos .m-cant { display: block; font-size: 12px; color: #999; margin-top: 2px; }

        .footer { margin-top: 30px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>

    <h1>JulySalon — Reporte de Ventas</h1>
    <p class="subtitle">
        @if ($periodo === 'dia')
            {{ $inicio->translatedFormat('l, d \d\e F \d\e Y') }}
        @else
            Del {{ $inicio->translatedFormat('d/m/Y') }} al {{ $fin->translatedFormat('d/m/Y') }}
        @endif
        &nbsp;|&nbsp; Generado el {{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i') }}
    </p>

    <table class="resumen">
        <tr>
            <td>
                <span class="label">Total del Período</span>
                <span class="valor total">${{ number_format($totalPeriodo, 2) }}</span>
            </td>
            <td>
                <span class="label"># de Ventas</span>
                <span class="valor">{{ $totalVentas }}</span>
            </td>
            <td>
                <span class="label">Ticket Promedio</span>
                <span class="valor">${{ number_format($ticketPromedio, 2) }}</span>
            </td>
        </tr>
    </table>

    <table class="resumen" style="margin-bottom:15px;">
        <tr>
            <td>
                <span class="label">Subtotal</span>
                <span class="valor" style="font-size:21px;">${{ number_format($totalSubtotal, 2) }}</span>
            </td>
            @if($totalDescuentos > 0)
                <td>
                    <span class="label">Descuentos Aplicados</span>
                    <span class="valor" style="font-size:21px; color:#dc2626;">-${{ number_format($totalDescuentos, 2) }}</span>
                </td>
            @endif
            <td>
                <span class="label">Total</span>
                <span class="valor total" style="font-size:21px;">${{ number_format($totalPeriodo, 2) }}</span>
            </td>
        </tr>
    </table>

    @if($porMetodoPago->isNotEmpty())
        <table class="metodos">
            <tr>
                @foreach (['efectivo', 'tarjeta', 'transferencia'] as $metodo)
                    <td>
                        <span class="m-label">{{ $metodo }}</span>
                        <span class="m-valor">${{ number_format(data_get($porMetodoPago, "$metodo.monto", 0), 2) }}</span>
                        <span class="m-cant">{{ data_get($porMetodoPago, "$metodo.cantidad", 0) }} {{ (data_get($porMetodoPago, "$metodo.cantidad", 0)) === 1 ? 'venta' : 'ventas' }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="detalle">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="center">Cantidad</th>
                <th class="num">Precio Unitario</th>
                <th class="num">Descuento</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productosVendidos as $fechaVenta => $detallesDelDia)
                <tr style="background:#f3f4f6;">
                    <td colspan="6" style="font-weight:bold; text-transform:uppercase; font-size:11px; color:#666;">
                        {{ \Carbon\Carbon::parse($fechaVenta)->translatedFormat('l, d \d\e F') }}
                    </td>
                </tr>
                @foreach ($detallesDelDia as $detalle)
                    <tr>
                        <td>
                            {{ $detalle->product->producto ?? 'Producto eliminado' }}
                            @if($detalle->product && $detalle->product->presentacion_valor)
                                <br><span style="font-size:11px; color:#999;">{{ rtrim(rtrim(number_format($detalle->product->presentacion_valor, 2), '0'), '.') }} {{ $detalle->product->presentacion_unidad }}</span>
                            @endif
                        </td>
                        <td>{{ $detalle->product->category->nombre ?? '—' }}</td>
                        <td class="center">{{ $detalle->cantidad }}</td>
                        <td class="num">${{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td class="num">
                            @if($detalle->descuento > 0)
                                <span style="color:#dc2626;">-${{ number_format($detalle->descuento, 2) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="num">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#999; padding: 20px;">No se registraron ventas en este período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($productosVendidos->isNotEmpty())
        <!-- Total en recuadro aparte, en letra grande -->
        <table class="totales">
            <tr>
                <td class="titulo">Total del período</td>
                <td>
                    <span class="t-valor" style="color:#db2777;">${{ number_format($totalPeriodo, 2) }}</span>
                </td>
            </tr>
        </table>
    @endif

    <p class="footer">JulySalon — Reporte generado automáticamente por el sistema.</p>

</body>
</html>