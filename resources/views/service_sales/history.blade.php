@extends('layouts.app')

@section('title', 'Historial de Servicios - JulySalon')

@section('body-data')
{ openViewModal: false, selectedSale: {}, viewSale(venta) { this.selectedSale = venta; this.openViewModal = true; } }
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Historial de Ventas de Servicios</h1>
            <p class="text-sm text-gray-400">Ventas de días anteriores. Solo consulta: las ventas de hoy están en "Ventas de servicios".</p>
        </div>

        <a href="{{ route('service-sales.index') }}" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Ventas de hoy
        </a>
    </div>

    <!-- Filtros: rango de fechas, empleada y búsqueda -->
    <form method="GET" action="{{ route('service-sales.history') }}" class="flex flex-wrap items-center gap-2 mb-6">
        <div class="flex items-center gap-1">
            <span class="text-xs text-gray-500">Desde</span>
            <input type="text" data-fecha data-max-hoy placeholder="dd/mm/aaaa" autocomplete="off" name="desde" value="{{ $desde }}" onchange="this.form.submit()"
                   class="w-32 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
        </div>
        <div class="flex items-center gap-1">
            <span class="text-xs text-gray-500">Hasta</span>
            <input type="text" data-fecha data-max-hoy placeholder="dd/mm/aaaa" autocomplete="off" name="hasta" value="{{ $hasta }}" onchange="this.form.submit()"
                   class="w-32 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
        </div>

        <select name="employee_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
            <option value="">Todas las empleadas</option>
            @foreach ($employees as $empleado)
                <option value="{{ $empleado->id }}" @selected((string) ($employeeFiltro ?? '') === (string) $empleado->id)>{{ $empleado->nombre }}</option>
            @endforeach
        </select>

        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por cliente o # de venta..."
               class="flex-1 min-w-[200px] px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">

        <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Buscar</button>

        @if($search || $employeeFiltro || $desde || $hasta)
            <a href="{{ route('service-sales.history') }}" class="text-xs text-pink-600 hover:underline whitespace-nowrap">Quitar filtros</a>
        @endif
    </form>

    <!-- Resumen de lo filtrado -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Total vendido</p>
            <p class="text-xs text-gray-400">
                @if($desde || $hasta)
                    {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'Inicio' }} al {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'ayer' }}
                @else
                    Todas las ventas anteriores a hoy
                @endif
                · {{ $cantidadFiltrada }} {{ $cantidadFiltrada === 1 ? 'venta completada' : 'ventas completadas' }} (sin canceladas)
            </p>
        </div>
        <p class="text-2xl font-bold text-pink-600">${{ number_format($totalFiltrado, 2) }}</p>
    </div>

    @include('service_sales.partials.tabla', ['mensajeVacio' => 'No hay ventas de servicios con esos filtros.'])
@endsection

@section('modals')
    @include('service_sales.partials.detalle-modal')
@endsection
