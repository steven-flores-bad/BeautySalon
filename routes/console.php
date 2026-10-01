<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Crear un usuario desde la terminal (útil para el primer acceso):
//   php artisan usuario:crear
Artisan::command('usuario:crear', function () {
    $nombre = text('Nombre', placeholder: 'Ej. María López', required: true);

    $email = text('Correo electrónico', required: true, validate: fn (string $valor) => match (true) {
        !filter_var($valor, FILTER_VALIDATE_EMAIL) => 'El correo no es válido.',
        User::where('email', $valor)->exists() => 'Ya existe un usuario con ese correo.',
        default => null,
    });

    $contrasena = password('Contraseña (mínimo 8 caracteres)', required: true,
        validate: fn (string $valor) => strlen($valor) < 8 ? 'Debe tener al menos 8 caracteres.' : null);

    $confirmacion = password('Repite la contraseña', required: true);

    if ($contrasena !== $confirmacion) {
        $this->error('Las contraseñas no coinciden. No se creó el usuario.');
        return 1;
    }

    User::create(['name' => $nombre, 'email' => $email, 'password' => $contrasena]);

    $this->info("Usuario \"{$nombre}\" creado. Ya puedes iniciar sesión con {$email}.");
})->purpose('Crear un usuario para iniciar sesión en JulySalon');
