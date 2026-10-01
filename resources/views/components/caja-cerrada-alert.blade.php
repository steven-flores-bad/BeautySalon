{{-- Aviso en las pantallas de ventas cuando no hay caja abierta hoy --}}
@if(!$cajaAbierta)
    <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>
                <p class="text-sm text-amber-800 font-bold">La caja está cerrada</p>
                <p class="text-xs text-amber-700">Para registrar ventas primero debes abrir la caja del día.</p>
            </div>
        </div>
        <a href="{{ route('cash-register.index') }}" class="shrink-0 text-center bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow transition">
            Ir a Caja
        </a>
    </div>
@endif
