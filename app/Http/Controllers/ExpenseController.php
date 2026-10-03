<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\ServiceSaleDetail;
use App\Support\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Gastos DE HOY, con búsqueda y filtro por categoría.
     * Los de días anteriores están en el historial.
     */
    public function index(Request $request)
    {
        return $this->listado($request, historial: false);
    }

    /**
     * Historial: gastos de días anteriores por período (día, semana o mes),
     * con búsqueda y filtro por categoría.
     */
    public function history(Request $request)
    {
        return $this->listado($request, historial: true);
    }

    private function listado(Request $request, bool $historial)
    {
        $search = $request->input('search');
        $categoria = $request->input('categoria');

        if ($historial) {
            $periodo = in_array($request->input('periodo'), Periodo::VALIDOS) ? $request->input('periodo') : 'mes';
            $fecha = $request->input('fecha', today()->subDay()->toDateString());

            // En el historial no hay "hoy": una fecha de hoy o futura pasa a ayer.
            try {
                if (Carbon::parse($fecha)->gte(today())) {
                    $fecha = today()->subDay()->toDateString();
                }
            } catch (\Throwable $e) {
                $fecha = today()->subDay()->toDateString();
            }
        } else {
            $periodo = 'dia';
            $fecha = today()->toDateString();
        }

        try {
            [$inicio, $fin, $fechaAnterior, $fechaSiguiente] = Periodo::rango($periodo, $fecha);
        } catch (\Throwable $e) {
            $fecha = today()->toDateString();
            [$inicio, $fin, $fechaAnterior, $fechaSiguiente] = Periodo::rango($periodo, $fecha);
        }

        // El historial solo incluye días anteriores a hoy.
        if ($historial && $fin->gte(today())) {
            $fin = today()->subDay()->endOfDay();
        }

        $query = Expense::with(['user', 'cashRegister'])
                    ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                    ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
                    ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                        $q->where('descripcion', 'like', "%{$search}%")
                          ->orWhere('notas', 'like', "%{$search}%");
                    }));

        $totalGastos = (float) (clone $query)->sum('monto');

        // Total de egresos del período: todos los gastos (sin filtros) más
        // las comisiones de las empleadas por los servicios vendidos.
        $gastosSinFiltros = (float) Expense::whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])->sum('monto');
        $totalComisiones = (float) ServiceSaleDetail::join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                                    ->where('service_sales.estado', 'completada')
                                    ->whereBetween('service_sales.created_at', [$inicio, $fin])
                                    ->sum('service_sale_details.comision_monto');
        $totalEgresos = $gastosSinFiltros + $totalComisiones;

        $expenses = $query->orderByDesc('fecha')
                          ->orderByDesc('id')
                          ->paginate(15)
                          ->withQueryString();

        $categorias = Expense::CATEGORIAS;
        $cajaAbierta = CashRegister::abiertaHoy();

        return view('expenses.index', compact(
            'historial', 'expenses', 'search', 'categoria', 'periodo', 'fecha', 'inicio', 'fin', 'fechaAnterior', 'fechaSiguiente',
            'totalGastos', 'totalComisiones', 'totalEgresos', 'categorias', 'cajaAbierta'
        ));
    }

    /**
     * Registrar un gasto nuevo.
     */
    public function store(Request $request)
    {
        $validated = $this->validar($request);

        DB::transaction(function () use ($validated) {
            Expense::create([
                ...$validated,
                'user_id' => auth()->id(),
                'cash_register_id' => $this->cajaParaGasto($validated),
            ]);
        });

        // Se muestra el día del gasto recién registrado (hoy o en el historial).
        $fechaGasto = Carbon::parse($validated['fecha']);
        $destino = $fechaGasto->isToday()
            ? route('expenses.index')
            : route('expenses.history', ['periodo' => 'dia', 'fecha' => $fechaGasto->toDateString()]);

        return redirect($destino)
                         ->with('success', 'Gasto "' . $validated['descripcion'] . '" registrado por $' . number_format($validated['monto'], 2) . '.');
    }

    /**
     * Editar un gasto (solo si no forma parte de una caja ya cerrada).
     */
    public function update(Request $request, Expense $expense)
    {
        if (!$expense->sePuedeModificar()) {
            return redirect()->back()->with('error', 'Este gasto ya forma parte del cierre de una caja y no se puede editar.');
        }

        $validated = $this->validar($request);

        DB::transaction(function () use ($expense, $validated) {
            $expense->update([
                ...$validated,
                'cash_register_id' => $this->cajaParaGasto($validated, $expense),
            ]);
        });

        return redirect()->back()->with('success', 'Gasto "' . $expense->descripcion . '" actualizado.');
    }

    /**
     * Eliminar un gasto (solo si no forma parte de una caja ya cerrada).
     */
    public function destroy(Expense $expense)
    {
        if (!$expense->sePuedeModificar()) {
            return redirect()->back()->with('error', 'Este gasto ya forma parte del cierre de una caja y no se puede eliminar.');
        }

        $expense->delete();

        return redirect()->back()->with('success', 'Gasto "' . $expense->descripcion . '" eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'categoria' => ['required', Rule::in(array_keys(Expense::CATEGORIAS))],
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'notas' => 'nullable|string',
        ], [
            'fecha.before_or_equal' => 'La fecha del gasto no puede ser futura.',
        ]);
    }

    /**
     * Un gasto en efectivo con fecha de hoy y la caja de hoy abierta sale
     * de esa caja: se liga a ella para restarlo del efectivo esperado.
     * Al editar, si el gasto ya estaba en una caja abierta de su misma
     * fecha (por ejemplo, una caja de ayer pendiente de cerrar), se queda
     * en esa caja. Se bloquea la caja para que no se cierre mientras tanto.
     */
    private function cajaParaGasto(array $datos, ?Expense $gasto = null): ?int
    {
        if ($datos['metodo_pago'] !== 'efectivo') {
            return null;
        }

        $fecha = Carbon::parse($datos['fecha']);

        if ($gasto?->cash_register_id) {
            $actual = CashRegister::lockForUpdate()->find($gasto->cash_register_id);
            if ($actual && $actual->estaAbierta() && $actual->fecha->isSameDay($fecha)) {
                return $actual->id;
            }
        }

        return $fecha->isToday() ? CashRegister::abiertaHoy(bloquear: true)?->id : null;
    }
}
