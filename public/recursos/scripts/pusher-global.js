(function() {
    if (typeof PUSHER_KEY === 'undefined' || !PUSHER_KEY) return;
    if (typeof Pusher === 'undefined') return;

    // Crear una única conexión de Pusher
    const pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });

    // Sistema global de callbacks registrables
    window.RFPusher = {
        _handlers: { 'solicitud.nueva': [], 'solicitud.actualizada': [] },
        
        on(evento, callback) {
            if (this._handlers[evento]) {
                this._handlers[evento].push(callback);
            }
        },

        triggerLocal(evento, data) {
            if (this._handlers[evento]) {
                this._handlers[evento].forEach(fn => {
                    try {
                        fn(data);
                    } catch (e) {
                        console.error('Error en callback de RFPusher:', e);
                    }
                });
            }
        }
    };

    // Suscribir al canal principal si está definido (kanban-admin, kanban-responsables, kanban-empleados)
    if (typeof PUSHER_CANAL !== 'undefined' && PUSHER_CANAL) {
        const canalPrincipal = pusher.subscribe(PUSHER_CANAL);
        ['solicitud.nueva', 'solicitud.actualizada'].forEach(evento => {
            canalPrincipal.bind(evento, (data) => {
                window.RFPusher.triggerLocal(evento, data);
            });
        });
    }

    // Suscribir al canal del cliente si está definido CLIENTE_ID
    if (typeof CLIENTE_ID !== 'undefined' && CLIENTE_ID) {
        const canalCliente = pusher.subscribe('cliente-' + CLIENTE_ID);
        
        // El cliente solo escucha actualizaciones sobre sus solicitudes
        canalCliente.bind('solicitud.actualizada', (data) => {
            window.RFPusher.triggerLocal('solicitud.actualizada', data);
        });
    }
})();
