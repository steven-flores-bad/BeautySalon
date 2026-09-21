<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Salón de Belleza</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js para interactividad de modales y menús -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased" x-data="{ openCreateModal: false, openEditModal: false, editForm: {} }">

    <div class="min-h-screen flex flex-col">

        <!-- BARRA DE NAVEGACIÓN GLOBAL -->
        @include('components.navbar')

        <!-- Contenido Principal -->
        <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Mensaje de éxito -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Errores de validación -->
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

            <!-- Barra de Acciones y Buscador -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <form method="GET" action="{{ route('products.index') }}" class="w-full sm:w-80">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por producto, código o categoría..."
                           class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                </form>

                <button @click="openCreateModal = true" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo Producto
                </button>
            </div>

            <!-- Tabla de Datos Moderna -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                @php
                                    $columns = [
                                        'codigo' => 'Código',
                                        'categoria' => 'Categoría',
                                        'producto' => 'Producto',
                                        'marca' => 'Marca / Presentación',
                                    ];
                                @endphp
                                @foreach ($columns as $key => $label)
                                    <th class="py-3 px-4 font-semibold">
                                        <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => $key, 'direction' => ($sort === $key && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                           class="flex items-center gap-1 hover:text-gray-900 transition">
                                            {{ $label }}
                                            @if ($sort === $key)
                                                <span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                            @else
                                                <span class="text-gray-300">↕</span>
                                            @endif
                                        </a>
                                    </th>
                                @endforeach
                                <th class="py-3 px-4 font-semibold text-center">
                                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'existencia', 'direction' => ($sort === 'existencia' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center justify-center gap-1 hover:text-gray-900 transition">
                                        Existencia
                                        @if ($sort === 'existencia')
                                            <span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-gray-300">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold">
                                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'precio_compra', 'direction' => ($sort === 'precio_compra' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition">
                                        P. Compra
                                        @if ($sort === 'precio_compra')
                                            <span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-gray-300">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold">
                                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'precio_venta', 'direction' => ($sort === 'precio_venta' && $direction === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 hover:text-gray-900 transition">
                                        P. Venta
                                        @if ($sort === 'precio_venta')
                                            <span class="text-pink-600">{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-gray-300">↕</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4 text-gray-500 font-mono text-xs">{{ $product->codigo ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 font-medium text-gray-800">{{ $product->category->nombre ?? 'Sin categoría' }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-semibold">{{ $product->producto }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ $product->marca }} @if($product->presentacion_valor)<span class="text-xs text-gray-400">({{ rtrim(rtrim(number_format($product->presentacion_valor, 2), '0'), '.') }} {{ $product->presentacion_unidad }})</span>@endif</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $product->existencia <= $product->stock_minimo ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $product->existencia }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">${{ number_format($product->precio_compra, 2) }}</td>
                                    <td class="py-3 px-4 text-pink-600 font-semibold">${{ number_format($product->precio_venta, 2) }}</td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <!-- Botón Editar -->
                                        <button @click="openEditModal = true; editForm = {{ Illuminate\Support\Js::from($product) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                            Editar
                                        </button>
                                        <!-- Botón Eliminar -->
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
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
                                    <td colspan="8" class="text-center py-8 text-gray-400">No hay productos registrados todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="p-4 border-t border-gray-200">
                    {{ $products->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL CREAR -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Registrar Nuevo Producto</h3>
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <p class="text-xs text-gray-400 mb-4">El código del producto se genera automáticamente al guardar.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Categoría *</label>
                        <select name="category_id" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                            <option value="" disabled selected>Selecciona una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Producto *</label>
                        <input type="text" name="producto" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Marca</label>
                        <input type="text" name="marca" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cantidad</label>
                        <input type="number" step="0.01" min="0" name="presentacion_valor" placeholder="Ej. 15" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Unidad</label>
                        <select name="presentacion_unidad" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                            <option value="" selected>Sin especificar</option>
                            <option value="ml">Mililitros (ml)</option>
                            <option value="l">Litros (l)</option>
                            <option value="g">Gramos (g)</option>
                            <option value="kg">Kilogramos (kg)</option>
                            <option value="oz">Onzas (oz)</option>
                            <option value="unidad">Unidad</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Existencia *</label>
                        <input type="number" name="existencia" value="0" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Stock Mínimo *</label>
                        <input type="number" name="stock_minimo" value="1" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Precio Compra ($) *</label>
                        <input type="number" step="0.01" name="precio_compra" value="0.00" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Precio Venta ($) *</label>
                        <input type="number" step="0.01" name="precio_venta" value="0.00" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Producto</h3>
            <form :action="'/products/' + editForm.id" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Código</label>
                        <input type="text" x-model="editForm.codigo" disabled class="w-full border border-gray-200 bg-gray-100 text-gray-500 rounded-lg p-2 text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Categoría *</label>
                        <select name="category_id" x-model="editForm.category_id" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="" disabled>Selecciona una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Producto *</label>
                        <input type="text" name="producto" x-model="editForm.producto" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Marca</label>
                        <input type="text" name="marca" x-model="editForm.marca" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cantidad</label>
                        <input type="number" step="0.01" min="0" name="presentacion_valor" x-model="editForm.presentacion_valor" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Unidad</label>
                        <select name="presentacion_unidad" x-model="editForm.presentacion_unidad" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="">Sin especificar</option>
                            <option value="ml">Mililitros (ml)</option>
                            <option value="l">Litros (l)</option>
                            <option value="g">Gramos (g)</option>
                            <option value="kg">Kilogramos (kg)</option>
                            <option value="oz">Onzas (oz)</option>
                            <option value="unidad">Unidad</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Existencia *</label>
                        <input type="number" name="existencia" x-model="editForm.existencia" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Stock Mínimo *</label>
                        <input type="number" name="stock_minimo" x-model="editForm.stock_minimo" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Precio Compra ($) *</label>
                        <input type="number" step="0.01" name="precio_compra" x-model="editForm.precio_compra" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Precio Venta ($) *</label>
                        <input type="number" step="0.01" name="precio_venta" x-model="editForm.precio_venta" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
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