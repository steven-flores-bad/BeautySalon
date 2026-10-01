<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si un usuario es desactivado mientras tiene la sesión abierta,
 * se le cierra la sesión en su siguiente acción.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                             ->withErrors(['email' => 'Tu usuario fue desactivado. Habla con el administrador.']);
        }

        return $next($request);
    }
}
