<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Servicios - BeautyControl</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <div class="min-h-screen flex flex-col">

        @include('components.navbar')

        <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Cabecera y Filtros -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <span>📊</span> Reporte de Servicios
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Mostrando registros 
                        <span class="font-semibold text-gray-700">{{ $inicio->translatedFormat('d/m/Y') }}</span> al 
                        <span class="font-semibold text-gray-700">{{ $fin->translatedFormat('d/m/Y') }}</span>
                    </p>
                </div>

                <!-- Formulario de Filtros -->
                <form method="GET" action="{{ route('reports.services') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <div>
                        <select name="periodo" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <option value="mes" {{ $periodo === 'mes' ? 'selected' : '' }}>Por Mes</option>
                            <option value="semana" {{ $periodo === 'semana' ? 'selected' : '' }}>Por Semana</option>
                            <option value="dia" {{ $periodo === 'dia' ? 'selected' : '' }}>Por Día</option>
                        </select>
                    </div>

                    <div>
                        <input type="date" name="fecha" value="{{ $fechaInput }}" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <a href="{{ route('reports.services.pdf', ['periodo' => $periodo, 'fecha' => $fechaInput]) }}" target="_blank" class="bg-pink-600 hover:bg-pink-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Exportar PDF
                    </a>
                </form>
            </div>

            <!-- Tarjetas de Resumen Principal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold tracking-wider text-gray-400 block mb-1">Total del Período</span>
                    <span class="text-2xl font-extrabold text-pink-600">${{ number_format($totalPeriodo, 2) }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold tracking-wider text-gray-400 block mb-1">Total de Transacciones</span>
                    <span class="text-2xl font-extrabold text-gray-900">{{ $totalVentas }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold tracking-wider text-gray-400 block mb-1">Ticket Promedio</span>
                    <span class="text-2xl font-extrabold text-indigo-600">${{ number_format($ticketPromedio, 2) }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold tracking-wider text-gray-400 block mb-1">Descuentos Aplicados</span>
                    <span class="text-2xl font-extrabold text-red-500">-${{ number_format($totalDescuentos, 2) }}</span>
                </div>
            </div>

            <!-- Métodos de Pago -->
            @if($porMetodoPago->isNotEmpty())
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-8 flex flex-wrap gap-8 items-center">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ingresos por Método de Pago:</span>
                    @foreach($porMetodoPago as $metodo => $monto)
                        <div class="flex items-center gap-2">
                            <span class="capitalize text-sm text-gray-600 font-medium">{{ $metodo }}:</span>
                            <span class="text-sm font-bold text-gray-900">${{ number_format($monto, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Tabla 1: Detalle General de Servicios Realizados -->
            <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden mb-10">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <h2 class="font-bold text-gray-900 text-base">Detalle General de Servicios Realizados</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">Servicio</th>
                                <th class="py-3 px-4 font-semibold">Atendido por</th>
                                <th class="py-3 px-4 font-semibold text-center">Cant.</th>
                                <th class="py-3 px-4 font-semibold text-right">Precio</th>
                                <th class="py-3 px-4 font-semibold text-right">Descuento</th>
                                <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($detallesVentas as $fechaVenta => $detallesDelDia)
                                <tr class="bg-gray-100/80">
                                    <td colspan="6" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider text-gray-600">
                                        📅 {{ \Carbon\Carbon::parse($fechaVenta)->translatedFormat('l, d \d\e F \d\e Y') }}
                                    </td>
                                </tr>
                                @foreach ($detallesDelDia as $detalle)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 text-gray-900 font-medium">{{ optional($detalle->service)->nombre ?? 'Servicio eliminado' }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ optional($detalle->employee)->nombre ?? '—' }}</td>
                                        <td class="py-3 px-4 text-center text-gray-600 font-semibold">{{ $detalle->cantidad }}</td>
                                        <td class="py-3 px-4 text-right text-gray-600">${{ number_format($detalle->precio, 2) }}</td>
                                        <td class="py-3 px-4 text-right text-red-500">
                                            @if($detalle->descuento > 0)
                                                -${{ number_format($detalle->descuento, 2) }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-semibold text-gray-900">${{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">No se registraron servicios en este período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($detallesVentas->isNotEmpty())
                            <tfoot>
                                <tr class="bg-gray-50 font-bold border-t-2 border-gray-200 text-gray-900">
                                    <td colspan="5" class="py-3 px-4 text-right">Total del período:</td>
                                    <td class="py-3 px-4 text-right text-pink-600">${{ number_format($totalPeriodo, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Tabla 2: Desglose de Rendimiento por Empleado -->
            <!-- Tabla 2: Desglose de Rendimiento por Empleado -->
            <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <h2 class="font-bold text-gray-900 text-base">Desglose de Rendimiento y Comisiones por Empleado</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">Empleado / Servicios Atendidos</th>
                                <th class="py-3 px-4 font-semibold text-center">Cant. Servicios</th>
                                <th class="py-3 px-4 font-semibold text-right">Comisión Acumulada</th>
                                <th class="py-3 px-4 font-semibold text-right">Total Producido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($porEmpleado as $employeeId => $itemsEmpleado)
                                @php
                                    $empleadoNombre = optional($itemsEmpleado->first()->employee)->nombre ?? 'Personal general';
                                    $totalProdEmpleado = $itemsEmpleado->sum('subtotal');
                                    $totalComisionEmpleado = $itemsEmpleado->sum('comision_monto');
                                    $totalCantServicios = $itemsEmpleado->sum('cantidad');
                                @endphp
                                <tr class="bg-pink-50/40">
                                    <td colspan="4" class="py-3 px-4 font-bold text-pink-700">
                                        👤 {{ $empleadoNombre }}
                                    </td>
                                </tr>
                                @foreach($itemsEmpleado as $linea)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-2.5 px-4 pl-8 text-gray-600">
                                            • {{ optional($linea->service)->nombre ?? 'Servicio' }} <span class="text-xs text-gray-400">(Cant: {{ $linea->cantidad }})</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center text-gray-600 font-semibold">{{ $linea->cantidad }}</td>
                                        <td class="py-2.5 px-4 text-right text-indigo-600 font-medium">
                                            ${{ number_format($linea->comision_monto, 2) }} <span class="text-xs text-gray-400">({{ $linea->comision_porcentaje }}%)</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right text-gray-800">${{ number_format($linea->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50/80 font-semibold border-b border-gray-200 text-gray-800 text-xs">
                                    <td class="py-2.5 px-4 text-right">Subtotal Empleado:</td>
                                    <td class="py-2.5 px-4 text-center text-gray-900">{{ $totalCantServicios }}</td>
                                    <td class="py-2.5 px-4 text-right text-indigo-700">${{ number_format($totalComisionEmpleado, 2) }}</td>
                                    <td class="py-2.5 px-4 text-right text-gray-900">${{ number_format($totalProdEmpleado, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-gray-400">Sin actividad de empleados en este período.</td>
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