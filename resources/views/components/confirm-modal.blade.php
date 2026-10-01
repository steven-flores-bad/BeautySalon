{{--
    Ventana de confirmación reutilizable (eliminar / cancelar).
    Uso en un formulario:
        <form ... @submit.prevent="$dispatch('confirm-action', {
            form: $el, type: 'delete', title: 'Eliminar producto',
            name: 'Shampoo', message: 'Esta acción no se puede deshacer.'
        })">
    Muestra: "¿Estás seguro de eliminar <b>Shampoo</b>? Esta acción no se puede deshacer."
    type: 'delete' (ícono de basura) o 'cancel' (ícono de advertencia).
--}}
<div x-data="{
        open: false,
        sending: false,
        form: null,
        type: 'delete',
        title: '',
        question: '',
        name: '',
        message: '',
        confirmText: 'Sí, eliminar',
        show(d) {
            this.form = d.form;
            this.type = d.type || 'delete';
            this.title = d.title || 'Confirmar acción';
            this.question = d.question || (this.type === 'cancel' ? '¿Estás seguro de cancelar' : '¿Estás seguro de eliminar');
            this.name = d.name || '';
            this.message = d.message || 'Esta acción no se puede deshacer.';
            this.confirmText = d.confirmText || (this.type === 'cancel' ? 'Sí, cancelar' : 'Sí, eliminar');
            this.sending = false;
            this.open = true;
        },
        confirm() {
            if (!this.form || this.sending) return;
            this.sending = true;
            Alpine.raw(this.form).submit();
        }
     }"
     @confirm-action.window="show($event.detail)"
     @keydown.escape.window="if (!sending) open = false"
     x-show="open"
     x-cloak
     x-transition.opacity
     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative"
         @click.away="if (!sending) open = false"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-start gap-4 mb-5">
            <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center"
                 :class="type === 'cancel' ? 'bg-amber-100' : 'bg-red-100'">
                {{-- Ícono eliminar --}}
                <svg x-show="type !== 'cancel'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{-- Ícono cancelar --}}
                <svg x-show="type === 'cancel'" class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900" x-text="title"></h3>
                <p class="text-sm text-gray-500 mt-1">
                    <span x-text="question"></span>
                    <span class="font-semibold text-gray-800" x-text="name"></span>?
                    <span x-text="message"></span>
                </p>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button type="button" @click="open = false" :disabled="sending"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition disabled:opacity-50">
                No, volver
            </button>
            <button type="button" @click="confirm()" :disabled="sending"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow transition disabled:opacity-60"
                    x-text="sending ? 'Procesando...' : confirmText">
            </button>
        </div>
    </div>
</div>
