<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Servicios - BeautyControl</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #db2777;
            padding-bottom: 8px;
        }
        .header h1 {
            color: #db2777;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 4px 0 0;
            color: #666;
            font-size: 10px;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-box td {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: center;
            width: 25%;
        }
        .summary-box .title {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
            margin-bottom: 2px;
        }
        .summary-box .value {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
        }
        h2 {
            font-size: 12px;
            color: #1f2937;
            margin-top: 15px;
            margin-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #4b5563;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            padding: 5px 6px;
            border-bottom: 1px solid #d1d5db;
        }
        table.data-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .date-header {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 10px;
            color: #374151;
        }
        .employee-header {
            background-color: #fdf2f8;
            color: #be185d;
            font-weight: bold;
        }
        .subtotal-row {
            background-color: #f9fafb;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>BeautyControl - Reporte de Servicios</h1>
        <p>Período del <strong>{{ $inicio->translatedFormat('d/m/Y') }}</strong> al <strong>{{ $fin->translatedFormat('d/m/Y') }}</strong></p>
    </div>

    <!-- Tarjetas de Resumen -->
    <table class="summary-box">
        <tr>
            <td>
                <span class="title">Total Período</span>
                <span class="value" style="color: #db2777;">${{ number_format($totalPeriodo, 2) }}</span>
            </td>
            <td>
                <span class="title">Transacciones</span>
                <span class="value">{{ $totalVentas }}</span>
            </td>
            <td>
                <span class="title">Ticket Promedio</span>
                <span class="value" style="color: #4f46e5;">${{ number_format($ticketPromedio, 2) }}</span>
            </td>
            <td>
                <span class="title">Descuentos</span>
                <span class="value" style="color: #dc2626;">-${{ number_format($totalDescuentos, 2) }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabla 1: Detalle General de Servicios Realizados -->
    <h2>Detalle General de Servicios Realizados</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Atendido por</th>
                <th class="text-center">Cant.</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Descuento</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detallesVentas as $fechaVenta => $detallesDelDia)
                <tr class="date-header">
                    <td colspan="6">📅 {{ \Carbon\Carbon::parse($fechaVenta)->translatedFormat('l, d \d\e F \d\e Y') }}</td>
                </tr>
                @foreach ($detallesDelDia as $detalle)
                    <tr>
                        <td>{{ optional($detalle->service)->nombre ?? 'Servicio eliminado' }}</td>
                        <td>{{ optional($detalle->employee)->nombre ?? '—' }}</td>
                        <td class="text-center font-bold">{{ $detalle->cantidad }}</td>
                        <td class="text-right">${{ number_format($detalle->precio, 2) }}</td>
                        <td class="text-right" style="color: #dc2626;">
                            {{ $detalle->descuento > 0 ? '-$' . number_format($detalle->descuento, 2) : '—' }}
                        </td>
                        <td class="text-right font-bold">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #9ca3af;">No se registraron servicios en este período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tabla 2: Rendimiento y Comisiones por Empleado -->
    <h2>Desglose de Rendimiento y Comisiones por Empleado</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Empleado / Servicios Atendidos</th>
                <th class="text-center">Cant.</th>
                <th class="text-right">Comisión Acumulada</th>
                <th class="text-right">Total Producido</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($porEmpleado as $employeeId => $itemsEmpleado)
                @php
                    $empleadoNombre = optional($itemsEmpleado->first()->employee)->nombre ?? 'Personal general';
                    $totalProdEmpleado = $itemsEmpleado->sum('subtotal');
                    $totalComisionEmpleado = $itemsEmpleado->sum('comision_monto');
                    $totalCantServicios = $itemsEmpleado->sum('cantidad');
                @endphp
                <tr class="employee-header">
                    <td colspan="4">👤 {{ $empleadoNombre }}</td>
                </tr>
                @foreach($itemsEmpleado as $linea)
                    <tr>
                        <td style="padding-left: 12px; color: #4b5563;">
                            • {{ optional($linea->service)->nombre ?? 'Servicio' }} <span style="font-size: 9px; color: #9ca3af;">(Cant: {{ $linea->cantidad }})</span>
                        </td>
                        <td class="text-center font-bold">{{ $linea->cantidad }}</td>
                        <td class="text-right" style="color: #4f46e5;">
                            ${{ number_format($linea->comision_monto, 2) }} ({{ $linea->comision_porcentaje }}%)
                        </td>
                        <td class="text-right">${{ number_format($linea->subtotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td class="text-right">Subtotal Empleado:</td>
                    <td class="text-center">{{ $totalCantServicios }}</td>
                    <td class="text-right" style="color: #4f46e5;">${{ number_format($totalComisionEmpleado, 2) }}</td>
                    <td class="text-right">${{ number_format($totalProdEmpleado, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 15px; color: #9ca3af;">Sin actividad de empleados en este período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>