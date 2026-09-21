<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use App\Models\ServiceSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceSaleController extends Controller
{
    /**
     * Listado de ventas de servicios con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $serviceSales = ServiceSale::with(['user', 'details.service', 'details.employee'])
                            ->withCount('details')
                            ->when($search, function ($query, $search) {
                                return $query->where('cliente_nombre', 'like', "%{$search}%")
                                             ->orWhere('id', $search);
                            })
                            ->latest()
                            ->paginate(10)
                            ->withQueryString();

        // Servicios (con categoría) y empleados activos, para armar el formulario.
        $services = Service::with('category')->orderBy('nombre')->get();
        $employees = Employee::where('activo', true)->orderBy('nombre')->get();

        return view('service_sales.index', compact('serviceSales', 'search', 'services', 'employees'));
    }

    /**
     * Registrar una nueva venta de servicios con una o varias líneas.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_nombre' => 'nullable|string|max:150',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'notas' => 'nullable|string',
            'servicios' => 'required|array|min:1',
            'servicios.*.service_id' => 'required|exists:services,id',
            'servicios.*.employee_id' => 'required|exists:employees,id',
            'servicios.*.cantidad' => 'required|integer|min:1',
            'servicios.*.descuento' => 'nullable|numeric|min:0',
            'servicios.*.comision_porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        $serviceSale = DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $totalDescuento = 0;
            $lineas = [];

            foreach ($validated['servicios'] as $item) {
                $service = Service::findOrFail($item['service_id']);

                $descuentoLinea = $item['descuento'] ?? 0;
                $bruto = $service->precio * $item['cantidad'];

                if ($descuentoLinea > $bruto) {
                    throw new \Exception("El descuento de \"{$service->nombre}\" no puede ser mayor al subtotal de esa línea.");
                }

                $subtotalLinea = $bruto - $descuentoLinea;
                $comisionMonto = $subtotalLinea * ($item['comision_porcentaje'] / 100);

                $subtotal += $bruto;
                $totalDescuento += $descuentoLinea;

                $lineas[] = [
                    'service_id' => $service->id,
                    'employee_id' => $item['employee_id'],
                    'cantidad' => $item['cantidad'],
                    'precio' => $service->precio,
                    'descuento' => $descuentoLinea,
                    'subtotal' => $subtotalLinea,
                    'comision_porcentaje' => $item['comision_porcentaje'],
                    'comision_monto' => $comisionMonto,
                ];
            }

            $total = $subtotal - $totalDescuento;

            $serviceSale = ServiceSale::create([
                'user_id' => auth()->id(),
                'cliente_nombre' => $validated['cliente_nombre'] ?? null,
                'metodo_pago' => $validated['metodo_pago'],
                'subtotal' => $subtotal,
                'descuento' => $totalDescuento,
                'total' => $total,
                'estado' => 'completada',
                'notas' => $validated['notas'] ?? null,
            ]);

            foreach ($lineas as $linea) {
                $serviceSale->details()->create($linea);
            }

            return $serviceSale;
        });

        return redirect()->route('service-sales.index')
                         ->with('success', 'Venta de servicios #' . $serviceSale->id . ' registrada. Total: $' . number_format($serviceSale->total, 2));
    }

    /**
     * Cancelar una venta de servicios (no se borra, se conserva para reportes).
     */
    public function destroy(ServiceSale $serviceSale)
    {
        if ($serviceSale->estado === 'cancelada') {
            return redirect()->route('service-sales.index')->with('error', 'Esta venta ya estaba cancelada.');
        }

        $serviceSale->update(['estado' => 'cancelada']);

        return redirect()->route('service-sales.index')->with('success', 'Venta de servicios #' . $serviceSale->id . ' cancelada.');
    }
}
