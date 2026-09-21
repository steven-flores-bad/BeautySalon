<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeautyControl - Inicio</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js para los menús desplegables -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <!-- LLAMANDO AL MENÚ DE NAVEGACIÓN AQUÍ -->
@include('components.navbar')

    <!-- CONTENIDO DE MUESTRA -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">¡Bienvenido a BeautyControl! ✨</h1>
           <p class="text-gray-600 mt-2">El menú se ha cargado correctamente mediante include y el botón de <strong>Inicio</strong> muestra el fondo blanco.</p>
        </div>
    </main>

</body>
</html>