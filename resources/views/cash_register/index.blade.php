@extends('layouts.app')

@section('title', 'Caja - Salón de Belleza')
@section('max-width', 'max-w-3xl')

@section('body-data')
{ openCloseModal: false }
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

    @if (!$caja)
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Apertura</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($caja->monto_apertura, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ventas en Efectivo Hoy</p>
                <p class="text-2xl font-bold text-emerald-600">+${{ number_format($ventasEfectivoHoy, 2) }}</p>
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

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">Caja abierta</h2>
                <p class="text-xs text-gray-400">Cuando termines el día, cuenta el efectivo real y cierra la caja.</p>
            </div>
            <button @click="openCloseModal = true" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-900 text-white font-medium px-5 py-2.5 rounded-lg shadow transition text-sm whitespace-nowrap">
                Cerrar Caja
            </button>
        </div>

    @else
        <!-- CAJA YA CERRADA: resumen final -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Apertura</p>
                <p class="text-xl font-bold text-gray-800">${{ number_format($caja->monto_apertura, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ventas en Efectivo</p>
                <p class="text-xl font-bold text-emerald-600">+${{ number_format($ventasEfectivoHoy, 2) }}</p>
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

        <p class="text-xs text-gray-400 text-center mt-6">La caja de hoy ya fue cerrada. Mañana podrás abrir una nueva.</p>
    @endif
@endsection

@section('modals')
    @if ($caja && $caja->estado === 'abierta')
        <!-- MODAL CERRAR CAJA -->
        <div x-show="openCloseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative" @click.away="openCloseModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Cerrar Caja</h3>
                <p class="text-sm text-gray-500 mb-5">
                    Efectivo esperado: <span class="font-semibold text-gray-700">${{ number_format($efectivoEsperado, 2) }}</span>.
                    Cuenta el efectivo real y escríbelo abajo.
                </p>

                <form action="{{ route('cash-register.close') }}" method="POST">
                    @csrf
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
