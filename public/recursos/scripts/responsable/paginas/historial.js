/**
 * Obtiene y muestra el detalle completo de un pedido del historial
 * @param {*} idAtencion 
 */
async function verDetalleHistorial(idAtencion) {
    // Notificación de carga
    Swal.fire({
        title: 'CARGANDO EXPEDIENTE',
        html: 'Buscando registros en el historial...',
        background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#0d0d0d",
        color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const baseUrl = window.BASE_URL || '/';

    try {
        // Cargamos detalle e historial de asignaciones en paralelo
        const [response, responseHist] = await Promise.all([
            fetch(`${baseUrl}responsable/pedidos/detalle?id=${idAtencion}`),
            fetch(`${baseUrl}responsable/empleados/historial-asignaciones?idatencion=${idAtencion}`).catch(() => null)
        ]);

        const res = await response.json();
        let histData = { success: false, data: [] };
        if (responseHist) {
            try {
                histData = await responseHist.json();
            } catch (e) {
                console.error('Error parseando historial de asignaciones:', e);
            }
        }

        Swal.close();

        if (res.success) {
            renderizarDetalleHistorial(res.data, res.archivos, res.tracking, histData.data || []);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#0d0d0d", color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff" });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo establecer comunicación con el servidor', background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#0d0d0d", color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff" });
    }
}

/**
 * Renderiza la vista de detalle con estética Premium
 * Mantiene todas las funcionalidades originales pero con código más limpio y sin estilos inline
 * @param {*} req 
 * @param {*} archivos 
 * @param {*} tracking 
 * @param {*} historialAsignaciones 
 * @returns 
 */
function renderizarDetalleHistorial(req, archivos, tracking, historialAsignaciones = []) {
    const modalElement = document.getElementById('modalHistorial');
    if (!modalElement) { return };

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

        <!-- HISTORIAL DE ASIGNACIONES (Solo si se ha reasignado) -->
        ${_renderHistorialAsignaciones(historialAsignaciones)}
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
    } catch (e) { return f; }
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

/**
 * Renderiza la sección de historial de asignaciones como una línea de tiempo.
 * Solo se muestra si el historial no está vacío (es decir, si se ha reasignado).
 * @param {Array} historial
 * @returns {string} HTML
 */
function _renderHistorialAsignaciones(historial) {
    if (!historial || historial.length === 0) return '';

    const items = historial.map((h, i) => {
        const nombreAnterior = h.nombre_anterior
            ? `${escaparHtml(h.nombre_anterior)} ${escaparHtml(h.apellidos_anterior || '')}`
            : '<em>Sin asignar</em>';
        const nombreNuevo = `${escaparHtml(h.nombre_nuevo || '')} ${escaparHtml(h.apellidos_nuevo || '')}`;
        const responsable = `${escaparHtml(h.nombre_responsable || '')} ${escaparHtml(h.apellidos_responsable || '')}`;
        const fecha = h.fecha_asignacion ? formatearFechaLimpia(h.fecha_asignacion) : '---';

        return `
            <div class="hist-asig-item ${i === 0 ? 'hist-asig-item--latest' : ''}">
                <div class="hist-asig-dot"></div>
                <div class="hist-asig-body">
                    <div class="hist-asig-transfer">
                        <span class="hist-asig-from">${nombreAnterior}</span>
                        <i class="bi bi-arrow-right hist-asig-arrow"></i>
                        <span class="hist-asig-to">${nombreNuevo}</span>
                    </div>
                    <div class="hist-asig-motivo">"${escaparHtml(h.motivo_cambio || 'Sin motivo registrado')}"</div>
                    <div class="hist-asig-meta">
                        <i class="bi bi-person-gear me-1"></i>${responsable}
                        <span class="hist-asig-sep">·</span>
                        <i class="bi bi-clock me-1"></i>${fecha}
                    </div>
                </div>
            </div>`;
    }).join('');

    return `
        <div class="hist-asig-section">
            <div class="hist-asig-header">
                <i class="bi bi-arrow-left-right me-2"></i>HISTORIAL DE REASIGNACIONES
                <span class="hist-asig-count">${historial.length}</span>
            </div>
            <div class="hist-asig-timeline">
                ${items}
            </div>
        </div>`;
}

// Paginación e Historial Dinámico del Responsable

// Variables y Estado Global
let misCompletados = window.MIS_COMPLETADOS || [];
let areaCompletados = window.AREA_COMPLETADOS || [];
let pagePersonal = 1;
let pageArea = 1;
let searchText = "";
const itemsPerPage = 10;

// Valida si un pedido coincide con los criterios de búsqueda
function matchesFilter(pedido, query) {
    if (!query) return true;
    const q = query.toLowerCase();

    // Obtener fechas para la búsqueda
    const fechaFormateada = pedido.fechacompletado ? formatearFechaLimpia(pedido.fechacompletado).toLowerCase() : "";
    const fechaOriginal = pedido.fechacompletado ? pedido.fechacompletado.toLowerCase() : "";

    return (
        (pedido.titulo && pedido.titulo.toLowerCase().includes(q)) ||
        (pedido.empresa_nombre && pedido.empresa_nombre.toLowerCase().includes(q)) ||
        (pedido.servicio_nombre && pedido.servicio_nombre.toLowerCase().includes(q)) ||
        (pedido.empleado_nombre && pedido.empleado_nombre.toLowerCase().includes(q)) ||
        (pedido.id_requerimiento && String(pedido.id_requerimiento).includes(q)) ||
        (pedido.prioridad && pedido.prioridad.toLowerCase().includes(q)) ||
        (fechaOriginal && fechaOriginal.includes(q)) ||
        (fechaFormateada && fechaFormateada.includes(q))
    );
}

// Genera card del historial personal
function buildPersonalCard(pedido) {
    const fechacompletado = (pedido.fechacompletado);
    return `
    <div class="pedido-card-historial">
        <div class="historial-header">
            <div>
                <div class="historial-empresa">
                    ${escaparHtml(pedido.empresa_nombre?.toUpperCase())} — #REQ-${pedido.id_requerimiento}
                </div>
                <h3 class="historial-titulo">${escaparHtml(pedido.titulo)}</h3>
            </div>
            <span class="historial-status">
                <i class="bi bi-check-circle-fill"></i> FINALIZADO
            </span>
        </div>

        <div class="historial-body">
            <div class="historial-info-item">
                <span class="historial-info-label">Servicio</span>
                <span class="historial-info-value"><i class="bi bi-gear-fill"></i>
                    ${escaparHtml(pedido.servicio_nombre)}</span>
            </div>
            <div class="historial-info-item">
                <span class="historial-info-label">Completado</span>
                <span class="historial-info-value"><i class="bi bi-calendar-check-fill"></i>
                    ${fechacompletado}</span>
            </div>
            <div class="historial-info-item">
                <span class="historial-info-label">Prioridad</span>
                <span class="historial-info-value"><i class="bi bi-flag-fill"></i>
                    ${escaparHtml(pedido.prioridad)}</span>
            </div>
        </div>

        <div class="historial-footer">
            <button class="btn-historial-detalle" onclick="verDetalleHistorial(${pedido.id})">
                <i class="bi bi-eye"></i> VER DETALLE COMPLETO
            </button>
        </div>
    </div>`;
}

// Genera card del historial del área
function buildAreaCard(pedido) {
    const fechacompletado = formatearFechaLimpia(pedido.fechacompletado);
    return `
    <div class="pedido-card-historial">
        <div class="historial-header">
            <div>
                <div class="historial-empresa">
                    ${escaparHtml(pedido.empresa_nombre?.toUpperCase())} — #REQ-${pedido.id_requerimiento}
                </div>
                <h3 class="historial-titulo">${escaparHtml(pedido.titulo)}</h3>
            </div>
            <span class="historial-status">
                <i class="bi bi-check-circle-fill"></i> FINALIZADO
            </span>
        </div>

        <div class="historial-body">
            <div class="historial-info-item">
                <span class="historial-info-label">Ejecutor</span>
                <span class="historial-info-value"><i class="bi bi-person-fill"></i>
                    ${escaparHtml(pedido.empleado_nombre || 'Desconocido')}</span>
            </div>
            <div class="historial-info-item">
                <span class="historial-info-label">Servicio</span>
                <span class="historial-info-value"><i class="bi bi-gear-fill"></i>
                    ${escaparHtml(pedido.servicio_nombre)}</span>
            </div>
            <div class="historial-info-item">
                <span class="historial-info-label">Finalización</span>
                <span class="historial-info-value"><i class="bi bi-calendar-check-fill"></i>
                    ${fechacompletado}</span>
            </div>
        </div>

        <div class="historial-footer">
            <button class="btn-historial-detalle" onclick="verDetalleHistorial(${pedido.id})">
                <i class="bi bi-eye"></i> VER DETALLE COMPLETO
            </button>
        </div>
    </div>`;
}

// Genera HTML de paginador
function renderPaginationHTML(page, totalPages, totalItems, type) {
    if (totalPages <= 1) return "";
    
    let html = `
        <div class="card-footer bg-transparent border-none d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
            <small class="text-dim" style="font-weight: 600;">
                Mostrando página ${page} de ${totalPages} (Total: ${totalItems} completados)
            </small>
            <nav aria-label="Paginación de historial">
                <ul class="pagination pagination-rf mb-0">
                    <li class="page-item ${page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPaginaTab('${type}', ${page - 1}); return false;" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                    </li>`;

    const range = 2;
    const startPage = Math.max(1, page - range);
    const endPage = Math.min(totalPages, page + range);

    if (startPage > 1) {
        html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="cambiarPaginaTab('${type}', 1); return false;">1</a>
                    </li>`;
        if (startPage > 2) {
            html += `
                    <li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `
                    <li class="page-item ${page === i ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPaginaTab('${type}', ${i}); return false;">${i}</a>
                    </li>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `
                    <li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="cambiarPaginaTab('${type}', ${totalPages}); return false;">${totalPages}</a>
                    </li>`;
    }

    html += `
                    <li class="page-item ${page === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPaginaTab('${type}', ${page + 1}); return false;" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>`;
        
    return html;
}

// Render del listado de historial personal
function renderPersonalTab() {
    const container = document.getElementById("contenedor-mis-completados");
    const paginCont = document.getElementById("paginacion-mis-completados");
    const counter = document.getElementById("historial-counter-personal");
    if (!container) return;

    const filtered = misCompletados.filter(p => matchesFilter(p, searchText));
    const totalItems = filtered.length;

    if (totalItems === 0) {
        container.innerHTML = `
            <div class="historial-empty">
                <i class="bi bi-clock-history"></i>
                <p>Aún no tienes tareas personales finalizadas con ese criterio</p>
            </div>`;
        paginCont.innerHTML = "";
        counter.textContent = "0 tareas personales";
        return;
    }

    const totalPages = Math.ceil(totalItems / itemsPerPage);
    if (pagePersonal > totalPages) pagePersonal = totalPages;
    if (pagePersonal < 1) pagePersonal = 1;

    const startIdx = (pagePersonal - 1) * itemsPerPage;
    const paginated = filtered.slice(startIdx, startIdx + itemsPerPage);

    container.innerHTML = paginated.map(p => buildPersonalCard(p)).join("");
    paginCont.innerHTML = renderPaginationHTML(pagePersonal, totalPages, totalItems, 'personal');
    counter.textContent = `${totalItems} tarea${totalItems !== 1 ? 's' : ''} personal${totalItems !== 1 ? 'es' : ''} finalizada${totalItems !== 1 ? 's' : ''}`;
}

// Render del listado de historial del área
function renderAreaTab() {
    const container = document.getElementById("contenedor-area-completados");
    const paginCont = document.getElementById("paginacion-area-completados");
    const counter = document.getElementById("historial-counter-area");
    if (!container) return;

    const filtered = areaCompletados.filter(p => matchesFilter(p, searchText));
    const totalItems = filtered.length;

    if (totalItems === 0) {
        container.innerHTML = `
            <div class="historial-empty">
                <i class="bi bi-people-fill"></i>
                <p>No hay tareas finalizadas en el área con ese criterio</p>
            </div>`;
        paginCont.innerHTML = "";
        counter.textContent = "0 tareas del área";
        return;
    }

    const totalPages = Math.ceil(totalItems / itemsPerPage);
    if (pageArea > totalPages) pageArea = totalPages;
    if (pageArea < 1) pageArea = 1;

    const startIdx = (pageArea - 1) * itemsPerPage;
    const paginated = filtered.slice(startIdx, startIdx + itemsPerPage);

    container.innerHTML = paginated.map(p => buildAreaCard(p)).join("");
    paginCont.innerHTML = renderPaginationHTML(pageArea, totalPages, totalItems, 'area');
    counter.textContent = `${totalItems} tarea${totalItems !== 1 ? 's' : ''} del área finalizada${totalItems !== 1 ? 's' : ''}`;
}

// Handler de eventos de paginación
window.cambiarPaginaTab = (type, nuevaPagina) => {
    if (type === 'personal') {
        pagePersonal = nuevaPagina;
        renderPersonalTab();
    } else {
        pageArea = nuevaPagina;
        renderAreaTab();
    }
    // Desplazamiento suave al principio de las pestañas
    document.getElementById('pills-tab').scrollIntoView({ behavior: 'smooth' });
};

// Event listener al cargar la página
document.addEventListener("DOMContentLoaded", function () {
    const inputBuscar = document.getElementById("buscador-historial");
    if (inputBuscar) {
        let timer;
        inputBuscar.addEventListener("input", function (e) {
            clearTimeout(timer);
            timer = setTimeout(() => {
                searchText = e.target.value;
                pagePersonal = 1;
                pageArea = 1;
                renderPersonalTab();
                renderAreaTab();
            }, 300);
        });
    }

    // Carga inicial
    renderPersonalTab();
    renderAreaTab();
});