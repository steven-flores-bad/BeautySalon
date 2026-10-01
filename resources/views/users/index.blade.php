@extends('layouts.app')

@section('title', 'Usuarios - JulySalon')
@section('max-width', 'max-w-5xl')

@section('body-data')
{ openCreateModal: {{ $errors->any() && !old('_edit_id') ? 'true' : 'false' }}, openEditModal: {{ old('_edit_id') ? 'true' : 'false' }}, editForm: {{ Illuminate\Support\Js::from(old('_edit_id') ? ['id' => old('_edit_id'), 'name' => old('name'), 'email' => old('email')] : new stdClass) }} }
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

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-900 w-full">Usuarios</h1>

        <form method="GET" action="{{ route('users.index') }}" class="w-full sm:w-72">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o correo..."
                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-sm">
        </form>

        <button @click="openCreateModal = true" class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white font-medium px-5 py-2 rounded-lg shadow transition text-sm flex items-center justify-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Usuario
        </button>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 font-semibold">Nombre</th>
                        <th class="py-3 px-4 font-semibold">Correo</th>
                        <th class="py-3 px-4 font-semibold text-center">Estado</th>
                        <th class="py-3 px-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse ($users as $usuario)
                        <tr class="hover:bg-gray-50/50 transition {{ $usuario->activo ? '' : 'opacity-60' }}">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full {{ $usuario->activo ? 'bg-pink-600' : 'bg-gray-400' }} text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ $usuario->iniciales() }}
                                    </div>
                                    <span class="text-gray-900 font-semibold">{{ $usuario->name }}</span>
                                    @if($usuario->is(auth()->user()))
                                        <span class="text-xs text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full">Tú</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">{{ $usuario->email }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $usuario->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                                <button @click="openEditModal = true; editForm = {{ Illuminate\Support\Js::from($usuario->only('id', 'name', 'email')) }}"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-2.5 py-1 rounded-md transition">
                                    Editar
                                </button>
                                @unless($usuario->is(auth()->user()))
                                    @if($usuario->activo)
                                        <form action="{{ route('users.toggle', $usuario) }}" method="POST" class="inline-block" @submit.prevent="$dispatch('confirm-action', { form: $el, type: 'cancel', title: 'Desactivar usuario', question: '¿Estás seguro de desactivar a', name: @js($usuario->name), message: 'Ya no podrá iniciar sesión. Sus ventas se conservan.', confirmText: 'Sí, desactivar' })">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 px-2.5 py-1 rounded-md transition">
                                                Desactivar
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.toggle', $usuario) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-900 font-medium text-xs bg-emerald-50 px-2.5 py-1 rounded-md transition">
                                                Activar
                                            </button>
                                        </form>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-400">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
@endsection

@section('modals')
    <!-- MODAL CREAR -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative" @click.away="openCreateModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Nuevo Usuario</h3>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="name" value="{{ old('_edit_id') ? '' : old('name') }}" required placeholder="Ej. María López" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Correo electrónico *</label>
                        <input type="email" name="email" value="{{ old('_edit_id') ? '' : old('email') }}" required autocomplete="off" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        <p class="text-xs text-gray-400 mt-1">Con este correo iniciará sesión.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Contraseña *</label>
                            <input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Repetir contraseña *</label>
                            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Mínimo 8 caracteres.</p>
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Usuario</h3>
            <form :action="'/users/' + editForm.id" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_edit_id" :value="editForm.id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="name" x-model="editForm.name" required class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Correo electrónico *</label>
                        <input type="email" name="email" x-model="editForm.email" required autocomplete="off" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <p class="text-xs font-medium text-gray-700 mb-2">Cambiar contraseña <span class="font-normal text-gray-400">(déjalo vacío para conservar la actual)</span></p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input type="password" name="password" minlength="8" placeholder="Nueva contraseña" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                            <input type="password" name="password_confirmation" minlength="8" placeholder="Repetir contraseña" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="openEditModal = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
