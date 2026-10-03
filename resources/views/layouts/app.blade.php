{{--
    Layout principal del sistema.
    Secciones disponibles en las vistas:
      @section('title')       Título de la pestaña
      @section('body-data')   Estado Alpine (x-data) del <body>, ej. { openCreateModal: false }
      @section('max-width')   Ancho del contenido (por defecto max-w-6xl)
      @section('body-class')  Fondo del <body> (por defecto bg-gray-50)
      @section('content')     Contenido principal
      @section('modals')      Ventanas emergentes de la página
      @push('scripts')        JavaScript propio de la página
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'JulySalon')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js para modales, menús y formularios -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <!-- Calendario en español con formato dd/mm/aaaa (no depende del idioma del navegador) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
    @stack('styles')
</head>
<body class="@yield('body-class', 'bg-gray-50') text-gray-800 font-sans antialiased" @hasSection('body-data') x-data="{!! trim($__env->yieldContent('body-data')) !!}" @endif>

    <div class="min-h-screen flex flex-col">

        <!-- BARRA DE NAVEGACIÓN GLOBAL -->
        @include('components.navbar')

        <!-- Contenido Principal -->
        <main class="flex-grow @yield('max-width', 'max-w-6xl') w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @include('components.flash')

            @yield('content')
        </main>
    </div>

    @yield('modals')

    @include('components.confirm-modal')

    @include('components.session-timeout')

    <script>
        // Campos de fecha: se muestran como dd/mm/aaaa, pero al servidor se
        // envían como aaaa-mm-dd (lo que esperan los controladores).
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[data-fecha]').forEach(campo => {
                flatpickr(campo, {
                    locale: 'es',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: true,
                    disableMobile: true,
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
