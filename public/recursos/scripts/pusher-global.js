(function () {
    if (typeof PUSHER_KEY === 'undefined' || !PUSHER_KEY) return;
    if (typeof Pusher === 'undefined') return;

    // CONEXIÓN ÚNICA A PUSHER 
    const pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });

    // SISTEMA GLOBAL DE CALLBACKS (RFPusher)
    window.RFPusher = {
        _handlers: { 'solicitud.nueva': [], 'solicitud.actualizada': [] },

        on(evento, callback) {
            if (this._handlers[evento]) {
                this._handlers[evento].push(callback);
            }
        },

        triggerLocal(evento, data) {
            (this._handlers[evento] || []).forEach(fn => {
                try { fn(data); } catch (e) {
                    console.error('[RFPusher] Error en callback:', e);
                }
            });
        }
    };

    // CANAL PRINCIPAL (roles internos: admin, responsable, empleado) 
    if (typeof PUSHER_CANAL !== 'undefined' && PUSHER_CANAL) {
        const canalPrincipal = pusher.subscribe(PUSHER_CANAL);
        ['solicitud.nueva', 'solicitud.actualizada'].forEach(evento => {
            canalPrincipal.bind(evento, data => window.RFPusher.triggerLocal(evento, data));
        });
    }

    // CANAL PERSONAL DEL CLIENTE
    if (typeof CLIENTE_ID !== 'undefined' && CLIENTE_ID) {
        const canalCliente = pusher.subscribe('cliente-' + CLIENTE_ID);
        canalCliente.bind('solicitud.actualizada', data =>
            window.RFPusher.triggerLocal('solicitud.actualizada', data)
        );
    }

    // MÓDULO CLIENTE
    // Se activa solo cuando existe CLIENTE_ID (plantilla cliente.php)
    if (typeof CLIENTE_ID !== 'undefined' && CLIENTE_ID) {

        /**
         * Actualiza el badge y dot de notificaciones del cliente en tiempo real.
         */
        async function actualizarNotificacionesCliente() {
            try {
                const response = await fetch(BASE_URL + 'cliente/notificaciones/contar');
                const data = await response.json();

                // Badge del sidebar
                const sidebarBadge = document.querySelector('.nav-badge.notif');
                if (sidebarBadge) {
                    sidebarBadge.textContent = data.total;
                    sidebarBadge.style.display = data.total > 0 ? 'inline-block' : 'none';
                }

                // Dot del topbar
                const topbarDot = document.querySelector('.notif-dot');
                if (topbarDot) {
                    topbarDot.style.display = data.total > 0 ? 'inline-block' : 'none';
                }
            } catch (e) {
                console.error('[RFPusher/Cliente] Error actualizando notificaciones:', e);
            }
        }

        // Registrar callbacks de cliente
        window.RFPusher.on('solicitud.actualizada', function (data) {
            actualizarNotificacionesCliente();

            if (typeof window.cargarPedidos === 'function') {
                window.cargarPedidos();
            } else {
                setTimeout(() => window.location.reload(true), 600);
            }
        });
    }

    // MÓDULO RESPONSABLE
    // Se activa solo cuando PUSHER_CANAL === 'kanban-responsables'
    if (typeof PUSHER_CANAL !== 'undefined' && PUSHER_CANAL === 'kanban-responsables') {

        /**
         * Actualiza los badges del sidebar del responsable en tiempo real.
         */
        async function actualizarNotificacionesResponsable() {
            try {
                const response = await fetch(BASE_URL + 'responsable/notificaciones/contar');
                const data = await response.json();
                if (data.status !== 'success') return;

                // Helper: actualiza o crea un badge en un enlace del sidebar
                function actualizarBadge(selector, count, claseExtra) {
                    const link = document.querySelector(selector);
                    if (!link) return;
                    let badge = link.querySelector('.nav-badge');
                    if (count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'nav-badge' + (claseExtra ? ' ' + claseExtra : '');
                            link.appendChild(badge);
                        }
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                    } else if (badge) {
                        badge.style.display = 'none';
                    }
                }

                actualizarBadge('a[href*="bandeja"]', data.pendientes_asignar, '');
                actualizarBadge('a[href*="en-proceso"]', data.en_proceso, 'accent');
                actualizarBadge('a[href*="retroalimentacion"]', data.devoluciones, 'warning');

            } catch (e) {
                console.error('[RFPusher/Responsable] Error actualizando notificaciones:', e);
            }
        }

        /**
         * Recarga la vista activa del responsable según la URL actual.
         */
        function recargarVistaResponsable() {
            const path = window.location.pathname;
            if (path.includes('responsable/bandeja') && typeof window.cargarBandeja === 'function') {
                window.cargarBandeja();
                if (typeof window.cargarEmpleados === 'function') {
                    window.cargarEmpleados();
                }
            } else if (path.includes('responsable/en-proceso') && typeof window.cargarTareasEnProceso === 'function') {
                window.cargarTareasEnProceso();
            } else if (path.includes('responsable/retroalimentacion') && typeof window.cargarRetroalimentacion === 'function') {
                window.cargarRetroalimentacion();
            } else if (typeof cargarBandeja === 'function') {
                cargarBandeja();
            } else if (typeof cargarTareas === 'function') {
                cargarTareas();
            } else if (typeof cargarDatosDashboard === 'function') {
                cargarDatosDashboard();
            } else if (typeof cargarTareasEquipo === 'function') {
                cargarTareasEquipo();
            }
        }

        // Registrar callbacks de responsable
        window.RFPusher.on('solicitud.nueva', function (data) {
            recargarVistaResponsable();
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Nuevo pedido #' + data.id + ' recibido', 'info');
            }
        });

        window.RFPusher.on('solicitud.actualizada', function (data) {
            actualizarNotificacionesResponsable();
            recargarVistaResponsable();
        });

        // Actualizar badges al cargar la página
        document.addEventListener('DOMContentLoaded', actualizarNotificacionesResponsable);
    }

    // MÓDULO EMPLEADO
    // Se activa solo cuando PUSHER_CANAL === 'kanban-empleados'
    if (typeof PUSHER_CANAL !== 'undefined' && PUSHER_CANAL === 'kanban-empleados') {
        console.log('[RFPusher/Empleado] Módulo inicializado en canal:', PUSHER_CANAL);

        /**
         * Actualiza el badge de retroalimentación del sidebar del empleado en tiempo real.
         */
        async function actualizarBadgeRetroEmpleado() {
            try {
                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
                const res = await fetch(cleanBase + 'empleado/retroalimentacion-json');
                const json = await res.json();
                if (!json.success) return;

                let badge = document.querySelector('.nav-enlace[href*="retroalimentacion"] .nav-badge');
                if (json.count > 0) {
                    if (!badge) {
                        const link = document.querySelector('.nav-enlace[href*="retroalimentacion"]');
                        if (link) {
                            badge = document.createElement('span');
                            badge.className = 'nav-badge';
                            badge.style.background = '#ef4444';
                            link.appendChild(badge);
                        }
                    }
                    if (badge) { badge.textContent = json.count; badge.style.display = 'inline-block'; }
                } else if (badge) {
                    badge.style.display = 'none';
                }
            } catch(e) {
                console.error('[RFPusher/Empleado] Error actualizando badge retro:', e);
            }
        }

        /**
         * Recarga la vista activa del empleado sin recargar la página completa.
         */
        function recargarVistaEmpleado() {
            const path = window.location.pathname;
            console.log('[RFPusher/Empleado] Ruta detectada:', path);

            if (path.includes('empleado/retroalimentacion')) {
                console.log('[RFPusher/Empleado] Recargando retroalimentación de forma asíncrona...');
                if (typeof window.cargarRetroalimentacion === 'function') {
                    window.cargarRetroalimentacion();
                } else {
                    // Fallback: esperar un momento para que el script de la página se cargue
                    setTimeout(() => {
                        if (typeof window.cargarRetroalimentacion === 'function') {
                            window.cargarRetroalimentacion();
                        } else {
                            window.location.reload();
                        }
                    }, 300);
                }
            } else if (path.includes('empleado/mis_pedidos') && typeof window.cargarPedidos === 'function') {
                window.cargarPedidos();
            }
        }

        window.RFPusher.on('solicitud.actualizada', function (data) {
            console.log('[RFPusher/Empleado] Evento recibido: solicitud.actualizada', data);
            recargarVistaEmpleado();
            actualizarBadgeRetroEmpleado();
        });

        window.RFPusher.on('solicitud.nueva', function (data) {
            console.log('[RFPusher/Empleado] Evento recibido: solicitud.nueva', data);
            recargarVistaEmpleado();
            actualizarBadgeRetroEmpleado();
        });

        // Actualizar badge al cargar la página
        document.addEventListener('DOMContentLoaded', actualizarBadgeRetroEmpleado);
    }

})();