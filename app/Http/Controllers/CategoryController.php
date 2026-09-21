<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Muestra una lista de las categorías, con búsqueda, orden y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Columnas por las que se permite ordenar (whitelist por seguridad).
        $sortable = ['nombre', 'descripcion', 'created_at'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'nombre';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $categories = Category::withCount('products')
                        ->when($search, function ($query, $search) {
                            return $query->where('nombre', 'like', "%{$search}%")
                                         ->orWhere('descripcion', 'like', "%{$search}%");
                        })
                        ->orderBy($sort, $direction)
                        ->paginate(10)
                        ->withQueryString();

        return view('categories.index', compact('categories', 'search', 'sort', 'direction'));
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categories,nombre',
            'descripcion' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
                         ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Muestra el formulario para editar una categoría existente.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Actualiza la categoría en la base de datos.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categories,nombre,' . $category->id,
            'descripcion' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
                         ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Elimina una categoría de la base de datos.
     */
    public function destroy(Category $category)
    {
        // Opcional: Validar si tiene productos asociados antes de borrar
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                             ->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        $category->delete();

        return redirect()->route('categories.index')
                         ->with('success', 'Categoría eliminada exitosamente.');
    }
}