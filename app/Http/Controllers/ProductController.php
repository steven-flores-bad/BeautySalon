<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Mostrar lista de productos y el formulario para crear/editar
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Columnas por las que se permite ordenar (whitelist por seguridad,
        // para no dejar que cualquier texto arbitrario llegue a orderBy()).
        $sortable = ['codigo', 'producto', 'marca', 'categoria', 'existencia', 'precio_compra', 'precio_venta'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = Product::with('category')
                    ->when($search, function ($query, $search) {
                        return $query->where('producto', 'like', "%{$search}%")
                                     ->orWhere('codigo', 'like', "%{$search}%")
                                     ->orWhereHas('category', function ($q) use ($search) {
                                         $q->where('nombre', 'like', "%{$search}%");
                                     });
                    });

        // "categoria" no es una columna de products, así que para ordenar por
        // el nombre de la categoría hay que hacer join con la tabla categories.
        if ($sort === 'categoria') {
            $query->join('categories', 'products.category_id', '=', 'categories.id')
                  ->orderBy('categories.nombre', $direction)
                  ->select('products.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $products = $query->paginate(10)->withQueryString();

        // Se envían todas las categorías para llenar el <select> del formulario
        $categories = Category::orderBy('nombre')->get();

        return view('products.index', compact('products', 'search', 'categories', 'sort', 'direction'));
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'codigo' ya no viene del formulario, se genera automáticamente abajo
            'category_id' => 'required|exists:categories,id',
            'producto' => 'required|string|max:150',
            'marca' => 'nullable|string|max:100',
            'presentacion_valor' => 'nullable|numeric|min:0',
            'presentacion_unidad' => 'nullable|in:ml,l,g,kg,unidad,oz',
            'existencia' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        $product = Product::create($validated);

        // El código depende del id autoincremental, así que se genera después
        // de crear el registro y se guarda en una segunda escritura.
        $product->update([
            'codigo' => 'PROD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('products.index')->with('success', 'Producto registrado exitosamente.');
    }

    // Actualizar producto existente
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'producto' => 'required|string|max:150',
            'marca' => 'nullable|string|max:100',
            'presentacion_valor' => 'nullable|numeric|min:0',
            'presentacion_unidad' => 'nullable|in:ml,l,g,kg,unidad,oz',
            'existencia' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    // Eliminar producto
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado.');
    }
}