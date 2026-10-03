<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
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
        $fechaFiltro = $request->input('fecha');
        $employeeFiltro = $request->input('employee_id');

        $serviceSales = ServiceSale::with(['user', 'details.service', 'details.employee', 'cashRegister'])
                            ->withSum('details', 'cantidad')
                            ->when($search, function ($query, $search) {
                                return $query->where('cliente_nombre', 'like', "%{$search}%")
                                             ->orWhere('id', $search);
                            })
                            ->when($fechaFiltro, function ($query, $fechaFiltro) {
                                return $query->whereDate('created_at', $fechaFiltro);
                            })
                            ->when($employeeFiltro, function ($query, $employeeFiltro) {
                                return $query->whereHas('details', function ($q) use ($employeeFiltro) {
                                    $q->where('employee_id', $employeeFiltro);
                                });
                            })
                            ->latest()
                            ->paginate(10)
                            ->withQueryString();

        // Servicios (con categoría) y empleados activos, para armar el formulario.
        $services = Service::with('category')->orderBy('nombre')->get();
        $employees = Employee::where('activo', true)->orderBy('nombre')->get();

        $cajaAbierta = CashRegister::abiertaHoy();

        return view('service_sales.index', compact('serviceSales', 'search', 'fechaFiltro', 'employeeFiltro', 'services', 'employees', 'cajaAbierta'));
    }

    /**
     * Registrar una nueva venta de servicios con una o varias líneas.
     */
    public function store(Request $request)
    {
        $validated = $this->validar($request);

        try {
            $serviceSale = DB::transaction(function () use ($validated) {
                // Bloquea la caja para que no se cierre a mitad de la venta.
                $caja = CashRegister::abiertaHoy(bloquear: true);

                if (!$caja) {
                    throw new \Exception('Debes abrir la caja antes de registrar ventas.');
                }

                [$lineas, $subtotal, $totalDescuento] = $this->armarLineas($validated['servicios']);
                $total = max($subtotal - $totalDescuento, 0);

                $serviceSale = ServiceSale::create([
                    'user_id' => auth()->id(),
                    'cash_register_id' => $caja->id,
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
        } catch (\Exception $e) {
            return redirect()->route('service-sales.index')->with('error', $e->getMessage());
        }

        return redirect()->route('service-sales.index')
                         ->with('success', 'Venta de servicios #' . $serviceSale->id . ' registrada. Total: $' . number_format($serviceSale->total, 2));
    }

    /**
     * Corregir una venta de servicios (cliente, pago, servicios, empleada,
     * cantidades, descuentos y comisiones). Solo ventas de la caja de hoy
     * mientras está abierta. Como la caja y los reportes se calculan con
     * las líneas guardadas, quitar un servicio resta su precio y comisión.
     * Los servicios que ya estaban conservan el precio con que se vendieron.
     */
    public function update(Request $request, ServiceSale $serviceSale)
    {
        $validated = $this->validar($request);

        try {
            $venta = DB::transaction(function () use ($validated, $serviceSale) {
                $venta = ServiceSale::with('details')->lockForUpdate()->findOrFail($serviceSale->id);
                $venta->setRelation('cashRegister', CashRegister::lockForUpdate()->find($venta->cash_register_id));

                if (!$venta->sePuedeCancelar()) {
                    throw new \Exception('No se puede editar la venta de servicios #' . $venta->id . ' porque está cancelada, es de un día anterior o su caja ya fue cerrada.');
                }

                $preciosAnteriores = $venta->details->pluck('precio', 'service_id')->all();
                [$lineas, $subtotal, $totalDescuento] = $this->armarLineas($validated['servicios'], $preciosAnteriores);
                $total = max($subtotal - $totalDescuento, 0);

                // Registro de la corrección (se conserva aunque se editen las notas).
                $registros = $venta->registrosDeEdicion();
                $registros[] = '[Editada el ' . now()->format('d/m/Y H:i') . ' por ' . (auth()->user()->name ?? 'usuario')
                             . ': total $' . number_format($venta->total, 2) . ' → $' . number_format($total, 2) . ']';
                $notas = trim(implode("\n", array_filter([trim($validated['notas'] ?? ''), ...$registros])));

                $venta->details()->delete();
                foreach ($lineas as $linea) {
                    $venta->details()->create($linea);
                }

                $venta->update([
                    'cliente_nombre' => $validated['cliente_nombre'] ?? null,
                    'metodo_pago' => $validated['metodo_pago'],
                    'subtotal' => $subtotal,
                    'descuento' => $totalDescuento,
                    'total' => $total,
                    'notas' => $notas !== '' ? $notas : null,
                ]);

                return $venta;
            });
        } catch (\Exception $e) {
            return redirect()->route('service-sales.index')->with('error', $e->getMessage());
        }

        return redirect()->route('service-sales.index')
                         ->with('success', 'Venta de servicios #' . $venta->id . ' actualizada. Total: $' . number_format($venta->total, 2));
    }

    private function validar(Request $request): array
    {
        return $request->validate([
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
    }

    /**
     * Arma las líneas de la venta y calcula subtotal y descuentos.
     * $preciosAnteriores (service_id => precio): al editar, los servicios
     * que ya estaban en la venta conservan el precio con que se vendieron.
     *
     * @return array{0: array, 1: float, 2: float}
     */
    private function armarLineas(array $servicios, array $preciosAnteriores = []): array
    {
        $subtotal = 0;
        $totalDescuento = 0;
        $lineas = [];

        foreach ($servicios as $item) {
            $service = Service::findOrFail($item['service_id']);
            $precio = $preciosAnteriores[$service->id] ?? $service->precio;

            $descuentoLinea = $item['descuento'] ?? 0;
            $bruto = $precio * $item['cantidad'];

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
                'precio' => $precio,
                'descuento' => $descuentoLinea,
                'subtotal' => $subtotalLinea,
                'comision_porcentaje' => $item['comision_porcentaje'],
                'comision_monto' => $comisionMonto,
            ];
        }

        return [$lineas, $subtotal, $totalDescuento];
    }

    /**
     * Cancelar una venta de servicios (no se borra, se conserva para reportes).
     * Solo se permite mientras la caja de la venta siga abierta.
     */
    public function destroy(ServiceSale $serviceSale)
    {
        try {
            DB::transaction(function () use ($serviceSale) {
                // Se releen venta y caja con bloqueo: evita cancelar dos veces
                // y que la caja se cierre a mitad de la cancelación.
                $venta = ServiceSale::lockForUpdate()->findOrFail($serviceSale->id);
                $venta->setRelation('cashRegister', CashRegister::lockForUpdate()->find($venta->cash_register_id));

                if ($venta->estado === 'cancelada') {
                    throw new \Exception('Esta venta ya estaba cancelada.');
                }

                if (!$venta->sePuedeCancelar()) {
                    throw new \Exception('No se puede cancelar la venta de servicios #' . $venta->id . ' porque es de un día anterior o su caja ya fue cerrada.');
                }

                $venta->update(['estado' => 'cancelada']);
            });
        } catch (\Exception $e) {
            return redirect()->route('service-sales.index')->with('error', $e->getMessage());
        }

        return redirect()->route('service-sales.index')->with('success', 'Venta de servicios #' . $serviceSale->id . ' cancelada.');
    }
}
