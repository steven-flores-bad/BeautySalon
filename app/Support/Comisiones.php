<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\SaleDetail;
use App\Models\ServiceSaleDetail;
use Illuminate\Support\Collection;

/**
 * Comisiones de las empleadas por ventas de servicios y de productos
 * (solo ventas completadas). Las usan la caja, la pantalla de Gastos y
 * los reportes, para que todos calculen lo mismo.
 */
class Comisiones
{
    /**
     * Total de comisiones de las ventas registradas en una caja.
     */
    public static function deCaja(int $cajaId): float
    {
        $servicios = ServiceSaleDetail::join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                        ->where('service_sales.cash_register_id', $cajaId)
                        ->where('service_sales.estado', 'completada')
                        ->sum('service_sale_details.comision_monto');

        $productos = SaleDetail::join('sales', 'sales.id', '=', 'sale_details.sale_id')
                        ->where('sales.cash_register_id', $cajaId)
                        ->where('sales.estado', 'completada')
                        ->sum('sale_details.comision_monto');

        return (float) $servicios + (float) $productos;
    }

    /**
     * Comisiones del período por empleada, separando servicios y productos.
     *
     * @return Collection<int, object{nombre: string, servicios: int, productos: int, monto_servicios: float, monto_productos: float, monto: float}>
     */
    public static function porEmpleada($inicio, $fin): Collection
    {
        $servicios = ServiceSaleDetail::join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                        ->where('service_sales.estado', 'completada')
                        ->whereBetween('service_sales.created_at', [$inicio, $fin])
                        ->where('service_sale_details.comision_monto', '>', 0)
                        ->groupBy('service_sale_details.employee_id')
                        ->selectRaw('service_sale_details.employee_id, SUM(service_sale_details.cantidad) as cantidad, SUM(service_sale_details.comision_monto) as monto')
                        ->get()
                        ->keyBy('employee_id');

        $productos = SaleDetail::join('sales', 'sales.id', '=', 'sale_details.sale_id')
                        ->where('sales.estado', 'completada')
                        ->whereBetween('sales.created_at', [$inicio, $fin])
                        ->whereNotNull('sale_details.employee_id')
                        ->where('sale_details.comision_monto', '>', 0)
                        ->groupBy('sale_details.employee_id')
                        ->selectRaw('sale_details.employee_id, SUM(sale_details.cantidad) as cantidad, SUM(sale_details.comision_monto) as monto')
                        ->get()
                        ->keyBy('employee_id');

        $ids = $servicios->keys()->merge($productos->keys())->unique();
        $nombres = Employee::whereIn('id', $ids)->pluck('nombre', 'id');

        return $ids->map(function ($id) use ($servicios, $productos, $nombres) {
                    $montoServicios = (float) ($servicios[$id]->monto ?? 0);
                    $montoProductos = (float) ($productos[$id]->monto ?? 0);

                    return (object) [
                        'nombre' => $nombres[$id] ?? 'Empleado eliminado',
                        'servicios' => (int) ($servicios[$id]->cantidad ?? 0),
                        'productos' => (int) ($productos[$id]->cantidad ?? 0),
                        'monto_servicios' => $montoServicios,
                        'monto_productos' => $montoProductos,
                        'monto' => $montoServicios + $montoProductos,
                    ];
                })
                ->sortByDesc('monto')
                ->values();
    }

    /**
     * Total de comisiones (servicios + productos) del período.
     */
    public static function total($inicio, $fin): float
    {
        return (float) self::porEmpleada($inicio, $fin)->sum('monto');
    }
}
