<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\ServiceSaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Listado de gastos con búsqueda, filtro por mes y por categoría.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoria = $request->input('categoria');
        $mes = $request->input('mes', today()->format('Y-m'));

        try {
            $inicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        } catch (\Throwable $e) {
            $inicioMes = today()->startOfMonth();
            $mes = $inicioMes->format('Y-m');
        }

        $query = Expense::with(['user', 'cashRegister'])
                    ->whereBetween('fecha', [$inicioMes->toDateString(), $inicioMes->copy()->endOfMonth()->toDateString()])
                    ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
                    ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                        $q->where('descripcion', 'like', "%{$search}%")
                          ->orWhere('notas', 'like', "%{$search}%");
                    }));

        $totalMes = (clone $query)->sum('monto');

        // Para el total de egresos del mes: todos los gastos (sin filtros)
        // más las comisiones de las empleadas por los servicios vendidos.
        $finMes = $inicioMes->copy()->endOfMonth();
        $gastosMesSinFiltros = (float) Expense::whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])->sum('monto');
        $comisionesMes = (float) ServiceSaleDetail::join('service_sales', 'service_sales.id', '=', 'service_sale_details.service_sale_id')
                                    ->where('service_sales.estado', 'completada')
                                    ->whereBetween('service_sales.created_at', [$inicioMes, $finMes])
                                    ->sum('service_sale_details.comision_monto');
        $totalEgresos = $gastosMesSinFiltros + $comisionesMes;

        $expenses = $query->orderByDesc('fecha')
                          ->orderByDesc('id')
                          ->paginate(15)
                          ->withQueryString();

        $categorias = Expense::CATEGORIAS;
        $cajaAbierta = CashRegister::abiertaHoy();

        return view('expenses.index', compact('expenses', 'search', 'categoria', 'mes', 'inicioMes', 'totalMes', 'comisionesMes', 'totalEgresos', 'categorias', 'cajaAbierta'));
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

        return redirect()->route('expenses.index', ['mes' => Carbon::parse($validated['fecha'])->format('Y-m')])
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
