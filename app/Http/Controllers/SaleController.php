<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Listado de ventas con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $sales = Sale::with(['user', 'details.product'])
                    ->withCount('details')
                    ->when($search, function ($query, $search) {
                        return $query->where('cliente_nombre', 'like', "%{$search}%")
                                     ->orWhere('id', $search);
                    })
                    ->latest()
                    ->paginate(10)
                    ->withQueryString();

        // Se envían todos los productos (con su precio y existencia actual)
        // para armar el formulario de nueva venta en el frontend.
        $products = Product::with('category')->orderBy('producto')->get();

        return view('sales.index', compact('sales', 'search', 'products'));
    }

    /**
     * Registrar una nueva venta con uno o varios productos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_nombre' => 'nullable|string|max:150',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'notas' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.product_id' => 'required|exists:products,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descuento' => 'nullable|numeric|min:0',
        ]);

        try {
            $sale = DB::transaction(function () use ($validated) {
                $subtotal = 0;
                $totalDescuento = 0;
                $lineas = [];

                foreach ($validated['productos'] as $item) {
                    // lockForUpdate evita que dos ventas simultáneas descuenten
                    // el mismo stock al mismo tiempo (condición de carrera).
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->existencia < $item['cantidad']) {
                        throw new \Exception("No hay suficiente existencia de \"{$product->producto}\" (disponible: {$product->existencia}).");
                    }

                    $precioUnitario = $product->precio_venta;
                    $descuentoLinea = $item['descuento'] ?? 0;
                    $bruto = $precioUnitario * $item['cantidad'];

                    if ($descuentoLinea > $bruto) {
                        throw new \Exception("El descuento de \"{$product->producto}\" no puede ser mayor al subtotal de esa línea.");
                    }

                    $lineaSubtotal = $bruto - $descuentoLinea;
                    $subtotal += $bruto;
                    $totalDescuento += $descuentoLinea;

                    $lineas[] = [
                        'product' => $product,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $precioUnitario,
                        'descuento' => $descuentoLinea,
                        'subtotal' => $lineaSubtotal,
                    ];
                }

                $total = $subtotal - $totalDescuento;

                $sale = Sale::create([
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
                    $sale->details()->create([
                        'product_id' => $linea['product']->id,
                        'cantidad' => $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'descuento' => $linea['descuento'],
                        'subtotal' => $linea['subtotal'],
                    ]);

                    $linea['product']->decrement('existencia', $linea['cantidad']);
                }

                return $sale;
            });
        } catch (\Exception $e) {
            return redirect()->route('sales.index')->with('error', $e->getMessage());
        }

        return redirect()->route('sales.index')
                         ->with('success', 'Venta #' . $sale->id . ' registrada. Total: $' . number_format($sale->total, 2));
    }

    /**
     * Cancelar una venta: NO se borra el registro (se conserva para reportes),
     * se marca como "cancelada" y se restaura el stock de cada producto.
     */
    public function destroy(Sale $sale)
    {
        if ($sale->estado === 'cancelada') {
            return redirect()->route('sales.index')->with('error', 'Esta venta ya estaba cancelada.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                $detail->product->increment('existencia', $detail->cantidad);
            }

            $sale->update(['estado' => 'cancelada']);
        });

        return redirect()->route('sales.index')->with('success', 'Venta #' . $sale->id . ' cancelada y stock restaurado.');
    }
}