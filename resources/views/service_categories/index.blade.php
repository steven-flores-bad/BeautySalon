<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías de Servicio - Salón de Belleza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased" x-data="{ openCreateModal: false, openEditModal: false, editForm: {} }">

    <div class="min-h-screen flex flex-col">

        @include('components.navbar')

        <main class="flex-grow max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-700 font-bold">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside text-xs text-red-600 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900 w-full">Categorías de Servicio</h1>

                <form method="GET" action="{{ route('service-categories.index') }}" class="w-full sm:w-85">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o descripción..."
                           class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                </form>

                <button @click="openCreateModal = true" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Categoría
                </button>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">
                                    <a href="{{ route('service-categories.index', array_merge(request()->query(), ['sort' => 'nombre', 'direction' => ($sort === 'nombre' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition">
                                        Nombre
                                        @if ($sort === 'nombre')
                                            <span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-gray-300">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold">
                                    <a href="{{ route('service-categories.index', array_merge(request()->query(), ['sort' => 'descripcion', 'direction' => ($sort === 'descripcion' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition">
                                        Descripción
                                        @if ($sort === 'descripcion')
                                            <span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-gray-300">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold text-center"># Servicios</th>
                                <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($serviceCategories as $categoria)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4 text-gray-900 font-semibold">{{ $categoria->nombre }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $categoria->descripcion }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                            {{ $categoria->services_count }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button @click="openEditModal = true; editForm = {{ Illuminate\Support\Js::from($categoria) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                            Editar
                                        </button>
                                        <form action="{{ route('service-categories.destroy', $categoria->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 px-2.5 py-1 rounded-md transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-400">No hay categorías de servicio registradas todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $serviceCategories->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL CREAR -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Nueva Categoría de Servicio</h3>
            <form action="{{ route('service-categories.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="nombre" required placeholder="Ej. Cabello, Uñas, Cejas" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Categoría de Servicio</h3>
            <form :action="'/service-categories/' + editForm.id" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="nombre" x-model="editForm.nombre" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" x-model="editForm.descripcion" rows="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="openEditModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
