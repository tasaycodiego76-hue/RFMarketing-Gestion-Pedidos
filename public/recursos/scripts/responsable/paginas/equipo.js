/**
 * Mi Equipo - Responsable de Área
 * Consume endpoint:
 * - GET responsable/empleados/mi-area-json
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    cargarEquipo();
});

/**
 * Cargar miembros del equipo
 */
function cargarEquipo() {
    const contenedor = document.getElementById('contenedor-equipo');
    contenedor.innerHTML = generarSkeletonCards(4);

    fetch(`${base_url}responsable/empleados/mi-area-json`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const empleados = data.data || [];
                actualizarContador(empleados.length);

                if (empleados.length === 0) {
                    mostrarEstadoVacio();
                } else {
                    renderizarEquipo(empleados);
                }
            } else {
                mostrarError(data.message || 'Error al cargar el equipo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar el equipo');
        });
}

/**
 * Renderizar cards del equipo
 */
function renderizarEquipo(empleados) {
    const contenedor = document.getElementById('contenedor-equipo');
    const estadoVacio = document.getElementById('estado-vacio');

    estadoVacio?.classList.add('d-none');

    contenedor.innerHTML = empleados.map(emp => {
        const iniciales = obtenerIniciales(emp.nombre_completo);
        const esResponsable = emp.esresponsable;

        return `
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <div class="miembro-card ${esResponsable ? 'responsable' : ''}">
                    <div class="miembro-header">
                        <div class="miembro-avatar ${esResponsable ? 'responsable' : ''}">
                            ${esResponsable
                                ? '<i class="bi bi-shield-check" style="font-size:24px;"></i>'
                                : iniciales
                            }
                        </div>
                        <div class="miembro-info">
                            <div class="miembro-nombre">${escaparHtml(emp.nombre_completo)}</div>
                            <div class="miembro-rol">
                                ${esResponsable
                                    ? '<span class="badge-jefe">Jefe de Área</span>'
                                    : '<span class="badge-miembro">Miembro del Equipo</span>'
                                }
                            </div>
                        </div>
                    </div>
                    ${emp.pendientes !== undefined || emp.en_proceso !== undefined || emp.completados !== undefined ? `
                    <div class="miembro-stats">
                        <div class="stat-box">
                            <div class="stat-valor pendiente">${emp.pendientes || 0}</div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-valor proceso">${emp.en_proceso || 0}</div>
                            <div class="stat-label">En Proceso</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-valor completado">${emp.completados || 0}</div>
                            <div class="stat-label">Completados</div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Mostrar estado vacío
 */
function mostrarEstadoVacio() {
    const contenedor = document.getElementById('contenedor-equipo');
    const estadoVacio = document.getElementById('estado-vacio');

    contenedor.innerHTML = '';
    estadoVacio?.classList.remove('d-none');
    actualizarContador(0);
}

/**
 * Actualizar contador
 */
function actualizarContador(cantidad) {
    const contador = document.getElementById('contador-equipo');
    if (contador) {
        contador.innerHTML = `<i class="bi bi-people-fill"></i> ${cantidad} miembro${cantidad !== 1 ? 's' : ''}`;
    }
}

/**
 * Mostrar error
 */
function mostrarError(mensaje) {
    const contenedor = document.getElementById('contenedor-equipo');
    contenedor.innerHTML = `
        <div class="col-12 text-center py-5" style="color:#ef4444;">
            <i class="bi bi-exclamation-triangle-fill mb-3" style="font-size:48px;display:block;"></i>
            <p>${escaparHtml(mensaje)}</p>
            <button class="btn-rf mt-3" onclick="cargarEquipo()">
                <i class="bi bi-arrow-clockwise"></i> Reintentar
            </button>
        </div>
    `;
}

/**
 * Generar skeleton loading
 */
function generarSkeletonCards(cantidad) {
    return Array(cantidad).fill(0).map(() => `
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="skeleton-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="skeleton-avatar"></div>
                    <div style="flex:1;">
                        <div class="skeleton-line" style="width:70%;"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
                <div style="display:flex;gap:12px;">
                    <div style="flex:1;text-align:center;">
                        <div class="skeleton-line" style="height:24px;width:50%;margin:0 auto 4px;"></div>
                        <div class="skeleton-line" style="width:80%;margin:0 auto;"></div>
                    </div>
                    <div style="flex:1;text-align:center;">
                        <div class="skeleton-line" style="height:24px;width:50%;margin:0 auto 4px;"></div>
                        <div class="skeleton-line" style="width:80%;margin:0 auto;"></div>
                    </div>
                    <div style="flex:1;text-align:center;">
                        <div class="skeleton-line" style="height:24px;width:50%;margin:0 auto 4px;"></div>
                        <div class="skeleton-line" style="width:80%;margin:0 auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Utilidades
 */
function escaparHtml(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

function obtenerIniciales(nombre) {
    if (!nombre) return '?';
    const partes = nombre.trim().split(' ');
    const primera = partes[0]?.[0] || '';
    const segunda = partes[1]?.[0] || '';
    return (primera + segunda).toUpperCase();
}
