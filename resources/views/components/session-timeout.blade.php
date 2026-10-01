{{--
    Cierre de sesión por inactividad.
    - Detecta mouse, teclado, scroll y toques. Si no hay actividad durante
      SESSION_LIFETIME minutos, cierra la sesión y manda al login.
    - Un minuto antes muestra un aviso con cuenta regresiva.
    - Mientras hay actividad, avisa al servidor (ping) cada minuto para que
      la sesión no venza aunque el usuario no envíe formularios.
    - La última actividad se comparte entre pestañas (localStorage): usar el
      sistema en una pestaña mantiene viva la sesión en las demás.
--}}
@auth
<div x-data="sessionTimeout({
        limite: {{ (int) config('session.lifetime') * 60 }},
        aviso: 60,
        pingUrl: @js(route('session.ping')),
        loginUrl: @js(route('login')),
     })"
     x-init="iniciar()">

    <form x-ref="logout" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="inactividad" value="1">
    </form>

    <div x-show="mostrarAviso" x-cloak x-transition.opacity
         class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">¿Sigues ahí?</h3>
            <p class="text-sm text-gray-500 mt-1">
                Por inactividad, tu sesión se cerrará en
                <span class="font-bold text-gray-800" x-text="restante"></span> segundos.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" @click="cerrar()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Cerrar sesión
                </button>
                <button type="button" @click="seguir()" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow transition">
                    Seguir conectado
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function sessionTimeout({ limite, aviso, pingUrl, loginUrl }) {
        const CLAVE = 'julysalon.ultimaActividad';

        return {
            mostrarAviso: false,
            restante: aviso,
            ultimaActividad: Date.now(),
            ultimoPing: Date.now(),
            ultimaMarca: 0,
            cerrando: false,

            iniciar() {
                this.guardar(this.ultimaActividad);

                ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'wheel'].forEach(evento =>
                    window.addEventListener(evento, () => this.actividad(), { passive: true })
                );

                setInterval(() => this.revisar(), 1000);
            },

            // Cualquier interacción (ignorada mientras se muestra el aviso:
            // ahí hay que pulsar "Seguir conectado").
            actividad() {
                if (this.mostrarAviso || this.cerrando) return;

                const ahora = Date.now();
                if (ahora - this.ultimaMarca < 5000) return; // no más de una vez cada 5 s
                this.ultimaMarca = ahora;
                this.ultimaActividad = ahora;
                this.guardar(ahora);

                if (ahora - this.ultimoPing > 60000) this.ping();
            },

            seguir() {
                this.mostrarAviso = false;
                this.ultimaMarca = 0;
                this.actividad();
                this.ping();
            },

            revisar() {
                if (this.cerrando) return;

                // Se toma la actividad más reciente de cualquier pestaña.
                const ultima = Math.max(this.ultimaActividad, this.leer());
                const inactivo = Math.floor((Date.now() - ultima) / 1000);

                if (inactivo >= limite) {
                    this.cerrar();
                } else if (inactivo >= limite - aviso) {
                    this.mostrarAviso = true;
                    this.restante = limite - inactivo;
                } else {
                    this.mostrarAviso = false;
                }
            },

            async ping() {
                this.ultimoPing = Date.now();
                try {
                    const respuesta = await fetch(pingUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    // La sesión ya no es válida en el servidor: ir al login.
                    if (respuesta.status === 401 || respuesta.status === 419) {
                        this.cerrando = true;
                        window.location.href = loginUrl;
                    }
                } catch (e) {
                    // Sin conexión: se reintenta con la siguiente actividad.
                }
            },

            cerrar() {
                if (this.cerrando) return;
                this.cerrando = true;
                this.$refs.logout.submit();
            },

            guardar(valor) {
                try { localStorage.setItem(CLAVE, String(valor)); } catch (e) {}
            },

            leer() {
                try { return Number(localStorage.getItem(CLAVE)) || 0; } catch (e) { return 0; }
            },
        };
    }
</script>
@endauth
