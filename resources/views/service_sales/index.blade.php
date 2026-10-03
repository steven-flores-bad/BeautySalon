@extends('layouts.app')

@section('title', 'Ventas de Servicios - JulySalon')

@section('body-data')
serviceSaleForm()
@endsection

@section('content')
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

    @include('components.caja-cerrada-alert')

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="w-full">
            <h1 class="text-2xl font-bold text-gray-900">Ventas de Servicios de Hoy</h1>
            <p class="text-sm text-gray-400">{{ ucfirst(today()->translatedFormat('l, d \d\e F \d\e Y')) }}</p>
        </div>

        <a href="{{ route('service-sales.history') }}" class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg shadow-sm transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Historial
        </a>

        <button @click="openModal()" @disabled(!$cajaAbierta) title="{{ $cajaAbierta ? '' : 'Abre la caja para poder vender' }}" class="disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva Venta
        </button>
    </div>

    <!-- Filtros: buscador y empleada (solo ventas de hoy) -->
    <form method="GET" action="{{ route('service-sales.index') }}" class="flex flex-wrap items-center gap-2 mb-6">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por cliente o # de venta..."
               class="flex-1 min-w-[200px] px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">

        <select name="employee_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
            <option value="">Todas las empleadas</option>
            @foreach ($employees as $empleado)
                <option value="{{ $empleado->id }}" {{ (string) ($employeeFiltro ?? '') === (string) $empleado->id ? 'selected' : '' }}>{{ $empleado->nombre }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Buscar</button>

        @if($search || $employeeFiltro)
            <a href="{{ route('service-sales.index') }}" class="text-xs text-pink-600 hover:underline whitespace-nowrap">Quitar filtros</a>
        @endif
    </form>

    @include('service_sales.partials.tabla', ['mensajeVacio' => 'No hay ventas de servicios registradas hoy.'])
@endsection

@section('modals')
    <!-- MODAL NUEVA VENTA / EDITAR VENTA -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900" :class="editingId ? 'mb-1' : 'mb-4'" x-text="editingId ? 'Editar venta de servicios #' + editingId : 'Nueva Venta de Servicios'"></h3>
            <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 mb-4" x-show="editingId">
                Corrige los datos y guarda. Puedes quitar servicios con la ✕: su precio y su comisión se restan de la venta y de la caja.
                Los servicios que ya estaban conservan el precio con que se vendieron.
            </p>

            <form :action="editingId ? '{{ url('service-sales') }}/' + editingId : '{{ route('service-sales.store') }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PUT" :disabled="!editingId">

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
                                <th class="text-left py-2 px-3 w-36">Atendió</th>
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

                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notas (opcional)</label>
                    <textarea name="notas" x-model="notas" rows="2" placeholder="Ej. cliente pidió reagendar, alergia a producto X, etc." class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                </div>

                <div class="flex justify-end mb-4">
                    <div class="text-right space-y-0.5">
                        <p class="text-xs text-gray-500">Subtotal: <span x-text="'$' + subtotal().toFixed(2)"></span></p>
                        <p class="text-xs text-gray-500" x-show="totalDescuento() > 0">Descuento total: <span class="text-red-500" x-text="'-$' + totalDescuento().toFixed(2)"></span></p>                        <p class="text-xs text-gray-500">Comisiones totales: <span class="text-indigo-500" x-text="'$' + totalComision().toFixed(2)"></span></p>
                        <span class="text-lg font-bold text-pink-600">Total a cobrar: <span x-text="'$' + total().toFixed(2)"></span></span>
                        <p class="text-xs" x-show="editingId" :class="total() < totalAnterior ? 'text-red-500' : 'text-gray-500'">
                            Total anterior: <span x-text="'$' + totalAnterior.toFixed(2)"></span>
                            <span x-show="Math.abs(total() - totalAnterior) >= 0.01" x-text="'(' + (total() < totalAnterior ? '−' : '+') + '$' + Math.abs(total() - totalAnterior).toFixed(2) + ')'"></span>
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="text-white px-5 py-2 rounded-lg text-sm font-medium shadow"
                            :class="editingId ? 'bg-amber-600 hover:bg-amber-700' : 'bg-pink-600 hover:bg-pink-700'"
                            x-text="editingId ? 'Guardar cambios' : 'Registrar Venta'">Registrar Venta</button>
                </div>
            </form>
        </div>
    </div>

    @include('service_sales.partials.detalle-modal')
@endsection

@push('scripts')
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
                notas: '',
                editingId: null,
                totalAnterior: 0,
                preciosAnteriores: {},

                openModal() {
                    this.editingId = null;
                    this.preciosAnteriores = {};
                    this.items = [{ service_id: '', employee_id: '', cantidad: 1, descuento: 0, comision_porcentaje: 0, search: '', open: false }];
                    this.cliente_nombre = '';
                    this.metodo_pago = 'efectivo';
                    this.notas = '';
                    this.openCreateModal = true;
                },

                // Abre el mismo formulario con los datos de una venta para corregirla.
                editSale(venta) {
                    this.editingId = venta.id;
                    this.totalAnterior = venta.total;
                    this.cliente_nombre = venta.cliente_nombre || '';
                    this.metodo_pago = venta.metodo_pago;
                    this.notas = venta.notas || '';
                    this.preciosAnteriores = {};
                    venta.items.forEach(item => { this.preciosAnteriores[item.service_id] = item.precio; });
                    this.items = venta.items.map(item => ({ ...item, open: false }));
                    this.openCreateModal = true;
                },

                // Precio de la línea: al editar, los servicios que ya estaban en la
                // venta conservan su precio original (igual que en el servidor).
                precioDe(item) {
                    if (this.editingId && this.preciosAnteriores[item.service_id] !== undefined) {
                        return parseFloat(this.preciosAnteriores[item.service_id]);
                    }
                    const service = this.services.find(s => s.id === item.service_id);
                    return service ? parseFloat(service.precio) : 0;
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
                    if (!item.service_id || !item.cantidad) return 0;
                    const bruto = this.precioDe(item) * item.cantidad;
                    return Math.max(bruto - (parseFloat(item.descuento) || 0), 0);
                },

                subtotal() {
                    return this.items.reduce((sum, item) => {
                        if (!item.service_id || !item.cantidad) return sum;
                        return sum + (this.precioDe(item) * item.cantidad);
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
@endpush
