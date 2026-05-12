/**
 * Obtiene y muestra el detalle completo de un pedido del historial
 * @param {*} idAtencion 
 */
async function verDetalleHistorial(idAtencion) {
    // Notificación de carga
    Swal.fire({
        title: 'CARGANDO EXPEDIENTE',
        html: 'Buscando registros en el historial...',
        background: '#0d0d0d',
        color: '#fff',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const baseUrl = window.BASE_URL || '/';
    
    try {
        const response = await fetch(`${baseUrl}responsable/pedidos/detalle?id=${idAtencion}`);
        const res = await response.json();
        
        Swal.close();

        if (res.success) {
            renderizarDetalleHistorial(res.data, res.archivos, res.tracking);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#0d0d0d', color: '#fff' });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo establecer comunicación con el servidor', background: '#0d0d0d', color: '#fff' });
    }
}

/**
 * Renderiza la vista de detalle con estética Premium
 * Mantiene todas las funcionalidades originales pero con código más limpio y sin estilos inline
 * @param {*} req 
 * @param {*} archivos 
 * @param {*} tracking 
 * @returns 
 */
function renderizarDetalleHistorial(req, archivos, tracking) {
    const modalElement = document.getElementById('modalHistorial');
    if (!modalElement){return };

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const cuerpo = document.getElementById('modal-cuerpo-historial');
    const titulo = document.getElementById('modal-titulo-historial');

    // Título con ID de Requerimiento
    if (titulo) {
        titulo.innerHTML = `
            <span class="text-oro">#REQ-${req.idrequerimiento}</span> — ${escaparHtml(req.titulo)}
            ${req.observacion_revision ? '<span class="badge bg-danger ms-2 badge-devuelto">DEVUELTO</span>' : ''}
        `;
    }

    // Configuración de Estados y Prioridades usando clases CSS (en historial.css)
    const estadosMap = {
        'pendiente': { label: 'PENDIENTE', class: 'kd-pill-pendiente', icon: 'bi-clock-history' },
        'pendiente_asignado': { label: 'ASIGNADO', class: 'kd-pill-asignado', icon: 'bi-person-check' },
        'en_proceso': { label: 'EN PROCESO', class: 'kd-pill-proceso', icon: 'bi-play-circle' },
        'en_revision': { label: 'EN REVISIÓN', class: 'kd-pill-revision', icon: 'bi-eye' },
        'finalizado': { label: 'FINALIZADO', class: 'kd-pill-finalizado', icon: 'bi-check-circle' }
    };
    const es = estadosMap[req.estado] || { label: req.estado?.toUpperCase(), class: '', icon: 'bi-question-circle' };

    const priosMap = {
        'alta': { label: 'ALTA', class: 'kd-pill-pendiente', icon: 'bi-chevron-double-up' },
        'media': { label: 'MEDIA', class: 'kd-pill-proceso', icon: 'bi-chevron-up' },
        'baja': { label: 'BAJA', class: 'kd-pill-asignado', icon: 'bi-chevron-down' }
    };

    const pri = priosMap[req.prioridad?.toLowerCase()] || { label: req.prioridad?.toUpperCase(), class: '', icon: 'bi-dash' };

    // Clasificación de archivos
    const archivosCliente = archivos.filter(a => !a.idatencion);
    const archivosEntrega = archivos.filter(a => a.idatencion);

    // Sección de Entrega Final
    let entregaHtml = '';
    if (['finalizado', 'en_revision'].includes(req.estado)) {
        entregaHtml = `
            <div class="entrega-container mb-4">
                <div class="entrega-title"><i class="bi bi-send-check-fill me-2"></i> DETALLES DE LA ENTREGA FINAL</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="kd-label">URL DEL ENTREGABLE</label>
                        ${req.url_entrega ? 
                            `<a href="${req.url_entrega}" target="_blank" class="btn btn-sm btn-outline-success font-size-12"><i class="bi bi-link-45deg"></i> ABRIR TRABAJO FINAL</a>` : 
                            '<span class="text-dim font-size-12 italic">No se proporcionó una URL externa</span>'}
                    </div>
                    <div class="col-12">
                        <label class="kd-label">OBSERVACIONES DE ENTREGA</label>
                        <div class="kd-val bg-05 p-10 br-6 text-pre-wrap">${escaparHtml(req.observacion_revision || 'Sin observaciones adicionales.')}</div>
                    </div>
                    <div class="col-12">
                        <label class="kd-label">ARCHIVOS ADJUNTOS DE ENTREGA</label>
                        <div class="archivos-entrega-grid mt-1">
                            ${archivosEntrega.length > 0 ? archivosEntrega.map(a => `
                                <a href="${window.BASE_URL}${a.ruta}" target="_blank" class="archivo-entrega-item">
                                    <i class="bi ${getFileIcon(a.nombre)} archivo-entrega-icon"></i>
                                    <span class="archivo-entrega-name">${escaparHtml(a.nombre)}</span>
                                </a>
                            `).join('') : '<span class="text-dim font-size-12 italic">No hay archivos físicos adjuntos</span>'}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Estructura Principal del Detalle
    cuerpo.innerHTML = `
        <div class="row g-4">
            <div class="col-lg-8">
                ${entregaHtml}
                
                <div class="kd-sec">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="kd-pill ${es.class}"><i class="bi ${es.icon} me-1"></i>${es.label}</span>
                        <span class="kd-pill ${pri.class}"><i class="bi ${pri.icon} me-1"></i>PRIORIDAD ${pri.label}</span>
                    </div>

                    <div class="kd-sec-title">DATOS DEL REQUERIMIENTO</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="kd-label">Objetivo de Comunicación</label>
                            <div class="kd-val br-6">${escaparHtml(req.objetivo_comunicacion || '---')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="kd-label">Público Objetivo</label>
                            <div class="kd-val br-6">${escaparHtml(req.publico_objetivo || '---')}</div>
                        </div>
                        <div class="col-12">
                            <hr class="border-dark my-2">
                            <label class="kd-label">Descripción Detallada</label>
                            <div class="kd-val text-pre-wrap bg-05 p-10 br-6">${escaparHtml(req.descripcion || 'Sin descripción.')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="kd-label">Canales de Difusión</label>
                            <div class="d-flex flex-wrap gap-1 mt-1">${formatearLista(req.canales_difusion)}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="kd-label">Formatos Solicitados</label>
                            <div class="d-flex flex-wrap gap-1 mt-1">${formatearLista(req.formatos_solicitados)}</div>
                        </div>
                        ${req.url_subida ? `
                        <div class="col-12">
                            <hr class="border-dark my-2">
                            <label class="kd-label">URL de Subida (Cliente)</label>
                            <div class="kd-val mt-1">
                                <a href="${escaparHtml(req.url_subida)}" target="_blank" class="text-info text-decoration-underline font-size-13">${escaparHtml(req.url_subida)}</a>
                            </div>
                        </div>
                        ` : ''}
                        <div class="col-12">
                            <label class="kd-label">Materiales de Referencia (Cliente)</label>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                ${archivosCliente.length > 0 ? archivosCliente.map(a => `
                                    <a href="${window.BASE_URL}${a.ruta}" target="_blank" class="badge bg-dark border border-secondary p-2 text-decoration-none text-truncate-rf">
                                        <i class="bi bi-paperclip me-1 text-oro"></i> ${escaparHtml(a.nombre)}
                                    </a>
                                `).join('') : '<span class="text-dim font-size-12 italic">No se adjuntaron materiales</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kd-sec bg-0a mb-4">
                    <div class="kd-sec-title">RESUMEN DEL PEDIDO</div>
                    <div class="mb-3"><label class="kd-label">Empresa / Cliente</label><div class="text-white-bold word-break">${escaparHtml(req.nombre_empresa)}</div></div>
                    <div class="mb-3"><label class="kd-label">Área / Departamento</label><div class="text-white-bold">${escaparHtml(req.nombre_area || '---')}</div></div>
                    <div class="mb-3"><label class="kd-label">Servicio Contratado</label><div class="text-white-bold word-break">${escaparHtml(req.nombre_servicio || req.servicio)}</div></div>
                    <div class="mb-3"><label class="kd-label">Solicitado por</label><div class="text-white-bold">${escaparHtml(req.nombre_cliente || '---')}</div></div>
                    <div class="mb-3"><label class="kd-label">Ejecutor Asignado</label><div class="text-oro-bold word-break">${escaparHtml(req.empleado_nombre || 'Sin asignar')}</div></div>
                    
                    <hr class="border-dark my-3">
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="kd-label m-0">Fecha Solicitud:</span>
                        <span class="text-dim font-size-12">${formatearFechaLimpia(req.fechacreacion)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="kd-label m-0">Fecha Finalización:</span>
                        <span class="text-green-bold font-size-12">${formatearFechaLimpia(req.fechacompletado)}</span>
                    </div>
                </div>

                <div class="timeline-panel">
                    <div class="timeline-title"><i class="bi bi-clock-history me-2"></i> LÍNEA DE TIEMPO</div>
                    <div class="timeline-scroll">
                        ${tracking && tracking.length > 0 ? tracking.map(t => `
                            <div class="timeline-entry">
                                <div class="timeline-dot" style="background: ${t.estado === 'finalizado' ? 'var(--verde)' : '#444'};"></div>
                                <div class="timeline-action">${escaparHtml(t.accion)}</div>
                                <div class="timeline-date">${formatearFechaLimpia(t.fecha_registro)}</div>
                            </div>
                        `).join('') : '<p class="text-center text-dim py-3">No hay registros de actividad.</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;

    modal.show();
}

/* Helpers y Utilidades de UI */

/**
 * Escapa caracteres especiales de HTML para prevenir ataques XSS
 * @param {*} t 
 * @returns 
 */
function escaparHtml(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

/**
 * Retorna un icono de Bootstrap según la extensión del archivo
 * @param {*} n 
 * @returns 
 */
function getFileIcon(n) {
    const e = n?.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif'].includes(e)) return 'bi-file-earmark-image';
    if (e === 'pdf') return 'bi-file-earmark-pdf';
    if (['zip', 'rar'].includes(e)) return 'bi-file-earmark-zip';
    if (['doc', 'docx'].includes(e)) return 'bi-file-earmark-word';
    return 'bi-file-earmark-text';
}

/**
 * Formatea una fecha de DB a un formato legible por el usuario (DD/MM/YYYY HH:MM)
 * @param {*} f 
 * @returns 
 */
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

/**
 * Convierte un string o JSON de items en una lista de tags HTML (Spans)
 * @param {*} v 
 * @returns 
 */
function formatearLista(v) {
    if (!v) return '';
    let items = [];
    try {
        items = v.startsWith('[') ? JSON.parse(v) : v.split(',').map(s => s.trim());
    } catch (e) {
        items = v.split(',').map(s => s.trim());
    }
    return items.filter(i => i).map(item => `<span class="kd-tag">${escaparHtml(item)}</span>`).join('');
}