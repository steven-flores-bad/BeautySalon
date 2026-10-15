{{-- Tarjetas de caja para los PDF: "Caja iniciada con" y "Total en caja".
     Requiere $periodo, $cajasDelPeriodo, $totalApertura y $resumenCaja. --}}
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
