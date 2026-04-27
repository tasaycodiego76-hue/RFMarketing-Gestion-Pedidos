<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('title') ?>Tareas en Proceso - Mi Equipo<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-white mb-2">
                        <i class="bi bi-activity me-2"></i>Tareas en Proceso
                    </h2>
                    <p class="text-muted mb-0">Seguimiento de tareas asignadas a los miembros del equipo</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm" onclick="actualizarTareas()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="exportarReporte()">
                        <i class="bi bi-download me-1"></i>Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card-dark-main p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-warning mb-1">Total Tareas</h6>
                        <h3 class="text-white mb-0" id="total-tareas">0</h3>
                    </div>
                    <div class="text-warning" style="font-size: 2rem;">
                        <i class="bi bi-list-task"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-dark-main p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-info mb-1">Empleados Activos</h6>
                        <h3 class="text-white mb-0" id="total-empleados">0</h3>
                    </div>
                    <div class="text-info" style="font-size: 2rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-dark-main p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-success mb-1">Prioridad Alta</h6>
                        <h3 class="text-white mb-0" id="total-alta">0</h3>
                    </div>
                    <div class="text-danger" style="font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Empleados con sus Tareas -->
    <div class="row" id="empleados-container">
        <!-- Se cargará dinámicamente -->
    </div>
</div>

<!-- Modal para ver detalles de tarea -->
<div class="modal fade" id="modal-detalle-tarea" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detalles de la Tarea
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalle-tarea-content">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let tareasData = [];
const base_url = '<?= base_url(); ?>';

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    cargarTareasEnProceso();
});

/**
 * Cargar tareas en proceso desde el backend
 */
function cargarTareasEnProceso() {
    fetch(`${base_url}responsable/tareas/en-proceso`)
        .then(response => response.json())
        .then(data => {
            console.log('Tareas en proceso:', data);
            if (data.success) {
                tareasData = data.data || [];
                actualizarEstadisticas(data);
                renderizarEmpleados(tareasData);
            } else {
                mostrarError(data.message || 'Error al cargar las tareas');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar las tareas');
        });
}

/**
 * Actualizar estadísticas en el header
 */
function actualizarEstadisticas(data) {
    document.getElementById('total-tareas').textContent = data.total_tareas || 0;
    document.getElementById('total-empleados').textContent = data.total_empleados || 0;
    
    // Contar prioridades altas
    let totalAlta = 0;
    data.data.forEach(empleado => {
        empleado.tareas.forEach(tarea => {
            if (tarea.prioridad && (tarea.prioridad || '').toLowerCase() === 'alta') {
                totalAlta++;
            }
        });
    });
    document.getElementById('total-alta').textContent = totalAlta;
}

/**
 * Renderizar la lista de empleados con sus tareas
 */
function renderizarEmpleados(empleados) {
    const container = document.getElementById('empleados-container');
    
    if (empleados.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay tareas en proceso actualmente</p>
                </div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = empleados.map(empleado => `
        <div class="col-lg-6 mb-4">
            <div class="card-dark-main p-4">
                <!-- Header del Empleado -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="empleado-avatar ${empleado.esresponsable ? 'responsable' : ''}">
                            ${empleado.esresponsable ? '<i class="bi bi-shield-check"></i>' : obtenerIniciales(empleado.nombre)}
                        </div>
                        <div class="ms-3">
                            <h5 class="text-white mb-1">${escaparHtml(empleado.nombre)}</h5>
                            <span class="badge ${empleado.esresponsable ? 'bg-warning text-dark' : 'bg-info'}">
                                ${empleado.esresponsable ? 'Jefe de Área' : 'Miembro del Equipo'}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-warning">
                            <strong>${empleado.total_tareas}</strong>
                        </div>
                        <small class="text-muted">tareas</small>
                    </div>
                </div>
                
                <!-- Lista de Tareas -->
                <div class="tareas-lista">
                    ${empleado.tareas.length === 0 ? `
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-check-circle me-2"></i>Sin tareas asignadas
                        </div>
                    ` : empleado.tareas.map(tarea => `
                        <div class="tarea-item mb-2 p-3 border-secondary rounded" onclick="verDetalleTarea(${tarea.id})">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge-priority prio-${(tarea.prioridad || 'media').toLowerCase()} me-2">
                                            ${tarea.prioridad || 'Media'}
                                        </span>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>${formatearFecha(tarea.fechacreacion || tarea.fechacreacion)}
                                        </small>
                                    </div>
                                    <h6 class="text-white mb-1">${escaparHtml(tarea.titulo || 'Sin título')}</h6>
                                    <p class="text-muted small mb-2">
                                        ${tarea.objetivo ? escaparHtml(tarea.objetivo) : '<em class="text-muted">Sin objetivo</em>'}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-building me-1"></i>${tarea.nombre_empresa ? escaparHtml(tarea.nombre_empresa) : '<em class="text-muted">N/A</em>'}
                                        </span>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-gear me-1"></i>${tarea.servicio ? escaparHtml(tarea.servicio) : '<em class="text-muted">N/A</em>'}
                                        </span>
                                    </div>
                                </div>
                                <div class="ms-2">
                                    <button class="btn btn-sm btn-outline-light" onclick="event.stopPropagation(); verDetalleTarea(${tarea.idatencion})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Ver detalles de una tarea específica
 */
function verDetalleTarea(idAtencion) {
    fetch(`${base_url}responsable/pedidos/detalle?id=${idAtencion}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarModalDetalle(data.data, data.archivos);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudieron cargar los detalles',
                    background: '#161616',
                    color: '#fff',
                    confirmButtonColor: '#f5c400'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al cargar los detalles',
                background: '#161616',
                color: '#fff',
                confirmButtonColor: '#f5c400'
            });
        });
}

/**
 * Mostrar modal con detalles de la tarea
 */
function mostrarModalDetalle(requerimiento, archivos) {
    const content = document.getElementById('detalle-tarea-content');
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <h6 class="text-warning mb-3">Información del Requerimiento</h6>
                <div class="mb-3">
                    <label class="text-muted small">Título</label>
                    <p class="text-white mb-0">${escaparHtml(requerimiento.titulo || 'Sin título')}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Objetivo de Comunicación</label>
                    <div class="content-box">
                        <div class="content-text">${escaparHtml(requerimiento.objetivo_comunicacion || 'No especificado')}</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Público Objetivo</label>
                    <div class="content-box">
                        <div class="content-text">${escaparHtml(requerimiento.publico_objetivo || 'No especificado')}</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Descripción Detallada</label>
                    <div class="content-box">
                        <div class="content-text">${escaparHtml(requerimiento.descripcion || 'No especificada')}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="text-warning mb-3">Detalles Adicionales</h6>
                <div class="mb-3">
                    <label class="text-muted small">Cliente</label>
                    <p class="text-white mb-0">${requerimiento.nombre_empresa ? escaparHtml(requerimiento.nombre_empresa) : '<em class="text-muted">N/A</em>'}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Servicio</label>
                    <p class="text-white mb-0">${requerimiento.nombre_servicio ? escaparHtml(requerimiento.nombre_servicio) : '<em class="text-muted">N/A</em>'}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Prioridad</label>
                    <p class="mb-0">
                        <span class="badge-priority prio-${(requerimiento.prioridad || 'media').toLowerCase()}">
                            ${requerimiento.prioridad || 'Media'}
                        </span>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Fecha de Creación</label>
                    <p class="text-white mb-0">${formatearFecha(requerimiento.fechacreacion)}</p>
                </div>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('modal-detalle-tarea'));
    modal.show();
}

/**
 * Actualizar tareas
 */
function actualizarTareas() {
    cargarTareasEnProceso();
}

/**
 * Exportar reporte (placeholder)
 */
function exportarReporte() {
    Swal.fire({
        icon: 'info',
        title: 'Próximamente',
        text: 'Función de exportación en desarrollo',
        background: '#161616',
        color: '#fff',
        confirmButtonColor: '#f5c400'
    });
}

/**
 * Mostrar error
 */
function mostrarError(mensaje) {
    const container = document.getElementById('empleados-container');
    container.innerHTML = `
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${escaparHtml(mensaje)}
            </div>
        </div>
    `;
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

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    if (isNaN(date.getTime())) return fecha;
    return date.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}
</script>

<style>
.empleado-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.empleado-avatar.responsable {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.tarea-item {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 8px;
    padding: 12px;
}

.tarea-item:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: #f5c400;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(245, 196, 0, 0.2);
}

.badge-priority {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.prio-alta {
    background: #dc3545;
    color: white;
}

.prio-media {
    background: #ffc107;
    color: black;
}

.prio-baja {
    background: #28a745;
    color: white;
}

.card-dark-main {
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.content-box {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 8px;
    max-height: 200px;
    overflow-y: auto;
}

.content-text {
    color: #fff;
    font-size: 13px;
    line-height: 1.4;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    hyphens: auto;
}
</style>
<?= $this->endSection() ?>
