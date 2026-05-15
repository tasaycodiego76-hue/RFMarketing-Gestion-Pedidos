/**
 * Sistema de Notificaciones para el Administrador
 * Maneja la campana de revisiones pendientes
 */

$(document).ready(function () {
    const $btn = $('#notifications-btn');
    const $dropdown = $('#notifications-dropdown');
    const $badge = $('#notifications-badge');
    const $list = $('#notifications-list');

    // 1. Cargar notificaciones inicialmente
    cargarNotificaciones();

    // 2. Intervalo para actualizar cada 30 segundos
    setInterval(cargarNotificaciones, 30000);

    // 3. Toggle dropdown
    $btn.on('click', function (e) {
        e.stopPropagation();
        $dropdown.toggleClass('show');
    });

    // Cerrar al hacer clic fuera
    $(document).on('click', function () {
        $dropdown.removeClass('show');
    });

    $dropdown.on('click', function (e) {
        e.stopPropagation();
    });

    /**
     * Obtiene los pedidos en revisión desde el servidor
     */
    function cargarNotificaciones() {
        $.ajax({
            url: BASE_URL + 'admin/notificaciones/revisiones',
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    actualizarUI(res.data, res.total);
                }
            },
            error: function (err) {
                console.error('Error cargando notificaciones:', err);
            }
        });
    }

    /**
     * Renderiza las notificaciones en el dropdown y actualiza el badge
     */
    function actualizarUI(data, total) {
        // Actualizar Badge
        if (total > 0) {
            $badge.text(total).show();
        } else {
            $badge.hide();
        }

        // Limpiar lista
        $list.empty();

        if (total === 0) {
            $list.append(`
                <div class="notification-empty">
                    <i class="bi bi-check2-all"></i>
                    <p>No hay pedidos pendientes de revisión</p>
                </div>
            `);
            return;
        }

        // Agregar items
        data.forEach(item => {
            const timeAgo = calcularTiempo(item.fechacreacion); 
            // Usamos empresa e idarea_agencia reales para el link
            const link = `${BASE_URL}admin/kanban/${item.idempresa}/${item.idarea_agencia}?ver=${item.id}`;
            
            $list.append(`
                <a href="${link}" class="notification-item" data-id="${item.id}">
                    <div class="notification-icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div class="notification-content">
                        <span class="notification-title">${item.titulo}</span>
                        <span class="notification-meta">${item.empresa} • ${item.area_nombre}</span>
                        <span class="notification-meta" style="color: #444; font-size: 10px; margin-top: 4px;">
                            <i class="bi bi-clock"></i> Pendiente hace ${timeAgo}
                        </span>
                    </div>
                </a>
            `);
        });

        // Evento clic en item
        $('.notification-item').on('click', function(e) {
            const idAtencion = $(this).data('id');
            // Si ya estamos en la página de Kanban CORRECTA, solo abrimos el modal
            const urlParams = new URLSearchParams(window.location.search);
            const currentPath = window.location.pathname;
            const targetPath = $(this).attr('href').split('?')[0].replace(BASE_URL, '');
            
            if (currentPath.includes(targetPath) && typeof verDetalle === 'function') {
                e.preventDefault();
                verDetalle(idAtencion);
                $dropdown.removeClass('show');
            }
        });
    }

    /**
     * Calcula tiempo transcurrido de forma simple
     */
    function calcularTiempo(fecha) {
        const diff = new Date() - new Date(fecha);
        const mins = Math.floor(diff / 60000);
        if (mins < 60) return mins + ' min';
        const horas = Math.floor(mins / 60);
        if (horas < 24) return horas + ' h';
        return Math.floor(horas / 24) + ' d';
    }
});
