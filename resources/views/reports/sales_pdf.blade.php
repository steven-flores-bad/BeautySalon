<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 12px; }
        h1 { color: #db2777; font-size: 20px; margin-bottom: 0; }
        .subtitle { color: #999; font-size: 11px; margin-top: 2px; margin-bottom: 20px; }

        .resumen { width: 100%; margin-bottom: 20px; }
        .resumen td { width: 33%; padding: 10px; border: 1px solid #e5e7eb; }
        .resumen .label { font-size: 9px; text-transform: uppercase; color: #999; display: block; margin-bottom: 4px; }
        .resumen .valor { font-size: 18px; font-weight: bold; color: #333; }
        .resumen .valor.total { color: #db2777; }

        table.detalle { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detalle th { background: #f3f4f6; text-transform: uppercase; font-size: 9px; text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
        table.detalle td { padding: 8px; border-bottom: 1px solid #f0f0f0; font-size: 11px; }
        table.detalle .num { text-align: right; }
        table.detalle .center { text-align: center; }
        table.detalle tfoot td { font-weight: bold; border-top: 2px solid #333; }

        .metodos { margin-top: 15px; margin-bottom: 15px; }
        .metodos span { display: inline-block; margin-right: 25px; }
        .metodos .m-label { font-size: 9px; text-transform: capitalize; color: #999; display: block; }
        .metodos .m-valor { font-size: 13px; font-weight: bold; }

        .footer { margin-top: 30px; font-size: 9px; color: #aaa; text-align: center; }
    </style>
</head>
<body>

    <h1>✨ BeautyControl — Reporte de Ventas</h1>
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

    @if($totalDescuentos > 0)
        <table class="resumen" style="margin-bottom:15px;">
            <tr>
                <td>
                    <span class="label">Subtotal (sin descuentos)</span>
                    <span class="valor" style="font-size:14px;">${{ number_format($totalSubtotal, 2) }}</span>
                </td>
                <td>
                    <span class="label">Descuentos Aplicados</span>
                    <span class="valor" style="font-size:14px; color:#dc2626;">-${{ number_format($totalDescuentos, 2) }}</span>
                </td>
                <td>
                    <span class="label">Total Neto</span>
                    <span class="valor total" style="font-size:14px;">${{ number_format($totalPeriodo, 2) }}</span>
                </td>
            </tr>
        </table>
    @endif

    @if($porMetodoPago->isNotEmpty())
        <div class="metodos">
            @foreach ($porMetodoPago as $metodo => $monto)
                <span>
                    <span class="m-label">{{ $metodo }}</span>
                    <span class="m-valor">${{ number_format($monto, 2) }}</span>
                </span>
            @endforeach
        </div>
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
                    <td colspan="6" style="font-weight:bold; text-transform:uppercase; font-size:9px; color:#666;">
                        {{ \Carbon\Carbon::parse($fechaVenta)->translatedFormat('l, d \d\e F') }}
                    </td>
                </tr>
                @foreach ($detallesDelDia as $detalle)
                    <tr>
                        <td>
                            {{ $detalle->product->producto ?? 'Producto eliminado' }}
                            @if($detalle->product && $detalle->product->presentacion_valor)
                                <br><span style="font-size:9px; color:#999;">{{ rtrim(rtrim(number_format($detalle->product->presentacion_valor, 2), '0'), '.') }} {{ $detalle->product->presentacion_unidad }}</span>
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
        @if($productosVendidos->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="num">Total del período:</td>
                    <td class="num">${{ number_format($totalPeriodo, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="footer">BeautyControl — Reporte generado automáticamente por el sistema.</p>

</body>
</html>