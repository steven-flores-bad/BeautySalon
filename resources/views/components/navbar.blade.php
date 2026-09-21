<nav class="bg-gray-50 border-b border-gray-200 sticky top-0 z-40 shadow-sm" x-data="{ mobileMenuOpen: false, profileDropdown: false, reportsDropdown: false, servicesDropdown: false, salesDropdown: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- Logo y Enlaces Principales -->
            <div class="flex items-center space-x-8">
                <!-- Logo / Nombre del Negocio -->
                <div class="flex-shrink-0 flex items-center">
                    <span class="text-xl font-extrabold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
                        ✨ BeautyControl
                    </span>
                </div>

                <!-- Menú de Escritorio -->
                <div class="hidden md:flex items-center space-x-2">
                    <!-- INICIO CON VALIDACIÓN DINÁMICA -->
                    <a href="{{ route('inicio') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('inicio') ? 'bg-white shadow-sm border border-gray-200 text-pink-600' : 'text-gray-700 hover:text-pink-600 hover:bg-pink-50' }}">
                        Inicio
                    </a>
                    
                    <!-- PRODUCTOS CON VALIDACIÓN DINÁMICA -->
                    <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('products.*') ? 'bg-white shadow-sm border border-gray-200 text-pink-600' : 'text-gray-700 hover:text-pink-600 hover:bg-pink-50' }}">
                        Productos
                    </a>

                    <!-- CATEGORIA CON VALIDACIÓN DINÁMICA -->
                    <a href="{{ route('categories.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('categories.*') ? 'bg-white shadow-sm border border-gray-200 text-pink-600' : 'text-gray-700 hover:text-pink-600 hover:bg-pink-50' }}">
                        Categoria
                    </a>

                    <!-- SERVICIOS CON DROPDOWN -->
                    <div class="relative" @click.away="servicesDropdown = false">
                        <button @click="servicesDropdown = !servicesDropdown"
                                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('services.*') || request()->routeIs('service-categories.*') ? 'bg-white shadow-sm border border-gray-200 text-pink-600' : 'text-gray-700 hover:text-pink-600 hover:bg-pink-50' }}">
                            Servicios
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': servicesDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="servicesDropdown" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('services.index') }}"
                               class="block px-4 py-2 text-sm {{ request()->routeIs('services.*') ? 'text-pink-600 bg-pink-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                Servicios
                            </a>
                            <a href="{{ route('service-categories.index') }}"
                               class="block px-4 py-2 text-sm {{ request()->routeIs('service-categories.*') ? 'text-pink-600 bg-pink-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                Categoría
                            </a>
                        </div>
                    </div>

                    <!-- VENTAS CON DROPDOWN (Ventas de productos y Ventas de servicios) -->
                    <div class="relative" @click.away="salesDropdown = false">
                        <button @click="salesDropdown = !salesDropdown"
                                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('sales.products.*') || request()->routeIs('sales.services.*') ? 'bg-white shadow-sm border border-gray-200 text-pink-600' : 'text-gray-700 hover:text-pink-600 hover:bg-pink-50' }}">
                            Ventas
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': salesDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- En la versión de escritorio -->
                        <div x-show="salesDropdown" x-cloak class="absolute left-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('sales.products.index') }}"
                            class="block px-4 py-2 text-sm {{ request()->routeIs('sales.products.*') ? 'text-pink-600 bg-pink-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                Ventas de productos
                            </a>
                            <a href="{{ route('service-sales.index') }}"
                            class="block px-4 py-2 text-sm {{ request()->routeIs('service-sales.*') ? 'text-pink-600 bg-pink-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                Ventas de servicios
                            </a>
                        </div>
                    </div>

                    <a href="#" class="px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:text-pink-600 hover:bg-pink-50 transition">
                        Gastos
                    </a>

                    <!-- REPORTES CON DROPDOWN -->
                    <div class="relative" @click.away="reportsDropdown = false">
                        <button @click="reportsDropdown = !reportsDropdown"
                                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-white shadow-sm border border-gray-200 text-pink-600' : 'text-gray-700 hover:text-pink-600 hover:bg-pink-50' }}">
                            Reportes
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': reportsDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="reportsDropdown" x-cloak class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('reports.sales') }}"
                               class="block px-4 py-2 text-sm {{ request()->routeIs('reports.sales') ? 'text-pink-600 bg-pink-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                Reporte de Ventas
                            </a>
                            <!-- NUEVA RUTA DE REPORTE DE SERVICIOS -->
                            <a href="{{ route('reports.services') }}"
                               class="block px-4 py-2 text-sm {{ request()->routeIs('reports.services') ? 'text-pink-600 bg-pink-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                Reporte de Servicios
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menú de Usuario / Perfil (Derecha) -->
            <div class="hidden md:flex items-center space-x-4">
                <div class="relative" @click.away="profileDropdown = false">
                    <button @click="profileDropdown = !profileDropdown" class="flex items-center space-x-3 focus:outline-none bg-white hover:bg-gray-100 p-1.5 rounded-full transition border border-gray-200 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-pink-600 text-white flex items-center justify-center font-bold text-xs">
                            AD
                        </div>
                        <span class="text-sm font-medium text-gray-700 pr-2">Administración</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown de perfil -->
                    <div x-show="profileDropdown" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Configuración</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form action="#" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Botón del Menú Móvil (Hamburguesa) -->
            <div class="flex items-center md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Menú Desplegable para Móviles -->
    <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-200 bg-white px-4 pt-2 pb-4 space-y-1">
        <a href="{{ route('inicio') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('inicio') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Inicio</a>
        <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('products.*') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Productos</a>
        <a href="{{ route('categories.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('categories.*') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Categoria</a>

        <!-- SERVICIOS MÓVIL -->
        <div class="pt-1">
            <p class="px-3 py-1 text-xs uppercase tracking-wide font-semibold text-gray-400">Servicios</p>
            <a href="{{ route('services.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('services.*') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Servicios</a>
            <a href="{{ route('service-categories.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('service-categories.*') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Categoría</a>
        </div>

        <!-- VENTAS MÓVIL -->
       <!-- En la versión para dispositivos móviles -->
            <div class="pt-1">
                <p class="px-3 py-1 text-xs uppercase tracking-wide font-semibold text-gray-400">Ventas</p>
                <a href="{{ route('sales.products.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('sales.products.*') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Ventas de productos</a>
                <a href="{{ route('service-sales.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('service-sales.*') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Ventas de servicios</a>
            </div>

        <a href="#" class="block px-3 py-2 rounded-lg text-base font-semibold text-gray-700 hover:bg-pink-50 hover:text-pink-600">Gastos</a>

        <!-- REPORTES MÓVIL -->
       <!-- REPORTES MÓVIL -->
        <div class="pt-1">
            <p class="px-3 py-1 text-xs uppercase tracking-wide font-semibold text-gray-400">Reportes</p>
            <a href="{{ route('reports.sales') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('reports.sales') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Reporte de Ventas</a>
            <a href="{{ route('reports.services') }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('reports.services') ? 'bg-gray-100 border border-gray-200 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">Reporte de Servicios</a>
        </div>

        <div class="border-t border-gray-200 pt-3 mt-3">
            <a href="#" class="block px-3 py-2 rounded-lg text-base font-medium text-red-600 hover:bg-red-50">Cerrar Sesión</a>
        </div>
    </div>
</nav>