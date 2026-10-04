<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'activo' => \App\Http\Middleware\EnsureUserIsActive::class,
            'wordpress' => \App\Http\Middleware\VerifyWordpressToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Si la sesión venció (inactividad) y se envía un formulario, en lugar
        // de la página "419 Page Expired" se manda al login con un aviso.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419 && !$request->expectsJson()) {
                return redirect()->route('login')
                                 ->with('status', 'Tu sesión expiró por inactividad. Inicia sesión de nuevo.');
            }
        });
    })->create();
