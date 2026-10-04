@extends('layouts.app')

@section('title', 'Citas - JulySalon')

@section('content')
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Citas</h1>
            <p class="text-sm text-gray-400">Citas que los clientes reservan desde la página web.</p>
        </div>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <a href="{{ route('citas.index', ['vista' => 'proximas', 'estado' => 'pendiente']) }}" class="bg-amber-50 rounded-xl border border-amber-200 shadow-sm p-5 hover:shadow transition">
            <p class="text-xs text-amber-600 uppercase tracking-wide font-semibold mb-1">Pendientes de confirmar</p>
            <p class="text-2xl font-bold text-amber-700">{{ $resumen['pendientes'] }}</p>
        </a>
        <a href="{{ route('citas.index', ['vista' => 'hoy']) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow transition">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Citas de hoy por atender</p>
            <p class="text-2xl font-bold text-gray-800">{{ $resumen['hoy'] }}</p>
        </a>
    </div>

    <!-- Vistas y filtros -->
    <form method="GET" action="{{ route('citas.index') }}" class="flex flex-wrap items-center gap-2 mb-6">
        <div class="flex rounded-lg border border-gray-300 bg-white overflow-hidden shadow-sm text-sm">
            @foreach (['proximas' => 'Próximas', 'hoy' => 'Hoy', 'anteriores' => 'Anteriores'] as $clave => $texto)
                <a href="{{ route('citas.index', array_merge(request()->except(['page', 'vista']), ['vista' => $clave])) }}"
                   class="px-4 py-2 {{ $vista === $clave ? 'bg-pink-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-50' }}">{{ $texto }}</a>
            @endforeach
        </div>
        <input type="hidden" name="vista" value="{{ $vista }}">

        <select name="estado" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
            <option value="">Todos los estados</option>
            @foreach (\App\Models\Cita::ESTADOS as $clave => $texto)
                <option value="{{ $clave }}" @selected($estado === $clave)>{{ $texto }}</option>
            @endforeach
        </select>

        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por cliente, teléfono o servicio..."
               class="flex-1 min-w-[200px] px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">

        <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Buscar</button>

        @if($estado || $search)
            <a href="{{ route('citas.index', ['vista' => $vista]) }}" class="text-xs text-pink-600 hover:underline whitespace-nowrap">Quitar filtros</a>
        @endif
    </form>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 font-semibold">Fecha y hora</th>
                        <th class="py-3 px-4 font-semibold">Cliente</th>
                        <th class="py-3 px-4 font-semibold">Servicio</th>
                        <th class="py-3 px-4 font-semibold text-center">Estado</th>
                        <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse ($citas as $cita)
                        @php
                            $colores = [
                                'pendiente' => 'bg-amber-100 text-amber-700',
                                'confirmada' => 'bg-blue-100 text-blue-700',
                                'atendida' => 'bg-emerald-100 text-emerald-700',
                                'cancelada' => 'bg-gray-200 text-gray-600',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition {{ $cita->estado === 'cancelada' ? 'opacity-60' : '' }}">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900">{{ $cita->fecha->format('d/m/Y') }}</span>
                                <span class="block text-xs text-gray-500">{{ ucfirst($cita->fecha->translatedFormat('l')) }} · {{ $cita->horaTexto() }}</span>
                                @if($cita->fecha->isToday())
                                    <span class="inline-block mt-1 text-[10px] font-bold uppercase bg-pink-100 text-pink-700 px-1.5 py-0.5 rounded">Hoy</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-gray-900 font-medium">{{ $cita->cliente_nombre }}</span>
                                <span class="block text-xs text-gray-500">{{ $cita->telefono }}@if($cita->email) · {{ $cita->email }}@endif</span>
                                @if($cita->notas)
                                    <span class="block text-xs text-gray-400 mt-0.5">"{{ \Illuminate\Support\Str::limit($cita->notas, 80) }}"</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-700">
                                {{ $cita->servicio }}
                                @if($cita->service)
                                    <span class="block text-xs text-gray-400">${{ number_format($cita->service->precio, 2) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $colores[$cita->estado] ?? 'bg-gray-100 text-gray-700' }}">{{ $cita->nombreEstado() }}</span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                @foreach ([
                                    'confirmada' => ['Confirmar', 'text-blue-700 bg-blue-50 hover:text-blue-900', ['pendiente']],
                                    'atendida' => ['Atendida', 'text-emerald-700 bg-emerald-50 hover:text-emerald-900', ['pendiente', 'confirmada']],
                                    'cancelada' => ['Cancelar', 'text-red-600 bg-red-50 hover:text-red-900', ['pendiente', 'confirmada']],
                                    'pendiente' => ['Reabrir', 'text-gray-600 bg-gray-100 hover:text-gray-900', ['cancelada', 'atendida']],
                                ] as $nuevo => [$texto, $clases, $desde])
                                    @if (in_array($cita->estado, $desde))
                                        <form action="{{ route('citas.estado', $cita) }}" method="POST" class="inline-block"
                                              @if($nuevo === 'cancelada') @submit.prevent="$dispatch('confirm-action', { form: $el, type: 'cancel', title: 'Cancelar cita', question: '¿Cancelar la cita de', name: @js($cita->cliente_nombre), message: 'Podrás reabrirla si fue un error.', confirmText: 'Sí, cancelar cita' })" @endif>
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="{{ $nuevo }}">
                                            <button type="submit" class="font-medium text-xs px-2.5 py-1 rounded-md transition {{ $clases }}">{{ $texto }}</button>
                                        </form>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400">No hay citas en esta vista.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $citas->links() }}
        </div>
    </div>
@endsection
