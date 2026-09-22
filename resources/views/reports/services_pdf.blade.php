<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 11px; }
        h1 { color: #db2777; font-size: 20px; margin-bottom: 0; }
        .subtitle { color: #999; font-size: 11px; margin-top: 2px; margin-bottom: 20px; }

        .resumen { width: 100%; margin-bottom: 15px; }
        .resumen td { width: 25%; padding: 10px; border: 1px solid #e5e7eb; }
        .resumen .label { font-size: 9px; text-transform: uppercase; color: #999; display: block; margin-bottom: 4px; }
        .resumen .valor { font-size: 16px; font-weight: bold; color: #333; }
        .resumen .valor.total { color: #db2777; }
        .resumen .valor.comision { color: #4f46e5; }

        table.detalle { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detalle th { background: #f3f4f6; text-transform: uppercase; font-size: 8px; text-align: left; padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.detalle td { padding: 6px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
        table.detalle .num { text-align: right; }
        table.detalle .center { text-align: center; }
        table.detalle tfoot td { font-weight: bold; border-top: 2px solid #333; }

        .metodos { margin-top: 10px; margin-bottom: 15px; }
        .metodos span { display: inline-block; margin-right: 20px; }
        .metodos .m-label { font-size: 9px; text-transform: capitalize; color: #999; display: block; }
        .metodos .m-valor { font-size: 12px; font-weight: bold; }

        .footer { margin-top: 25px; font-size: 9px; color: #aaa; text-align: center; }
    </style>
</head>
<body>

    <h1>✨ BeautyControl — Reporte de Servicios</h1>
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
            <td>
                <span class="label">Comisiones Totales</span>
                <span class="valor comision">${{ number_format($totalComisiones, 2) }}</span>
            </td>
        </tr>
    </table>

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
                <th>Servicio</th>
                <th>Categoría</th>
                <th>Atendió</th>
                <th class="center">Cant.</th>
                <th class="num">Precio</th>
                <th class="num">Descuento</th>
                <th class="num">Subtotal</th>
                <th class="num">Comisión</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($serviciosVendidos as $fechaVenta => $detallesDelDia)
                <tr style="background:#f3f4f6;">
                    <td colspan="8" style="font-weight:bold; text-transform:uppercase; font-size:8px; color:#666;">
                        {{ \Carbon\Carbon::parse($fechaVenta)->translatedFormat('l, d \d\e F') }}
                    </td>
                </tr>
                @foreach ($detallesDelDia as $detalle)
                    <tr>
                        <td>{{ $detalle->service->nombre ?? 'Servicio eliminado' }}</td>
                        <td>{{ $detalle->service->category->nombre ?? '—' }}</td>
                        <td>{{ $detalle->employee->nombre ?? '—' }}</td>
                        <td class="center">{{ $detalle->cantidad }}</td>
                        <td class="num">${{ number_format($detalle->precio, 2) }}</td>
                        <td class="num">
                            @if($detalle->descuento > 0)
                                <span style="color:#dc2626;">-${{ number_format($detalle->descuento, 2) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="num">${{ number_format($detalle->subtotal, 2) }}</td>
                        <td class="num" style="color:#4f46e5;">${{ number_format($detalle->comision_monto, 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#999; padding: 20px;">No se registraron servicios en este período.</td>
                </tr>
            @endforelse
        </tbody>
        @if($serviciosVendidos->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6" class="num">Totales del período:</td>
                    <td class="num">${{ number_format($totalPeriodo, 2) }}</td>
                    <td class="num">${{ number_format($totalComisiones, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="footer">BeautyControl — Reporte generado automáticamente por el sistema.</p>

</body>
</html>