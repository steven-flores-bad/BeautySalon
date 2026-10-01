{{-- Notificación flotante de éxito: se oculta sola a los 5 segundos --}}
@if(session('success'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-[-0.5rem]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-[100] w-[calc(100%-2rem)] max-w-sm bg-white border border-emerald-200 rounded-xl shadow-lg overflow-hidden"
         role="status">
        <div class="flex items-start gap-3 p-4">
            <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="flex-1 text-sm text-gray-700 font-medium">{{ session('success') }}</p>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="h-1 bg-emerald-100">
            <div class="h-full bg-emerald-500 flash-progress"></div>
        </div>
    </div>
    <style>
        .flash-progress { width: 100%; animation: flash-shrink 5s linear forwards; }
        @keyframes flash-shrink { from { width: 100%; } to { width: 0%; } }
    </style>
@endif
