<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo la página de WordPress (que conoce la clave secreta) puede enviar
 * citas. La clave va en el encabezado "X-Api-Key" de la petición.
 */
class VerifyWordpressToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Las respuestas de la API siempre en JSON (también los errores).
        $request->headers->set('Accept', 'application/json');

        $token = (string) config('services.wordpress.token');
        $enviado = (string) $request->header('X-Api-Key');

        if ($token === '' || !hash_equals($token, $enviado)) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        return $next($request);
    }
}
