<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Listado de usuarios del sistema.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
                        return $query->where('name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orderByDesc('activo')
                    ->orderBy('name')
                    ->paginate(10)
                    ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Crear un usuario nuevo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create($validated);

        return redirect()->route('users.index')
                         ->with('success', 'Usuario "' . $validated['name'] . '" creado.');
    }

    /**
     * Editar nombre, correo y (opcionalmente) contraseña.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Si la contraseña se deja vacía, se conserva la actual.
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
                         ->with('success', 'Usuario "' . $user->name . '" actualizado.');
    }

    /**
     * Activar / desactivar. No se borran usuarios porque sus ventas y
     * cajas siguen ligadas a ellos para los reportes.
     */
    public function toggle(User $user)
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')
                             ->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $user->update(['activo' => !$user->activo]);

        return redirect()->route('users.index')
                         ->with('success', 'Usuario "' . $user->name . '" ' . ($user->activo ? 'activado' : 'desactivado') . '.');
    }
}
