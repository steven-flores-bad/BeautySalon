@extends('layouts.app')

@section('title', 'Reporte de Gastos - JulySalon')
@section('max-width', 'max-w-5xl')

@section('content')
    <!-- Encabezado y descarga -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-900 w-full">Reporte de Gastos</h1>

        <a href="{{ route('reports.expenses.pdf', ['periodo' => $periodo, 'fecha' => $fecha]) }}"
           class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Descargar PDF
        </a>
    </div>

    <!-- Selector de Período y Fecha -->
    <form method="GET" action="{{ route('reports.expenses') }}" class="flex flex-wrap items-center gap-2 mb-2">
        <select name="periodo" onchange="this.form.submit()" class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
            <option value="dia" @selected($periodo === 'dia')>Día</option>
            <option value="semana" @selected($periodo === 'semana')>Semana</option>
            <option value="mes" @selected($periodo === 'mes')>Mes</option>
        </select>

        <a href="{{ route('reports.expenses', ['periodo' => $periodo, 'fecha' => $fechaAnterior]) }}"
           class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-500">‹</a>
        <input type="text" data-fecha placeholder="dd/mm/aaaa" autocomplete="off" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()"
               class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
        <a href="{{ route('reports.expenses', ['periodo' => $periodo, 'fecha' => $fechaSiguiente]) }}"
           class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-500">›</a>

        @if ($fecha !== \Carbon\Carbon::today()->toDateString())
            <a href="{{ route('reports.expenses', ['periodo' => $periodo]) }}" class="text-xs text-pink-600 hover:underline whitespace-nowrap ml-1">Hoy</a>
        @endif
    </form>

    <p class="text-sm text-gray-400 mb-6">
        Mostrando resultados de:
        <span class="font-semibold text-gray-700">
            @if ($periodo === 'dia')
                {{ $inicio->translatedFormat('l, d \d\e F \d\e Y') }}
            @else
                {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}
            @endif
        </span>
    </p>

    <!-- Resumen: ingresos - (gastos + comisiones) = ganancia -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ingresos productos</p>
            <p class="text-2xl font-bold text-gray-800">${{ number_format($ingresosProductos, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ingresos servicios</p>
            <p class="text-2xl font-bold text-gray-800">${{ number_format($ingresosServicios, 2) }}</p>
        </div>
        <div class="rounded-xl border shadow-sm p-5 {{ $ganancia >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' }}">
            <p class="text-xs uppercase tracking-wide font-semibold mb-1 {{ $ganancia >= 0 ? 'text-emerald-500' : 'text-red-500' }}">{{ $ganancia >= 0 ? 'Ganancia' : 'Pérdida' }}</p>
            <p class="text-2xl font-bold {{ $ganancia >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $ganancia < 0 ? '−' : '' }}${{ number_format(abs($ganancia), 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Ingresos ${{ number_format($totalIngresos, 2) }} − egresos ${{ number_format($totalGastos + $totalComisiones, 2) }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Gastos</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($totalGastos, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $gastos->count() }} {{ $gastos->count() === 1 ? 'gasto registrado' : 'gastos registrados' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Comisiones</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($totalComisiones, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $comisionesPorEmpleada->count() }} {{ $comisionesPorEmpleada->count() === 1 ? 'empleada' : 'empleadas' }}</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm p-5">
            <p class="text-xs text-red-400 uppercase tracking-wide font-semibold mb-1">Total de egresos</p>
            <p class="text-2xl font-bold text-red-700">${{ number_format($totalGastos + $totalComisiones, 2) }}</p>
            <p class="text-xs text-red-400 mt-1">Gastos ${{ number_format($totalGastos, 2) }} + comisiones ${{ number_format($totalComisiones, 2) }}</p>
        </div>
    </div>

    @include('components.reporte-caja')

    <!-- Gastos por categoría -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-8">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Gastos por categoría</h2>
        @forelse($porCategoria as $cat)
            <div class="mb-3 last:mb-0">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">{{ $cat->nombre }} <span class="text-xs text-gray-400">({{ $cat->cantidad }})</span></span>
                    <span class="font-semibold text-gray-800">${{ number_format($cat->monto, 2) }} <span class="text-xs text-gray-400 font-normal">{{ number_format($cat->porcentaje, 0) }}%</span></span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-red-400 rounded-full" style="width: {{ max($cat->porcentaje, 1) }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">No hay gastos en este período.</p>
        @endforelse
    </div>

    <!-- Comisiones por empleada -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">Comisiones de empleadas</h2>
            <span class="text-xs text-gray-400">Se pagan en efectivo desde la caja</span>
        </div>
        @forelse($comisionesPorEmpleada as $emp)
            <div class="flex justify-between items-center text-sm py-2 border-b border-gray-100 last:border-0">
                <span class="text-gray-700">{{ $emp->nombre }} <span class="text-xs text-gray-400">({{ $emp->servicios }} {{ $emp->servicios === 1 ? 'servicio' : 'servicios' }})</span></span>
                <span class="font-semibold text-gray-800">${{ number_format($emp->monto, 2) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">No hay comisiones en este período.</p>
        @endforelse
        @if($comisionesPorEmpleada->isNotEmpty())
            <div class="flex justify-between items-center text-sm pt-3 mt-1 border-t-2 border-gray-200 font-bold">
                <span class="text-gray-700">Total de comisiones</span>
                <span class="text-red-600">${{ number_format($totalComisiones, 2) }}</span>
            </div>
        @endif
    </div>

    <!-- Detalle de gastos -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-700">Detalle de gastos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 font-semibold">Fecha</th>
                        <th class="py-3 px-4 font-semibold">Descripción</th>
                        <th class="py-3 px-4 font-semibold">Categoría</th>
                        <th class="py-3 px-4 font-semibold">Pago</th>
                        <th class="py-3 px-4 font-semibold">Registró</th>
                        <th class="py-3 px-4 font-semibold text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse ($gastos as $gasto)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ $gasto->fecha->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-gray-900 font-medium">{{ $gasto->descripcion }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $gasto->nombreCategoria() }}</td>
                            <td class="py-3 px-4 text-gray-600 capitalize">{{ $gasto->metodo_pago }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $gasto->user->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-right text-red-600 font-semibold whitespace-nowrap">${{ number_format($gasto->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400">No se registraron gastos en este período.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($gastos->isNotEmpty())
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200 font-bold text-sm">
                            <td colspan="5" class="py-3 px-4 text-right text-gray-700">Total de gastos:</td>
                            <td class="py-3 px-4 text-right text-red-600">${{ number_format($totalGastos, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
