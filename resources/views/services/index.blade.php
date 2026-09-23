<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Salón de Belleza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased" x-data="{ openCreateModal: false, openEditModal: false, openDeleteModal: false, editForm: {}, deleteForm: {} }">

    <div class="min-h-screen flex flex-col">

        @include('components.navbar')

        <main class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- MENSAJE DE ERROR (CUANDO TIENE VENTAS ASOCIADAS) -->
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
                    <p class="text-sm text-red-700 font-bold">¡Acción bloqueada! {{ session('error') }}</p>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <form method="GET" action="{{ route('services.index') }}" class="w-full sm:w-80">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por servicio o categoría..."
                           class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                </form>

                <button @click="openCreateModal = true" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo Servicio
                </button>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">
                                    <a href="{{ route('services.index', array_merge(request()->query(), ['sort' => 'nombre', 'direction' => ($sort === 'nombre' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition">
                                        Servicio
                                        @if ($sort === 'nombre')<span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>@else<span class="text-gray-300">↕</span>@endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold">
                                    <a href="{{ route('services.index', array_merge(request()->query(), ['sort' => 'categoria', 'direction' => ($sort === 'categoria' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition">
                                        Categoría
                                        @if ($sort === 'categoria')<span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>@else<span class="text-gray-300">↕</span>@endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold">Descripción</th>
                                <th class="py-3 px-4 font-semibold text-right">
                                    <a href="{{ route('services.index', array_merge(request()->query(), ['sort' => 'precio', 'direction' => ($sort === 'precio' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center justify-end gap-1 hover:text-gray-900 transition">
                                        Precio
                                        @if ($sort === 'precio')<span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>@else<span class="text-gray-300">↕</span>@endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($services as $service)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4 text-gray-900 font-semibold">{{ $service->nombre }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $service->category->nombre ?? 'Sin categoría' }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ $service->descripcion }}</td>
                                    <td class="py-3 px-4 text-right text-pink-600 font-semibold">${{ number_format($service->precio, 2) }}</td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button @click="openEditModal = true; editForm = {{ Illuminate\Support\Js::from($service) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                            Editar
                                        </button>
                                        <button type="button"
                                                @click="openDeleteModal = true; deleteForm = { id: {{ $service->id }}, nombre: @js($service->nombre) }"
                                                class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 px-2.5 py-1 rounded-md transition">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-400">No hay servicios registrados todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $services->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL CREAR -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Nuevo Servicio</h3>
            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Categoría *</label>
                        <select name="service_category_id" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                            <option value="" disabled selected>Selecciona una categoría</option>
                            @foreach ($serviceCategories as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre del Servicio *</label>
                        <input type="text" name="nombre" required placeholder="Ej. Corte, Mechas con gorro" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Precio ($) *</label>
                        <input type="number" step="0.01" min="0" name="precio" required value="0.00" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="2" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openEditModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Servicio</h3>
            <form :action="'/services/' + editForm.id" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Categoría *</label>
                        <select name="service_category_id" x-model="editForm.service_category_id" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="" disabled>Selecciona una categoría</option>
                            @foreach ($serviceCategories as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre del Servicio *</label>
                        <input type="text" name="nombre" x-model="editForm.nombre" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Precio ($) *</label>
                        <input type="number" step="0.01" min="0" name="precio" x-model="editForm.precio" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" x-model="editForm.descripcion" rows="2" class="w-full border border-gray-300 rounded-lg p-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="openEditModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINACIÓN -->
    <div x-show="openDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative" @click.away="openDeleteModal = false">
            <div class="flex items-start gap-4 mb-5">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Eliminar servicio</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        ¿Estás seguro de eliminar <span class="font-semibold text-gray-800" x-text="deleteForm.nombre"></span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
            <form :action="'/services/' + deleteForm.id" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button" @click="openDeleteModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>