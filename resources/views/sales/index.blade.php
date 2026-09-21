<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas / Caja - Salón de Belleza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased"
      x-data="saleForm()">

    <div class="min-h-screen flex flex-col">

        @include('components.navbar')

        <main class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

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

            <!-- Encabezado, buscador y botón -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900 w-full">Ventas / Caja</h1>

                <form method="GET" action="{{ route('sales.index') }}" class="w-full sm:w-72">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por cliente o # de venta..."
                           class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                </form>

                <button @click="openModal()" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Venta
                </button>
            </div>

            <!-- Tabla de Ventas -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">#</th>
                                <th class="py-3 px-4 font-semibold">Fecha</th>
                                <th class="py-3 px-4 font-semibold">Cliente</th>
                                <th class="py-3 px-4 font-semibold text-center">Productos</th>
                                <th class="py-3 px-4 font-semibold">Pago</th>
                                <th class="py-3 px-4 font-semibold">Total</th>
                                <th class="py-3 px-4 font-semibold text-center">Estado</th>
                                <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($sales as $sale)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4 text-gray-500 font-mono text-xs">#{{ $sale->id }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $sale->cliente_nombre ?? 'Cliente general' }}</td>
                                    <td class="py-3 px-4 text-center text-gray-600">{{ $sale->details_count }}</td>
                                    <td class="py-3 px-4 text-gray-600 capitalize">{{ $sale->metodo_pago }}</td>
                                    <td class="py-3 px-4 text-pink-600 font-semibold">${{ number_format($sale->total, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sale->estado === 'completada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ ucfirst($sale->estado) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button @click="viewSale({{ Illuminate\Support\Js::from($sale->load('details.product')) }})"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                            Ver
                                        </button>
                                        @if($sale->estado === 'completada')
                                            <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Cancelar esta venta? Se restaurará el stock de los productos.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 px-2.5 py-1 rounded-md transition">
                                                    Cancelar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-gray-400">No hay ventas registradas todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $sales->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL NUEVA VENTA -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Nueva Venta</h3>

            <form :action="'{{ route('sales.store') }}'" method="POST">
                @csrf

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cliente (opcional)</label>
                        <input type="text" name="cliente_nombre" x-model="cliente_nombre" placeholder="Cliente general" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Método de Pago *</label>
                        <select name="metodo_pago" x-model="metodo_pago" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>

                <!-- Líneas de producto dinámicas -->
                <div class="border border-gray-200 rounded-lg mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase rounded-t-lg">
                                <th class="text-left py-2 px-3 rounded-tl-lg">Producto</th>
                                <th class="text-center py-2 px-3 w-20">Cant.</th>
                                <th class="text-right py-2 px-3 w-24">Subtotal</th>
                                <th class="w-10 rounded-tr-lg"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 px-3 relative" @click.away="item.open = false">
                                        <input type="hidden" :name="`productos[${index}][product_id]`" :value="item.product_id">
                                        <input type="text"
                                               x-model="item.search"
                                               @focus="item.open = true"
                                               @input="item.product_id = ''; item.open = true"
                                               placeholder="Buscar por nombre, marca o tamaño (ej. 250 ml)..."
                                               autocomplete="off"
                                               class="w-full border border-gray-300 rounded-lg p-1.5 text-sm focus:ring-pink-500 focus:border-pink-500">

                                        <div x-show="item.open && filteredProducts(item).length > 0" x-cloak
                                             class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-300 rounded-lg shadow-2xl">
                                            <template x-for="p in filteredProducts(item)" :key="p.id">
                                                <button type="button"
                                                        @click="selectProduct(item, p)"
                                                        class="w-full text-left px-3 py-2.5 text-sm hover:bg-pink-50 border-b border-gray-100 last:border-b-0">
                                                    <span class="font-medium text-gray-800" x-text="productLabel(p)"></span>
                                                    <span class="block text-xs text-gray-400">
                                                        <span x-show="p.marca" x-text="p.marca + ' — '"></span>$<span x-text="parseFloat(p.precio_venta).toFixed(2)"></span> — stock: <span x-text="p.existencia"></span>
                                                    </span>
                                                </button>
                                            </template>
                                        </div>

                                        <p x-show="item.open && item.search && filteredProducts(item).length === 0" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-2xl px-3 py-2 text-xs text-gray-400">
                                            No se encontraron productos.
                                        </p>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" :name="`productos[${index}][cantidad]`" x-model.number="item.cantidad" min="1" required class="w-full border border-gray-300 rounded-lg p-1.5 text-sm text-center">
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-600" x-text="'$' + lineTotal(item).toFixed(2)"></td>
                                    <td class="py-2 px-3 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-xs" x-show="items.length > 1">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <button type="button" @click="addItem()" class="w-full text-xs text-pink-600 hover:bg-pink-50 py-2 font-medium border-t border-gray-100 rounded-b-lg">
                        + Agregar producto
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Descuento ($)</label>
                        <input type="number" step="0.01" min="0" name="descuento" x-model.number="descuento" value="0" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div class="flex flex-col justify-end text-right">
                        <span class="text-xs text-gray-500">Subtotal: <span x-text="'$' + subtotal().toFixed(2)"></span></span>
                        <span class="text-lg font-bold text-pink-600">Total: <span x-text="'$' + total().toFixed(2)"></span></span>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Registrar Venta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL VER DETALLE DE VENTA -->
    <div x-show="openViewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openViewModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Venta <span x-text="'#' + selectedSale.id"></span></h3>
            <p class="text-xs text-gray-400 mb-4" x-text="selectedSale.cliente_nombre || 'Cliente general'"></p>

            <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                            <th class="text-left py-2 px-3">Producto</th>
                            <th class="text-center py-2 px-3">Cant.</th>
                            <th class="text-right py-2 px-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="detail in (selectedSale.details || [])" :key="detail.id">
                            <tr class="border-t border-gray-100">
                                <td class="py-2 px-3" x-text="detail.product ? productLabel(detail.product) : 'Producto eliminado'"></td>
                                <td class="py-2 px-3 text-center" x-text="detail.cantidad"></td>
                                <td class="py-2 px-3 text-right" x-text="'$' + parseFloat(detail.subtotal).toFixed(2)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="text-right text-sm text-gray-600 space-y-1 mb-4">
                <p>Subtotal: $<span x-text="parseFloat(selectedSale.subtotal || 0).toFixed(2)"></span></p>
                <p>Descuento: $<span x-text="parseFloat(selectedSale.descuento || 0).toFixed(2)"></span></p>
                <p class="text-lg font-bold text-pink-600">Total: $<span x-text="parseFloat(selectedSale.total || 0).toFixed(2)"></span></p>
            </div>

            <div class="flex justify-end">
                <button type="button" @click="openViewModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function saleForm() {
            return {
                openCreateModal: false,
                openViewModal: false,
                selectedSale: {},
                products: @json($products),
                items: [{ product_id: '', cantidad: 1, search: '', open: false }],
                cliente_nombre: '',
                metodo_pago: 'efectivo',
                descuento: 0,

                openModal() {
                    this.items = [{ product_id: '', cantidad: 1, search: '', open: false }];
                    this.cliente_nombre = '';
                    this.metodo_pago = 'efectivo';
                    this.descuento = 0;
                    this.openCreateModal = true;
                },

                addItem() {
                    this.items.push({ product_id: '', cantidad: 1, search: '', open: false });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                filteredProducts(item) {
                    if (!item.search) return this.products;
                    const term = item.search.toLowerCase();
                    return this.products.filter(p => {
                        const presentacion = `${p.presentacion_valor ?? ''} ${p.presentacion_unidad ?? ''}`.toLowerCase();
                        return p.producto.toLowerCase().includes(term)
                            || (p.marca && p.marca.toLowerCase().includes(term))
                            || presentacion.includes(term);
                    });
                },

                productLabel(p) {
                    let label = p.producto;
                    if (p.presentacion_valor) {
                        const valor = parseFloat(p.presentacion_valor);
                        label += ` — ${valor % 1 === 0 ? valor.toFixed(0) : valor} ${p.presentacion_unidad ?? ''}`;
                    }
                    return label;
                },

                selectProduct(item, product) {
                    item.product_id = product.id;
                    item.search = this.productLabel(product);
                    item.open = false;
                },

                lineTotal(item) {
                    const product = this.products.find(p => p.id === item.product_id);
                    if (!product || !item.cantidad) return 0;
                    return parseFloat(product.precio_venta) * item.cantidad;
                },

                subtotal() {
                    return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
                },

                total() {
                    return Math.max(this.subtotal() - (parseFloat(this.descuento) || 0), 0);
                },

                viewSale(sale) {
                    this.selectedSale = sale;
                    this.openViewModal = true;
                },
            };
        }
    </script>

</body>
</html>