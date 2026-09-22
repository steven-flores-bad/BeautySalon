<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Muestra el listado de servicios, con búsqueda, orden y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $sortable = ['nombre', 'categoria', 'precio'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'nombre';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query = Service::with('category')
                    ->when($search, function ($query, $search) {
                        return $query->where('nombre', 'like', "%{$search}%")
                                     ->orWhereHas('category', function ($q) use ($search) {
                                         $q->where('nombre', 'like', "%{$search}%");
                                     });
                    });

        if ($sort === 'categoria') {
            $query->join('service_categories', 'service_categories.id', '=', 'services.service_category_id')
                  ->orderBy('service_categories.nombre', $direction)
                  ->select('services.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $services = $query->paginate(10)->withQueryString();

        // Se envían todas las categorías para llenar el <select> del formulario
        $serviceCategories = ServiceCategory::orderBy('nombre')->get();

        return view('services.index', compact('services', 'search', 'serviceCategories', 'sort', 'direction'));
    }

    /**
     * Almacena un nuevo servicio.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
        ]);

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Servicio registrado exitosamente.');
    }

    /**
     * Actualiza un servicio existente.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Elimina un servicio de forma segura validando relaciones.
     */
    public function destroy(Service $service)
    {
        // Validación de integridad: Verificamos si está vinculado a ventas previas
        if ($service->serviceSaleDetails()->exists()) {
            return redirect()->route('services.index')
                ->with('error', 'No se puede eliminar el servicio "' . $service->nombre . '" porque ya tiene transacciones o ventas registradas en el historial.');
        }

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Servicio eliminado correctamente.');
    }
}