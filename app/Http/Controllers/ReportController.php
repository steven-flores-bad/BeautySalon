<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\ServiceSale;
use App\Models\ServiceSaleDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Muestra el reporte de ventas en pantalla.
     */
    public function sales(Request $request)
    {
        $datos = $this->obtenerDatosReporte($request);

        return view('reports.sales', $datos);
    }

    /**
     * Genera el mismo reporte de ventas, pero como PDF descargable.
     */
    public function salesPdf(Request $request)
    {
        $datos = $this->obtenerDatosReporte($request);

        $pdf = Pdf::loadView('reports.sales_pdf', $datos)->setPaper('letter', 'portrait');

        $nombreArchivo = 'reporte-ventas-' . $datos['inicio']->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Muestra el reporte de servicios en pantalla.
     */
    public function services(Request $request)
    {
        $datos = $this->obtenerDatosReporteServicios($request);

        return view('reports.services', $datos);
    }

    /**
     * Genera el mismo reporte de servicios, pero como PDF descargable.
     */
    public function servicesPdf(Request $request)
    {
        $datos = $this->obtenerDatosReporteServicios($request);

        $pdf = Pdf::loadView('reports.services_pdf', $datos)->setPaper('letter', 'portrait');

        $nombreArchivo = 'reporte-servicios-' . $datos['inicio']->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Lógica compartida: calcula el rango de fechas según el período elegido
     * y arma todos los datos del reporte de ventas de productos.
     */
    private function obtenerDatosReporte(Request $request): array
    {
        $periodo = in_array($request->input('periodo'), ['dia', 'semana', 'mes'])
                    ? $request->input('periodo')
                    : 'dia';

        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $fechaAncla = Carbon::parse($fecha);

        switch ($periodo) {
            case 'semana':
                $inicio = $fechaAncla->copy()->startOfWeek(Carbon::MONDAY);
                $fin = $fechaAncla->copy()->endOfWeek(Carbon::SUNDAY);
                $fechaAnterior = $inicio->copy()->subWeek()->toDateString();
                $fechaSiguiente = $inicio->copy()->addWeek()->toDateString();
                break;

            case 'mes':
                $inicio = $fechaAncla->copy()->startOfMonth();
                $fin = $fechaAncla->copy()->endOfMonth();
                $fechaAnterior = $inicio->copy()->subMonth()->toDateString();
                $fechaSiguiente = $inicio->copy()->addMonth()->toDateString();
                break;

            default: // dia
                $inicio = $fechaAncla->copy()->startOfDay();
                $fin = $fechaAncla->copy()->endOfDay();
                $fechaAnterior = $inicio->copy()->subDay()->toDateString();
                $fechaSiguiente = $inicio->copy()->addDay()->toDateString();
                break;
        }

        // Solo ventas completadas (las canceladas no cuentan como ingreso real).
        $ventasDelPeriodo = Sale::whereBetween('created_at', [$inicio, $fin])
                                ->where('estado', 'completada')
                                ->get();

        $totalPeriodo = $ventasDelPeriodo->sum('total');
        $totalSubtotal = $ventasDelPeriodo->sum('subtotal');
        $totalDescuentos = $ventasDelPeriodo->sum('descuento');
        $totalVentas = $ventasDelPeriodo->count();
        $ticketPromedio = $totalVentas > 0 ? $totalPeriodo / $totalVentas : 0;

        $productosVendidos = SaleDetail::select(
                                'sale_details.*',
                                DB::raw('DATE(sales.created_at) as fecha_venta'),
                                'sales.id as venta_id'
                            )
                            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
                            ->where('sales.estado', 'completada')
                            ->whereBetween('sales.created_at', [$inicio, $fin])
                            ->with('product')
                            ->get()
                            ->groupBy('fecha_venta')
                            ->map(function ($grupo) {
                                return $grupo->sortByDesc('subtotal')->values();
                            })
                            ->sortKeys();

        $porMetodoPago = $ventasDelPeriodo->groupBy('metodo_pago')->map(function ($grupo) {
            return $grupo->sum('total');
        });

        return [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'fechaInput' => $fecha,
            'inicio' => $inicio,
            'fin' => $fin,
            'fechaAnterior' => $fechaAnterior,
            'fechaSiguiente' => $fechaSiguiente,
            'ventasDelPeriodo' => $ventasDelPeriodo->sortBy('created_at')->values(),
            'totalPeriodo' => $totalPeriodo,
            'totalSubtotal' => $totalSubtotal,
            'totalDescuentos' => $totalDescuentos,
            'totalVentas' => $totalVentas,
            'ticketPromedio' => $ticketPromedio,
            'productosVendidos' => $productosVendidos,
            'porMetodoPago' => $porMetodoPago,
        ];
    }

    /**
     * Lógica compartida: calcula el rango de fechas y agrupa los detalles de servicios
     * y el rendimiento por empleado para el reporte de servicios.
     */
    private function obtenerDatosReporteServicios(Request $request): array
    {
        $periodo = in_array($request->input('periodo'), ['dia', 'semana', 'mes'])
                    ? $request->input('periodo')
                    : 'mes';

        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $fechaAncla = Carbon::parse($fecha);

        switch ($periodo) {
            case 'semana':
                $inicio = $fechaAncla->copy()->startOfWeek(Carbon::MONDAY);
                $fin = $fechaAncla->copy()->endOfWeek(Carbon::SUNDAY);
                $fechaAnterior = $inicio->copy()->subWeek()->toDateString();
                $fechaSiguiente = $inicio->copy()->addWeek()->toDateString();
                break;

            case 'mes':
                $inicio = $fechaAncla->copy()->startOfMonth();
                $fin = $fechaAncla->copy()->endOfMonth();
                $fechaAnterior = $inicio->copy()->subMonth()->toDateString();
                $fechaSiguiente = $inicio->copy()->addMonth()->toDateString();
                break;

            default: // dia
                $inicio = $fechaAncla->copy()->startOfDay();
                $fin = $fechaAncla->copy()->endOfDay();
                $fechaAnterior = $inicio->copy()->subDay()->toDateString();
                $fechaSiguiente = $inicio->copy()->addDay()->toDateString();
                break;
        }

        // Obtener ventas de servicios completadas en el rango
        $ventasServicios = ServiceSale::whereBetween('created_at', [$inicio, $fin])
                                    ->where('estado', 'completada')
                                    ->get();

        $totalPeriodo = $ventasServicios->sum('total');
        $totalSubtotal = $ventasServicios->sum('subtotal');
        $totalDescuentos = $ventasServicios->sum('descuento');
        $totalVentas = $ventasServicios->count();
        $ticketPromedio = $totalVentas > 0 ? $totalPeriodo / $totalVentas : 0;

        // Desglose por método de pago
        $porMetodoPago = $ventasServicios->groupBy('metodo_pago')->map(function ($grupo) {
            return $grupo->sum('total');
        });

        // Detalles de servicios agrupados por fecha (para la primera tabla)
        $detallesVentas = ServiceSaleDetail::select(
                                'service_sale_details.*',
                                DB::raw('DATE(service_sales.created_at) as fecha_venta'),
                                'service_sales.id as service_sale_id'
                            )
                            ->join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                            ->where('service_sales.estado', 'completada')
                            ->whereBetween('service_sales.created_at', [$inicio, $fin])
                            ->with(['service', 'employee'])
                            ->get()
                            ->groupBy('fecha_venta')
                            ->map(function ($grupo) {
                                return $grupo->sortByDesc('subtotal')->values();
                            })
                            ->sortKeys();

        // Desglose agrupado por empleado (para la segunda tabla y pdf)
        // $porEmpleado = ServiceSaleDetail::select(
        //                         'service_sale_details.employee_id',
        //                         DB::raw('SUM(service_sale_details.cantidad) as total_cantidad'),
        //                         DB::raw('SUM(service_sale_details.subtotal) as total_producido'),
        //                         DB::raw('SUM(service_sale_details.comision_monto) as total_comision')
        //                     )
        //                     ->join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
        //                     ->where('service_sales.estado', 'completada')
        //                     ->whereBetween('service_sales.created_at', [$inicio, $fin])
        //                     ->with('employee')
        //                     ->groupBy('service_sale_details.employee_id')
        //                     ->get();
        // Desglose agrupado por empleado manteniendo las líneas individuales (mañana, tarde, etc.)
        $porEmpleado = ServiceSaleDetail::join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                            ->where('service_sales.estado', 'completada')
                            ->whereBetween('service_sales.created_at', [$inicio, $fin])
                            ->with(['employee', 'service'])
                            ->get()
                            ->groupBy('employee_id');

        return [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'fechaInput' => $fecha,
            'inicio' => $inicio,
            'fin' => $fin,
            'fechaAnterior' => $fechaAnterior,
            'fechaSiguiente' => $fechaSiguiente,
            'totalPeriodo' => $totalPeriodo,
            'totalSubtotal' => $totalSubtotal,
            'totalDescuentos' => $totalDescuentos,
            'totalVentas' => $totalVentas,
            'ticketPromedio' => $ticketPromedio,
            'porMetodoPago' => $porMetodoPago,
            'detallesVentas' => $detallesVentas,
            'porEmpleado' => $porEmpleado,
        ];
    }
}