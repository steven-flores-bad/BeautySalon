<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 30px 40px; }
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 13px; }
        h1 { color: #db2777; font-size: 22px; margin-bottom: 0; }
        h2 { font-size: 14px; color: #333; margin: 18px 0 6px; }
        .subtitle { color: #999; font-size: 13px; margin-top: 2px; margin-bottom: 20px; }

        .resumen { width: 100%; margin-bottom: 15px; }
        .resumen td { width: 33%; padding: 12px 14px; border: 1px solid #e5e7eb; }
        .resumen .label { font-size: 12px; text-transform: uppercase; color: #999; display: block; margin-bottom: 4px; }
        .resumen .valor { font-size: 22px; font-weight: bold; color: #333; }

        table.detalle { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.detalle th { background: #f3f4f6; text-transform: uppercase; font-size: 11px; text-align: left; padding: 8px 6px; border-bottom: 1px solid #e5e7eb; }
        table.detalle td { padding: 7px 6px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        table.detalle .num { text-align: right; }

        table.totales { width: 100%; border-collapse: collapse; border-top: 2px solid #333; background: #f9fafb; }
        table.totales td { padding: 12px 10px; text-align: right; }
        table.totales .titulo { text-align: left; font-size: 18px; font-weight: bold; color: #333; }
        table.totales .t-valor { font-size: 20px; font-weight: bold; color: #dc2626; }

        .footer { margin-top: 25px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>

    <h1>JulySalon — Reporte de Gastos</h1>
    <p class="subtitle">
        @if ($periodo === 'dia')
            {{ $inicio->translatedFormat('l, d \d\e F \d\e Y') }}
        @else
            Del {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}
        @endif
        &nbsp;|&nbsp; Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </p>

    <table class="resumen">
        <tr>
            <td>
                <span class="label">Ingresos productos</span>
                <span class="valor">${{ number_format($ingresosProductos, 2) }}</span>
            </td>
            <td>
                <span class="label">Ingresos servicios</span>
                <span class="valor">${{ number_format($ingresosServicios, 2) }}</span>
            </td>
            <td style="background: {{ $ganancia >= 0 ? '#ecfdf5' : '#fef2f2' }};">
                <span class="label">{{ $ganancia >= 0 ? 'Ganancia' : 'Pérdida' }}</span>
                <span class="valor" style="color: {{ $ganancia >= 0 ? '#059669' : '#dc2626' }};">{{ $ganancia < 0 ? '-' : '' }}${{ number_format(abs($ganancia), 2) }}</span>
                <span style="display:block; font-size:11px; color:#666; margin-top:3px;">Ingresos ${{ number_format($totalIngresos, 2) }} - egresos ${{ number_format($totalGastos + $totalComisiones, 2) }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Gastos</span>
                <span class="valor" style="color:#dc2626;">${{ number_format($totalGastos, 2) }}</span>
                <span style="display:block; font-size:11px; color:#999; margin-top:3px;">{{ $gastos->count() }} {{ $gastos->count() === 1 ? 'gasto registrado' : 'gastos registrados' }}</span>
            </td>
            <td>
                <span class="label">Comisiones</span>
                <span class="valor" style="color:#dc2626;">${{ number_format($totalComisiones, 2) }}</span>
                <span style="display:block; font-size:11px; color:#999; margin-top:3px;">{{ $comisionesPorEmpleada->count() }} {{ $comisionesPorEmpleada->count() === 1 ? 'empleada' : 'empleadas' }}</span>
            </td>
            <td style="background:#fef2f2;">
                <span class="label">Total de egresos</span>
                <span class="valor" style="color:#b91c1c;">${{ number_format($totalGastos + $totalComisiones, 2) }}</span>
                <span style="display:block; font-size:11px; color:#999; margin-top:3px;">Gastos + comisiones</span>
            </td>
        </tr>
    </table>

    <table class="resumen">
        <tr>
            <td style="width:50%;">
                <span class="label">{{ $periodo === 'dia' ? 'Caja iniciada con' : 'Total de aperturas de caja' }}</span>
                @if($cajasDelPeriodo->isEmpty())
                    <span style="font-size:15px; color:#999;">No se abrió caja en este período.</span>
                @else
                    <span class="valor">${{ number_format($totalApertura, 2) }}</span>
                    <span style="display:block; font-size:12px; color:#999; margin-top:2px;">
                        @if($periodo === 'dia')
                            Caja {{ $cajasDelPeriodo->first()->estado }}@if($cajasDelPeriodo->first()->estado === 'cerrada') — contado al cierre: ${{ number_format($cajasDelPeriodo->first()->monto_cierre_real, 2) }}@endif
                        @else
                            {{ $cajasDelPeriodo->count() }} {{ $cajasDelPeriodo->count() === 1 ? 'día con caja' : 'días con caja' }}:
                            {{ $cajasDelPeriodo->map(fn ($c) => $c->fecha->format('d/m') . ' $' . number_format($c->monto_apertura, 2))->implode(' · ') }}
                        @endif
                    </span>
                @endif
            </td>
            <td style="width:50%; background:#fdf2f8;">
                <span class="label">Total en caja</span>
                @if($cajasDelPeriodo->isEmpty())
                    <span style="font-size:15px; color:#999;">No se abrió caja en este período.</span>
                @else
                    <span class="valor" style="color:#db2777;">${{ number_format($resumenCaja->totalEnCaja, 2) }}</span>
                    <span style="display:block; font-size:12px; color:#666; margin-top:4px;">
                        Apertura ${{ number_format($resumenCaja->apertura, 2) }}
                        + ventas en efectivo ${{ number_format($resumenCaja->ventasEfectivo, 2) }}
                        @if($resumenCaja->gastosEfectivo > 0)
                            - gastos en efectivo ${{ number_format($resumenCaja->gastosEfectivo, 2) }}
                        @endif
                        @if($resumenCaja->comisiones > 0)
                            - comisiones ${{ number_format($resumenCaja->comisiones, 2) }}
                        @endif
                    </span>
                    @if($resumenCaja->ventasOtros > 0)
                        <span style="display:block; font-size:11px; color:#999; margin-top:2px;">Además ${{ number_format($resumenCaja->ventasOtros, 2) }} con tarjeta o transferencia (no entran a la caja).</span>
                    @endif
                @endif
            </td>
        </tr>
    </table>

    <h2>Gastos por categoría</h2>
    <table class="detalle">
        <thead>
            <tr>
                <th style="width:55%;">Categoría</th>
                <th class="num" style="width:15%;">Cantidad</th>
                <th class="num" style="width:15%;">%</th>
                <th class="num" style="width:15%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($porCategoria as $cat)
                <tr>
                    <td>{{ $cat->nombre }}</td>
                    <td class="num">{{ $cat->cantidad }}</td>
                    <td class="num">{{ number_format($cat->porcentaje, 0) }}%</td>
                    <td class="num">${{ number_format($cat->monto, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center; color:#999; padding:16px;">No hay gastos en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Comisiones de empleadas</h2>
    <table class="detalle">
        <thead>
            <tr>
                <th style="width:60%;">Empleada</th>
                <th class="num" style="width:20%;">Servicios</th>
                <th class="num" style="width:20%;">Comisión</th>
            </tr>
        </thead>
        <tbody>
            @forelse($comisionesPorEmpleada as $emp)
                <tr>
                    <td>{{ $emp->nombre }}</td>
                    <td class="num">{{ $emp->servicios }}</td>
                    <td class="num">${{ number_format($emp->monto, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center; color:#999; padding:16px;">No hay comisiones en este período.</td></tr>
            @endforelse
        </tbody>
        @if($comisionesPorEmpleada->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2" class="num" style="font-weight:bold; border-top:2px solid #333;">Total de comisiones</td>
                    <td class="num" style="font-weight:bold; color:#dc2626; border-top:2px solid #333;">${{ number_format($totalComisiones, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <h2>Detalle de gastos</h2>
    <table class="detalle">
        <thead>
            <tr>
                <th style="width:13%;">Fecha</th>
                <th style="width:37%;">Descripción</th>
                <th style="width:22%;">Categoría</th>
                <th style="width:14%;">Pago</th>
                <th class="num" style="width:14%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($gastos as $gasto)
                <tr>
                    <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                    <td>{{ $gasto->descripcion }}</td>
                    <td>{{ $gasto->nombreCategoria() }}</td>
                    <td style="text-transform:capitalize;">{{ $gasto->metodo_pago }}</td>
                    <td class="num" style="color:#dc2626;">${{ number_format($gasto->monto, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; color:#999; padding:16px;">No se registraron gastos en este período.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($gastos->isNotEmpty())
        <table class="totales">
            <tr>
                <td class="titulo">Total de gastos</td>
                <td><span class="t-valor">${{ number_format($totalGastos, 2) }}</span></td>
            </tr>
        </table>
    @endif

    <p class="footer">JulySalon — Reporte generado automáticamente por el sistema.</p>

</body>
</html>
