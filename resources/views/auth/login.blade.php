<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - JulySalon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50 text-gray-800 font-sans antialiased flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
                ✨ JulySalon
            </h1>
            <p class="text-sm text-gray-500 mt-2">Inicia sesión para continuar</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8">
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                    <p class="text-sm text-red-700 font-medium">{{ $errors->first() }}</p>
                </div>
            @elseif (session('status'))
                <div class="mb-5 bg-amber-50 border-l-4 border-amber-500 p-3 rounded-r-lg">
                    <p class="text-sm text-amber-800 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST" x-data="{ showPassword: false, sending: false }" @submit="sending = true">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" type="password" id="password" name="password" required autocomplete="current-password"
                               class="w-full border border-gray-300 rounded-lg p-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-gray-600"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" :disabled="sending"
                        class="w-full bg-pink-600 hover:bg-pink-700 text-white font-medium py-2.5 rounded-lg shadow transition text-sm disabled:opacity-60"
                        x-text="sending ? 'Entrando...' : 'Iniciar sesión'">
                    Iniciar sesión
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">¿Olvidaste tu contraseña? Pídele al administrador que la cambie.</p>
    </div>

</body>
</html>
