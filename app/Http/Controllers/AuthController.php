<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Pantalla de inicio de sesión.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Valida las credenciales. Tras 5 intentos fallidos con el mismo correo
     * desde la misma IP, se bloquea el acceso por un minuto.
     */
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $clave = Str::lower($credenciales['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Intenta de nuevo en {$segundos} segundos.",
            ]);
        }

        // Solo usuarios activos pueden entrar. Sin "recordarme": la sesión
        // debe cerrarse tras unos minutos de inactividad (SESSION_LIFETIME).
        if (!Auth::attempt([...$credenciales, 'activo' => true])) {
            RateLimiter::hit($clave, 60);

            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña son incorrectos.',
            ]);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return redirect()->intended(route('inicio'));
    }

    /**
     * Cerrar sesión (manual o automática por inactividad).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redireccion = redirect()->route('login');

        return $request->boolean('inactividad')
            ? $redireccion->with('status', 'Tu sesión se cerró por inactividad.')
            : $redireccion;
    }

    /**
     * La página lo llama mientras el usuario está activo para que la
     * sesión del servidor no venza.
     */
    public function ping()
    {
        return response()->noContent();
    }
}
