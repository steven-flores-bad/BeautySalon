<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
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
}