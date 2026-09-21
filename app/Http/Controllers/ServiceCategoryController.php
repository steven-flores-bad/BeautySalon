<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /**
     * Muestra el listado de categorías de servicio, con búsqueda, orden y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $sortable = ['nombre', 'descripcion', 'created_at'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'nombre';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $serviceCategories = ServiceCategory::withCount('services')
                                ->when($search, function ($query, $search) {
                                    return $query->where('nombre', 'like', "%{$search}%")
                                                 ->orWhere('descripcion', 'like', "%{$search}%");
                                })
                                ->orderBy($sort, $direction)
                                ->paginate(10)
                                ->withQueryString();

        return view('service_categories.index', compact('serviceCategories', 'search', 'sort', 'direction'));
    }

    /**
     * Almacena una nueva categoría de servicio.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:service_categories,nombre',
            'descripcion' => 'nullable|string',
        ]);

        ServiceCategory::create($validated);

        return redirect()->route('service-categories.index')
                         ->with('success', 'Categoría de servicio creada exitosamente.');
    }

    /**
     * Actualiza una categoría de servicio existente.
     */
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:service_categories,nombre,' . $serviceCategory->id,
            'descripcion' => 'nullable|string',
        ]);

        $serviceCategory->update($validated);

        return redirect()->route('service-categories.index')
                         ->with('success', 'Categoría de servicio actualizada exitosamente.');
    }

    /**
     * Elimina una categoría de servicio, siempre que no tenga servicios asociados.
     */
    public function destroy(ServiceCategory $serviceCategory)
    {
        if ($serviceCategory->services()->count() > 0) {
            return redirect()->route('service-categories.index')
                             ->with('error', 'No se puede eliminar la categoría porque tiene servicios asociados.');
        }

        $serviceCategory->delete();

        return redirect()->route('service-categories.index')
                         ->with('success', 'Categoría de servicio eliminada exitosamente.');
    }
}
