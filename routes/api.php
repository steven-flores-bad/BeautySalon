<?php

use App\Http\Controllers\Api\AppointmentController;
use Illuminate\Support\Facades\Route;

// Citas que los clientes reservan desde la página de WordPress.
// URL: POST /api/v1/citas/recibir  (encabezado X-Api-Key con la clave secreta)
// Máximo 30 peticiones por minuto para evitar abusos.
Route::middleware(['wordpress', 'throttle:30,1'])->group(function () {
    Route::post('/v1/citas/recibir', [AppointmentController::class, 'store'])->name('api.citas.recibir');
});
