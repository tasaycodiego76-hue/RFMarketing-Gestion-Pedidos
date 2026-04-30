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

    const colores = ['yellow', 'green', 'purple'];

    contenedor.innerHTML = empleados.map((emp, index) => {
        const iniciales = obtenerIniciales(emp.nombre_completo);
        const esResponsable = emp.esresponsable;
        const colorAvatar = esResponsable ? 'blue' : colores[index % colores.length];
        
        const enProceso = (emp.en_proceso || 0);
        const completados = (emp.completados || 0);
        const total = (emp.pendientes || 0) + enProceso + completados;

        return `
            <div class="employee-card ${esResponsable ? 'jefe' : ''}">
              <div class="card-header">
                <div class="avatar ${colorAvatar}">
                  ${iniciales}
                </div>
                <div class="card-info">
                  <div class="employee-name">${escaparHtml((emp.nombre_completo || '').toUpperCase())}</div>
                  <span class="employee-role ${esResponsable ? 'jefe' : 'miembro'}">
                    ${esResponsable ? '🛡️ Jefe de Área' : 'Miembro del Equipo'}
                  </span>
                </div>
              </div>

              <div class="metrics">
                <div class="metric">
                  <span class="metric-value warning">${enProceso}</span>
                  <span class="metric-label">En proceso</span>
                </div>
                <div class="metric">
                  <span class="metric-value success">${completados}</span>
                  <span class="metric-label">Completados</span>
                </div>
                <div class="metric">
                  <span class="metric-value info">${total}</span>
                  <span class="metric-label">Total</span>
                </div>
              </div>

              <div class="card-actions">
                <button class="btn btn-secondary w-100" style="width: 100%;" onclick="verDetalleMiembro(${emp.id})">Ver Tareas</button>
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
        contador.innerHTML = `👥 ${cantidad} miembro${cantidad !== 1 ? 's' : ''}`;
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
        <div class="employee-card">
            <div class="card-header">
                <div class="skeleton-avatar"></div>
                <div class="card-info" style="width: 100%;">
                    <div class="skeleton-line" style="width:70%;"></div>
                    <div class="skeleton-line short"></div>
                </div>
            </div>
            <div class="metrics">
                <div class="metric">
                    <div class="skeleton-line" style="height:24px;width:50%;margin:0 auto 4px;"></div>
                    <div class="skeleton-line" style="width:80%;margin:0 auto;"></div>
                </div>
                <div class="metric">
                    <div class="skeleton-line" style="height:24px;width:50%;margin:0 auto 4px;"></div>
                    <div class="skeleton-line" style="width:80%;margin:0 auto;"></div>
                </div>
                <div class="metric">
                    <div class="skeleton-line" style="height:24px;width:50%;margin:0 auto 4px;"></div>
                    <div class="skeleton-line" style="width:80%;margin:0 auto;"></div>
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

/**
 * Ver detalles y tareas de un miembro
 */
function verDetalleMiembro(idEmpleado) {
    fetch(`${base_url}responsable/equipo/miembro/${idEmpleado}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar título del modal
                document.getElementById('nombreMiembroModal').innerText = `Tareas: ${data.empleado.nombre_completo}`;
                
                // Preparar la tabla
                const tbody = document.getElementById('bodyTareasMiembro');
                const table = document.getElementById('tablaTareasMiembro');
                const sinTareas = document.getElementById('sinTareasMiembro');
                
                tbody.innerHTML = '';
                
                if (data.tareas && data.tareas.length > 0) {
                    table.classList.remove('d-none');
                    sinTareas.classList.add('d-none');
                    
                    data.tareas.forEach(tarea => {
                        // Badge de estado
                        let badgeClass = 'bg-secondary';
                        let estadoTexto = tarea.estado;
                        if (tarea.estado === 'en_proceso') { badgeClass = 'bg-warning text-dark'; estadoTexto = 'En Proceso'; }
                        else if (tarea.estado === 'finalizado' || tarea.estado === 'completado') { badgeClass = 'bg-success'; estadoTexto = 'Completado'; }
                        
                        // Badge de prioridad
                        let prioClass = 'bg-secondary';
                        if (tarea.prioridad === 'Alta') prioClass = 'bg-danger';
                        else if (tarea.prioridad === 'Media') prioClass = 'bg-warning text-dark';
                        else if (tarea.prioridad === 'Baja') prioClass = 'bg-info text-dark';
                        
                        tbody.innerHTML += `
                            <tr>
                                <td class="text-truncate" style="max-width: 250px;" title="${escaparHtml(tarea.titulo)}">
                                    <div style="font-weight: 500;">${escaparHtml(tarea.titulo)}</div>
                                    <div style="font-size: 0.8rem; color: #888;">${escaparHtml(tarea.empresa_nombre || '')}</div>
                                </td>
                                <td style="vertical-align: middle;">${escaparHtml(tarea.servicio_nombre || '-')}</td>
                                <td style="vertical-align: middle;"><span class="badge ${badgeClass}">${estadoTexto}</span></td>
                                <td style="vertical-align: middle;"><span class="badge ${prioClass}">${tarea.prioridad || '-'}</span></td>
                                <td style="vertical-align: middle; font-size: 0.85rem; color: #aaa;">${tarea.fechainicio ? tarea.fechainicio.split(' ')[0] : '-'}</td>
                            </tr>
                        `;
                    });
                } else {
                    table.classList.add('d-none');
                    sinTareas.classList.remove('d-none');
                }
                
                // Mostrar modal con Bootstrap
                const modalElement = document.getElementById('modalDetalleMiembro');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al obtener detalles', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false });
        });
}
