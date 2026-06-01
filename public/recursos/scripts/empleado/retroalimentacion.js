/**
 * retroalimentacion.js — Empleado
 * Recarga las tarjetas de retroalimentación sin recargar la página.
 * Llamado por Pusher (pusher-global.js) cuando llega un evento en tiempo real.
 */
window.cargarRetroalimentacion = async function () {
    const baseUrl = window.BASE_URL || '/';
    const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';

    function esc(t) {
        if (!t) return '';
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    try {
        const res = await fetch(cleanBase + 'empleado/retroalimentacion-json');
        const json = await res.json();
        if (!json.success) return;

        const contenedor = document.getElementById('contenedor-retro');
        if (!contenedor) return;

        if (json.count === 0) {
            contenedor.innerHTML = `
            <div class="text-center py-5" style="background: var(--panel); border: 1px dashed var(--borde); border-radius: 24px; margin-top: 20px;">
                <div class="mb-4">
                    <i class="bi bi-chat-heart" style="font-size: 60px; color: var(--amarillo); opacity: 0.2;"></i>
                </div>
                <h5 style="font-family: 'Bebas Neue'; letter-spacing: 2px; color: var(--texto);">¡Todo impecable!</h5>
                <p style="font-size: 12px; color: var(--texto-3); text-transform: uppercase; letter-spacing: 2px; font-weight: 600;">No tienes pedidos con correcciones pendientes.</p>
            </div>`;
            // Actualizar badge del sidebar
            const badge = document.querySelector('.nav-enlace[href*="retroalimentacion"] .nav-badge');
            if (badge) badge.style.display = 'none';
            return;
        }

        const cardsHtml = json.data.map(r => {
            const fecha = r.fecha ? r.fecha.substring(0, 10).split('-').reverse().join('/') : '---';
            const evalNombre = esc((r.evaluador_nombre || '') + ' ' + (r.evaluador_apellidos || ''));
            const evalInicial = (r.evaluador_nombre || 'A').charAt(0).toUpperCase();
            return `
            <div class="retro-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="retro-badge"><i class="bi bi-exclamation-triangle-fill me-1"></i> Corrección</span>
                    <span class="text-dim-small"><i class="bi bi-clock-history"></i> ${fecha}</span>
                </div>
                <h4 class="title-bebas-retro">${esc(r.pedido_titulo || '')}</h4>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="badge-servicio-retro">${esc(r.servicio_nombre || '')}</div>
                    <div class="text-muted-extra-small"><i class="bi bi-building"></i> ${esc(r.empresa_nombre || '')}</div>
                </div>
                <div class="retro-msg-container">
                    <div class="retro-msg-label">Observación del Evaluador</div>
                    <p class="retro-msg-text">"${esc(r.contenido || '')}"</p>
                </div>
                <div class="retro-footer">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle-retro">${evalInicial}</div>
                        <div class="d-flex flex-column">
                            <span class="specialist-label">Administrador</span>
                            <span class="specialist-name">${evalNombre}</span>
                        </div>
                    </div>
                    <a href="${cleanBase}empleado/mis_pedidos?highlight=${r.id_atencion || ''}" class="btn-retro-action">
                        CORREGIR <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>`;
        }).join('');

        contenedor.innerHTML = `<div class="retro-grid">${cardsHtml}</div>`;

        // Actualizar badge del sidebar
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

    } catch (e) {
        console.error('[Empleado/Retro] Error al cargar retroalimentación:', e);
    }
};
