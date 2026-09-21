<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas de Servicios - Salón de Belleza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased"
      x-data="serviceSaleForm()">

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

            @if($employees->isEmpty())
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-yellow-700 font-medium">No hay empleados activos registrados. Agrega al menos uno directamente en la base de datos antes de registrar una venta.</p>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900 w-full">Ventas de Servicios</h1>

                <form method="GET" action="{{ route('service-sales.index') }}" class="w-full sm:w-85">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por cliente o # de venta..."
                           class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
                </form>

                <button @click="openModal()" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Venta
                </button>
            </div>

            <!-- Tabla de Ventas de Servicios -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 font-semibold">#</th>
                                <th class="py-3 px-4 font-semibold">Fecha</th>
                                <th class="py-3 px-4 font-semibold">Cliente</th>
                                <th class="py-3 px-4 font-semibold text-center">Servicios</th>
                                <th class="py-3 px-4 font-semibold">Pago</th>
                                <th class="py-3 px-4 font-semibold text-right">Total</th>
                                <th class="py-3 px-4 font-semibold text-center">Estado</th>
                                <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($serviceSales as $venta)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4 text-gray-500 font-mono text-xs">#{{ $venta->id }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $venta->cliente_nombre ?? 'Cliente general' }}</td>
                                    <td class="py-3 px-4 text-center text-gray-600">{{ $venta->details_count }}</td>
                                    <td class="py-3 px-4 text-gray-600 capitalize">{{ $venta->metodo_pago }}</td>
                                    <td class="py-3 px-4 text-right text-pink-600 font-semibold">${{ number_format($venta->total, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $venta->estado === 'completada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ ucfirst($venta->estado) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button @click="viewSale({{ Illuminate\Support\Js::from($venta) }})"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                            Ver
                                        </button>
                                        @if($venta->estado === 'completada')
                                            <form action="{{ route('service-sales.destroy', $venta->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Cancelar esta venta de servicios?');">
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
                                    <td colspan="8" class="text-center py-8 text-gray-400">No hay ventas de servicios registradas todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $serviceSales->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL NUEVA VENTA -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Nueva Venta de Servicios</h3>

            <form action="{{ route('service-sales.store') }}" method="POST">
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

                <!-- Líneas de servicio dinámicas -->
                <div class="border border-gray-200 rounded-lg mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                                <th class="text-left py-2 px-3 rounded-tl-lg">Servicio</th>
                                <th class="text-left py-2 px-3 w-36">Atendió</th>a
                                <th class="text-center py-2 px-3 w-16">Cant.</th>
                                <th class="text-center py-2 px-3 w-20">Desc. ($)</th>
                                <th class="text-center py-2 px-3 w-20">Com. (%)</th>
                                <th class="text-right py-2 px-3 w-24">Subtotal</th>
                                <th class="w-10 rounded-tr-lg"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 px-3 relative" @click.away="item.open = false">
                                        <input type="hidden" :name="`servicios[${index}][service_id]`" :value="item.service_id">
                                        <input type="text"
                                               x-model="item.search"
                                               @focus="item.open = true"
                                               @input="item.service_id = ''; item.open = true"
                                               placeholder="Buscar servicio..."
                                               autocomplete="off"
                                               class="w-full border border-gray-300 rounded-lg p-1.5 text-sm focus:ring-pink-500 focus:border-pink-500">

                                        <div x-show="item.open && filteredServices(item).length > 0" x-cloak
                                             class="absolute z-20 mt-1 w-64 max-h-56 overflow-y-auto bg-white border border-gray-300 rounded-lg shadow-2xl">
                                            <template x-for="s in filteredServices(item)" :key="s.id">
                                                <button type="button"
                                                        @click="selectService(item, s)"
                                                        class="w-full text-left px-3 py-2.5 text-sm hover:bg-pink-50 border-b border-gray-100 last:border-b-0">
                                                    <span class="font-medium text-gray-800" x-text="s.nombre"></span>
                                                    <span class="block text-xs text-gray-400">
                                                        $<span x-text="parseFloat(s.precio).toFixed(2)"></span>
                                                    </span>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <select :name="`servicios[${index}][employee_id]`" x-model.number="item.employee_id" required class="w-full border border-gray-300 rounded-lg p-1.5 text-sm">
                                            <option value="" disabled>Empleado</option>
                                            <template x-for="e in employees" :key="e.id">
                                                <option :value="e.id" x-text="e.nombre"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" :name="`servicios[${index}][cantidad]`" x-model.number="item.cantidad" min="1" required class="w-full border border-gray-300 rounded-lg p-1.5 text-sm text-center">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" step="0.01" min="0" :name="`servicios[${index}][descuento]`" x-model.number="item.descuento" placeholder="0.00" class="w-full border border-gray-300 rounded-lg p-1.5 text-sm text-center">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="number" step="0.01" min="0" max="100" :name="`servicios[${index}][comision_porcentaje]`" x-model.number="item.comision_porcentaje" required placeholder="0" class="w-full border border-gray-300 rounded-lg p-1.5 text-sm text-center">
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
                        + Agregar servicio
                    </button>
                </div>

                <div class="flex justify-end mb-4">
                    <div class="text-right space-y-0.5">
                        <p class="text-xs text-gray-500">Subtotal: <span x-text="'$' + subtotal().toFixed(2)"></span></p>
                        <p class="text-xs text-gray-500" x-show="totalDescuento() > 0">Descuento total: <span class="text-red-500" x-text="'-$' + totalDescuento().toFixed(2)"></span></p>
                        <p class="text-xs text-gray-500">Comisiones totales: <span class="text-indigo-500" x-text="'$' + totalComision().toFixed(2)"></span></p>
                        <span class="text-lg font-bold text-pink-600">Total a cobrar: <span x-text="'$' + total().toFixed(2)"></span></span>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Registrar Venta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL VER DETALLE -->
    <div x-show="openViewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl relative" @click.away="openViewModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Venta de Servicios <span x-text="'#' + selectedSale.id"></span></h3>
            <p class="text-xs text-gray-400 mb-4" x-text="selectedSale.cliente_nombre || 'Cliente general'"></p>

            <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                            <th class="text-left py-2 px-3">Servicio</th>
                            <th class="text-left py-2 px-3">Atendió</th>
                            <th class="text-center py-2 px-3">Cant.</th>
                            <th class="text-right py-2 px-3">Desc.</th>
                            <th class="text-right py-2 px-3">Subtotal</th>
                            <th class="text-right py-2 px-3">Comisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="detail in (selectedSale.details || [])" :key="detail.id">
                            <tr class="border-t border-gray-100">
                                <td class="py-2 px-3" x-text="detail.service ? detail.service.nombre : 'Servicio eliminado'"></td>
                                <td class="py-2 px-3" x-text="detail.employee ? detail.employee.nombre : '—'"></td>
                                <td class="py-2 px-3 text-center" x-text="detail.cantidad"></td>
                                <td class="py-2 px-3 text-right" x-text="detail.descuento > 0 ? '-$' + parseFloat(detail.descuento).toFixed(2) : '—'"></td>
                                <td class="py-2 px-3 text-right" x-text="'$' + parseFloat(detail.subtotal).toFixed(2)"></td>
                                <td class="py-2 px-3 text-right text-indigo-600" x-text="'$' + parseFloat(detail.comision_monto).toFixed(2) + ' (' + parseFloat(detail.comision_porcentaje) + '%)'"></td>
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
        function serviceSaleForm() {
            return {
                openCreateModal: false,
                openViewModal: false,
                selectedSale: {},
                services: @json($services),
                employees: @json($employees),
                items: [{ service_id: '', employee_id: '', cantidad: 1, descuento: 0, comision_porcentaje: 0, search: '', open: false }],
                cliente_nombre: '',
                metodo_pago: 'efectivo',

                openModal() {
                    this.items = [{ service_id: '', employee_id: '', cantidad: 1, descuento: 0, comision_porcentaje: 0, search: '', open: false }];
                    this.cliente_nombre = '';
                    this.metodo_pago = 'efectivo';
                    this.openCreateModal = true;
                },

                addItem() {
                    this.items.push({ service_id: '', employee_id: '', cantidad: 1, descuento: 0, comision_porcentaje: 0, search: '', open: false });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                filteredServices(item) {
                    if (!item.search) return this.services;
                    const term = item.search.toLowerCase();
                    return this.services.filter(s => s.nombre.toLowerCase().includes(term));
                },

                selectService(item, service) {
                    item.service_id = service.id;
                    item.search = service.nombre;
                    item.open = false;
                },

                lineTotal(item) {
                    const service = this.services.find(s => s.id === item.service_id);
                    if (!service || !item.cantidad) return 0;
                    const bruto = parseFloat(service.precio) * item.cantidad;
                    return Math.max(bruto - (parseFloat(item.descuento) || 0), 0);
                },

                subtotal() {
                    return this.items.reduce((sum, item) => {
                        const service = this.services.find(s => s.id === item.service_id);
                        if (!service || !item.cantidad) return sum;
                        return sum + (parseFloat(service.precio) * item.cantidad);
                    }, 0);
                },

                totalDescuento() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.descuento) || 0), 0);
                },

                totalComision() {
                    return this.items.reduce((sum, item) => {
                        return sum + (this.lineTotal(item) * ((parseFloat(item.comision_porcentaje) || 0) / 100));
                    }, 0);
                },

                total() {
                    return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
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
