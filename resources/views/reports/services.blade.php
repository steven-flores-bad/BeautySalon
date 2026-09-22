<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Servicios - Salón de Belleza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <div class="min-h-screen flex flex-col">

        @include('components.navbar')

        <main class="flex-grow max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Encabezado y botones de descarga -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900 w-full">Reporte de Servicios</h1>

                <div class="flex gap-2 w-full sm:w-auto">
                    <a href="{{ route('reports.employees.pdf', ['periodo' => $periodo, 'fecha' => $fecha]) }}"
                       class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg shadow-sm transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Reporte de Empleados
                    </a>
                    <a href="{{ route('reports.services.pdf', ['periodo' => $periodo, 'fecha' => $fecha]) }}"
                       class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Descargar PDF
                    </a>
                </div>
            </div>

            <!-- Selector de Período y Fecha -->
            <form method="GET" action="{{ route('reports.services') }}" class="flex flex-wrap items-center gap-2 mb-2">
                <select name="periodo" onchange="this.form.submit()" class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    <option value="dia" {{ $periodo === 'dia' ? 'selected' : '' }}>Día</option>
                    <option value="semana" {{ $periodo === 'semana' ? 'selected' : '' }}>Semana</option>
                    <option value="mes" {{ $periodo === 'mes' ? 'selected' : '' }}>Mes</option>
                </select>

                <a href="{{ route('reports.services', ['periodo' => $periodo, 'fecha' => $fechaAnterior]) }}"
                   class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-500">
                    ‹
                </a>
                <input type="date" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()"
                       class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                <a href="{{ route('reports.services', ['periodo' => $periodo, 'fecha' => $fechaSiguiente]) }}"
                   class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-500">
                    ›
                </a>

                @if ($fecha !== \Carbon\Carbon::today()->toDateString())
                    <a href="{{ route('reports.services', ['periodo' => $periodo]) }}" class="text-xs text-pink-600 hover:underline whitespace-nowrap ml-1">Hoy</a>
                @endif
            </form>

            <p class="text-sm text-gray-400 mb-6">
                @if ($periodo === 'dia')
                    Mostrando resultados de: <span class="font-semibold text-gray-700">{{ $inicio->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                @else
                    Mostrando resultados de: <span class="font-semibold text-gray-700">{{ $inicio->translatedFormat('d/m/Y') }} al {{ $fin->translatedFormat('d/m/Y') }}</span>
                @endif
            </p>

            <!-- Tarjetas de resumen -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Total del Período</p>
                    <p class="text-2xl font-bold text-pink-600">${{ number_format($totalPeriodo, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1"># de Ventas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalVentas }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Ticket Promedio</p>
                    <p class="text-2xl font-bold text-gray-800">${{ number_format($ticketPromedio, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Comisiones Totales</p>
                    <p class="text-2xl font-bold text-indigo-600">${{ number_format($totalComisiones, 2) }}</p>
                </div>
            </div>

            <!-- Desglose Subtotal / Descuento -->
            @if($totalDescuentos > 0)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-8">
                    <div class="flex flex-wrap gap-6">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Subtotal (sin descuentos)</p>
                            <p class="text-lg font-bold text-gray-800">${{ number_format($totalSubtotal, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Descuentos Aplicados</p>
                            <p class="text-lg font-bold text-red-500">-${{ number_format($totalDescuentos, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Total Neto</p>
                            <p class="text-lg font-bold text-pink-600">${{ number_format($totalPeriodo, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Desglose por método de pago -->
            @if($porMetodoPago->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-8">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Desglose por Método de Pago</h2>
                    <div class="flex flex-wrap gap-6">
                        @foreach ($porMetodoPago as $metodo => $monto)
                            <div>
                                <p class="text-xs text-gray-400 capitalize">{{ $metodo }}</p>
                                <p class="text-lg font-bold text-gray-800">${{ number_format($monto, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- TABLA 1: Servicios Vendidos (cronológico) -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mb-8">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Servicios Vendidos</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">Servicio</th>
                                <th class="py-3 px-4 font-semibold">Categoría</th>
                                <th class="py-3 px-4 font-semibold">Atendió</th>
                                <th class="py-3 px-4 font-semibold text-center">Cant.</th>
                                <th class="py-3 px-4 font-semibold text-right">Precio</th>
                                <th class="py-3 px-4 font-semibold text-right">Descuento</th>
                                <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                                <th class="py-3 px-4 font-semibold text-right">Comisión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($serviciosVendidos as $fechaVenta => $detallesDelDia)
                                <tr class="bg-gray-50">
                                    <td colspan="8" class="py-2 px-4 text-xs font-bold text-gray-500 uppercase tracking-wide">
                                        {{ \Carbon\Carbon::parse($fechaVenta)->translatedFormat('l, d \d\e F') }}
                                    </td>
                                </tr>
                                @foreach ($detallesDelDia as $detalle)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 text-gray-900 font-semibold">{{ $detalle->service->nombre ?? 'Servicio eliminado' }}</td>
                                        <td class="py-3 px-4 text-gray-500">{{ $detalle->service->category->nombre ?? '—' }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $detalle->employee->nombre ?? '—' }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $detalle->cantidad }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right text-gray-600">${{ number_format($detalle->precio, 2) }}</td>
                                        <td class="py-3 px-4 text-right">
                                            @if($detalle->descuento > 0)
                                                <span class="text-red-500 font-medium">-${{ number_format($detalle->descuento, 2) }}</span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right text-pink-600 font-semibold">${{ number_format($detalle->subtotal, 2) }}</td>
                                        <td class="py-3 px-4 text-right text-indigo-600">${{ number_format($detalle->comision_monto, 2) }} <span class="text-xs text-gray-400">({{ number_format($detalle->comision_porcentaje, 0) }}%)</span></td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-10 text-gray-400">No se registraron servicios en este período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($serviciosVendidos->isNotEmpty())
                            <tfoot>
                                <tr class="bg-gray-50 border-t-2 border-gray-200 font-bold text-sm">
                                    <td colspan="6" class="py-3 px-4 text-right text-gray-700">Totales del período:</td>
                                    <td class="py-3 px-4 text-right text-pink-600">${{ number_format($totalPeriodo, 2) }}</td>
                                    <td class="py-3 px-4 text-right text-indigo-600">${{ number_format($totalComisiones, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- TABLA 2: Desglose por Empleado -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Servicios por Empleado</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">Servicio</th>
                                <th class="py-3 px-4 font-semibold">Fecha</th>
                                <th class="py-3 px-4 font-semibold text-center">Cant.</th>
                                <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                                <th class="py-3 px-4 font-semibold text-right">Comisión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($porEmpleado as $grupo)
                                <tr class="bg-indigo-50">
                                    <td colspan="5" class="py-2 px-4">
                                        <span class="text-sm font-bold text-indigo-900">{{ $grupo->empleado->nombre ?? 'Empleado eliminado' }}</span>
                                        <span class="text-xs text-indigo-500 ml-2">{{ $grupo->total_servicios }} servicio(s) — total comisión: ${{ number_format($grupo->total_comision, 2) }}</span>
                                    </td>
                                </tr>
                                @foreach ($grupo->detalles as $detalle)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 text-gray-900 font-semibold">{{ $detalle->service->nombre ?? 'Servicio eliminado' }}</td>
                                        <td class="py-3 px-4 text-gray-500">{{ \Carbon\Carbon::parse($detalle->fecha_venta)->translatedFormat('d/m/Y') }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $detalle->cantidad }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right text-pink-600 font-semibold">${{ number_format($detalle->subtotal, 2) }}</td>
                                        <td class="py-3 px-4 text-right text-indigo-600">${{ number_format($detalle->comision_monto, 2) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-400">No hay comisiones registradas en este período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
