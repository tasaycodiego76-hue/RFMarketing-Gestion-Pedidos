document.addEventListener('DOMContentLoaded', () => {
    cargarEquipo();
});

/**
 * Carga la lista de miembros del equipo desde la API
 * @returns 
 */
async function cargarEquipo() {
    const contenedor = document.getElementById('contenedor-equipo');
    if (!contenedor) return;

    contenedor.innerHTML = generarSkeletonCards(4);

    try {
        const response = await fetch(`${base_url}responsable/empleados/mi-area-json`);
        const res = await response.json();

        if (res.success) {
            const empleados = res.data || [];
            actualizarContador(empleados.length);

            if (empleados.length === 0) {
                mostrarEstadoVacio();
            } else {
                renderizarEquipo(empleados);
            }
        } else {
            mostrarError(res.message || 'Error al cargar el equipo');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error de conexión con el servidor');
    }
}

/**
 * Renderiza las tarjetas de los miembros con diseño optimizado
 * @param {*} empleados 
 * @returns 
 */
function renderizarEquipo(empleados) {
    const contenedor = document.getElementById('contenedor-equipo');
    if (!contenedor) return;

    document.getElementById('estado-vacio')?.classList.add('d-none');
    const colores = ['yellow', 'green', 'purple'];

    contenedor.innerHTML = empleados.map((emp, i) => {
        const iniciales = obtenerIniciales(emp.nombre_completo);
        const colorAvatar = emp.esresponsable ? 'blue' : colores[i % colores.length];
        const totalActivas = (parseInt(emp.pendientes) || 0) + (parseInt(emp.en_proceso) || 0);

        return `
            <div class="employee-card ${emp.esresponsable ? 'jefe' : ''}">
              <div class="card-header">
                <div class="avatar ${colorAvatar}">${iniciales}</div>
                <div class="card-info">
                  <div class="employee-name">${escaparHtml((emp.nombre_completo || '').toUpperCase())}</div>
                  <span class="employee-role ${emp.esresponsable ? 'jefe' : 'miembro'}">
                    ${emp.esresponsable ? '🛡️ Jefe de Área' : 'Miembro del Equipo'}
                  </span>
                </div>
              </div>
              <div class="metrics">
                <div class="metric"><span class="metric-value info">${emp.pendientes || 0}</span><span class="metric-label">En espera</span></div>
                <div class="metric"><span class="metric-value warning">${emp.en_proceso || 0}</span><span class="metric-label">En proceso</span></div>
                <div class="metric"><span class="metric-value success">${emp.completados || 0}</span><span class="metric-label">Listos</span></div>
              </div>
              <div class="card-actions mt-2">
                <button class="btn btn-outline-light btn-sm w-full" onclick="verDetalleMiembro(${emp.id})" style="border-radius: 6px; font-size: 11px; letter-spacing: 1px; padding: 6px 0; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                    <i class="bi bi-list-task me-1"></i> VER TAREAS (${totalActivas})
                </button>
              </div>
            </div>
        `;
    }).join('');
}

/**
 * Obtiene los datos del miembro y dispara el renderizado del modal
 * @param {*} idEmpleado 
 */
async function verDetalleMiembro(idEmpleado) {
    Swal.fire({
        title: 'CONSULTANDO TAREAS',
        background: '#161616',
        color: '#fff',
        didOpen: () => { Swal.showLoading(); }
    });

    try {
        const response = await fetch(`${base_url}responsable/equipo/miembro/${idEmpleado}`);
        const res = await response.json();
        Swal.close();

        if (res.success) {
            renderizarModalTareas(res.empleado, res.tareas);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#161616', color: '#fff' });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', background: '#161616', color: '#fff' });
    }
}

/**
 * Renderiza el contenido del modal de tareas (Optimizado como en Historial)
 * @param {*} emp 
 * @param {*} tareas 
 * @returns 
 */
function renderizarModalTareas(emp, tareas) {
    const modalElement = document.getElementById('modalDetalleMiembro');
    const tbody = document.getElementById('bodyTareasMiembro');
    if (!modalElement || !tbody) return;

    document.getElementById('nombreMiembroModal').innerText = `TAREAS: ${emp.nombre_completo.toUpperCase()}`;

    if (!tareas || tareas.length === 0) {
        document.getElementById('tablaTareasMiembro')?.classList.add('d-none');
        document.getElementById('sinTareasMiembro')?.classList.remove('d-none');
    } else {
        document.getElementById('tablaTareasMiembro')?.classList.remove('d-none');
        document.getElementById('sinTareasMiembro')?.classList.add('d-none');

        const estMap = {
            'pendiente': { t: 'PENDIENTE', c: 'badge-estado-pendiente' },
            'en_proceso': { t: 'EN PROCESO', c: 'badge-estado-proceso' },
            'en_revision': { t: 'EN REVISIÓN', c: 'badge-estado-proceso' },
            'finalizado': { t: 'COMPLETADO', c: 'badge-estado-finalizado' },
            'completado': { t: 'COMPLETADO', c: 'badge-estado-finalizado' }
        };

        const priMap = {
            'Alta': 'badge-prio-alta',
            'Media': 'badge-prio-media',
            'Baja': 'badge-prio-baja'
        };

        tbody.innerHTML = tareas.map(t => {
            const e = estMap[t.estado] || { t: t.estado, c: 'badge-estado-pendiente' };
            const p = priMap[t.prioridad] || 'badge-estado-pendiente';
            
            // Iconos por estado
            const iconMap = {
                'pendiente': 'bi-clock-history',
                'en_proceso': 'bi-play-circle-fill',
                'en_revision': 'bi-eye-fill',
                'finalizado': 'bi-check-circle-fill',
                'completado': 'bi-check-circle-fill'
            };
            const icon = iconMap[t.estado] || 'bi-circle';

            return `
                <tr class="task-row-premium">
                    <td class="vertical-align-middle ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi ${icon} text-oro" style="font-size: 1.1rem;"></i>
                            <div>
                                <div class="task-title-premium" title="${escaparHtml(t.titulo)}">${escaparHtml(t.titulo)}</div>
                                <div class="task-subtitle-premium"><i class="bi bi-building"></i> ${escaparHtml(t.empresa_nombre || '---')}</div>
                            </div>
                        </div>
                    </td>
                    <td class="vertical-align-middle">
                        <span class="service-chip-premium">${escaparHtml(t.servicio_nombre || '-')}</span>
                    </td>
                    <td class="vertical-align-middle">
                        <span class="badge-status-premium ${e.c}">${e.t}</span>
                    </td>
                    <td class="vertical-align-middle">
                        <span class="badge-priority-premium ${p}">${t.prioridad || '-'}</span>
                    </td>
                    <td class="vertical-align-middle text-center pe-3">
                        <div style="font-size: 12px; color: #aaa; line-height: 1.4;">
                            <i class="bi bi-calendar-event me-1"></i> ${t.fechainicio ? t.fechainicio.split(' ')[0] : '---'}<br>
                            <i class="bi bi-clock me-1"></i> ${t.fechainicio ? t.fechainicio.split(' ')[1].substring(0, 5) : '--:--'}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

/* Funciones de Estado y UI */

// Actualiza el contador visual de miembros del equipo
function actualizarContador(n) {
    const c = document.getElementById('contador-equipo');
    if (c) c.innerHTML = `👥 ${n} miembro${n !== 1 ? 's' : ''}`;
}

// Muestra un estado visual vacío cuando no hay miembros
function mostrarEstadoVacio() {
    const cont = document.getElementById('contenedor-equipo');
    if (cont) cont.innerHTML = '';
    document.getElementById('estado-vacio')?.classList.remove('d-none');
    actualizarContador(0);
}

// Muestra un mensaje de error con opción de reintento
function mostrarError(m) {
    const c = document.getElementById('contenedor-equipo');
    if (!c) return;
    c.innerHTML = `<div class="col-12 text-center py-5 text-danger-rf"><i class="bi bi-exclamation-triangle-fill mb-3 icon-xl-rf"></i><p class="font-weight-500">${escaparHtml(m)}</p><button class="btn btn-dark-rf mt-3 px-4" onclick="cargarEquipo()"><i class="bi bi-arrow-clockwise"></i> REINTENTAR</button></div>`;
}

// Genera el HTML para el estado de carga (skeleton cards)
function generarSkeletonCards(n) {
    return Array(n).fill(0).map(() => `<div class="employee-card skeleton-card"><div class="card-header"><div class="skeleton-avatar"></div><div class="card-info w-full"><div class="skeleton-line skeleton-width-70"></div><div class="skeleton-line short"></div></div></div><div class="metrics"><div class="metric"><div class="skeleton-line skeleton-metric-value"></div><div class="skeleton-line skeleton-metric-label"></div></div><div class="metric"><div class="skeleton-line skeleton-metric-value"></div><div class="skeleton-line skeleton-metric-label"></div></div><div class="metric"><div class="skeleton-line skeleton-metric-value"></div><div class="skeleton-line skeleton-metric-label"></div></div></div></div>`).join('');
}

/* Helpers y Utilidades de UI */

// Escapa caracteres especiales de HTML para prevenir ataques XSS
function escaparHtml(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

// Obtiene las iniciales de un nombre completo (Máximo 2 letras)
function obtenerIniciales(n) {
    if (!n) return '?';
    const p = n.trim().split(' ');
    return ((p[0]?.[0] || '') + (p[1]?.[0] || '')).toUpperCase();
}

// Obtiene el mes abreviado (Ene, Feb, etc)
function obtenerMesCorto(f) {
    if (!f) return '---';
    const meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    const d = new Date(f);
    return meses[d.getMonth()] || '---';
}