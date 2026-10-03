@extends('layouts.app')

@section('title', 'Caja - JulySalon')
@section('max-width', 'max-w-3xl')

@section('body-data')
{ openCloseModal: false, openEditModal: {{ $errors->has('monto_apertura') && $caja ? 'true' : 'false' }}, cierre: {} }
@endsection

@section('content')
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <ul class="list-disc list-inside text-xs text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Caja</h1>
    <p class="text-sm text-gray-400 mb-6">{{ \Carbon\Carbon::today()->translatedFormat('l, d \d\e F \d\e Y') }}</p>

    @if ($pendiente)
        <!-- CAJA DE UN DÍA ANTERIOR QUE QUEDÓ ABIERTA -->
        <div class="bg-amber-50 border border-amber-300 rounded-xl shadow-sm p-5 mb-6">
            <div class="flex items-start gap-3 mb-4">
                <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div>
                    <h2 class="text-base font-bold text-amber-900">
                        La caja del {{ $pendiente->fecha->translatedFormat('l d/m/Y') }} quedó abierta
                    </h2>
                    <p class="text-sm text-amber-800 mt-0.5">
                        Ciérrala con el efectivo que se contó ese día. Hasta entonces no se puede abrir la caja de hoy.
                        @if($cajasPendientes > 1)
                            <span class="font-semibold">Hay {{ $cajasPendientes }} cajas pendientes; se cierran de la más antigua a la más reciente.</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                <div class="bg-white rounded-lg border border-amber-200 p-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Apertura</p>
                    <p class="text-lg font-bold text-gray-800">${{ number_format($pendiente->monto_apertura, 2) }}</p>
                </div>
                <div class="bg-white rounded-lg border border-amber-200 p-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Ventas en efectivo</p>
                    <p class="text-lg font-bold text-emerald-600">+${{ number_format($pendiente->totalVentas(['efectivo']), 2) }}</p>
                    @if($pendiente->totalGastosEfectivo() > 0)
                        <p class="text-xs text-red-500 mt-0.5">−${{ number_format($pendiente->totalGastosEfectivo(), 2) }} en gastos</p>
                    @endif
                    @if($pendiente->totalComisiones() > 0)
                        <p class="text-xs text-red-500 mt-0.5">−${{ number_format($pendiente->totalComisiones(), 2) }} en comisiones</p>
                    @endif
                </div>
                <div class="bg-white rounded-lg border border-amber-200 p-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Efectivo esperado</p>
                    <p class="text-lg font-bold text-pink-600">${{ number_format($pendiente->efectivoEsperado(), 2) }}</p>
                </div>
            </div>

            <button type="button"
                    @click="cierre = {{ Illuminate\Support\Js::from(['id' => $pendiente->id, 'fecha' => $pendiente->fecha->format('d/m/Y'), 'esperado' => number_format($pendiente->efectivoEsperado(), 2)]) }}; openCloseModal = true"
                    class="w-full sm:w-auto bg-amber-600 hover:bg-amber-700 text-white font-medium px-5 py-2.5 rounded-lg shadow transition text-sm">
                Cerrar caja del {{ $pendiente->fecha->format('d/m/Y') }}
            </button>
        </div>
    @endif

    @if (!$caja && $pendiente)
        <!-- NO SE PUEDE ABRIR LA DE HOY HASTA CERRAR LA PENDIENTE -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 opacity-60">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Abrir caja de hoy</h2>
            <p class="text-sm text-gray-500">Disponible después de cerrar la caja pendiente.</p>
        </div>

    @elseif (!$caja)
        <!-- SIN CAJA ABIERTA HOY: formulario de apertura -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Abrir caja de hoy</h2>
            <p class="text-sm text-gray-500 mb-5">Ingresa el monto con el que inicias (cambio/fondo de caja).</p>

            <form action="{{ route('cash-register.open') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Monto de Apertura ($) *</label>
                    <input type="number" step="0.01" min="0" name="monto_apertura" required value="0.00"
                           class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-pink-500 focus:border-pink-500">
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notas (opcional)</label>
                    <textarea name="notas" rows="2" placeholder="Ej. cambio en monedas de $1 y $5" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2.5 rounded-lg shadow transition text-sm">
                    Abrir Caja
                </button>
            </form>
        </div>

    @elseif ($caja->estado === 'abierta')
        <!-- CAJA ABIERTA: totales en vivo + botón de cierre -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Apertura</p>
                    <button type="button" @click="openEditModal = true"
                            class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2 py-0.5 rounded-md transition">
                        Editar
                    </button>
                </div>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($caja->monto_apertura, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ventas en Efectivo Hoy</p>
                <p class="text-2xl font-bold text-emerald-600">+${{ number_format($ventasEfectivoHoy, 2) }}</p>
            </div>
            <!-- Egresos: lo que salió de la caja (gastos en efectivo + comisiones) -->
            <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm p-5">
                <p class="text-xs text-red-400 uppercase tracking-wide font-semibold mb-1">Egresos</p>
                <p class="text-2xl font-bold text-red-600">−${{ number_format($gastosEfectivoHoy + $comisionesHoy, 2) }}</p>
                <div class="mt-2 space-y-0.5 text-xs text-red-500">
                    <div class="flex justify-between gap-2"><span>Gastos</span><span>${{ number_format($gastosEfectivoHoy, 2) }}</span></div>
                    <div class="flex justify-between gap-2"><span>Comisiones</span><span>${{ number_format($comisionesHoy, 2) }}</span></div>
                </div>
            </div>
            <div class="bg-pink-50 rounded-xl border border-pink-200 shadow-sm p-5">
                <p class="text-xs text-pink-400 uppercase tracking-wide font-semibold mb-1">Efectivo Esperado</p>
                <p class="text-2xl font-bold text-pink-600">${{ number_format($efectivoEsperado, 2) }}</p>
            </div>
        </div>

        @if($ventasTarjetaTransferenciaHoy > 0)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ventas con Tarjeta / Transferencia Hoy</p>
                <p class="text-lg font-bold text-gray-600">${{ number_format($ventasTarjetaTransferenciaHoy, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Este dinero no entra físicamente a la caja, por eso no se incluye en el "Efectivo Esperado".</p>
            </div>
        @endif

        @if($caja->notas)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Notas</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $caja->notas }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">Caja abierta</h2>
                <p class="text-xs text-gray-400">Cuando termines el día, cuenta el efectivo real y cierra la caja.</p>
            </div>
            <button @click="cierre = {{ Illuminate\Support\Js::from(['id' => $caja->id, 'fecha' => $caja->fecha->format('d/m/Y'), 'esperado' => number_format($efectivoEsperado, 2)]) }}; openCloseModal = true" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-900 text-white font-medium px-5 py-2.5 rounded-lg shadow transition text-sm whitespace-nowrap">
                Cerrar Caja
            </button>
        </div>

    @else
        <!-- CAJA YA CERRADA: resumen final -->
        {{-- Las ventas y gastos de una caja cerrada ya no se pueden modificar --}}
        @php($gastosCierre = $caja->totalGastosEfectivo())
        @php($comisionesCierre = $caja->comisionesDescontadas())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Apertura</p>
                <p class="text-xl font-bold text-gray-800">${{ number_format($caja->monto_apertura, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ventas en Efectivo</p>
                <p class="text-xl font-bold text-emerald-600">+${{ number_format($caja->totalVentas(['efectivo']), 2) }}</p>
            </div>
            <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm p-5">
                <p class="text-xs text-red-400 uppercase tracking-wide font-semibold mb-1">Egresos</p>
                <p class="text-xl font-bold text-red-600">−${{ number_format($gastosCierre + $comisionesCierre, 2) }}</p>
                <div class="mt-2 space-y-0.5 text-xs text-red-500">
                    <div class="flex justify-between gap-2"><span>Gastos</span><span>${{ number_format($gastosCierre, 2) }}</span></div>
                    <div class="flex justify-between gap-2"><span>Comisiones</span><span>${{ number_format($comisionesCierre, 2) }}</span></div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Efectivo Esperado</p>
                <p class="text-xl font-bold text-gray-800">${{ number_format($caja->monto_cierre_esperado, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Efectivo Contado (Real)</p>
                <p class="text-xl font-bold text-gray-800">${{ number_format($caja->monto_cierre_real, 2) }}</p>
            </div>
        </div>

        <div class="rounded-xl border p-6 text-center
            {{ abs($caja->diferencia) < 0.01 ? 'bg-emerald-50 border-emerald-200' : ($caja->diferencia > 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200') }}">
            <p class="text-xs uppercase tracking-wide font-semibold mb-1
                {{ abs($caja->diferencia) < 0.01 ? 'text-emerald-500' : ($caja->diferencia > 0 ? 'text-blue-500' : 'text-red-500') }}">
                @if(abs($caja->diferencia) < 0.01)
                    Caja Cuadrada
                @elseif($caja->diferencia > 0)
                    Sobrante
                @else
                    Faltante
                @endif
            </p>
            <p class="text-3xl font-bold
                {{ abs($caja->diferencia) < 0.01 ? 'text-emerald-600' : ($caja->diferencia > 0 ? 'text-blue-600' : 'text-red-600') }}">
                {{ $caja->diferencia > 0 ? '+' : '' }}${{ number_format($caja->diferencia, 2) }}
            </p>
        </div>

        @if($caja->notas)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mt-6">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Notas</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $caja->notas }}</p>
            </div>
        @endif

        <p class="text-xs text-gray-400 text-center mt-6">La caja de hoy ya fue cerrada: no se pueden registrar ni cancelar ventas hasta abrir una nueva mañana.</p>
    @endif
@endsection

@section('modals')
    @if ($caja && $caja->estado === 'abierta')
        <!-- MODAL EDITAR APERTURA -->
        <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative" @click.away="openEditModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Editar apertura</h3>
                <p class="text-sm text-gray-500 mb-5">Corrige el monto con el que se abrió la caja. El cambio quedará anotado en las notas.</p>

                <form action="{{ route('cash-register.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Monto de Apertura ($) *</label>
                        <input type="number" step="0.01" min="0" name="monto_apertura" required
                               value="{{ old('monto_apertura', number_format($caja->monto_apertura, 2, '.', '')) }}"
                               class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notas (opcional)</label>
                        <textarea name="notas" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-pink-500 focus:border-pink-500">{{ old('notas', $caja->notasEditables()) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="openEditModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if (($caja && $caja->estado === 'abierta') || $pendiente)
        <!-- MODAL CERRAR CAJA (la de hoy o una pendiente de otro día) -->
        <div x-show="openCloseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative" @click.away="openCloseModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Cerrar caja del <span x-text="cierre.fecha"></span></h3>
                <p class="text-sm text-gray-500 mb-5">
                    Efectivo esperado: <span class="font-semibold text-gray-700">$<span x-text="cierre.esperado"></span></span>.
                    Cuenta el efectivo real y escríbelo abajo.
                </p>

                <form action="{{ route('cash-register.close') }}" method="POST">
                    @csrf
                    <input type="hidden" name="caja_id" :value="cierre.id">
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Efectivo Contado (Real) ($) *</label>
                        <input type="number" step="0.01" min="0" name="monto_cierre_real" required
                               class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notas de cierre (opcional)</label>
                        <textarea name="notas_cierre" rows="2" placeholder="Ej. faltaron $2, posible error de vuelto" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="openCloseModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Confirmar Cierre</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
