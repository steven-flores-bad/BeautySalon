{{-- Tarjetas de caja de los reportes: "Caja iniciada con" y "Total en caja"
     (apertura + ventas en efectivo - gastos en efectivo - comisiones).
     Requiere $periodo, $cajasDelPeriodo, $totalApertura y $resumenCaja. --}}
<!-- Apertura de caja -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">
                    {{ $periodo === 'dia' ? 'Caja iniciada con' : 'Total de aperturas de caja' }}
                </p>
                @if($cajasDelPeriodo->isEmpty())
                    <p class="text-sm text-gray-500">No se abrió caja en este período.</p>
                @else
                    <p class="text-2xl font-bold text-gray-800">${{ number_format($totalApertura, 2) }}</p>
                @endif
            </div>

            @if($periodo === 'dia' && $caja = $cajasDelPeriodo->first())
                <div class="text-sm sm:text-right">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $caja->estado === 'abierta' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                        Caja {{ $caja->estado }}
                    </span>
                    @if($caja->estado === 'cerrada')
                        <p class="text-xs text-gray-500 mt-2">Efectivo contado al cierre: <span class="font-semibold text-gray-700">${{ number_format($caja->monto_cierre_real, 2) }}</span></p>
                    @endif
                </div>
            @elseif($cajasDelPeriodo->count() > 0)
                <p class="text-xs text-gray-500">{{ $cajasDelPeriodo->count() }} {{ $cajasDelPeriodo->count() === 1 ? 'día con caja' : 'días con caja' }}</p>
            @endif
        </div>

        @if($periodo !== 'dia' && $cajasDelPeriodo->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($cajasDelPeriodo as $caja)
                    <span class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600">
                        {{ $caja->fecha->translatedFormat('D d/m') }}: <span class="font-semibold text-gray-800">${{ number_format($caja->monto_apertura, 2) }}</span>
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Total en caja: apertura + ventas en efectivo - gastos en efectivo - comisiones -->
    <div class="bg-pink-50 rounded-xl border border-pink-200 shadow-sm p-5">
        <p class="text-xs text-pink-400 uppercase tracking-wide font-semibold mb-1">Total en caja</p>
        @if($cajasDelPeriodo->isEmpty())
            <p class="text-sm text-gray-500">No se abrió caja en este período.</p>
        @else
            <p class="text-2xl font-bold text-pink-600">${{ number_format($resumenCaja->totalEnCaja, 2) }}</p>
            <div class="mt-3 space-y-0.5 text-xs text-gray-600">
                <div class="flex justify-between"><span>Apertura</span><span>${{ number_format($resumenCaja->apertura, 2) }}</span></div>
                <div class="flex justify-between"><span>+ Ventas en efectivo</span><span class="text-emerald-600">${{ number_format($resumenCaja->ventasEfectivo, 2) }}</span></div>
                @if($resumenCaja->gastosEfectivo > 0)
                    <div class="flex justify-between"><span>− Gastos pagados en efectivo</span><span class="text-red-500">${{ number_format($resumenCaja->gastosEfectivo, 2) }}</span></div>
                @endif
                @if($resumenCaja->comisiones > 0)
                    <div class="flex justify-between"><span>− Comisiones de empleadas</span><span class="text-red-500">${{ number_format($resumenCaja->comisiones, 2) }}</span></div>
                @endif
            </div>
            @if($resumenCaja->ventasOtros > 0)
                <p class="text-xs text-gray-400 mt-2">Además ${{ number_format($resumenCaja->ventasOtros, 2) }} en ventas con tarjeta o transferencia (no entran a la caja).</p>
            @endif
        @endif
    </div>
</div>
