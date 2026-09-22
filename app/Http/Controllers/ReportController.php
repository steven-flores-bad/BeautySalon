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
     * Genera el mismo reporte, pero como PDF descargable.
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
     * Genera el reporte de servicios como PDF descargable.
     */
    public function servicesPdf(Request $request)
    {
        $datos = $this->obtenerDatosReporteServicios($request);

        $pdf = Pdf::loadView('reports.services_pdf', $datos)->setPaper('letter', 'portrait');

        $nombreArchivo = 'reporte-servicios-' . $datos['inicio']->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Genera el reporte de comisiones por empleado como PDF descargable.
     * Usa los mismos datos y el mismo período que el reporte de servicios,
     * para que ambos reportes siempre coincidan entre sí.
     */
    public function employeesPdf(Request $request)
    {
        $datos = $this->obtenerDatosReporteServicios($request);

        $pdf = Pdf::loadView('reports.employees_pdf', $datos)->setPaper('letter', 'portrait');

        $nombreArchivo = 'reporte-empleados-' . $datos['inicio']->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Calcula el rango de fechas (inicio/fin) y la navegación anterior/siguiente
     * según el período elegido. Lo comparten el reporte de ventas y de servicios.
     */
    private function calcularRangoPeriodo(string $periodo, string $fecha): array
    {
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

        return [$inicio, $fin, $fechaAnterior, $fechaSiguiente];
    }

    /**
     * Lógica compartida: calcula el rango de fechas según el período elegido
     * y arma todos los datos del reporte. La reutilizan tanto la vista en
     * pantalla como la generación del PDF, para que ambas siempre coincidan.
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
     * Arma todos los datos del reporte de servicios: totales generales,
     * servicios vendidos día por día, y desglose de comisiones por empleado.
     * La reutilizan la vista en pantalla, el PDF de servicios y el PDF de
     * empleados, para que los tres siempre muestren los mismos números.
     */
    private function obtenerDatosReporteServicios(Request $request): array
    {
        $periodo = in_array($request->input('periodo'), ['dia', 'semana', 'mes'])
                    ? $request->input('periodo')
                    : 'dia';

        $fecha = $request->input('fecha', Carbon::today()->toDateString());

        [$inicio, $fin, $fechaAnterior, $fechaSiguiente] = $this->calcularRangoPeriodo($periodo, $fecha);

        // Solo ventas de servicios completadas (las canceladas no cuentan).
        $ventasDelPeriodo = ServiceSale::whereBetween('created_at', [$inicio, $fin])
                                ->where('estado', 'completada')
                                ->get();

        $totalPeriodo = $ventasDelPeriodo->sum('total');
        $totalSubtotal = $ventasDelPeriodo->sum('subtotal');
        $totalDescuentos = $ventasDelPeriodo->sum('descuento');
        $totalVentas = $ventasDelPeriodo->count();
        $ticketPromedio = $totalVentas > 0 ? $totalPeriodo / $totalVentas : 0;

        // Una sola consulta con todas las líneas de servicio del período,
        // que luego se reutiliza para armar AMBAS tablas (por fecha y por
        // empleado) sin repetir la consulta a la base de datos.
        $detallesPeriodo = ServiceSaleDetail::select(
                                'service_sale_details.*',
                                DB::raw('DATE(service_sales.created_at) as fecha_venta'),
                                'service_sales.id as venta_id',
                                'service_sales.cliente_nombre as cliente_nombre'
                            )
                            ->join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                            ->where('service_sales.estado', 'completada')
                            ->whereBetween('service_sales.created_at', [$inicio, $fin])
                            ->with(['service.category', 'employee'])
                            ->get();

        $totalComisiones = $detallesPeriodo->sum('comision_monto');

        // Tabla 1: servicios vendidos, agrupados por día (igual que productos).
        $serviciosVendidos = $detallesPeriodo->groupBy('fecha_venta')
                                ->map(function ($grupo) {
                                    return $grupo->sortByDesc('subtotal')->values();
                                })
                                ->sortKeys();

        // Tabla 2: desglose por empleado — qué atendió cada una y cuánto generó.
        $porEmpleado = $detallesPeriodo->groupBy('employee_id')
                            ->map(function ($grupo) {
                                return (object) [
                                    'empleado' => $grupo->first()->employee,
                                    'detalles' => $grupo->sortBy('fecha_venta')->values(),
                                    'total_servicios' => $grupo->sum('cantidad'),
                                    'total_subtotal' => $grupo->sum('subtotal'),
                                    'total_comision' => $grupo->sum('comision_monto'),
                                ];
                            })
                            ->sortByDesc('total_comision')
                            ->values();

        $porMetodoPago = $ventasDelPeriodo->groupBy('metodo_pago')->map(function ($grupo) {
            return $grupo->sum('total');
        });

        return [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'inicio' => $inicio,
            'fin' => $fin,
            'fechaAnterior' => $fechaAnterior,
            'fechaSiguiente' => $fechaSiguiente,
            'totalPeriodo' => $totalPeriodo,
            'totalSubtotal' => $totalSubtotal,
            'totalDescuentos' => $totalDescuentos,
            'totalVentas' => $totalVentas,
            'ticketPromedio' => $ticketPromedio,
            'totalComisiones' => $totalComisiones,
            'serviciosVendidos' => $serviciosVendidos,
            'porEmpleado' => $porEmpleado,
            'porMetodoPago' => $porMetodoPago,
        ];
    }
}