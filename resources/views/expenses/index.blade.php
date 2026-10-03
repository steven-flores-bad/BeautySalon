@extends('layouts.app')

@section('title', ($historial ? 'Historial de Gastos' : 'Gastos') . ' - JulySalon')

@section('body-data')
{ openCreateModal: {{ $errors->any() && !old('_edit_id') ? 'true' : 'false' }}, openEditModal: {{ old('_edit_id') ? 'true' : 'false' }}, editForm: {{ Illuminate\Support\Js::from(old('_edit_id') ? ['id' => old('_edit_id'), 'fecha' => old('fecha'), 'categoria' => old('categoria'), 'descripcion' => old('descripcion'), 'monto' => old('monto'), 'metodo_pago' => old('metodo_pago'), 'notas' => old('notas')] : new stdClass) }} }
@endsection

@section('content')
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <p class="text-sm text-red-700 font-bold">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside text-xs text-red-600 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // Página actual (gastos de hoy o historial) y texto del período.
        $ruta = $historial ? 'expenses.history' : 'expenses.index';
        $textoPeriodo = match ($periodo) {
            'semana' => 'semana del ' . $inicio->format('d/m') . ' al ' . $fin->format('d/m/Y'),
            'mes' => ucfirst($inicio->translatedFormat('F Y')),
            default => $historial ? $inicio->format('d/m/Y') : 'hoy',
        };
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">{{ $historial ? 'Historial de Gastos' : 'Gastos de Hoy' }}</h1>
            <p class="text-sm text-gray-400">
                @if ($periodo === 'dia')
                    {{ ucfirst($inicio->translatedFormat('l, d \d\e F \d\e Y')) }}
                @else
                    Del {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}
                @endif
                @if ($historial)
                    · Gastos de días anteriores. Los de hoy están en "Gastos".
                @endif
            </p>
        </div>

        @if ($historial)
            <a href="{{ route('expenses.index') }}" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Gastos de hoy
            </a>
        @else
            <a href="{{ route('expenses.history') }}" class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg shadow-sm transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Historial
            </a>
            <button @click="openCreateModal = true" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Gasto
            </button>
        @endif
    </div>

    <!-- Filtros: período (solo en el historial), categoría y búsqueda -->
    <form method="GET" action="{{ route($ruta) }}" class="flex flex-wrap items-center gap-2 mb-6">
        @if ($historial)
            <select name="periodo" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                <option value="dia" @selected($periodo === 'dia')>Día</option>
                <option value="semana" @selected($periodo === 'semana')>Semana</option>
                <option value="mes" @selected($periodo === 'mes')>Mes</option>
            </select>

            <div class="flex items-center gap-1">
                <a href="{{ route($ruta, array_merge(request()->except('page'), ['periodo' => $periodo, 'fecha' => $fechaAnterior])) }}"
                   class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-500">‹</a>
                <input type="text" data-fecha data-max-hoy placeholder="dd/mm/aaaa" autocomplete="off" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()"
                       class="w-32 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                @if (\Carbon\Carbon::parse($fechaSiguiente)->lt(today()))
                    <a href="{{ route($ruta, array_merge(request()->except('page'), ['periodo' => $periodo, 'fecha' => $fechaSiguiente])) }}"
                       class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-500">›</a>
                @endif
            </div>
        @endif

        <select name="categoria" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $clave => $nombre)
                <option value="{{ $clave }}" @selected($categoria === $clave)>{{ $nombre }}</option>
            @endforeach
        </select>

        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por descripción..."
               class="flex-1 min-w-[200px] px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">

        <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Buscar</button>

        @if($search || $categoria)
            <a href="{{ $historial ? route($ruta, ['periodo' => $periodo, 'fecha' => $fecha]) : route($ruta) }}" class="text-xs text-pink-600 hover:underline whitespace-nowrap">Quitar filtros</a>
        @endif
    </form>

    <!-- Totales del período: gastos, comisiones y total de egresos -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Gastos — {{ $textoPeriodo }}</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($totalGastos, 2) }}</p>
            @if($categoria || $search)
                <p class="text-xs text-gray-400 mt-1">Con los filtros aplicados{{ $categoria ? ': ' . ($categorias[$categoria] ?? '') : '' }}</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Comisiones de empleadas</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($totalComisiones, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Por los servicios vendidos — {{ $textoPeriodo }}</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm p-5">
            <p class="text-xs text-red-400 uppercase tracking-wide font-semibold mb-1">Total de egresos</p>
            <p class="text-2xl font-bold text-red-700">${{ number_format($totalEgresos, 2) }}</p>
            <p class="text-xs text-red-400 mt-1">Todos los gastos + comisiones — {{ $textoPeriodo }}</p>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 font-semibold">Fecha</th>
                        <th class="py-3 px-4 font-semibold">Descripción</th>
                        <th class="py-3 px-4 font-semibold">Categoría</th>
                        <th class="py-3 px-4 font-semibold">Pago</th>
                        <th class="py-3 px-4 font-semibold text-right">Monto</th>
                        <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse ($expenses as $gasto)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ $gasto->fecha->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                <span class="text-gray-900 font-medium">{{ $gasto->descripcion }}</span>
                                @if($gasto->notas)
                                    <span class="block text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($gasto->notas, 80) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $gasto->nombreCategoria() }}</span>
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                <span class="capitalize">{{ $gasto->metodo_pago }}</span>
                                @if($gasto->cash_register_id)
                                    <span class="block text-xs text-gray-400">Salió de la caja</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-red-600 font-semibold whitespace-nowrap">${{ number_format($gasto->monto, 2) }}</td>
                            <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                                @if($gasto->sePuedeModificar())
                                    <button @click="openEditModal = true; editForm = {{ Illuminate\Support\Js::from([
                                                'id' => $gasto->id,
                                                'fecha' => $gasto->fecha->format('Y-m-d'),
                                                'categoria' => $gasto->categoria,
                                                'descripcion' => $gasto->descripcion,
                                                'monto' => $gasto->monto,
                                                'metodo_pago' => $gasto->metodo_pago,
                                                'notas' => $gasto->notas,
                                            ]) }}; $nextTick(() => $refs.editFecha._flatpickr?.setDate(editForm.fecha, false))"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                        Editar
                                    </button>
                                    <form action="{{ route('expenses.destroy', $gasto) }}" method="POST" class="inline-block" @submit.prevent="$dispatch('confirm-action', { form: $el, type: 'delete', title: 'Eliminar gasto', name: @js($gasto->descripcion), message: 'Esta acción no se puede deshacer.' })">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 px-2.5 py-1 rounded-md transition">
                                            Eliminar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs px-2.5 py-1" title="Forma parte del cierre de una caja">Caja cerrada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">No hay gastos registrados en este período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $expenses->links() }}
        </div>
    </div>
@endsection

@section('modals')
    @php($hoy = today()->format('Y-m-d'))

    <!-- MODAL NUEVO GASTO -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openCreateModal = false" x-data="{ metodo: @js(old('_edit_id') ? 'efectivo' : old('metodo_pago', 'efectivo')) }">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Nuevo Gasto</h3>
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Fecha *</label>
                            <input type="text" data-fecha data-max-hoy name="fecha" required autocomplete="off" value="{{ old('_edit_id') ? $hoy : old('fecha', $hoy) }}"
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Monto ($) *</label>
                            <input type="number" step="0.01" min="0.01" name="monto" required placeholder="0.00" value="{{ old('_edit_id') ? '' : old('monto') }}"
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descripción *</label>
                        <input type="text" name="descripcion" required maxlength="255" placeholder="Ej. Recibo de luz de septiembre" value="{{ old('_edit_id') ? '' : old('descripcion') }}"
                               class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Categoría *</label>
                            <select name="categoria" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                                @foreach ($categorias as $clave => $nombre)
                                    <option value="{{ $clave }}" @selected(!old('_edit_id') && old('categoria') === $clave)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Método de pago *</label>
                            <select name="metodo_pago" x-model="metodo" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>
                    <p x-show="metodo === 'efectivo'" class="text-xs {{ $cajaAbierta ? 'text-amber-700 bg-amber-50' : 'text-gray-500 bg-gray-50' }} rounded-lg px-3 py-2">
                        @if($cajaAbierta)
                            Si la fecha es hoy, este gasto saldrá de la caja abierta y se restará del efectivo esperado al cerrar.
                        @else
                            No hay caja abierta hoy: el gasto se registra, pero no se resta de ninguna caja.
                        @endif
                    </p>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notas (opcional)</label>
                        <textarea name="notas" rows="2" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">{{ old('_edit_id') ? '' : old('notas') }}</textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR GASTO -->
    <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openEditModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Gasto</h3>
            <form :action="'/expenses/' + editForm.id" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_edit_id" :value="editForm.id">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Fecha *</label>
                            <input type="text" data-fecha data-max-hoy x-ref="editFecha" name="fecha" required autocomplete="off" :value="editForm.fecha"
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Monto ($) *</label>
                            <input type="number" step="0.01" min="0.01" name="monto" x-model="editForm.monto" required
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descripción *</label>
                        <input type="text" name="descripcion" x-model="editForm.descripcion" required maxlength="255"
                               class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Categoría *</label>
                            <select name="categoria" x-model="editForm.categoria" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                                @foreach ($categorias as $clave => $nombre)
                                    <option value="{{ $clave }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Método de pago *</label>
                            <select name="metodo_pago" x-model="editForm.metodo_pago" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notas (opcional)</label>
                        <textarea name="notas" x-model="editForm.notas" rows="2" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="openEditModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
