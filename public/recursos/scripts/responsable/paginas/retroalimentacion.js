document.addEventListener('DOMContentLoaded', () => {
    // Inicialización si es necesaria
});

async function verDetalle(idAtencion) {
    const cuerpo = document.getElementById('detalleCuerpo');
    cuerpo.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:400px;gap:20px;background:#0a0a0a;border-radius:12px;">
            <div class="spinner-border text-warning" style="width: 3rem; height: 3rem; border-width: 4px;"></div>
            <div style="font-family:'Bebas Neue',sans-serif; letter-spacing:2px; color:#555; font-size:18px;">CARGANDO EXPEDIENTE...</div>
        </div>
    `;
    
    const myModal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    myModal.show();

    try {
        const response = await fetch(`${BASE_URL}responsable/pedidos/detalle?id=${idAtencion}`);
        const result = await response.json();

        if (!result.success) {
            cuerpo.innerHTML = `<div class="p-5 text-center text-danger">${result.message}</div>`;
            return;
        }

        window.requerimientoActualRetro = result.data; // Guardar para edición
        renderizarDetalle(result.data, result.archivos, result.tracking);
    } catch (error) {
        console.error(error);
        cuerpo.innerHTML = `<div class="p-5 text-center text-danger">Error al cargar el detalle</div>`;
    }
}

function renderizarDetalle(req, archivos, tracking) {
    const cuerpo = document.getElementById('detalleCuerpo');
    
    // Formateo de datos
    const fReq = req.fecharequerida;
    const fSol = req.fechacreacion;
    const fIni = req.fechainicio;
    const fFin = req.fechacompletado;

    // Colores y badges
    const pri = (req.prioridad || 'Media').toLowerCase();
    const priC = pri === 'alta' ? '#ef4444' : (pri === 'baja' ? '#3b82f6' : '#f59e0b');
    const priI = pri === 'alta' ? 'bi-arrow-up-circle-fill' : (pri === 'baja' ? 'bi-arrow-down-circle-fill' : 'bi-dash-circle-fill');

    const estadoMap = {
        pendiente_asignado: { c: '#f59e0b', label: 'POR ASIGNAR', i: 'bi-hourglass-split' },
        en_proceso: { c: '#F5C400', label: 'EN DESARROLLO', i: 'bi-lightning-charge-fill' },
        en_revision: { c: '#8b5cf6', label: 'EN REVISIÓN', i: 'bi-eye-fill' },
        finalizado: { c: '#22c55e', label: 'COMPLETADO', i: 'bi-check2-circle' },
        cancelado: { c: '#ef4444', label: 'CANCELADO', i: 'bi-x-circle-fill' }
    };
    const es = estadoMap[req.estado] || { c: '#aaa', label: req.estado, i: 'bi-circle' };

    // Bloque entrega si existe
    let entregaHtml = '';
    if (req.url_entrega || req.observacion_revision) {
        entregaHtml = _seccion('bi-box-arrow-up-right', 'Detalles de la Entrega / Observaciones', '#8b5cf6', `
            <div style="margin-bottom:15px;">
                ${_label('Link de entrega')}
                <div style="margin-top:5px;">
                    ${req.url_entrega ? `<a href="${req.url_entrega}" target="_blank" style="color:#60a5fa; text-decoration:underline; font-size:14px; word-break:break-all;">${req.url_entrega}</a>` : '<span class="text-muted small">Sin link de entrega</span>'}
                </div>
            </div>
            ${req.observacion_revision ? `
            <div>
                ${_label('Observaciones / Retroalimentación')}
                <div style="background:#1a1a1a; padding:12px; border-radius:8px; margin-top:8px; color:#f59e0b; font-size:14px; border-left:3px solid #f59e0b; font-style:italic;">
                    "${req.observacion_revision}"
                </div>
            </div>` : ''}
        `);
    }

    // Empleado Asignado
    let empleadoHtml = '';
    if (req.idempleado) {
        const ini = obtenerIniciales(req.empleado_nombre);
        empleadoHtml = `
            <div style="display:flex;align-items:center;gap:12px;margin-top:8px;background:#111;padding:10px;border-radius:10px;border:1px solid #1e1e1e;">
                <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#92400e);color:#000;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${ini}</div>
                <div style="min-width:0;">
                    <div style="color:#fff;font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escaparHtml(req.empleado_nombre)}</div>
                    <div style="color:#555;font-size:10px;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Especialista Asignado</div>
                </div>
            </div>`;
    } else {
        empleadoHtml = `<div class="text-muted small italic p-2 border border-dashed rounded" style="border-color:#333;">Sin asignar</div>`;
    }

    // Archivos
    let arcSolHtml = '';
    if (archivos && archivos.length) {
        arcSolHtml = `<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));gap:10px;margin-top:10px;">`;
        archivos.forEach(a => {
            const icon = getFileIcon(a.nombre);
            arcSolHtml += `
                <div class="archivo-item" onclick="abrirArchivo(${a.id})" style="background:#111;border:1px solid #1e1e1e;border-radius:8px;padding:10px;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:8px;">
                    <i class="bi ${icon}" style="color:#f59e0b;font-size:18px;"></i>
                    <span style="font-size:11px;color:#aaa;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escaparHtml(a.nombre)}</span>
                </div>`;
        });
        arcSolHtml += '</div>';
    } else {
        arcSolHtml = '<p style="color:#333;font-size:11px;font-style:italic;margin:8px 0 0;">No se adjuntaron archivos.</p>';
    }

    // URLs del Cliente
    let urlsClienteHtml = '';
    if (req.url_subida) {
        const link = `<a href="${escaparHtml(req.url_subida)}" target="_blank" style="color:#60a5fa;text-decoration:underline;font-size:13px;word-break:break-all;">${escaparHtml(req.url_subida)}</a>`;
        urlsClienteHtml = _seccion('bi-link-45deg', 'URLs enviadas por el Cliente', '#60a5fa', `
            <div>
                ${_label('Enlace / URLs')}
                <div style="margin-top:5px;">${link}</div>
            </div>
        `);
    }

    // HTML Final
    const html = `
    <style>
        .kd-sec { background: #0a0a0a; border: 1px solid #1e1e1e; border-left-width: 3px; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
        .kd-sec-title { font-family: 'Bebas Neue', sans-serif; font-size: 17px; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 14px; display: flex; align-items: center; gap: 7px; }
        .kd-label { font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #666; display: block; margin-bottom: 5px; }
        .kd-val { color: #e0e0e0; font-size: 15px; line-height: 1.7; word-wrap: break-word; overflow-wrap: break-word; word-break: break-word; }
        .kd-hr { border: none; border-top: 1px solid #1a1a1a; margin: 14px 0; }
        .kd-info-row { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; border-bottom: 1px solid #111; }
        .kd-info-row:last-child { border-bottom: none; }
    </style>

    <div style="font-family:'Segoe UI',system-ui,sans-serif;color:#c8c8c8;padding: 25px;">
        <div style="margin-bottom:20px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:12px;">
                ${_pill('', es.label, es.c, es.c + '18')}
                ${_pill('', (req.prioridad || 'Media'), priC, priC + '18')}
            </div>
            <h2 style="font-family:'Bebas Neue',sans-serif;color:#fff;font-size:32px;letter-spacing:2px;margin:0 0 5px; display:flex; align-items:center; gap:15px;">
                ${escaparHtml(req.titulo || 'Sin Título')}
                ${(req.nombre_servicio === 'Creación de Contenido') ? `
                    <button class="btn btn-sm btn-outline-warning" id="btn-editar-req-retro" onclick="activarEdicionRetroalimentacion()" style="font-size:11px; font-weight:700; height:24px; padding:0 10px; display:flex; align-items:center; gap:5px;">
                        <i class="bi bi-pencil-square"></i> EDITAR
                    </button>
                ` : ''}
            </h2>
            <p style="color:#777;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
                ${escaparHtml(req.nombre_empresa)} | ${escaparHtml(req.nombre_servicio || req.servicio)}
            </p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 285px;gap:25px;align-items:start;">
            <div>
                ${entregaHtml}
                
                ${_seccion('bi-bullseye', 'Objetivo de Comunicación', '#f59e0b', `
                    <div class="kd-val">${escaparHtml(req.objetivo_comunicacion || '---')}</div>
                `)}

                ${_seccion('bi-card-text', 'Descripción', '#555', `
                    <div class="kd-val" style="white-space:pre-wrap;">${escaparHtml(req.descripcion || 'Sin descripción.')}</div>
                `)}

                ${_seccion('bi-paperclip', 'Archivos Adjuntos', '#374151', arcSolHtml)}

                ${urlsClienteHtml}
            </div>

            <div>
                <div style="background:#0a0a0a;border:1px solid #1e1e1e;border-radius:12px;padding:20px;position:sticky;top:0;">
                    <div style="font-family:'Bebas Neue',sans-serif;font-size:15px;letter-spacing:2px;color:#555;margin-bottom:15px;">INFORMACIÓN DEL PEDIDO</div>
                    
                    <div class="mb-3">
                        <span class="kd-label">Área / Departamento</span>
                        <div style="color:#fff;font-weight:700;font-size:13px;">${escaparHtml(req.nombre_area || '---')}</div>
                    </div>

                    <div class="mb-4">
                        ${_label('Empleado asignado')}
                        ${empleadoHtml}
                    </div>

                    <hr class="kd-hr">

                    <div class="mb-3">
                        ${_label('Solicitado por')}
                        <div style="color:#fff;font-weight:700;font-size:13px;">${escaparHtml(req.nombre_cliente || '---')}</div>
                        <div style="color:#555;font-size:11px;">${escaparHtml(req.nombre_empresa || '---')}</div>
                    </div>

                    <div class="kd-info-row">
                        <div>
                            ${_label('Fecha requerida')}
                            <div style="color:${fReq ? '#f0f0f0' : '#555'};font-size:14px;font-weight:700;">${formatearFechaLimpia(fReq)}</div>
                        </div>
                    </div>

                    <div class="kd-info-row">
                        <div>
                            ${_label('Fecha de solicitud')}
                            <div style="color:#f0f0f0;font-size:14px;font-weight:700;">${formatearFechaLimpia(fSol)}</div>
                        </div>
                    </div>
                    
                    <hr class="kd-hr">

                    <div>
                        ${_label('Estado actual')}
                        <div style="margin-top:8px;">${_pill('', es.label, es.c, es.c + '18')}</div>
                    </div>
                    
                    <button class="btn w-100 mt-4" style="background:#f59e0b; color:#000; font-family:'Bebas Neue',sans-serif; font-size:16px; letter-spacing:2px;" data-bs-dismiss="modal">
                        CERRAR DETALLE
                    </button>
                </div>
            </div>
        </div>
    </div>`;

    cuerpo.innerHTML = html;
}

// Helpers
function _pill(icon, label, color, bg) {
    return `<span style="background:${bg};color:${color};border:1px solid ${color}33;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;">${label}</span>`;
}
function _seccion(icon, titulo, color, innerHtml) {
    return `<div class="kd-sec" style="border-left-color:${color};"><div class="kd-sec-title" style="color:${color};">${titulo}</div>${innerHtml}</div>`;
}
function _label(texto) { return `<span class="kd-label">${texto}</span>`; }
function escaparHtml(t) { if(!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
function obtenerIniciales(n) { if(!n) return '?'; const p = n.trim().split(' '); return (p[0]?.[0] || '') + (p[1]?.[0] || ''); }
function getFileIcon(n) { if(!n) return 'bi-file-earmark'; const e = n.split('.').pop().toLowerCase(); if(['jpg','jpeg','png'].includes(e)) return 'bi-file-earmark-image'; if(e==='pdf') return 'bi-file-earmark-pdf'; return 'bi-file-earmark'; }
function formatearFechaLimpia(f) { if(!f) return '---'; try { const d = new Date(f); return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`; } catch(e) { return f; } }
function abrirArchivo(id) { window.open(`${BASE_URL}responsable/archivos/vista-previa/${id}`, '_blank'); }

/**
 * Activa el modo edición en retroalimentación
 */
function activarEdicionRetroalimentacion() {
    const req = window.requerimientoActualRetro;
    if (!req) return;

    // Cabecera: Cambiar Título y Botones
    const headerH2 = document.querySelector('#modalDetalle h2');
    headerH2.innerHTML = `
        <span style="color:#F5C400;">MODO EDICIÓN</span>
        <div style="display:flex; gap:10px; margin-left:auto;">
            <button class="btn btn-sm btn-success" id="btn-guardar-retro" onclick="guardarEdicionRetroalimentacion()" style="font-weight:700; font-size:11px; height:24px;">
                <i class="bi bi-check-lg"></i> GUARDAR
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="verDetalle(${req.idatencion || req.id})" style="font-weight:700; font-size:11px; height:24px;">
                CANCELAR
            </button>
        </div>
    `;

    // Preparar Checkboxes
    const canalesLista = ['Facebook', 'Instagram', 'LinkedIn', 'TikTok', 'WhatsApp', 'Web', 'Correo Electrónico', 'Publicidad Digital (Ads)', 'Impreso', 'Otros'];
    const formatosLista = ['Imagen (Post/Story)', 'Video (Reel/TikTok)', 'Carrusel', 'PDF / Documento', 'GIF Animado', 'Motion Graphics', 'Fotografía', 'Ilustración', 'Texto / Copywriting', 'Otros'];

    const canalesActuales = (req.canales_difusion || '').split(',').map(s => s.trim());
    const formatosActuales = (req.formatos_solicitados || '').split(',').map(s => s.trim());

    const renderCheckboxes = (lista, actuales, name) => {
        return lista.map(item => `
            <div class="form-check form-check-inline" style="margin-bottom: 5px;">
                <input class="form-check-input check-premium" type="checkbox" name="${name}" value="${item}" id="chk-retro-${name}-${item.replace(/\s+/g, '-')}" ${actuales.includes(item) ? 'checked' : ''}>
                <label class="form-check-label text-white" for="chk-retro-${name}-${item.replace(/\s+/g, '-')}" style="font-size: 12px; cursor: pointer;">${item}</label>
            </div>
        `).join('');
    };

    // Transformar el cuerpo (el lado izquierdo principalmente)
    const container = document.querySelector('#detalleCuerpo > div > div > div:first-child');
    
    container.innerHTML = `
        <div class="mb-4">
            <span class="kd-label">Título del Requerimiento</span>
            <input type="text" id="edit-retro-titulo" class="form-control form-control-sm bg-dark text-white border-secondary" value="${escaparHtml(req.titulo)}">
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <span class="kd-label">Objetivo de Comunicación</span>
                <textarea id="edit-retro-objetivo" class="form-control form-control-sm bg-dark text-white border-secondary" rows="3">${req.objetivo_comunicacion || ''}</textarea>
            </div>
            <div class="col-md-6">
                <span class="kd-label">Público Objetivo</span>
                <textarea id="edit-retro-publico" class="form-control form-control-sm bg-dark text-white border-secondary" rows="3">${req.publico_objetivo || ''}</textarea>
            </div>
        </div>

        <div class="mb-4">
            <span class="kd-label">Descripción Detallada</span>
            <textarea id="edit-retro-descripcion" class="form-control form-control-sm bg-dark text-white border-secondary" rows="5">${req.descripcion || ''}</textarea>
        </div>

        <div class="mb-4">
            <span class="kd-label mb-2">Canales de Difusión</span>
            <div class="p-3 border border-secondary rounded bg-black-opacity">
                ${renderCheckboxes(canalesLista, canalesActuales, 'canales')}
            </div>
        </div>

        <div class="mb-4">
            <span class="kd-label mb-2">Formatos Solicitados</span>
            <div class="p-3 border border-secondary rounded bg-black-opacity">
                ${renderCheckboxes(formatosLista, formatosActuales, 'formatos')}
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <span class="kd-label">Fecha Requerida</span>
                <input type="date" id="edit-retro-fecha" class="form-control form-control-sm bg-dark text-white border-secondary" value="${req.fecharequerida ? req.fecharequerida.split(' ')[0] : ''}">
            </div>
            <div class="col-md-6">
                <span class="kd-label">URL de Materiales</span>
                <input type="url" id="edit-retro-url" class="form-control form-control-sm bg-dark text-white border-secondary" value="${req.url_subida || ''}">
            </div>
        </div>

        <div class="p-3 border border-warning rounded bg-warning-opacity mb-4">
            <span class="kd-label text-warning mb-2"><i class="bi bi-cloud-upload me-2"></i>Subir Más Materiales</span>
            <input type="file" id="edit-retro-archivos" class="form-control form-control-sm bg-dark text-white border-secondary" multiple>
        </div>
    `;

    // Estilo para los checkboxes si no existe
    if (!document.getElementById('style-retro-premium')) {
        const style = document.createElement('style');
        style.id = 'style-retro-premium';
        style.textContent = `
            .bg-black-opacity { background: rgba(0,0,0,0.3); }
            .bg-warning-opacity { background: rgba(245, 196, 0, 0.05); }
            .check-premium:checked { background-color: #F5C400; border-color: #F5C400; }
            .check-premium { background-color: #111; border-color: #444; }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Guarda los cambios desde retroalimentación
 */
function guardarEdicionRetroalimentacion() {
    const req = window.requerimientoActualRetro;
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

    // Obtener canales seleccionados
    const canales = Array.from(document.querySelectorAll('input[name="canales"]:checked')).map(el => el.value).join(', ');
    // Obtener formatos seleccionados
    const formatos = Array.from(document.querySelectorAll('input[name="formatos"]:checked')).map(el => el.value).join(', ');

    const formData = new FormData();
    formData.append('idrequerimiento', req.idrequerimiento || req.id);
    formData.append('titulo', document.getElementById('edit-retro-titulo').value);
    formData.append('descripcion', document.getElementById('edit-retro-descripcion').value);
    formData.append('objetivo_comunicacion', document.getElementById('edit-retro-objetivo').value);
    formData.append('publico_objetivo', document.getElementById('edit-retro-publico').value);
    formData.append('canales_difusion', canales);
    formData.append('formatos_solicitados', formatos);
    formData.append('fecharequerida', document.getElementById('edit-retro-fecha').value);
    formData.append('url_subida', document.getElementById('edit-retro-url').value);

    // Archivos
    const fileInput = document.getElementById('edit-retro-archivos');
    if (fileInput && fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('archivos_responsable[]', fileInput.files[i]);
        }
    }

    fetch(`${BASE_URL}responsable/pedidos/actualizar`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: data.message,
                background: '#161616',
                color: '#fff',
                timer: 2000,
                showConfirmButton: false
            });
            // Recargar detalles
            verDetalle(req.idatencion || req.id);
            // Recargar lista si existe la función
            if (typeof listarRetroalimentacion === 'function') listarRetroalimentacion();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, background: '#161616', color: '#fff' });
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', background: '#161616', color: '#fff' });
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
