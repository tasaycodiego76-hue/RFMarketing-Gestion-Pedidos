/**
 * Obtiene y muestra el detalle de un requerimiento
 * @param {*} idAtencion 
 * @returns 
 */
async function verDetalle(idAtencion) {
    const cuerpo = document.getElementById('detalleCuerpo');
    const modalElement = document.getElementById('modalDetalle');
    if (!cuerpo || !modalElement) return;

    // Estado de carga usando clases de retroalimentacion.css
    cuerpo.innerHTML = `
        <div class="loading-container">
            <div class="spinner-border text-oro" style="width: 3rem; height: 3rem;"></div>
            <div class="loading-text">CARGANDO EXPEDIENTE...</div>
        </div>
    `;

    const myModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    myModal.show();

    try {
        const url = `${window.BASE_URL || '/'}responsable/pedidos/detalle?id=${idAtencion}`;
        const response = await fetch(url);
        const res = await response.json();

        if (res.success) {
            renderizarDetalleRetro(res.data, res.archivos, res.tracking);
        } else {
            cuerpo.innerHTML = `<div class="p-5 text-center text-danger font-weight-bold">${res.message}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        cuerpo.innerHTML = `<div class="p-5 text-center text-danger">Error de conexión al cargar el detalle</div>`;
    }
}

/**
 * Renderiza el contenido del detalle de retroalimentación
 * @param {*} req 
 * @param {*} archivos 
 * @param {*} tracking 
 * @returns 
 */
function renderizarDetalleRetro(req, archivos, tracking) {
    const cuerpo = document.getElementById('detalleCuerpo');
    if (!cuerpo) return;

    // Mapeo de estados y prioridades
    const configEstado = {
        'pendiente_asignado': { label: 'POR ASIGNAR', color: '#f59e0b' },
        'en_proceso': { label: 'EN DESARROLLO', color: '#F5C400' },
        'en_revision': { label: 'EN REVISIÓN', color: '#8b5cf6' },
        'finalizado': { label: 'COMPLETADO', color: '#22c55e' }
    };
    const est = configEstado[req.estado] || { label: req.estado, color: '#aaa' };
    
    const prioColor = req.prioridad?.toLowerCase() === 'alta' ? '#ef4444' : (req.prioridad?.toLowerCase() === 'baja' ? '#3b82f6' : '#f59e0b');

    // Construcción de secciones
    const html = `
    <div class="p-4">
        <div class="mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="retro-badge" style="background: ${est.color}15; color: ${est.color}; border: 1px solid ${est.color}33;">${est.label}</span>
                <span class="retro-badge" style="background: ${prioColor}15; color: ${prioColor}; border: 1px solid ${prioColor}33;">
                    PRIORIDAD ${req.prioridad?.toUpperCase() || 'MEDIA'}
                </span>
            </div>
            <h2 class="title-bebas-retro mb-1">${escaparHtml(req.titulo)}</h2>
            <p class="text-muted-extra-small text-uppercase">
                ${escaparHtml(req.nombre_empresa)} | <span class="text-oro">${escaparHtml(req.nombre_servicio || req.servicio)}</span>
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                ${renderizarSeccionFeedback(req)}
                
                <div class="kd-sec">
                    <div class="kd-sec-title text-oro">DETALLES DEL REQUERIMIENTO</div>
                    <div class="mb-4">
                        <span class="kd-label">Objetivo de Comunicación</span>
                        <div class="kd-val">${escaparHtml(req.objetivo_comunicacion || '---')}</div>
                    </div>
                    <div class="mb-4">
                        <span class="kd-label">Descripción Detallada</span>
                        <div class="kd-val text-pre-wrap">${escaparHtml(req.descripcion || 'Sin descripción.')}</div>
                    </div>
                    
                    <div class="mb-4">
                        <span class="kd-label">Archivos del Cliente</span>
                        ${renderizarGridArchivos(archivos)}
                    </div>

                    ${req.url_subida ? `
                    <div class="mb-0">
                        <span class="kd-label">Enlace / URLs enviadas</span>
                        <div class="mt-1">
                            <a href="${escaparHtml(req.url_subida)}" target="_blank" class="text-info text-decoration-underline font-size-13">${escaparHtml(req.url_subida)}</a>
                        </div>
                    </div>` : ''}
                </div>
            </div>

            <div class="col-lg-4">
                <div class="bg-0a border border-dark br-12 p-4 position-sticky top-0">
                    <div class="font-bebas font-size-15 letter-spacing-2 text-muted mb-3">EXPEDIENTE</div>
                    
                    <div class="mb-4">
                        <span class="kd-label">Especialista Asignado</span>
                        ${renderizarBadgeEmpleado(req)}
                    </div>

                    <div class="mb-3">
                        <span class="kd-label">Solicitado por</span>
                        <div class="text-white font-weight-700 font-size-13">${escaparHtml(req.nombre_cliente || '---')}</div>
                        <div class="text-muted-extra-small">${escaparHtml(req.nombre_empresa)}</div>
                    </div>

                    <hr class="border-dark opacity-20">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="kd-label m-0">Fecha Requerida:</span>
                        <span class="text-white font-size-12 font-weight-700">${formatearFechaLimpia(req.fecharequerida)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="kd-label m-0">Fecha Solicitud:</span>
                        <span class="text-dim-small m-0">${formatearFechaLimpia(req.fechacreacion)}</span>
                    </div>
                    
                    <button class="btn btn-retro-action w-100 justify-content-center py-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> CERRAR VISTA
                    </button>
                </div>
            </div>
        </div>
    </div>`;

    cuerpo.innerHTML = html;
}

/**
 * Renderiza la sección de feedback/observaciones
 * @param {*} req 
 * @returns 
 */
function renderizarSeccionFeedback(req) {
    if (!req.url_entrega && !req.observacion_revision) return '';
    
    return `
    <div class="retro-msg-container mb-4">
        <div class="retro-msg-label">OBSERVACIONES DE REVISIÓN</div>
        ${req.url_entrega ? `
        <div class="mb-3">
            <span class="kd-label">Link de Entregable</span>
            <div class="mt-1">
                <a href="${req.url_entrega}" target="_blank" class="text-info text-decoration-underline font-size-14 word-break">${req.url_entrega}</a>
            </div>
        </div>` : ''}
        
        ${req.observacion_revision ? `
        <div>
            <span class="kd-label">Comentarios para Ajuste</span>
            <p class="retro-msg-text mt-2">"${req.observacion_revision}"</p>
        </div>` : ''}
    </div>`;
}

/**
 * Renderiza el grid de archivos adjuntos
 * @param {*} archivos 
 * @returns 
 */
function renderizarGridArchivos(archivos) {
    if (!archivos || archivos.length === 0) return '<p class="text-muted-extra-small italic">No hay archivos adjuntos.</p>';
    
    return `
    <div class="archivos-grid">
        ${archivos.map(a => `
            <div class="archivo-item" onclick="abrirArchivo(${a.id})">
                <i class="bi ${getFileIcon(a.nombre)} archivo-icon"></i>
                <span class="archivo-name" title="${escaparHtml(a.nombre)}">${escaparHtml(a.nombre)}</span>
            </div>
        `).join('')}
    </div>`;
}

/**
 * Renderiza el badge del empleado asignado
 * @param {*} req 
 * @returns 
 */
function renderizarBadgeEmpleado(req) {
    if (!req.idempleado) return '<div class="text-muted-extra-small italic">Sin asignar</div>';
    
    const ini = obtenerIniciales(req.empleado_nombre);
    return `
    <div class="empleado-badge">
        <div class="empleado-avatar-mini">${ini}</div>
        <div class="empleado-info-mini">
            <div class="empleado-name-mini">${escaparHtml(req.empleado_nombre)}</div>
            <div class="empleado-label-mini">Especialista</div>
        </div>
    </div>`;
}

/* Helpers de Utilidad */

// Escapa caracteres especiales de HTML para prevenir ataques XSS
function escaparHtml(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

// Obtiene las iniciales de un nombre completo
function obtenerIniciales(n) {
    if (!n) return '?';
    const p = n.trim().split(' ');
    return ((p[0]?.[0] || '') + (p[1]?.[0] || '')).toUpperCase();
}

// Retorna un icono de Bootstrap según la extensión del archivo
function getFileIcon(n) {
    const e = n?.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif'].includes(e)) return 'bi-file-earmark-image';
    if (e === 'pdf') return 'bi-file-earmark-pdf';
    if (['zip', 'rar'].includes(e)) return 'bi-file-earmark-zip';
    return 'bi-file-earmark-text';
}

//  Formatea una fecha de DB a un formato legible por el usuario (DD/MM/YYYY HH:MM)
function formatearFechaLimpia(f) {
    if (!f) return '---';
    try {
        const d = new Date(f.includes('.') ? f.split('.')[0] : f.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return f;
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    } catch(e) { return f; }
}

// Abre la vista previa de un archivo en una nueva pestaña
function abrirArchivo(id) {
    const url = `${window.BASE_URL || '/'}responsable/archivos/vista-previa/${id}`;
    window.open(url, '_blank');
}