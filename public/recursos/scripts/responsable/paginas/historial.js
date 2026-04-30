/**
 * RF MARKETING - Dashboard Responsable
 * Logic for History View
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Historial JS Loaded');
});

/**
 * Open detail modal for a completed task
 * @param {number} idAtencion 
 */
function verDetalleHistorial(idAtencion) {
    Swal.fire({
        title: 'Obteniendo detalles...',
        background: '#0d0d0d',
        color: '#fff',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Determinar BASE_URL de forma segura
    const baseUrl = window.BASE_URL || window.base_url || '/';
    const fetchUrl = `${baseUrl}responsable/pedidos/detalle?id=${idAtencion}`;

    fetch(fetchUrl)
        .then(response => response.json())
        .then(res => {
            Swal.close();
            if (res.success) {
                renderizarDetalleHistorialPremium(res.data, res.archivos, res.tracking);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'No se pudieron cargar los detalles',
                    background: '#0d0d0d',
                    color: '#fff',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al servidor',
                background: '#0d0d0d',
                color: '#fff',
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        });
}

/**
 * Render the detail modal content with the PREMIUM design (matching en_proceso.js)
 */
function renderizarDetalleHistorialPremium(req, archivos, tracking) {
    const modal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    const cuerpo = document.getElementById('modal-cuerpo-historial');
    const titulo = document.getElementById('modal-titulo-historial');

    titulo.innerHTML = `
        #REQ-${req.idrequerimiento} — ${req.titulo}
        ${req.observacion_revision ? '<span class="badge bg-danger ms-2" style="font-size:10px; vertical-align:middle;">DEVUELTO</span>' : ''}
    `;

    // Estilos de los estados
    const estados = {
        'pendiente': { label: 'PENDIENTE', c: '#ef4444', i: 'bi-clock-history' },
        'pendiente_asignado': { label: 'ASIGNADO', c: '#3b82f6', i: 'bi-person-check' },
        'en_proceso': { label: 'EN PROCESO', c: '#f5c400', i: 'bi-play-circle' },
        'en_revision': { label: 'EN REVISIÓN', c: '#a855f7', i: 'bi-eye' },
        'finalizado': { label: 'FINALIZADO', c: '#22c55e', i: 'bi-check-circle' }
    };
    const es = estados[req.estado] || { label: req.estado.toUpperCase(), c: '#999', i: 'bi-question-circle' };

    // Prioridad
    const prios = {
        'alta': { label: 'ALTA', c: '#ef4444', i: 'bi-chevron-double-up' },
        'media': { label: 'MEDIA', c: '#f5c400', i: 'bi-chevron-up' },
        'baja': { label: 'BAJA', c: '#3b82f6', i: 'bi-chevron-down' }
    };
    const p = (req.prioridad || 'media').toLowerCase();
    const pri = prios[p] || { label: p.toUpperCase(), c: '#999', i: 'bi-dash' };

    // Filtrar archivos
    const archivosCliente = archivos.filter(a => !a.idatencion);
    const archivosEntrega = archivos.filter(a => a.idatencion);

    // Fechas
    const fSol = req.fecha_formateada || req.fechacreacion;
    const fCom = req.fechacompletado;

    // ── HTML de la Entrega (Si existe) ──
    let entregaHtml = '';
    if (req.estado === 'finalizado' || req.estado === 'en_revision') {
        let arcEntHtml = '';
        if (archivosEntrega.length > 0) {
            arcEntHtml = '<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">';
            archivosEntrega.forEach(a => {
                arcEntHtml += `
                    <a href="${window.BASE_URL}${a.ruta}" target="_blank" style="display:flex;align-items:center;background:#111;border:1px solid #22c55e44;padding:8px 12px;border-radius:8px;color:#ddd;text-decoration:none;font-size:12px;gap:8px;transition:0.2s;">
                        <i class="bi bi-file-earmark-check-fill" style="color:#22c55e;"></i>
                        <span style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escaparHtml(a.nombre)}</span>
                    </a>
                `;
            });
            arcEntHtml += '</div>';
        }

        entregaHtml = `
            <div style="background:rgba(34,197,94,0.05); border:1px solid rgba(34,197,94,0.15); border-left-width:4px; border-left-color:#22c55e; border-radius:10px; padding:20px; margin-bottom:20px;">
                <div style="font-family:'Bebas Neue',sans-serif; font-size:17px; letter-spacing:1.5px; color:#22c55e; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-send-check-fill"></i> DETALLES DE LA ENTREGA FINAL
                </div>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label style="font-size:10px; font-weight:800; color:#555; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:6px;">URL DE TRABAJO</label>
                        ${req.url_entrega ? `<a href="${req.url_entrega}" target="_blank" class="btn btn-sm btn-outline-success" style="font-size:12px;"><i class="bi bi-link-45deg"></i> ABRIR ENTREGABLE</a>` : '<span style="color:#444; font-size:12px; font-style:italic;">No se proporcionó URL</span>'}
                    </div>
                    <div class="col-md-12">
                        <label style="font-size:10px; font-weight:800; color:#555; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:6px;">OBSERVACIONES / NOTAS</label>
                        <p style="color:#bbb; font-size:13px; line-height:1.6; margin:0;">${req.observacion_revision || 'Sin observaciones adicionales.'}</p>
                    </div>
                    <div class="col-md-12">
                        <label style="font-size:10px; font-weight:800; color:#555; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:6px;">ARCHIVOS ADJUNTOS</label>
                        ${arcEntHtml || '<span style="color:#444; font-size:12px; font-style:italic;">No hay archivos físicos.</span>'}
                    </div>
                </div>
            </div>
        `;
    }

    // ── Tracking HTML ──
    let trackingHtml = '';
    if (tracking && tracking.length > 0) {
        trackingHtml = `
            <div style="background:#0a0a0a; border:1px solid #1e1e1e; border-radius:10px; padding:20px; height:100%;">
                <div style="font-family:'Bebas Neue',sans-serif; font-size:17px; letter-spacing:1.5px; color:var(--amarillo); margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-clock-history"></i> LÍNEA DE TIEMPO
                </div>
                <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                    ${tracking.map(t => `
                        <div style="border-left: 2px solid #222; padding-left: 20px; position: relative; padding-bottom: 25px;">
                            <div style="position: absolute; left: -7px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: ${t.estado === 'finalizado' ? '#22c55e' : '#444'}; border: 3px solid #0a0a0a;"></div>
                            <div style="font-size: 13px; font-weight: 700; color: #eee; margin-bottom: 4px;">${t.accion}</div>
                            <div style="font-size: 11px; color: #555;">${t.fecha_registro}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    const html = `
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Entrega Primero -->
                ${entregaHtml}

                <div class="kd-sec">
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
                        <span class="kd-pill" style="border-color:${es.c}44; color:${es.c};"><i class="bi ${es.i} me-1"></i>${es.label}</span>
                        <span class="kd-pill" style="border-color:${pri.c}44; color:${pri.c};"><i class="bi ${pri.i} me-1"></i>PRIORIDAD ${pri.label}</span>
                    </div>
 
                    <div class="kd-sec-title">SOLICITUD ORIGINAL</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="kd-label">Objetivo de Comunicación</span>
                            <div class="kd-val" style="max-height:100px; overflow-y:auto; padding-right:5px; white-space:pre-wrap;">${escaparHtml(req.objetivo_comunicacion || '---')}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="kd-label">Público Objetivo</span>
                            <div class="kd-val" style="max-height:100px; overflow-y:auto; padding-right:5px; white-space:pre-wrap;">${escaparHtml(req.publico_objetivo || '---')}</div>
                        </div>
                        <div class="col-12">
                            <hr style="border-color:#1e1e1e; margin:10px 0;">
                            <span class="kd-label">Descripción Detallada</span>
                            <div class="kd-val" style="white-space:pre-wrap; max-height:200px; overflow-y:auto; padding-right:10px; word-break: break-word; background: #050505; padding: 10px; border-radius: 6px;">${escaparHtml(req.descripcion || 'Sin descripción.')}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="kd-label">Canales de Difusión</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">${formatearLista(req.canales_difusion)}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="kd-label">Formatos Solicitados</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">${formatearLista(req.formatos_solicitados)}</div>
                        </div>
                        ${req.url_subida ? `
                        <div class="col-12">
                            <hr style="border-color:#1e1e1e; margin:10px 0;">
                            <span class="kd-label">URL de Subida (Cliente)</span>
                            <div class="kd-val" style="margin-top:5px;">
                                <a href="${escaparHtml(req.url_subida)}" target="_blank" style="color:#60a5fa; text-decoration:underline; font-size:13px;">${escaparHtml(req.url_subida)}</a>
                            </div>
                        </div>
                        ` : ''}
                        <div class="col-12">
                            <span class="kd-label">Materiales de Referencia (Cliente)</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                ${archivosCliente.length > 0 ? archivosCliente.map(a => `
                                    <a href="${window.BASE_URL}${a.ruta}" target="_blank" class="badge bg-dark border border-secondary text-secondary p-2 text-decoration-none" style="font-size:10px; font-weight:400; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <i class="bi bi-paperclip me-1"></i> ${escaparHtml(a.nombre)}
                                    </a>
                                 `).join('') : '<span style="color:#444; font-size:12px; font-style:italic;">No hay adjuntos.</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
            <div class="col-lg-4">
                <div class="kd-sec" style="background:#0a0a0a; margin-bottom: 20px;">
                    <div class="kd-sec-title">RESUMEN DEL PEDIDO</div>
                    
                    <div class="mb-3">
                        <span class="kd-label">Empresa</span>
                        <div style="font-weight:700; color:#fff; word-break: break-word;">${escaparHtml(req.nombre_empresa)}</div>
                    </div>
                    <div class="mb-3">
                        <span class="kd-label">Área / Departamento</span>
                        <div style="font-weight:700; color:#fff;">${escaparHtml(req.nombre_area || '---')}</div>
                    </div>
                    <div class="mb-3">
                        <span class="kd-label">Servicio</span>
                        <div style="font-weight:700; color:#fff; word-break: break-word;">${escaparHtml(req.nombre_servicio || req.servicio)}</div>
                    </div>
                    <div class="mb-3">
                        <span class="kd-label">Solicitado por</span>
                        <div style="font-weight:700; color:#fff;">${escaparHtml(req.nombre_cliente || '---')}</div>
                    </div>
                    <div class="mb-3">
                        <span class="kd-label">Ejecutor / Especialista</span>
                        <div style="font-weight:700; color:var(--amarillo); word-break: break-word;">${escaparHtml(req.empleado_nombre || '---')}</div>
                    </div>
                    
                    <hr style="border-color:#1e1e1e; margin:15px 0;">
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="kd-label" style="margin:0;">Solicitado el:</span>
                        <span style="font-size:12px; color:#aaa;">${formatearFechaLimpia(fSol)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="kd-label" style="margin:0;">Completado el:</span>
                        <span style="font-size:12px; color:#22c55e; font-weight:700;">${formatearFechaLimpia(fCom)}</span>
                    </div>
                </div>

                <div style="background:#0a0a0a; border:1px solid #1e1e1e; border-radius:10px; padding:20px; max-height: 450px; display: flex; flex-direction: column;">
                    <div style="font-family:'Bebas Neue',sans-serif; font-size:17px; letter-spacing:1.5px; color:var(--amarillo); margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-clock-history"></i> LÍNEA DE TIEMPO
                    </div>
                    <div style="overflow-y: auto; padding-right: 10px; flex: 1;">
                        ${tracking && tracking.length > 0 ? tracking.map(t => `
                            <div style="border-left: 2px solid #222; padding-left: 15px; position: relative; padding-bottom: 15px;">
                                <div style="position: absolute; left: -7px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: ${t.estado === 'finalizado' ? '#22c55e' : '#444'}; border: 3px solid #0a0a0a;"></div>
                                <div style="font-size: 12px; font-weight: 700; color: #eee; margin-bottom: 2px; word-break: break-word;">${t.accion}</div>
                                <div style="font-size: 10px; color: #555;">${formatearFechaLimpia(t.fecha_registro)}</div>
                            </div>
                        `).join('') : '<p style="color:#444; font-size:11px; text-align:center;">No hay registros.</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;

    cuerpo.innerHTML = html;
    modal.show();
}

/**
 * UI Helpers (Matches en_proceso.js style)
 */
function escaparHtml(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

function formatearFechaLimpia(fecha) {
    if (!fecha) return '---';
    
    // Si es un string con microsegundos (formato PostgreSQL común: YYYY-MM-DD HH:MM:SS.mmmmmm)
    // Lo limpiamos antes de procesar
    let cleanedFecha = fecha;
    if (typeof fecha === 'string' && fecha.includes('.')) {
        cleanedFecha = fecha.split('.')[0];
    }

    try {
        const d = new Date(cleanedFecha);
        if (isNaN(d.getTime())) return cleanedFecha;
        
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    } catch (e) {
        return cleanedFecha;
    }
}

function formatearLista(valor) {
    if (!valor) return '';
    let items = [];
    try {
        const parsed = JSON.parse(valor);
        items = Array.isArray(parsed) ? parsed : [String(parsed)];
    } catch (e) {
        items = valor.split(',').map(s => s.trim()).filter(s => s);
    }
    return items.map(item => `<span style="display:inline-block;background:#1e1e1e;color:#ddd;border:1px solid #333;padding:5px 12px;border-radius:6px;font-size:11px;">${escaparHtml(item)}</span>`).join('');
}
