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
//     ⚠ Asignar empleado ≠ desarrollando.                                    ═══
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
            select.innerHTML += `<option value="${EMPLEADO_ACTUAL_ID}" style="font-weight:700;color:#F5C400;">⭐ Asignarme a mí mismo</option>`;
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
    cuerpo.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:220px;gap:14px;">
            <div class="spinner-border" style="color:#F5C400;width:2rem;height:2rem;border-width:3px;"></div>
            <span style="color:#555;font-size:10px;letter-spacing:2px;font-weight:700;text-transform:uppercase;">Cargando expediente...</span>
        </div>`;
    $('#modalDetalle').modal('show');

    try {
        const r = await fetch(BASE_URL + 'admin/kanban/detalle/' + idAtencion);
        const res = await r.json();

        if (res.status !== 'success') {
            cuerpo.innerHTML = _errorHtml('No se pudo cargar el expediente.');
            return;
        }

        const d = res.data;
        const archivos = res.archivos || [];

        // ── Fechas formateadas ──────────────────────────────────
        const fmt = s => s ? s.substring(0, 10).split('-').reverse().join('/') : null;
        const fReq = d.fecharequerida ? d.fecharequerida.substring(0, 10).split('-').reverse().join('/') : null;
        const fSol = fmt(d.r_fechacreacion);
        const fIni = fmt(d.fechainicio);
        const fFin = fmt(d.fechacompletado);

        // ── Estado del trabajo (según flujo real del sistema) ───
        //
        //   pendiente_asignado (sin empleado)     → "Pendiente de asignación"
        //   pendiente_asignado (con empleado)     → "Asignado — aún no iniciado"
        //   en_proceso (con empleado)             → "Desarrollando"
        //   finalizado                             → "Completado"
        let trabajoHtml;
        if (d.estado === 'finalizado') {
            trabajoHtml = _pill('bi-check2-circle', 'Completado', '#22c55e', '#052e16');
        } else if (!d.idempleado) {
            trabajoHtml = _pill('bi-hourglass-split', 'Pendiente de asignación', '#6b7280', '#111');
        } else if (d.estado === 'pendiente_asignado') {
            trabajoHtml = _pill('bi-person-check-fill', 'Asignado — aún no iniciado', '#F5C400', '#1a1500');
        } else {
            trabajoHtml = _pill('bi-lightning-charge-fill', 'Desarrollando', '#10b981', '#001a0f');
        }

        // ── Mapa de estados ─────────────────────────────────────
        const estadoMap = {
            pendiente_sin_asignar: { c: '#f59e0b', label: 'Pendiente sin asignar', i: 'bi-hourglass-split' },
            pendiente_asignado: { c: '#F5C400', label: 'Asignado al área', i: 'bi-send-check-fill' },
            en_proceso: { c: '#10b981', label: 'En proceso', i: 'bi-lightning-charge-fill' },
            en_revision: { c: '#a78bfa', label: 'En revisión', i: 'bi-eye-fill' },
            finalizado: { c: '#22c55e', label: 'Entregado', i: 'bi-check2-circle' },
            cancelado: { c: '#ef4444', label: 'Cancelado', i: 'bi-x-circle-fill' },
        };
        const es = estadoMap[d.estado] ?? { c: '#aaa', label: d.estado.replace(/_/g, ' '), i: 'bi-circle' };
        const priC = d.prioridad_admin === 'Alta' ? '#ef4444' : (d.prioridad_admin === 'Media' ? '#F5C400' : '#3b82f6');
        const priI = d.prioridad_admin === 'Alta' ? 'bi-arrow-up-circle-fill' : (d.prioridad_admin === 'Media' ? 'bi-dash-circle-fill' : 'bi-arrow-down-circle-fill');

        // ── Bloque empleado asignado ────────────────────────────
        let empleadoHtml;
        if (d.empleado_nombre) {
            const ini = (d.empleado_nombre[0] + (d.empleado_apellidos?.[0] ?? '')).toUpperCase();
            empleadoHtml = `
                <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                    <div style="width:36px;height:36px;border-radius:50%;
                        background:linear-gradient(135deg,#F5C400,#b45309);
                        color:#000;font-weight:800;font-size:13px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">${ini}</div>
                    <div>
                        <div style="color:#f0f0f0;font-weight:700;font-size:13px;">${d.empleado_nombre} ${d.empleado_apellidos}</div>
                        <div style="color:#555;font-size:10px;text-transform:uppercase;letter-spacing:.5px;">
                            ${d.estado === 'en_proceso' ? 'En desarrollo' : (d.estado === 'finalizado' ? 'Finalizado' : 'Asignado — sin iniciar')}
                        </div>
                    </div>
                </div>`;
        } else {
            empleadoHtml = `
                <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#111;
                        border:2px dashed #222;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-person-dash" style="color:#444;font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="color:#F5C400;font-weight:700;font-size:13px;">Sin asignar</div>
                        <div style="color:#555;font-size:10px;letter-spacing:.5px;">Esperando al responsable del área</div>
                    </div>
                </div>`;
        }

        // ── Archivos adjuntos ───────────────────────────────────
        let arcHtml = '';
        if (archivos.length) {
            arcHtml = `<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">`;
            archivos.forEach(a => {
                const ext = a.nombre.split('.').pop().toLowerCase();
                const icon = KB_ICONS[ext] ?? KB_ICONS.default;
                arcHtml += `
                    <a href="${BASE_URL}/cliente/archivos/${a.ruta.split('/').pop()}" target="_blank"
                       style="display:flex;align-items:center;gap:8px;padding:9px 12px;
                              background:#0a0a0a;border:1px solid #1e1e1e;border-radius:7px;
                              color:#aaa;text-decoration:none;font-size:11px;transition:border-color .15s;"
                       onmouseover="this.style.borderColor='#F5C400'"
                       onmouseout="this.style.borderColor='#1e1e1e'">
                        <i class="bi ${icon}" style="color:#F5C400;font-size:16px;flex-shrink:0;"></i>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${a.nombre}</span>
                    </a>`;
            });
            arcHtml += '</div>';
        } else {
            arcHtml = '<p style="color:#333;font-size:12px;font-style:italic;margin:8px 0 0;">No se adjuntaron archivos.</p>';
        }

        // ── Canales / Formatos como tags ────────────────────────
        const renderTags = json => {
            if (!json) return '<span style="color:#333;font-size:12px;">---</span>';
            return _parseList(json)
                .map(t => `<span style="background:#0a0a0a;color:#bbb;border:1px solid #1e1e1e;padding:3px 10px;border-radius:4px;font-size:11px;">${t}</span>`)
                .join(' ');
        };

        // ── URLs enviadas por el cliente (por aprobar / aprobado / en proceso) ─────
        let urlsClienteHtml = '';
        if (d.estado === 'pendiente_sin_asignar' || d.estado === 'pendiente_asignado' || d.estado === 'en_proceso') {
            const link = d.url_subida
                ? `<a href="${d.url_subida}" target="_blank" style="color:#60a5fa;text-decoration:underline;font-size:13px;word-break:break-all;">${d.url_subida}</a>`
                : '<span style="color:#333;font-size:12px;font-style:italic;">No se adjuntó enlace</span>';

            urlsClienteHtml = _seccion('bi-link-45deg', 'URLs enviadas por el Cliente', '#60a5fa', `
                <div>
                    ${_label('Enlace / URLs')}
                    <div style="margin-top:5px;">${link}</div>
                </div>
            `);
        }

        // ── Entregables (en_revision / finalizado) ──────────────
        let entregablesHtml = '';
        if (d.estado === 'en_revision' || d.estado === 'finalizado') {
            const link = d.url_entrega
                ? `<a href="${d.url_entrega}" target="_blank" style="color:#60a5fa;text-decoration:underline;font-size:13px;word-break:break-all;">${d.url_entrega}</a>`
                : '<span style="color:#333;font-size:12px;font-style:italic;">No se adjuntó enlace</span>';
            const msg = d.observacion_revision
                ? `<div style="color:#ccc;font-size:13px;line-height:1.7;">${d.observacion_revision}</div>`
                : '<span style="color:#333;font-size:12px;font-style:italic;">Sin mensaje adicional</span>';

            entregablesHtml = _seccion('bi-box-arrow-up-right', 'Entregables del Área', '#3b82f6', `
                <div style="margin-bottom:14px;">
                    ${_label('Enlace / Archivo de entrega')}
                    <div style="margin-top:5px;">${link}</div>
                </div>
                <div>
                    ${_label('Mensaje del desarrollador')}
                    <div style="margin-top:5px;">${msg}</div>
                </div>
            `);
        }

        // ── Panel cambio prioridad (solo admin en pendiente_sin_asignar) ──
        let prioridadPanel = '';
        if (d.estado === 'pendiente_sin_asignar') {
            prioridadPanel = _seccion('bi-flag-fill', 'Cambiar Prioridad', '#F5C400', `
                <div style="display:flex;align-items:center;gap:8px;">
                    <select id="detalle-prioridad"
                        style="background:#0a0a0a;border:1px solid #1e1e1e;color:#f0f0f0;
                               padding:6px 10px;border-radius:6px;font-size:12px;outline:none;
                               cursor:pointer;">
                        <option value="Baja"  ${d.prioridad_admin === 'Baja' ? 'selected' : ''}>Baja</option>
                        <option value="Media" ${d.prioridad_admin === 'Media' ? 'selected' : ''}>Media</option>
                        <option value="Alta"  ${d.prioridad_admin === 'Alta' ? 'selected' : ''}>Alta</option>
                    </select>
                    <button onclick="cambiarPrioridad(${d.id})"
                        style="background:#F5C400;color:#000;border:none;padding:7px 20px;
                               border-radius:6px;font-family:'Bebas Neue',sans-serif;
                               font-size:15px;letter-spacing:2px;cursor:pointer;">
                        GUARDAR
                    </button>
                </div>
            `);
        }

        // ══════════════════════════════════
        // ══  HTML FINAL DEL MODAL        ══
        // ══════════════════════════════════
        const html = `
        <style>
            .kd-sec {
                background: #0a0a0a;
                border: 1px solid #1e1e1e;
                border-left-width: 3px;
                border-radius: 8px;
                padding: 16px 18px;
                margin-bottom: 12px;
            }
            .kd-sec-title {
                font-family: 'Bebas Neue', sans-serif;
                font-size: 14px;
                letter-spacing: 2px;
                text-transform: uppercase;
                margin-bottom: 14px;
                display: flex;
                align-items: center;
                gap: 7px;
            }
            .kd-label {
                font-size: 9px;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
                color: #444;
                display: block;
                margin-bottom: 4px;
            }
            .kd-val {
                color: #d0d0d0;
                font-size: 13px;
                line-height: 1.65;
            }
            .kd-hr { border: none; border-top: 1px solid #1a1a1a; margin: 12px 0; }
            .kd-info-row {
                display: flex; align-items: flex-start;
                gap: 10px; padding: 9px 0;
                border-bottom: 1px solid #111;
            }
            .kd-info-row:last-child { border-bottom: none; }
            .kd-icon {
                width: 28px; height: 28px; border-radius: 6px;
                background: #111; display: flex; align-items: center;
                justify-content: center; flex-shrink: 0;
                font-size: 13px; color: #444;
            }
            .kd-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            @media(max-width:640px) {
                .kd-2col { grid-template-columns: 1fr; }
                .kd-main { grid-template-columns: 1fr !important; }
            }
        </style>

        <div style="font-family:'Segoe UI',system-ui,sans-serif;color:#c8c8c8;">

            <!-- ══ CABECERA ══ -->
            <div style="margin-bottom:18px;">
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:12px;">
                    ${_pill(es.i, es.label, es.c, es.c + '18')}
                    ${_pill(priI, (d.prioridad_admin || 'Baja'), priC, priC + '18')}
                    ${d.servicio ? `<span style="background:#111;color:#888;border:1px solid #222;padding:4px 12px;border-radius:20px;font-size:11px;">${d.servicio}</span>` : ''}
                    ${d.tipo_requerimiento ? `<span style="background:#111;color:#555;border:1px solid #1e1e1e;padding:4px 12px;border-radius:20px;font-size:10px;">${d.tipo_requerimiento}</span>` : ''}
                </div>
                <h2 style="font-family:'Bebas Neue',sans-serif;color:#fff;font-size:26px;letter-spacing:2px;margin:0 0 2px;">${d.titulo || 'Sin Título'}</h2>
                <p style="color:#555;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin:0 0 10px;">
                    ${d.nombreempresa || ''} ${d.area_solicitante_nombre ? '· ' + d.area_solicitante_nombre : ''}
                </p>
                ${trabajoHtml}
            </div>

            <!-- ══ GRID PRINCIPAL ══ -->
            <div class="kd-main" style="display:grid;grid-template-columns:1fr 285px;gap:14px;align-items:start;">

                <!-- ══ IZQUIERDA ══ -->
                <div>

                    <!-- Brief -->
                    ${_seccion('bi-bullseye', 'Brief del Pedido', '#F5C400', `
                        <div class="kd-2col">
                            <div>${_label('Objetivo de comunicación')}<div class="kd-val">${d.objetivo_comunicacion || '---'}</div></div>
                            <div>${_label('Público objetivo')}<div class="kd-val">${d.publico_objetivo || '---'}</div></div>
                        </div>
                        <hr class="kd-hr">
                        <div class="kd-2col">
                            <div>${_label('Tipo de requerimiento')}<div class="kd-val">${d.tipo_requerimiento || 'Estándar'}</div></div>
                            <div>${_label('Área solicitante')}<div class="kd-val" style="text-transform:uppercase;">${d.area_solicitante_nombre || '---'}</div></div>
                        </div>
                    `)}

                    <!-- Descripción -->
                    ${_seccion('bi-card-text', 'Descripción', '#374151', `
                        <div class="kd-val" style="line-height:1.75;white-space:pre-wrap;">${d.descripcion || 'Sin descripción.'}</div>
                    `)}

                    <!-- Canales y formatos -->
                    ${_seccion('bi-broadcast', 'Canales y Formatos', '#374151', `
                        <div class="kd-2col">
                            <div>
                                ${_label('Canales de difusión')}
                                <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;">${renderTags(d.canales_difusion)}</div>
                            </div>
                            <div>
                                ${_label('Formatos solicitados')}
                                <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;">${renderTags(d.formatos_solicitados)}</div>
                            </div>
                        </div>
                    `)}

                    <!-- Archivos adjuntos -->
                    ${_seccion('bi-paperclip', 'Archivos Adjuntos', '#374151', arcHtml)}

                    <!-- URLs del cliente si aplica -->
                    ${urlsClienteHtml}

                    <!-- Entregables si aplica -->
                    ${entregablesHtml}

                    <!-- Panel prioridad admin -->
                    ${prioridadPanel}

                </div>

                <!-- ══ DERECHA: resumen sticky ══ -->
                <div>
                    <div style="background:#0a0a0a;border:1px solid #1e1e1e;border-radius:8px;padding:16px;position:sticky;top:0;">

                        <div style="font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:2px;color:#2a2a2a;margin-bottom:14px;">
                            Información del Pedido
                        </div>

                        <!-- Empleado -->
                        <div style="margin-bottom:14px;">
                            ${_label('Empleado asignado')}
                            ${empleadoHtml}
                        </div>

                        <hr class="kd-hr">

                        <!-- Fecha requerida -->
                        <div class="kd-info-row">
                            <div class="kd-icon"><i class="bi bi-calendar-event"></i></div>
                            <div>
                                ${_label('Fecha requerida')}
                                <div style="color:${fReq ? '#f0f0f0' : '#444'};font-size:13px;font-weight:700;">${fReq ?? 'No definida'}</div>
                            </div>
                        </div>

                        <!-- Fecha solicitud -->
                        <div class="kd-info-row">
                            <div class="kd-icon"><i class="bi bi-calendar-plus"></i></div>
                            <div>
                                ${_label('Fecha de solicitud')}
                                <div style="color:#f0f0f0;font-size:13px;font-weight:700;">${fSol ?? '---'}</div>
                            </div>
                        </div>

                        <!-- Inicio de trabajo -->
                        <div class="kd-info-row">
                            <div class="kd-icon"><i class="bi bi-play-circle" style="color:${fIni ? '#10b981' : '#333'};"></i></div>
                            <div>
                                ${_label('Inicio de trabajo')}
                                <div style="color:${fIni ? '#10b981' : '#444'};font-size:13px;font-weight:700;">
                                    ${fIni ?? 'Aún no iniciado'}
                                </div>
                            </div>
                        </div>

                        <!-- Completado (si aplica) -->
                        ${fFin ? `
                        <div class="kd-info-row">
                            <div class="kd-icon"><i class="bi bi-check2-all" style="color:#22c55e;"></i></div>
                            <div>
                                ${_label('Completado el')}
                                <div style="color:#22c55e;font-size:13px;font-weight:700;">${fFin}</div>
                            </div>
                        </div>` : ''}

                        <!-- Área de agencia -->
                        <div class="kd-info-row">
                            <div class="kd-icon"><i class="bi bi-building"></i></div>
                            <div>
                                ${_label('Área de agencia')}
                                <div style="color:#f0f0f0;font-size:12px;font-weight:700;text-transform:uppercase;">${d.area_nombre || '---'}</div>
                            </div>
                        </div>

                        <!-- Modificaciones -->
                        <div class="kd-info-row">
                            <div class="kd-icon"><i class="bi bi-arrow-repeat"></i></div>
                            <div>
                                ${_label('Modificaciones')}
                                <div style="color:#f0f0f0;font-size:13px;font-weight:700;">${d.num_modificaciones || 0}</div>
                            </div>
                        </div>

                        <hr class="kd-hr">

                        <!-- Estado actual -->
                        <div>
                            ${_label('Estado actual')}
                            <div style="margin-top:6px;">${_pill(es.i, es.label, es.c, es.c + '18')}</div>
                        </div>

                        <!-- Botón INICIAR TRABAJO:
                             Solo aparece si hay empleado asignado y aún no inicia -->
                        ${(d.idempleado && d.estado === 'pendiente_asignado') ? `
                        <hr class="kd-hr">
                        <button onclick="iniciarTrabajo(${d.id})"
                            style="width:100%;background:#F5C400;color:#000;border:none;
                                   padding:10px;border-radius:7px;font-family:'Bebas Neue',sans-serif;
                                   font-size:16px;letter-spacing:2px;cursor:pointer;
                                   display:flex;align-items:center;justify-content:center;gap:6px;">
                            <i class="bi bi-play-fill"></i> INICIAR TRABAJO
                        </button>` : ''}

                    </div>
                </div>

            </div>
        </div>`;

        // Título del modal
        document.getElementById('detalle-titulo').innerHTML =
            d.cliente_nombre ? d.cliente_nombre.toUpperCase() : 'CLIENTE';

        cuerpo.innerHTML = html;

    } catch (e) {
        console.error(e);
        cuerpo.innerHTML = _errorHtml('Error fatal al cargar los datos. Intente nuevamente.');
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
        padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.5px;
        text-transform:uppercase;display:inline-flex;align-items:center;gap:5px;">
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
    return `<span class="kd-label">${texto}</span>`;
}

function _errorHtml(msg) {
    return `<div style="color:#ef4444;padding:40px;text-align:center;">
        <i class="bi bi-exclamation-triangle" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        <p style="font-size:13px;">${msg}</p>
    </div>`;
}

function _parseList(json) {
    if (!json) return [];
    try { const l = JSON.parse(json); return Array.isArray(l) ? l : [json]; }
    catch { return [json]; }
}

// ═══════════════════════════════════════
// ═══ DRAG & DROP (SORTABLE.JS)        ══
// ═══════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const colAprobar = document.querySelector('.kb-col-body[data-estado="pendiente_sin_asignar"]');
    const colProceso = document.querySelector('.kb-col-body[data-estado="en_proceso"]');

    if (colAprobar && colProceso) {
        new Sortable(colAprobar, {
            // Permite mover la tarjeta a la columna destino (sin duplicar),
            // manteniendo el flujo: al soltar, se actualiza el estado en backend.
            group: { name: 'kanban', pull: true, put: false },
            sort: false,
            draggable: '.kb-card',
            animation: 150,
            // Más estable en algunos navegadores/Windows
            forceFallback: true,
            fallbackOnBody: true
        });

        new Sortable(colProceso, {
            group: { name: 'kanban', pull: false, put: true },
            draggable: '.kb-card',
            animation: 150,
            // En "EN PROCESO" no se permite mover tarjetas
            sort: false,
            forceFallback: true,
            fallbackOnBody: true,
            onAdd(evt) {
                const card = evt.item;
                const idAtencion = card.getAttribute('data-id');
                card.style.opacity = '0.4';

                _post('admin/kanban/cambiarEstado', {
                    idatencion: idAtencion,
                    estado: 'pendiente_asignado',
                    accion: 'Aprobado — enviado a Área (Drag & Drop)',
                    idareaagencia: AREA_ACTUAL
                }).then(data => {
                    if (data.status === 'success') location.reload();
                    else { alert(data.msg); location.reload(); }
                }).catch(() => { alert('Error procesando solicitud'); location.reload(); });
            }
        });
    }
});

