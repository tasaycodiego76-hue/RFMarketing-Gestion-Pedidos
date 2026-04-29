// ══════════════════════════════════════════════════════
// ══ FORZAR RECORTE DE TÍTULOS LARGOS EN LAS TARJETAS ══
// ══════════════════════════════════════════════════════
document.head.insertAdjacentHTML('beforeend', `
<style>
    .kb-card {
        max-width: 100% !important;
        overflow: hidden !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
    }
    .kb-card h3, 
    .kb-card h4, 
    .kb-card h5, 
    .kb-card-title, 
    .kb-card p {
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        hyphens: auto;
    }
</style>
`);


// ═══════════════════════════════════════════════
// ═══ KANBAN.JS — Flujo completo y sin errores ══
// ═══════════════════════════════════════════════

const KB_ICONS = {
    pdf: 'bi-file-earmark-pdf',
    doc: 'bi-file-earmark-word',
    docx: 'bi-file-earmark-word',
    xls: 'bi-file-earmark-excel',
    xlsx: 'bi-file-earmark-excel',
    png: 'bi-file-earmark-image',
    jpg: 'bi-file-earmark-image',
    jpeg: 'bi-file-earmark-image',
    zip: 'bi-file-earmark-zip',
    default: 'bi-file-earmark'
};

// ════════════════════════════════════════════════════════
// ═══ ADMIN — Solo envía el pedido a un ÁREA          ═══
//     El empleado queda vacío. El responsable asigna. ═══
// ════════════════════════════════════════════════════════
async function abrirModalAsignar(idAtencion) {
    _resetModal('Enviar Pedido al Área', 'Enviar al Área', 'confirmarAsignacion()');
    document.getElementById('asignar-idatencion').value = idAtencion;

    const select = document.getElementById('asignar-empleado');
    select.innerHTML = '<option value="">Cargando áreas...</option>';

    try {
        const r = await fetch(BASE_URL + 'admin/kanban/areas');
        const data = await r.json();
        select.innerHTML = '<option value="">— Seleccionar área —</option>';
        data.forEach(a => {
            select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
        });
    } catch {
        select.innerHTML = '<option value="">Error al cargar áreas</option>';
    }
    $('#modalAsignar').modal('show');
}

async function confirmarAsignacion() {
    const idAtencion = document.getElementById('asignar-idatencion').value;
    const idArea = document.getElementById('asignar-empleado').value;
    if (!idArea) { alert('Selecciona un área'); return; }

    const data = await _post('admin/kanban/asignarArea', { idatencion: idAtencion, idareaagencia: idArea });
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ═══════════════════════════════════════════════════════════════════════════════
// ═══ RESPONSABLE — Asigna empleado O se autoasigna                          ═══
//      Asignar empleado  desarrollando.                                    ═══
//       Solo cuando alguien pulsa "Iniciar Trabajo" se pone fechainicio.     ═══
// ═══════════════════════════════════════════════════════════════════════════════
async function abrirModalAsignarEmpleado(idAtencion, idArea) {
    _resetModal('Asignar Responsable del Pedido', 'Confirmar Asignación', 'confirmarAsignacionEmpleado()');
    document.getElementById('asignar-idatencion').value = idAtencion;

    const select = document.getElementById('asignar-empleado');
    select.innerHTML = '<option value="">Cargando empleados...</option>';

    try {
        const r = await fetch(BASE_URL + 'admin/kanban/empleados/' + idArea);
        const data = await r.json();

        if (!data.length) {
            select.innerHTML = '<option value="">No hay empleados en esta área</option>';
            $('#modalAsignar').modal('show');
            return;
        }

        select.innerHTML = '<option value="">— Seleccionar empleado —</option>';

        // Auto-asignación del responsable
        if (typeof EMPLEADO_ACTUAL_ID !== 'undefined' && EMPLEADO_ACTUAL_ID) {
            select.innerHTML += `<option value="${EMPLEADO_ACTUAL_ID}" style="font-weight:700;color:#F5C400;"> Asignarme a mí mismo</option>`;
        }

        data.forEach(u => {
            if (u.id == EMPLEADO_ACTUAL_ID) return;
            select.innerHTML += `<option value="${u.id}">${u.nombre} ${u.apellidos}</option>`;
        });
    } catch {
        select.innerHTML = '<option value="">Error al cargar empleados</option>';
    }
    $('#modalAsignar').modal('show');
}

async function confirmarAsignacionEmpleado() {
    const idAtencion = document.getElementById('asignar-idatencion').value;
    const idEmpleado = document.getElementById('asignar-empleado').value;
    if (!idEmpleado) { alert('Selecciona un empleado'); return; }

    // Solo asigna el empleado. El estado sigue siendo pendiente_asignado.
    // fechainicio se pone cuando alguien pulsa "Iniciar Trabajo".
    const data = await _post('admin/kanban/asignarEmpleado', { idatencion: idAtencion, idempleado: idEmpleado });
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ══════════════════════════════════════════════════════════════════
// ═══ INICIAR TRABAJO — pone fechainicio + estado = en_proceso  ═══
// ══════════════════════════════════════════════════════════════════
async function iniciarTrabajo(idAtencion) {
    if (!confirm('¿Confirmar inicio de trabajo en este pedido?')) return;
    const data = await _post('admin/kanban/iniciarTrabajo', { idatencion: idAtencion });
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ══════════════════════════════════════════════════════
// ═══ VER DETALLE — modal con estilo del sistema     ═══
// ══════════════════════════════════════════════════════
async function verDetalle(idAtencion) {
    const cuerpo = document.getElementById('detalle-cuerpo');
    if (!cuerpo) return;

    // Loader inicial
    cuerpo.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:300px;gap:20px;">
            <div class="spinner-border" style="color:#F5C400;width:3rem;height:3rem;border-width:4px;"></div>
            <span style="color:#F5C400;font-size:12px;letter-spacing:3px;font-weight:700;text-transform:uppercase;">Cargando expediente completo...</span>
        </div>`;

    $('#modalDetalle').modal('show');

    try {
        const r = await fetch(BASE_URL + 'admin/kanban/detalle/' + idAtencion);
        const res = await r.json();

        if (res.status !== 'success') {
            cuerpo.innerHTML = _errorHtml('No se pudo obtener la información detallada.');
            return;
        }

        const d = res.data;
        const archivosCliente = res.archivos_cliente || [];
        const archivosEmpleado = res.archivos_empleado || [];

        // ── Fechas (Ya vienen formateadas desde el servidor) ─────
        const fReq = d.fecharequerida || '---';
        const fSol = d.r_fechacreacion || '---'; // r_fechacreacion no se formatea en PHP, solo requerida/inicio/fin/completa
        const fIni = d.fechainicio || '---';
        const fFin = d.fechacompletado || '---';

        // Formateo extra solo para r_fechacreacion si viene como timestamp
        const fmtRaw = s => (s && s.length > 10) ? s.substring(0, 10).split('-').reverse().join('/') : s;
        const fSolFmt = fmtRaw(fSol);

        // ── Pill de Estado ──────────────────────────────────────
        let trabajoHtml;
        if (d.estado === 'finalizado') {
            trabajoHtml = _pill('bi-check2-all', 'COMPLETADO', '#22c55e', 'rgba(34,197,94,0.1)');
        } else if (!d.idempleado) {
            trabajoHtml = _pill('bi-hourglass', 'PENDIENTE ASIGNACIÓN', '#888', 'rgba(136,136,136,0.1)');
        } else if (d.estado === 'pendiente_asignado') {
            trabajoHtml = _pill('bi-person-check', 'ASIGNADO', '#F5C400', 'rgba(245,196,0,0.1)');
        } else {
            trabajoHtml = _pill('bi-lightning-charge', 'EN DESARROLLO', '#10b981', 'rgba(16,185,129,0.1)');
        }

        // ── Renderizado de Archivos ──────────────────────────────
        const renderArchivos = (lista, color = '#F5C400') => {
            if (!lista.length) return '<div style="color:#ffffff;font-size:14px;font-weight:700;padding:5px 0;">No se adjuntaron archivos.</div>';
            let html = `<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:10px;margin-top:10px;">`;
            lista.forEach(a => {
                const ext = a.nombre.split('.').pop().toLowerCase();
                const icon = KB_ICONS[ext] ?? KB_ICONS.default;
                html += `
                    <a href="${a.url_completa}" target="_blank" 
                       style="display:flex;align-items:center;gap:10px;padding:12px;background:#0a0a0a;border:1px solid #222;border-radius:8px;color:#fff;text-decoration:none;font-size:11px;transition:border-color 0.2s;">
                        <i class="bi ${icon}" style="color:${color};font-size:16px;"></i>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${a.nombre}</span>
                    </a>`;
            });
            html += '</div>';
            return html;
        };

        const arcClienteHtml = renderArchivos(archivosCliente);
        const arcEmpleadoHtml = renderArchivos(archivosEmpleado, '#10b981');

        // ── Tags para canales/formatos ──────────────────────────
        const renderTags = json => {
            if (!json) return '<div style="color:#ffffff;font-size:13px;font-weight:900;text-align:center;width:100%;">SIN ESPECIFICAR</div>';
            const list = _parseList(json);
            return `<div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:10px;width:100%;">` +
                list.map(t => {
                    const texto = String(t || '').toUpperCase();
                    return `<span style="background:#F5C400;color:#000;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:800;border:1px solid #fff;box-shadow:0 3px 8px rgba(245,196,0,0.2);white-space:nowrap;">${texto}</span>`;
                }).join('') +
                `</div>`;
        };

        // ── Bloque del Empleado ──────────────────────────────────
        let empleadoHtml;
        if (d.empleado_nombre) {
            const ini = (d.empleado_nombre[0] + (d.empleado_apellidos?.[0] ?? '')).toUpperCase();
            empleadoHtml = `
                <div style="display:flex; align-items:center; gap:12px; text-align:left; width:100%;">
                    <div style="width:50px; height:50px; background:linear-gradient(135deg, #F5C400, #ff8c00); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:900; color:#000; border:2px solid #fff; flex-shrink:0;">
                        ${ini}
                    </div>
                    <div>
                        <div style="color:#ffffff; font-size:15px; font-weight:800;">${d.empleado_nombre} ${d.empleado_apellidos}</div>
                        <div style="color:#F5C400; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px;">EJECUTOR ASIGNADO</div>
                    </div>
                </div>`;
        } else {
            empleadoHtml = `
                <div style="display:flex; align-items:center; gap:12px; text-align:left; width:100%;">
                    <div style="width:50px; height:50px; background:#111; border:1px dashed #F5C400; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#F5C400; flex-shrink:0;">
                        <i class="bi bi-person" style="font-size:24px;"></i>
                    </div>
                    <div>
                        <div style="color:#F5C400; font-size:14px; font-weight:800;">Sin asignar</div>
                        <div style="color:#888; font-size:10px;">Esperando responsable</div>
                    </div>
                </div>`;
        }

        // ── Sección de Entrega ──────────────────────────────────
        let entregablesHtml = '';
        if (d.estado === 'en_revision' || d.estado === 'finalizado') {
            entregablesHtml = `
                <div style="margin-top:25px; background:rgba(16,185,129,0.03); border:1px solid rgba(16,185,129,0.1); border-radius:15px; padding:25px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                        <i class="bi bi-check-all" style="color:#10b981;font-size:24px;"></i>
                        <span style="font-family:'Bebas Neue';font-size:22px;color:#fff;letter-spacing:1px;">RESULTADO DE LA ENTREGA</span>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px;">
                        <div>
                            ${_label('Enlace de Entrega Final')}
                            <div style="margin-top:8px;">
                                ${d.url_entrega ? `<a href="${d.url_entrega}" target="_blank" style="color:#10b981;font-weight:800;font-size:16px;text-decoration:underline;">Abrir Link de Entrega <i class="bi bi-box-arrow-up-right ml-1"></i></a>` : '<span style="color:#ffffff;">Sin link adjunto</span>'}
                            </div>
                        </div>
                        <div>
                            ${_label('Mensaje del Desarrollador')}
                            <div style="color:#ffffff;font-size:13px;line-height:1.6;margin-top:8px;white-space:pre-wrap;">${d.observacion_revision || 'No se incluyeron notas.'}</div>
                        </div>
                    </div>
                    <div style="margin-top:20px;">
                        ${_label('Archivos del Empleado')}
                        ${arcEmpleadoHtml}
                    </div>
                </div>`;
        }

        // ── Título del Modal ────────────────────────────────────
        document.getElementById('detalle-titulo').innerText = (d.nombreempresa || 'PEDIDO').toUpperCase();

        // ── CONSTRUCCIÓN FINAL DEL HTML ────────────────────────
        cuerpo.innerHTML = `
            <style>
                .det-container { padding: 5px 15px 20px; color: #ffffff; width: 100%; font-family: 'Inter', sans-serif; background: #080808; }
                
                /* Estructura Grid Principal */
                .det-main-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
                
                /* Secciones Universales */
                .det-card { background: #0a0a0a; border: 1px solid #1a1a1a; border-radius: 12px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
                .det-card-header { background: #000; padding: 12px 18px; border-bottom: 1px solid #1a1a1a; display: flex; align-items: center; gap: 10px; }
                .det-card-header i { font-size: 18px; color: #F5C400; }
                .det-card-header span { font-family: 'Bebas Neue'; font-size: 18px; letter-spacing: 1px; color: #F5C400; }
                .det-card-body { padding: 18px; }
                
                /* Etiquetas y Datos */
                .det-data-box { background: #050505; border: 1px solid #111; border-radius: 8px; padding: 12px; height: 100%; }
                .det-data-label { color: #555; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px; display: block; }
                .det-data-value { color: #fff; font-size: 13px; line-height: 1.5; font-weight: 600; }
                
                /* Botón Iniciar */
                .det-btn-start { background: #F5C400; color: #000; border: none; border-radius: 8px; padding: 14px; font-weight: 900; font-size: 13px; width: 100%; cursor: pointer; transition: all 0.2s; text-transform: uppercase; letter-spacing: 1px; }
                .det-btn-start:hover { background: #ffcf1a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(245, 196, 0, 0.3); }

                /* Responsive */
                @media (max-width: 992px) {
                    .det-main-grid { grid-template-columns: 1fr; }
                }
            </style>
            
            <div class="det-container">
                <!-- 1. HEADER PREMIUM -->
                <div class="det-header" style="display:flex; justify-content:space-between; align-items:flex-end; padding-bottom:20px; border-bottom:1px solid #1a1a1a; margin-bottom:25px;">
                    <div>
                        <div style="font-size:10px; color:#F5C400; font-weight:900; letter-spacing:3px; text-transform:uppercase; margin-bottom:6px;">ADMINISTRACIÓN DE REQUERIMIENTO</div>
                        <h2 style="font-family:'Bebas Neue', sans-serif; font-size:42px; color:#ffffff; letter-spacing:1px; margin:0; line-height:0.9;">${d.titulo || 'SIN TÍTULO'}</h2>
                        <div style="margin-top:10px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            <span style="color:#F5C400; font-size:14px; font-weight:800; display:flex; align-items:center; gap:6px;"><i class="bi bi-building"></i> ${d.nombreempresa}</span>
                            <span style="color:#222;">|</span>
                            <span style="color:#fff; font-size:13px; font-weight:800; text-transform:uppercase; background:#111; padding:2px 10px; border-radius:4px;">${d.servicio || 'GENERAL'}</span>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                        ${trabajoHtml}
                        <div style="font-size:12px; color:#F5C400; font-weight:900; letter-spacing:1px;">#REQ-${d.idrequerimiento || '---'}</div>
                        <div style="font-size:9px; color:#444; font-weight:700;">ATENCIÓN: #${d.id.toString().padStart(5, '0')}</div>
                    </div>
                </div>

                <div class="det-main-grid">
                    
                    <!-- COLUMNA IZQUIERDA: CONTENIDO -->
                    <div class="det-content-col">
                        
                        <!-- Brief Estratégico -->
                        <div class="det-card">
                            <div class="det-card-header"><i class="bi bi-bullseye"></i> <span>ESTRATEGIA Y OBJETIVOS</span></div>
                            <div class="det-card-body">
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                                    <div class="det-data-box">
                                        <span class="det-data-label">Objetivo de Comunicación</span>
                                        <div class="det-data-value" style="color:#F5C400;">${d.objetivo_comunicacion || '---'}</div>
                                    </div>
                                    <div class="det-data-box">
                                        <span class="det-data-label">Público Objetivo</span>
                                        <div class="det-data-value">${d.publico_objetivo || '---'}</div>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px; border-top:1px solid #111; padding-top:15px;">
                                    <div><span class="det-data-label">Categoría</span><div style="color:#fff; font-weight:800; font-size:12px;">${d.tipo_requerimiento || 'ESTÁNDAR'}</div></div>
                                    <div><span class="det-data-label">Área Solicitante</span><div style="color:#fff; font-weight:700; font-size:12px;">${d.area_solicitante_nombre || '---'}</div></div>
                                    <div><span class="det-data-label">Prioridad Cliente</span><div style="color:#F5C400; font-weight:900; font-size:12px;">${d.prioridad_cliente || 'MEDIA'}</div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Instrucciones -->
                        <div class="det-card">
                            <div class="det-card-header"><i class="bi bi-chat-right-text"></i> <span>DESCRIPCIÓN DEL PROYECTO</span></div>
                            <div class="det-card-body">
                                <div style="color:#ccc; font-size:14px; line-height:1.7; white-space:pre-wrap; background:#050505; padding:20px; border-radius:8px; border:1px solid #111;">${d.descripcion || 'Sin descripción detallada.'}</div>
                            </div>
                        </div>

                        <!-- Canales y Formatos -->
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                            <div class="det-card" style="margin-bottom:0;">
                                <div class="det-card-header"><i class="bi bi-share"></i> <span>CANALES DE DIFUSIÓN</span></div>
                                <div class="det-card-body" style="padding:15px;">${renderTags(d.canales_difusion)}</div>
                            </div>
                            <div class="det-card" style="margin-bottom:0;">
                                <div class="det-card-header"><i class="bi bi-box"></i> <span>FORMATOS REQUERIDOS</span></div>
                                <div class="det-card-body" style="padding:15px;">${renderTags(d.formatos_solicitados)}</div>
                            </div>
                        </div>

                        <!-- Archivos y Recursos -->
                        <div class="det-card">
                            <div class="det-card-header"><i class="bi bi-folder2-open"></i> <span>RECURSOS Y MATERIAL</span></div>
                            <div class="det-card-body">
                                ${arcClienteHtml}
                                ${d.url_subida ? `
                                <div style="margin-top:20px; background:#000; padding:15px; border-radius:8px; border:1px solid #F5C400; text-align:center;">
                                    <a href="${d.url_subida}" target="_blank" style="color:#F5C400; font-size:12px; font-weight:900; text-decoration:none; display:inline-flex; align-items:center; gap:8px; letter-spacing:1px;">
                                        <i class="bi bi-cloud-arrow-down" style="font-size:20px;"></i> ABRIR REPOSITORIO DE ARCHIVOS EXTERNO
                                    </a>
                                </div>` : ''}
                            </div>
                        </div>

                        ${entregablesHtml}
                    </div>

                    <!-- COLUMNA DERECHA: sidebar -->
                    <div class="det-sidebar-col">
                        
                        <!-- Ejecutor -->
                        <div class="det-card">
                            <div class="det-card-header"><i class="bi bi-person-badge"></i> <span>RESPONSABLE</span></div>
                            <div class="det-card-body" style="padding:20px 15px;">
                                ${empleadoHtml}
                            </div>
                        </div>

                        <!-- Tiempos -->
                        <div class="det-card">
                            <div class="det-card-header"><i class="bi bi-clock-history"></i> <span>CONTROL DE TIEMPOS</span></div>
                            <div class="det-card-body">
                                <div style="display:flex; flex-direction:column; gap:12px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:12px; border-radius:8px;">
                                        <span style="color:#444; font-size:10px; font-weight:900;">SOLICITUD</span>
                                        <span style="color:#fff; font-size:12px; font-weight:700;">${fSolFmt}</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(245,196,0,0.05); padding:12px; border-radius:8px; border:1px solid rgba(245,196,0,0.1);">
                                        <span style="color:#F5C400; font-size:10px; font-weight:900;">ENTREGA LÍMITE</span>
                                        <span style="color:#fff; font-size:13px; font-weight:900;">${fReq}</span>
                                    </div>
                                    ${d.fechainicio !== '---' && d.fechainicio !== '—' ? `
                                    <div style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:12px; border-radius:8px;">
                                        <span style="color:#444; font-size:10px; font-weight:900;">INICIO REAL</span>
                                        <span style="color:#fff; font-size:12px; font-weight:700;">${fIni}</span>
                                    </div>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Auditoría -->
                        <div class="det-card">
                            <div class="det-card-header"><i class="bi bi-arrow-repeat"></i> <span>AUDITORÍA</span></div>
                            <div class="det-card-body">
                                <div style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:12px; border-radius:8px;">
                                    <span style="color:#444; font-size:11px; font-weight:900;">MODIFICACIONES</span>
                                    <span style="background:#F5C400; color:#000; padding:2px 12px; border-radius:15px; font-size:13px; font-weight:900;">${d.num_modificaciones || 0}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 3. FOOTER ACCIONES -->
                <div style="margin-top:20px; padding-top:20px; border-top:1px solid #1a1a1a; display:flex; justify-content:center;">
                    <button class="btn btn-dark" data-dismiss="modal" style="background:#000; border:1px solid #222; font-family:'Bebas Neue'; font-size:20px; letter-spacing:2px; padding:12px 60px; border-radius:8px; color:#F5C400;">CERRAR EXPEDIENTE</button>
                </div>
            </div>
        `;

    } catch (e) {
        console.error("ERROR EN DETALLE:", e);
        cuerpo.innerHTML = _errorHtml('Hubo un error al procesar el expediente. Por favor, intenta de nuevo.');
    }
}

// ═══════════════════════════════════════
// ═══ OTRAS ACCIONES                   ══
// ═══════════════════════════════════════
async function cambiarPrioridad(id) {
    const p = document.getElementById('detalle-prioridad').value;
    const data = await _post('admin/kanban/cambiarPrioridad', { idatencion: id, prioridad: p });
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

async function cambiarEstado(id, est, acc) {
    if (!confirm('Confirmar acción: ' + acc)) return;
    const data = await _post('admin/kanban/cambiarEstado', { idatencion: id, estado: est, accion: acc });
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ── SOLICITAR RETROALIMENTACIÓN AL REGRESAR A PROCESO ──
function solicitarRetroalimentacion(id) {
    document.getElementById('retro-idatencion').value = id;
    document.getElementById('retro-mensaje').value = '';
    $('#modalRetro').modal('show');
}

async function enviarRetroalimentacion() {
    const id = document.getElementById('retro-idatencion').value;
    const msg = document.getElementById('retro-mensaje').value;

    if (!msg.trim()) {
        alert('Por favor, escribe un mensaje de mejora.');
        return;
    }

    const data = await _post('admin/kanban/regresarAProceso', {
        idatencion: id,
        mensaje: msg
    });

    if (data.status === 'success') {
        location.reload();
    } else {
        alert(data.msg);
    }
}

async function cancelarAtencion(id) {
    const m = prompt('Motivo de cancelación:');
    if (!m) return;
    const data = await _post('admin/kanban/cancelar', { idatencion: id, motivo: m });
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ═══════════════════════════════════════
// ═══ HELPERS PRIVADOS                 ══
// ═══════════════════════════════════════

async function _post(endpoint, body) {
    const r = await fetch(BASE_URL + endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    return r.json();
}

function _resetModal(titulo, btnTexto, btnOnclick) {
    document.querySelector('.kb-modal-title-asignar').textContent = titulo;
    const btn = document.querySelector('.kb-btn-confirmar-asignar');
    btn.textContent = btnTexto;
    btn.setAttribute('onclick', btnOnclick);
}

function _pill(icon, label, color, bg) {
    return `<span style="background:${bg};color:${color};border:1px solid ${color}33;
        padding:6px 14px;border-radius:20px;font-size:12px;font-weight:800;letter-spacing:0.5px;
        text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;">
        <i class="bi ${icon}"></i>${label}</span>`;
}

function _seccion(icon, titulo, color, innerHtml) {
    return `<div class="kd-sec" style="border-left-color:${color};">
        <div class="kd-sec-title" style="color:${color};">
            <i class="bi ${icon}"></i>${titulo}
        </div>
        ${innerHtml}
    </div>`;
}

function _label(texto) {
    return `<span style="color:#F5C400; font-size:12px; font-weight:900; letter-spacing:1.5px; text-transform:uppercase; display:block; margin-bottom:8px; border-bottom:2px solid #F5C400; padding-bottom:4px; display:inline-block; font-family:'Bebas Neue', sans-serif;">${texto}</span>`;
}

function _errorHtml(msg) {
    return `<div style="color:#ef4444;padding:40px;text-align:center;">
        <i class="bi bi-exclamation-triangle" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        <p style="font-size:13px;">${msg}</p>
    </div>`;
}

function _parseList(json) {
    if (!json) return [];
    try {
        const l = JSON.parse(json);
        return Array.isArray(l) ? l : [json];
    } catch {
        // Si no es un JSON o arreglo mágico, pero tiene comas, lo separa e ignora espacios vacíos
        if (typeof json === 'string' && json.includes(',')) {
            return json.split(',').map(s => s.trim()).filter(s => s);
        }
        return [json];
    }
}


// ═══════════════════════════════════════
// ═══ DRAG & DROP (SORTABLE.JS)        ══
// ═══════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const colAprobar = document.querySelector('.kb-col-body[data-estado="pendiente_sin_asignar"]');
    const colProceso = document.querySelector('.kb-col-body[data-estado="en_proceso"]');

    if (colAprobar && colProceso) {
        // Estilos para asegurar que toda la columna sea área de soltado
        const style = document.createElement('style');
        style.innerHTML = `
            .kb-col { display: flex !important; flex-direction: column !important; }
            .kb-col-body { flex-grow: 1 !important; min-height: 500px !important; }
        `;
        document.head.appendChild(style);

        // SOLO SE PUEDE SACAR DE AQUÍ
        new Sortable(colAprobar, {
            group: { name: 'kanban', pull: true, put: false },
            draggable: '.kb-card',
            animation: 150
        });

        // SOLO SE PUEDE SOLTAR AQUÍ (NO REGRESAR)
        new Sortable(colProceso, {
            group: { name: 'kanban', pull: false, put: true },
            draggable: '.kb-card',
            animation: 150,
            onAdd(evt) {
                const idAtencion = evt.item.getAttribute('data-id');
                _post('admin/kanban/cambiarEstado', {
                    idatencion: idAtencion,
                    estado: 'pendiente_asignado',
                    accion: 'Aprobado vía arrastre',
                    idareaagencia: AREA_ACTUAL
                }).then(() => location.reload()).catch(() => location.reload());
            }
        });
    }
});

