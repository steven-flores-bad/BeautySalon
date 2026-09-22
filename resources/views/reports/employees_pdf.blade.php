<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 11px; }
        h1 { color: #4f46e5; font-size: 20px; margin-bottom: 0; }
        .subtitle { color: #999; font-size: 11px; margin-top: 2px; margin-bottom: 20px; }

        .empleado-header { background: #eef2ff; padding: 8px 10px; margin-top: 18px; border-radius: 4px; }
        .empleado-header .nombre { font-size: 13px; font-weight: bold; color: #3730a3; }
        .empleado-header .resumen { font-size: 10px; color: #6366f1; margin-left: 10px; }

        table.detalle { width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 4px; }
        table.detalle th { background: #f3f4f6; text-transform: uppercase; font-size: 8px; text-align: left; padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.detalle td { padding: 6px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
        table.detalle .num { text-align: right; }
        table.detalle .center { text-align: center; }

        .total-general { margin-top: 20px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; text-align: right; }
        .total-general .label { font-size: 9px; text-transform: uppercase; color: #999; }
        .total-general .valor { font-size: 16px; font-weight: bold; color: #4f46e5; }

        .footer { margin-top: 25px; font-size: 9px; color: #aaa; text-align: center; }
    </style>
</head>
<body>

    <h1>✨ BeautyControl — Reporte de Empleados</h1>
    <p class="subtitle">
        Comisiones por servicios atendidos —
        @if ($periodo === 'dia')
            {{ $inicio->translatedFormat('l, d \d\e F \d\e Y') }}
        @else
            Del {{ $inicio->translatedFormat('d/m/Y') }} al {{ $fin->translatedFormat('d/m/Y') }}
        @endif
        &nbsp;|&nbsp; Generado el {{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i') }}
    </p>

    @forelse ($porEmpleado as $grupo)
        <div class="empleado-header">
            <span class="nombre">{{ $grupo->empleado->nombre ?? 'Empleado eliminado' }}</span>
            <span class="resumen">{{ $grupo->total_servicios }} servicio(s) — generó ${{ number_format($grupo->total_subtotal, 2) }} — comisión total: ${{ number_format($grupo->total_comision, 2) }}</span>
        </div>

        <table class="detalle">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th class="center">Cant.</th>
                    <th class="num">Precio</th>
                    <th class="num">Descuento</th>
                    <th class="num">Subtotal</th>
                    <th class="num">% Com.</th>
                    <th class="num">Comisión</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grupo->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->service->nombre ?? 'Servicio eliminado' }}</td>
                        <td>{{ $detalle->cliente_nombre ?? 'Cliente general' }}</td>
                        <td>{{ \Carbon\Carbon::parse($detalle->fecha_venta)->translatedFormat('d/m/Y') }}</td>
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
                        <td class="num">{{ number_format($detalle->comision_porcentaje, 0) }}%</td>
                        <td class="num" style="color:#4f46e5; font-weight:bold;">${{ number_format($detalle->comision_monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p style="text-align:center; color:#999; padding: 30px;">No hay comisiones registradas en este período.</p>
    @endforelse

    @if($porEmpleado->isNotEmpty())
        <div class="total-general">
            <span class="label">Total de comisiones a pagar en el período:</span><br>
            <span class="valor">${{ number_format($totalComisiones, 2) }}</span>
        </div>
    @endif

    <p class="footer">BeautyControl — Reporte generado automáticamente por el sistema.</p>

</body>
</html>
