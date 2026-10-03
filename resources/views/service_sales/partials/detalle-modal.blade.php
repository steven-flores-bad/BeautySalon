{{-- Ventana "Ver" con el detalle de una venta. Requiere en Alpine: openViewModal y selectedSale. --}}
<!-- MODAL VER DETALLE -->
<div x-show="openViewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl relative" @click.away="openViewModal = false">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Venta de Servicios <span x-text="'#' + selectedSale.id"></span></h3>
        <p class="text-xs text-gray-400 mb-1" x-text="selectedSale.cliente_nombre || 'Cliente general'"></p>
        <p class="text-xs text-gray-400 mb-4" x-show="selectedSale.notas" x-text="'Notas: ' + selectedSale.notas"></p>

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
