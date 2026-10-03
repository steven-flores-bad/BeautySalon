{{-- Tabla de ventas de servicios (lista del día e historial). Requiere $serviceSales; opcional $mensajeVacio. --}}
<!-- Tabla de Ventas de Servicios -->
<div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                    <th class="py-3 px-4 font-semibold">#</th>
                    <th class="py-3 px-4 font-semibold">Fecha</th>
                    <th class="py-3 px-4 font-semibold">Cliente</th>
                    <th class="py-3 px-4 font-semibold">Empleados</th>
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
                        <td class="py-3 px-4 text-gray-600 text-xs">
                            {{ $venta->details->pluck('employee.nombre')->filter()->unique()->implode(', ') ?: '—' }}
                        </td>
                        <td class="py-3 px-4 text-center text-gray-600">{{ $venta->details_sum_cantidad ?? 0 }}</td>
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
                            @if($venta->sePuedeCancelar())
                                <button @click="editSale({{ Illuminate\Support\Js::from([
                                            'id' => $venta->id,
                                            'cliente_nombre' => $venta->cliente_nombre,
                                            'metodo_pago' => $venta->metodo_pago,
                                            'notas' => $venta->notasEditables(),
                                            'total' => (float) $venta->total,
                                            'items' => $venta->details->map(fn ($d) => [
                                                'service_id' => $d->service_id,
                                                'employee_id' => $d->employee_id,
                                                'cantidad' => $d->cantidad,
                                                'descuento' => (float) $d->descuento,
                                                'comision_porcentaje' => (float) $d->comision_porcentaje,
                                                'precio' => (float) $d->precio,
                                                'search' => $d->service->nombre ?? '',
                                            ])->values(),
                                        ]) }})"
                                        class="text-amber-700 hover:text-amber-900 font-medium text-xs bg-amber-50 px-2.5 py-1 rounded-md transition">
                                    Editar
                                </button>
                                <form action="{{ route('service-sales.destroy', $venta->id) }}" method="POST" class="inline-block" @submit.prevent="$dispatch('confirm-action', { form: $el, type: 'cancel', title: 'Cancelar venta de servicios', question: '¿Estás seguro de cancelar la venta', name: '#{{ $venta->id }}', message: 'La venta quedará marcada como cancelada.', confirmText: 'Sí, cancelar venta' })">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 px-2.5 py-1 rounded-md transition">
                                        Cancelar
                                    </button>
                                </form>
                            @elseif($venta->estado === 'completada')
                                <span class="text-gray-400 text-xs px-2.5 py-1" title="Solo se pueden cancelar ventas de la caja de hoy mientras está abierta">No cancelable</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-400">{{ $mensajeVacio ?? 'No hay ventas de servicios.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">
        {{ $serviceSales->links() }}
    </div>
</div>
