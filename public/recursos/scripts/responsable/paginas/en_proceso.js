document.addEventListener('DOMContentLoaded', function () {
    cargarTareasEnProceso();
});

/**
 * Cargar tareas en proceso desde el backend
 */
function cargarTareasEnProceso() {
    renderizarEmpleados(empleadosData);
    document.getElementById('total-empleados').textContent = empleadosData.length;

    // Cargar tareas de todos los empleados
    window._totalTareas = 0;
    empleadosData.forEach(empleado => {
        cargarTareasEmpleado(empleado.id);
    });
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
                    <i class="bi bi-inbox" style="font-size: 2.5rem; color: #444;"></i>
                    <p class="mt-3" style="color: #666;">No hay empleados en el área</p>
                </div>
            </div>
        `;
        return;
    }

    // Ordenar para poner al usuario actual (Responsable) primero
    const sortedEmpleados = [...empleados].sort((a, b) => {
        if (a.id == window.currentUserId) return -1;
        if (b.id == window.currentUserId) return 1;
        return 0;
    });

    container.innerHTML = sortedEmpleados.map(empleado => {
        const isMe = empleado.id == window.currentUserId;
        const colClass = isMe ? 'col-12' : 'col-lg-6';

        return `
        <div class="${colClass} mb-4">
            <div class="card-dark-main p-4 ${isMe ? 'border-me' : ''}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="empleado-avatar ${empleado.esresponsable ? 'responsable' : ''} ${isMe ? 'me' : ''}">
                            ${empleado.esresponsable ? '<i class="bi bi-shield-check"></i>' : obtenerIniciales(empleado.nombre_completo)}
                        </div>
                        <div class="ms-3">
                            <h6 class="text-white mb-1" style="font-size: 15px;">
                                ${escaparHtml(empleado.nombre_completo)} 
                                ${isMe ? '<span class="badge bg-warning text-dark ms-2" style="font-size: 9px; vertical-align: middle;">TÚ</span>' : ''}
                            </h6>
                            <span style="font-size: 11px; color: ${empleado.esresponsable ? '#f5c400' : '#888'};">
                                ${empleado.esresponsable ? 'Jefe de Área' : 'Miembro del Equipo'}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-white" style="font-size: 20px; font-weight: 700;" id="tareas-count-${empleado.id}">0</span>
                        <br>
                        <small style="color: #666; font-size: 11px;">tareas</small>
                    </div>
                </div>
                
                <div class="tareas-lista" id="tareas-container-${empleado.id}">
                    <div class="text-center py-3" style="color: #555;">
                        <div class="spinner-border spinner-border-sm me-2" role="status" style="width: 14px; height: 14px;"></div>
                        <span style="font-size: 13px;">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    `}).join('');
}

/**
 * Cargar tareas de un empleado específico
 */
function cargarTareasEmpleado(idEmpleado) {
    const container = document.getElementById(`tareas-container-${idEmpleado}`);
    const countElement = document.getElementById(`tareas-count-${idEmpleado}`);

    fetch(`${window.base_url}responsable/tareas/empleado/${idEmpleado}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderizarTareasEmpleado(container, data.data, idEmpleado);
                countElement.textContent = data.total_tareas;

                // Actualizar total global
                window._totalTareas += data.total_tareas;
                document.getElementById('total-tareas').textContent = window._totalTareas;
            } else {
                container.innerHTML = `<div class="text-center py-3" style="color: #666; font-size: 13px;">Error al cargar</div>`;
                countElement.textContent = '0';
            }
        })
        .catch(() => {
            container.innerHTML = `<div class="text-center py-3" style="color: #666; font-size: 13px;">Error de conexión</div>`;
        });
}

/**
 * Renderizar tareas - SOLO título y prioridad
 */
function renderizarTareasEmpleado(container, tareas, idEmpleado) {
    if (tareas.length === 0) {
        container.innerHTML = `
            <div class="text-center py-3" style="color: #555; font-size: 13px;">
                Sin tareas asignadas
            </div>
        `;
        return;
    }

    container.innerHTML = tareas.map(tarea => {
        const isMe = (parseInt(idEmpleado) === parseInt(window.currentUserId));
        const hasStarted = (tarea.fechainicio && tarea.fechainicio !== '0000-00-00 00:00:00' && tarea.fechainicio !== '0000-00-00');
        const canDeliver = isMe && hasStarted && tarea.estado === 'en_proceso';

        return `
        <div class="tarea-item mb-2" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 12px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 0;">
                    <span class="badge-prio ${(tarea.prioridad || 'media').toLowerCase()}">${tarea.prioridad || 'Media'}</span>
                    <span class="tarea-titulo text-truncate" style="font-weight: 600; color: #fff;">${escaparHtml(tarea.titulo || 'Sin título')}</span>
                    ${tarea.observacion_revision ? `
                        <span class="badge-returned" title="Tarea devuelta con observaciones">DEVUELTO</span>
                    ` : ''}
                </div>
                <div class="d-flex gap-1">
                    ${(isMe && !hasStarted) ? `
                        <button class="btn btn-sm btn-warning" onclick="iniciarTrabajo(${tarea.id})" title="Registrar inicio de trabajo" style="padding: 4px 15px; font-size: 11px; font-weight: 800; background: #f5c400; color: #000; border: none; letter-spacing: 0.5px;">
                            <i class="bi bi-play-fill me-1"></i> INICIAR TRABAJO
                        </button>
                    ` : `
                        ${canDeliver ? `
                            <button class="btn btn-sm btn-success" onclick="abrirModalEntregar(${tarea.id})" title="Entregar mi trabajo" style="padding: 2px 8px; font-size: 11px;">
                                <i class="bi bi-send-fill me-1"></i> ENTREGAR
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-outline-warning" onclick="verDetalleTarea(${tarea.id})" title="Ver detalles" style="border-color: #444; color: #fff; padding: 2px 8px;">
                            <i class="bi bi-eye" style="font-size: 13px;"></i>
                        </button>
                    `}
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between mt-1">
                <div style="font-size: 11px; color: #777; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="color: #aaa; font-weight: 600; text-transform: uppercase; font-size: 10px;">${escaparHtml(tarea.nombre_servicio || 'Servicio')}</span>
                    

                    ${hasStarted ? `
                        <span style="color: #22c55e; background: rgba(34, 197, 94, 0.1); padding: 1px 6px; border-radius: 4px; font-size: 10px; border: 1px solid rgba(34, 197, 94, 0.2);">
                            <i class="bi bi-calendar-check me-1"></i> Iniciado: ${formatearFechaLimpia(tarea.fechainicio)}
                        </span>
                    ` : ''}
                </div>
                <div style="font-size: 10px; color: #444; font-style: italic;">
                    #REQ-${tarea.id_requerimiento || tarea.id}
                </div>
            </div>
        </div>`;
    }).join('');
}

/**
 * Ver detalles de una tarea
 */
function verDetalleTarea(idAtencion) {
    fetch(`${window.base_url}responsable/pedidos/detalle?id=${idAtencion}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.requerimientoActualEnProceso = data.data; // Guardar globalmente
                mostrarModalDetalle(data.data, data.archivos, data.tracking);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudieron cargar los detalles', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false });
        });
}

/**
 * Modal con diseño limpio - blanco/negro/amarillo
 */
function mostrarModalDetalle(req, archivos, tracking) {
    const cuerpo = document.getElementById('detalle-tarea-content');

    // ── Fechas ──
    const fReq = req.fecha_requerida_formateada || req.fecharequerida;
    const fSol = req.fecha_formateada || req.fechacreacion;
    const fIni = req.fecha_inicio_formateada || req.fechainicio;

    // ── Trabajo HTML ──
    let trabajoHtml;
    if (req.estado === 'finalizado' || req.estado === 'completado') {
        trabajoHtml = _pill('bi-check2-circle', 'Completado', '#22c55e', '#052e16');
    } else if (!req.empleado_asignado) {
        trabajoHtml = _pill('bi-hourglass-split', 'Pendiente de asignación', '#6b7280', '#111');
    } else if (req.estado === 'pendiente_asignado' || req.estado === 'pendiente') {
        trabajoHtml = _pill('bi-person-check-fill', 'Asignado — aún no iniciado', '#F5C400', '#1a1500');
    } else {
        trabajoHtml = _pill('bi-lightning-charge-fill', 'Desarrollando', '#10b981', '#001a0f');
    }

    // ── Estado Map ──
    const estadoMap = {
        pendiente_sin_asignar: { c: '#f59e0b', label: '📋 Nuevo requerimiento', i: 'bi-hourglass-split' },
        pendiente_asignado: { c: '#F5C400', label: '✅ Asignado al diseñador', i: 'bi-send-check-fill' },
        en_proceso: { c: '#10b981', label: '🚀 Trabajando en tu diseño', i: 'bi-lightning-charge-fill' },
        en_revision: { c: '#3b82f6', label: '👀 Listo para revisar', i: 'bi-eye-fill' },
        finalizado: { c: '#22c55e', label: '🎉 Entregado con éxito', i: 'bi-check2-circle' },
        cancelado: { c: '#ef4444', label: '❌ Cancelado', i: 'bi-x-circle-fill' },
    };
    const estKey = (req.estado || '').toLowerCase();
    const es = estadoMap[estKey] || { c: '#aaa', label: req.estado || 'N/A', i: 'bi-circle' };

    const pri = (req.prioridad || 'Media');
    const priC = pri === 'Alta' ? '#ef4444' : (pri === 'Media' ? '#F5C400' : '#3b82f6');
    const priI = pri === 'Alta' ? 'bi-arrow-up-circle-fill' : (pri === 'Media' ? 'bi-dash-circle-fill' : 'bi-arrow-down-circle-fill');

    // ── Empleado ──
    let empleadoHtml;
    if (req.empleado_asignado) {
        const ini = obtenerIniciales(req.empleado_asignado.nombre + ' ' + req.empleado_asignado.apellidos);
        empleadoHtml = `
            <div style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                <div style="width:36px;height:36px;border-radius:50%;
                    background:linear-gradient(135deg,#F5C400,#b45309);
                    color:#000;font-weight:800;font-size:13px;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">${ini}</div>
                <div>
                    <div style="color:#f0f0f0;font-weight:700;font-size:13px;">${escaparHtml(req.empleado_asignado.nombre)} ${escaparHtml(req.empleado_asignado.apellidos)}</div>
                    <div style="color:#555;font-size:10px;text-transform:uppercase;letter-spacing:.5px;">
                        ${req.estado === 'en_proceso' ? 'En desarrollo' : 'Asignado'}
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
                    <div style="color:#555;font-size:10px;letter-spacing:.5px;">Esperando asignación</div>
                </div>
            </div>`;
    }

    // ── Entrega info (si existe) ──
    let entregaHtml = '';
    if (req.estado === 'en_revision' || req.estado === 'finalizado') {
        const urlEntrega = req.url_entrega ? `<a href="${escaparHtml(req.url_entrega)}" target="_blank" class="btn btn-sm btn-outline-success" style="font-size:11px;"><i class="bi bi-box-arrow-up-right"></i> VER TRABAJO FINAL</a>` : '<span style="color:#555;">No se registró URL</span>';

        // Archivos de entrega
        let arcEntHtml = '';
        const archivosEntrega = archivos.filter(a => a.idatencion);
        if (archivosEntrega.length > 0) {
            arcEntHtml = `<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:8px;margin-top:8px;">`;
            archivosEntrega.forEach(a => {
                const icon = getFileIcon(a.nombre_original || a.nombre);
                arcEntHtml += `
                    <button type="button" onclick="abrirArchivo(${a.id})"
                       style="display:flex;align-items:center;gap:8px;padding:9px 12px;
                              background:#0a0a0a;border:1px solid #22c55e;border-radius:7px;
                              color:#aaa;text-decoration:none;font-size:11px;transition:all .15s;
                              min-width:0; overflow:hidden; width:100%; text-align:left;">
                        <i class="bi ${icon}" style="color:#22c55e;font-size:16px;flex-shrink:0;"></i>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;min-width:0;">${escaparHtml(a.nombre_original || a.nombre)}</span>
                    </button>`;
            });
            arcEntHtml += '</div>';
        } else {
            arcEntHtml = '<p style="color:#444;font-size:11px;font-style:italic;">No se subieron archivos físicos en la entrega.</p>';
        }

        entregaHtml = _seccion('bi-send-check', 'Información de la Entrega', '#22c55e', `
            <div class="mb-3">
                ${_label('URL de Entrega')}
                <div style="margin-top:5px;">${urlEntrega}</div>
            </div>
            <div class="mb-3">
                ${_label('Notas de Entrega / Observaciones')}
                <div class="kd-val" style="font-size:13px; color:#bbb;">${escaparHtml(req.observacion_revision || 'Sin observaciones')}</div>
            </div>
            <div>
                ${_label('Archivos de la Entrega')}
                ${arcEntHtml}
            </div>
        `);
    }

    // ── Archivos de la Solicitud (idatencion es nulo o ruta en requerimientos) ──
    const archivosSolicitud = archivos.filter(a => !a.idatencion || (a.ruta && a.ruta.includes('requerimientos')));
    let arcSolHtml = '';
    if (archivosSolicitud.length) {
        arcSolHtml = `<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:8px;margin-top:8px;max-height:200px;overflow-y:auto;padding-right:4px;">`;
        archivosSolicitud.forEach(a => {
            const icon = getFileIcon(a.nombre_original || a.nombre);
            arcSolHtml += `
                <button type="button" onclick="abrirArchivo(${a.id})"
                   style="display:flex;align-items:center;gap:8px;padding:9px 12px;
                          background:#0a0a0a;border:1px solid #1e1e1e;border-radius:7px;
                          color:#aaa;text-decoration:none;font-size:11px;transition:border-color .15s;
                          min-width:0; overflow:hidden; width:100%; text-align:left;"
                   onmouseover="this.style.borderColor='#F5C400'"
                   onmouseout="this.style.borderColor='#1e1e1e'">
                    <i class="bi ${icon}" style="color:#F5C400;font-size:16px;flex-shrink:0;"></i>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;min-width:0;">${escaparHtml(a.nombre_original || a.nombre)}</span>
                </button>`;
        });
        arcSolHtml += '</div>';
    } else {
        arcSolHtml = '<p style="color:#333;font-size:11px;font-style:italic;margin:8px 0 0;">No se adjuntaron archivos.</p>';
    }

    // ── URLs del Cliente ──
    let urlsClienteHtml = '';
    if (req.url_subida) {
        const link = `<a href="${escaparHtml(req.url_subida)}" target="_blank" style="color:#60a5fa;text-decoration:underline;font-size:13px;word-break:break-all;">${escaparHtml(req.url_subida)}</a>`;
        urlsClienteHtml = _seccion('', 'URLs enviadas por el Cliente', '#60a5fa', `
            <div>
                ${_label('Enlace / URLs')}
                <div style="margin-top:5px;">${link}</div>
            </div>
        `);
    }

    // ── Cliente info (derecha) ──
    const clienteHtml = `
        <div style="margin-top: 24px;">
            <div style="font-family:'Bebas Neue',sans-serif;font-size:15px;letter-spacing:2px;color:#555;margin-bottom:14px;">
                INFORMACIÓN DEL CLIENTE
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="background:#111;border:1px solid #1e1e1e;border-radius:8px;padding:12px;">
                    ${_label('Nombre completo')}
                    <div class="kd-val" style="margin-top:4px;">${escaparHtml(req.nombre_cliente || 'N/A')}</div>
                </div>
                <div style="background:#111;border:1px solid #1e1e1e;border-radius:8px;padding:12px;">
                    ${_label('Empresa')}
                    <div class="kd-val" style="margin-top:4px;">${escaparHtml(req.nombre_empresa || 'N/A')}</div>
                </div>
                <div style="background:#111;border:1px solid #1e1e1e;border-radius:8px;padding:12px;">
                    ${_label('Teléfono')}
                    <div class="kd-val" style="margin-top:4px;">${escaparHtml(req.telefono_cliente || 'N/A')}</div>
                </div>
                <div style="background:#111;border:1px solid #1e1e1e;border-radius:8px;padding:12px;">
                    ${_label('Correo')}
                    <div class="kd-val" style="margin-top:4px;word-break:break-all;">${escaparHtml(req.correo_cliente || 'N/A')}</div>
                </div>
            </div>
        </div>
    `;

    // ── HTML ──
    const html = `
    <div class="modal-ver-detalle" style="font-family:'Segoe UI',system-ui,sans-serif;color:#c8c8c8;padding-top:10px;">
        <!-- ══ CABECERA ══ -->
        <div style="margin-bottom:18px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:12px;">
                ${_pill(es.i, es.label, es.c, es.c + '18')}
                ${_pill(priI, pri, priC, priC + '18')}
                ${req.tipo_requerimiento ? `<span style="background:#111;color:#555;border:1px solid #1e1e1e;padding:4px 12px;border-radius:20px;font-size:10px;">${escaparHtml(req.tipo_requerimiento)}</span>` : ''}
            </div>
            <h2 style="font-family:'Bebas Neue',sans-serif;color:#fff;font-size:28px;letter-spacing:2px;margin:0 0 2px;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;hyphens:auto; display:flex; align-items:center; gap:15px;">
                ${escaparHtml(req.titulo || 'Sin Título')}
            </h2>
            <p style="color:#777;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin:0 0 10px;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">
                ${escaparHtml(req.nombre_empresa || 'Empresa no asignada')} | ${escaparHtml(req.nombre_servicio || req.servicio || 'Servicio no especificado')}
            </p>
            ${trabajoHtml}
        </div>

        <!-- ══ GRID PRINCIPAL ══ -->
        <div class="kd-main" style="display:grid;grid-template-columns:1fr 285px;gap:18px;align-items:start;">
            <!-- ══ IZQUIERDA ══ -->
            <div style="display:flex;flex-direction:column;gap:4px;min-width:0;">
                
                ${entregaHtml}

                ${_seccion('', 'Tipo de Requerimiento', '#3b82f6', `
                    <div class="kd-val" style="font-weight:700; color:#fff;">${escaparHtml(req.tipo_requerimiento || 'Sin especificar')}</div>
                `)}

                ${_seccion('', 'Objetivo de Comunicación', '#F5C400', `
                    <div class="kd-val" style="white-space:pre-wrap;">${escaparHtml(req.objetivo_comunicacion || '---')}</div>
                `)}

                ${_seccion('', 'Público Objetivo', '#F5C400', `
                    <div class="kd-val" style="white-space:pre-wrap;">${escaparHtml(req.publico_objetivo || '---')}</div>
                `)}

                ${_seccion('', 'Descripción', '#555', `
                    <div class="kd-val" style="line-height:1.75;white-space:pre-wrap;word-break:break-word;max-height:300px;overflow-y:auto;padding-right:5px;">${escaparHtml(req.descripcion || 'Sin descripción.')}</div>
                `)}

                ${_seccion('', 'Canales de Difusión', '#555', `
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">${formatearLista(req.canales_difusion)}</div>
                `)}

                ${_seccion('', 'Formatos Solicitados', '#555', `
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">${formatearLista(req.formatos_solicitados)}</div>
                `)}

                ${_seccion('', 'Archivos Adjuntos a la Solicitud', '#374151', arcSolHtml)}

                ${urlsClienteHtml}

            </div>

            <!-- ══ DERECHA: resumen sticky ══ -->
            <div style="min-width:285px;">
                <div style="background:#0a0a0a;border:1px solid #1e1e1e;border-radius:8px;padding:16px;position:sticky;top:0;">
                    
                    <div style="font-family:'Bebas Neue',sans-serif;font-size:15px;letter-spacing:2px;color:#555;margin-bottom:14px;">
                        INFORMACIÓN DEL PEDIDO
                    </div>

                    <!-- 1. Quién solicita (Cliente, Empresa, Área) -->
                    <div class="mb-3">
                        ${_label('Solicitado por')}
                        <div style="color:#fff;font-weight:700;font-size:15px;margin-bottom:12px;">${escaparHtml(req.nombre_cliente || '---')}</div>
                        
                        <div class="mb-2">
                            <span style="font-size:9px; color:#555; text-transform:uppercase; font-weight:800; letter-spacing:1px; display:block; margin-bottom:2px;">Área</span>
                            <div style="color:#e0e0e0; font-size:12px; font-weight:600;">${escaparHtml(req.nombre_area || 'Área no especificada')}</div>
                        </div>
                        <div>
                            <span style="font-size:9px; color:#555; text-transform:uppercase; font-weight:800; letter-spacing:1px; display:block; margin-bottom:2px;">Empresa</span>
                            <div style="color:#F5C400; font-size:13px; font-weight:700;">${escaparHtml(req.nombre_empresa || '---')}</div>
                        </div>
                    </div>

                    <hr class="kd-hr">

                    <!-- 2. Asignación (Empleado) -->
                    <div style="margin-bottom:14px;">
                        ${_label('Empleado asignado')}
                        ${empleadoHtml}
                    </div>

                    <hr class="kd-hr">

                    <!-- 3. Fechas -->
                    <div class="kd-info-row">
                        <div>
                            ${_label('Fecha requerida')}
                            <div style="color:${fReq ? '#f0f0f0' : '#555'};font-size:14px;font-weight:700;">${formatearFechaLimpia(fReq) || 'No definida'}</div>
                        </div>
                    </div>

                    <div class="kd-info-row">
                        <div>
                            ${_label('Fecha de solicitud')}
                            <div style="color:#f0f0f0;font-size:14px;font-weight:700;">${formatearFechaLimpia(fSol) || '---'}</div>
                        </div>
                    </div>

                    <div class="kd-info-row">
                        <div>
                            ${_label('Inicio de trabajo')}
                            <div style="color:${fIni ? '#10b981' : '#555'};font-size:14px;font-weight:700;">
                                ${formatearFechaLimpia(fIni) || 'Aún no iniciado'}
                            </div>
                        </div>
                    </div>

                    <hr class="kd-hr">

                    <!-- 4. Estado -->
                    <div>
                        ${_label('Estado actual')}
                        <div style="margin-top:6px;">${_pill(es.i, es.label, es.c, es.c + '18')}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>`;

    cuerpo.innerHTML = html;

    // ── Gestionar Botones en la Cabecera ──
    const modalHeader = document.querySelector('.modal-detalle-header');
    const existingBtn = document.getElementById('btn-formalizar-header');
    if (existingBtn) existingBtn.remove();
    const existingStartBtn = document.getElementById('btn-iniciar-header');
    if (existingStartBtn) existingStartBtn.remove();
    const existingEditBtns = document.getElementById('container-botones-edicion');
    if (existingEditBtns) existingEditBtns.remove();

    const isContentArea = (parseInt(req.idarea_agencia) === 3);
    const esServicioEditable = isContentArea && (req.estado === 'pendiente_asignado' || req.estado === 'en_proceso' || req.estado === 'pendiente');
    const isMeTask = (req.idempleado == window.currentUserId);
    const needsStartHeader = isMeTask && !req.fechainicio;

    if (needsStartHeader) {
        const btnStart = document.createElement('button');
        btnStart.id = 'btn-iniciar-header';
        btnStart.className = 'btn btn-sm btn-success ms-auto me-3';
        btnStart.style.cssText = 'font-weight:800; font-size:11px; letter-spacing:1px; padding:6px 16px; border-radius:6px; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2); transition: all 0.2s; border:none; background: #22c55e; color: #fff;';
        btnStart.innerHTML = '<i class="bi bi-play-fill me-2"></i> INICIAR TRABAJO';
        btnStart.onclick = () => {
            iniciarTrabajo(req.idatencion || req.id);
        };

        // Insertar antes del botón de cerrar
        const closeBtn = modalHeader.querySelector('.btn-close');
        modalHeader.insertBefore(btnStart, closeBtn);
    } else if (esServicioEditable) {
        const btn = document.createElement('button');
        btn.id = 'btn-formalizar-header';
        btn.className = 'btn btn-sm btn-warning ms-auto me-3';
        btn.style.cssText = 'font-weight:800; font-size:11px; letter-spacing:1px; padding:6px 16px; border-radius:6px; box-shadow: 0 4px 15px rgba(245, 196, 0, 0.2); transition: all 0.2s; border:none;';
        btn.innerHTML = '<i class="bi bi-pencil-square me-2"></i> EDITAR REQUERIMIENTO';
        btn.onclick = () => activarEdicionRequerimientoEnProceso();

        // Insertar antes del botón de cerrar
        const closeBtn = modalHeader.querySelector('.btn-close');
        modalHeader.insertBefore(btn, closeBtn);
    }

    const modalElement = document.getElementById('modal-detalle-tarea');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}

// ===== FUNCIONES AUXILIARES =====
function abrirArchivo(idArchivo) {
    window.open(`${window.base_url}responsable/archivos/vista-previa/${idArchivo}`, '_blank');
}

function _pill(icon, label, color, bg) {
    return `<span style="background:${bg};color:${color};border:1px solid ${color}33;
        padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.5px;
        text-transform:uppercase;display:inline-flex;align-items:center;gap:5px;">
        ${label}</span>`;
}

function _seccion(icon, titulo, color, innerHtml) {
    return `<div class="kd-sec" style="border-left-color:${color};">
        <div class="kd-sec-title" style="color:${color};">
            ${titulo}
        </div>
        ${innerHtml}
    </div>`;
}

function _label(texto) {
    return `<span class="kd-label">${texto}</span>`;
}

function getFileIcon(nombre) {
    if (!nombre) return 'bi-file-earmark';
    const ext = nombre.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) return 'bi-file-earmark-image';
    if (ext === 'pdf') return 'bi-file-earmark-pdf';
    if (['doc', 'docx'].includes(ext)) return 'bi-file-earmark-word';
    if (['xls', 'xlsx'].includes(ext)) return 'bi-file-earmark-excel';
    if (['ppt', 'pptx'].includes(ext)) return 'bi-file-earmark-slides';
    return 'bi-file-earmark';
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
    return items.map(item => `<span style="display:inline-block;background:#1e1e1e;color:#e8e8e8;border:1px solid #333;padding:6px 14px;border-radius:6px;font-size:13px;margin-bottom:6px;box-shadow:0 2px 4px rgba(0,0,0,0.2);">${escaparHtml(item)}</span>`).join('');
}

function escaparHtml(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

function formatearFechaLimpia(fecha) {
    if (!fecha) return '---';
    // Si ya viene formateada dd/mm/yyyy hh:mm, la dejamos así
    if (fecha.includes('/') && fecha.length <= 16) return fecha;

    try {
        const d = new Date(fecha);
        if (isNaN(d.getTime())) return fecha;

        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');

        return `${day}/${month}/${year} ${hours}:${minutes}`;
    } catch (e) {
        return fecha;
    }
}

function obtenerIniciales(nombre) {
    if (!nombre) return '?';
    const p = nombre.trim().split(' ');
    const first = p[0]?.[0] || '';
    const last = p[1]?.[0] || '';
    return (first + last).toUpperCase();
}

/**
 * Lógica de entrega para el Responsable
 */
function abrirModalEntregar(idAtencion) {
    Swal.fire({
        title: 'REALIZAR ENTREGA',
        html: `
            <div class="text-start" style="font-family: 'Inter', sans-serif;">
                <div class="mb-3">
                    <label class="form-label text-white-50" style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Link del Entregable</label>
                    <input type="text" id="swal-url-entrega" class="form-control" placeholder="Google Drive, Canva, Figma..." style="background: #111; border: 1px solid #333; color: #fff; font-size: 13px;">
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50" style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Subir Archivos (Opcional)</label>
                    <input type="file" id="swal-archivos-entrega" class="form-control" multiple style="background: #111; border: 1px solid #333; color: #fff; font-size: 13px;">
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50" style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Notas adicionales</label>
                    <textarea id="swal-notas-entrega" class="form-control" placeholder="Escribe aquí algún detalle..." style="background: #111; border: 1px solid #333; color: #fff; font-size: 13px; height: 80px;"></textarea>
                </div>
            </div>
        `,
        background: '#161616',
        color: '#fff',
        showCancelButton: true,
        confirmButtonText: 'ENVIAR ENTREGA',
        cancelButtonText: 'CANCELAR',
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#333',
        allowOutsideClick: false,
        allowEscapeKey: false,
        preConfirm: () => {
            const url = document.getElementById('swal-url-entrega').value;
            const files = document.getElementById('swal-archivos-entrega').files;
            const notas = document.getElementById('swal-notas-entrega').value;

            if (!url && files.length === 0) {
                Swal.showValidationMessage('Debes proporcionar al menos un link o un archivo');
                return false;
            }

            return { url, files, notas };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarEntrega(idAtencion, result.value);
        }
    });
}

function ejecutarEntrega(idAtencion, data) {
    const formData = new FormData();
    formData.append('url_entrega', data.url);
    formData.append('notas', data.notas);

    for (let i = 0; i < data.files.length; i++) {
        formData.append('archivos_entrega[]', data.files[i]);
    }

    Swal.fire({
        title: 'Enviando entrega...',
        didOpen: () => { Swal.showLoading(); },
        background: '#161616',
        color: '#fff',
        allowOutsideClick: false
    });

    fetch(`${window.base_url}responsable/pedido-entregar/${idAtencion}`, {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false })
                    .then(() => { location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión al servidor', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400', allowOutsideClick: false, allowEscapeKey: false });
        });
}

/**
 * Activa el modo edición en el modal de en proceso
 */
function activarEdicionRequerimientoEnProceso() {
    const req = window.requerimientoActualEnProceso;
    if (!req) return;

    // Cargar servicios si no están disponibles
    if (!window.serviciosList) {
        fetch(`${window.base_url}responsable/servicios/listar`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.serviciosList = data.data;
                    activarEdicionRequerimientoEnProceso(); // Re-ejecutar ahora con datos
                }
            });
        return;
    }

    // Cambiar botones en la cabecera
    const modalHeader = document.querySelector('.modal-detalle-header');
    const formalizarBtn = document.getElementById('btn-formalizar-header');
    if (formalizarBtn) formalizarBtn.remove();

    const containerBotones = document.createElement('div');
    containerBotones.id = 'container-botones-edicion';
    containerBotones.className = 'ms-auto me-3 d-flex gap-2';
    containerBotones.innerHTML = `
            <button class="btn btn-sm btn-success" onclick="guardarEdicionRequerimientoEnProceso()" style="font-weight:800; font-size:11px; padding:6px 16px;">GUARDAR CAMBIOS</button>
            <button class="btn btn-sm btn-outline-light" onclick="verDetalleTarea(${req.idatencion || req.id})" style="font-weight:800; font-size:11px; padding:6px 16px; border-color:#444;">CANCELAR</button>
        `;
    const closeBtn = modalHeader.querySelector('.btn-close');
    modalHeader.insertBefore(containerBotones, closeBtn);

    // Mensaje de Modo Edición en el cuerpo
    const headerH2 = document.querySelector('.modal-ver-detalle h2');
    headerH2.innerHTML = `<span style="color:#F5C400; font-family:'Bebas Neue'; letter-spacing:2px;">MODO FORMALIZACIÓN DE REQUERIMIENTO</span>`;

    // Transformar campos de la izquierda
    const leftContainer = document.querySelector('.kd-main > div:first-child');

    // Listas para checkboxes (Estándar - Actualizadas)
    const canalesStandard = [
        'Por correo',
        'Página web',
        'Redes sociales',
        'SIGU o Aula Virtual Estudiantes',
        'SIGU o Aula Virtual Docentes',
        'Impresión física de folletos',
        'Banner físico',
        'Letreros',
        'Merch para eventos específicos'
    ];
    const formatosStandard = [
        'Emailing (pieza para correo)',
        'Post de Facebook/Instagram',
        'Historia Facebook/Instagram',
        'Historia de Whatsapp',
        'Post de LinkedIn',
        'SIGU (comunicado)',
        'Aula Virtual (Pop up)',
        'Wallpaper – Computadoras',
        'Banner Web Portada',
        'Volante A5',
        'Afiche A4',
        'Afiche A3',
        'Credenciales',
        'Banner 2x1',
        'Tarjeta Personal',
        'Tríptico',
        'Díptico',
        'Folder A4',
        'Brochure',
        'Cartilla',
        'Banderola',
        'Módulos',
        'SMS',
        'IVR',
        'Marcos Selfie',
        'Boletín',
        'Guías (para proceso, trámites, pagos, etc)',
        'Imagen JPG - PNG'
    ];

    const canalesActuales = (req.canales_difusion || '').split(',').map(s => s.trim()).filter(s => s !== '');
    const formatosActuales = (req.formatos_solicitados || '').split(',').map(s => s.trim()).filter(s => s !== '');

    // Detectar si hay valores "Otros"
    const canalesOtrosValues = []; // Canales ya no tiene "Otros"
    const formatosOtrosValues = formatosActuales.filter(f => !formatosStandard.includes(f));

    const tieneOtrosFormatos = formatosOtrosValues.length > 0;

    leftContainer.innerHTML = `
        <div class="kd-sec" style="border-left-color:#F5C400; background: #0e0e0e;">
            <div class="kd-sec-title"><i class="bi bi-pencil-square"></i> EDITAR DATOS DEL REQUERIMIENTO</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="kd-label">Título del Requerimiento</label>
                    <input type="text" id="edit-pro-titulo" class="form-control form-control-sm bg-dark text-white border-secondary" value="${escaparHtml(req.titulo)}">
                </div>
                <input type="hidden" id="edit-pro-servicio" value="${req.idservicio}">
                <div class="col-md-6">
                    <label class="kd-label">Tipo de Requerimiento</label>
                    <select id="edit-pro-tipo-req" class="form-select form-select-sm bg-dark text-white border-secondary">
                        <option value="" ${!req.tipo_requerimiento ? 'selected' : ''}>Seleccionar...</option>
                        <option value="Adaptación de Arte" ${req.tipo_requerimiento === 'Adaptación de Arte' ? 'selected' : ''}>Adaptación de Arte — 2 días hábiles</option>
                        <option value="Creación de Arte" ${req.tipo_requerimiento === 'Creación de Arte' ? 'selected' : ''}>Creación de Arte — 4 días hábiles</option>
                        <option value="Creación de editorial" ${req.tipo_requerimiento?.includes('editorial') && req.tipo_requerimiento?.includes('Creación') ? 'selected' : ''}>Creación de editorial (revistas, boletines, guías, similares) — 7 días hábiles</option>
                        <option value="Adaptación de editorial" ${req.tipo_requerimiento?.includes('editorial') && req.tipo_requerimiento?.includes('Adaptación') ? 'selected' : ''}>Adaptación de editorial (revistas, boletines, guías, similares) — 7 días hábiles</option>
                        <option value="Creación de Videos" ${req.tipo_requerimiento?.includes('Videos') ? 'selected' : ''}>Creación de Vídeos (institucionales, reels, etc) — 7 días hábiles</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="kd-label">Objetivo de Comunicación</label>
                    <textarea id="edit-pro-objetivo" class="form-control form-control-sm bg-dark text-white border-secondary" rows="3">${req.objetivo_comunicacion || ''}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="kd-label">Público Objetivo</label>
                    <textarea id="edit-pro-publico" class="form-control form-control-sm bg-dark text-white border-secondary" rows="3">${req.publico_objetivo || ''}</textarea>
                </div>
                <div class="col-12">
                    <label class="kd-label">Descripción Detallada</label>
                    <textarea id="edit-pro-descripcion" class="form-control form-control-sm bg-dark text-white border-secondary" rows="5">${req.descripcion || ''}</textarea>
                </div>
                
                <div class="col-12">
                    <label class="kd-label mb-2">Canales de Difusión</label>
                    <div class="d-flex flex-wrap gap-3 p-3 border border-dark rounded bg-black" style="background-color: #050505 !important;">
                        ${canalesStandard.map(c => `
                            <div class="form-check custom-check">
                                <input class="form-check-input check-canal" type="checkbox" value="${c}" id="canal-${c.replace(/\s+/g, '')}" ${canalesActuales.includes(c) ? 'checked' : ''} onchange="validarMaxCanales(this)">
                                <label class="form-check-label" for="canal-${c.replace(/\s+/g, '')}">${c}</label>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="col-12">
                    <label class="kd-label mb-2">Formatos Solicitados</label>
                    <div class="d-flex flex-wrap gap-3 p-3 border border-dark rounded bg-black" style="background-color: #050505 !important;">
                        ${formatosStandard.map(f => `
                            <div class="form-check custom-check">
                                <input class="form-check-input check-formato" type="checkbox" value="${f}" id="formato-${f.replace(/\s+/g, '')}" ${formatosActuales.includes(f) ? 'checked' : ''}>
                                <label class="form-check-label" for="formato-${f.replace(/\s+/g, '')}">${f}</label>
                            </div>
                        `).join('')}
                        <div class="form-check custom-check">
                            <input class="form-check-input check-formato-otros" type="checkbox" value="Otros" id="formato-Otros" onchange="document.getElementById('container-otros-formatos').classList.toggle('d-none', !this.checked)">
                            <label class="form-check-label" for="formato-Otros">Otros</label>
                        </div>
                        <div id="container-otros-formatos" class="w-100 mt-2 ${tieneOtrosFormatos ? '' : 'd-none'}">
                            <input type="text" id="edit-pro-otros-formatos" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Especifique otros formatos separados por coma..." value="${formatosOtrosValues.join(', ')}">
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="alert alert-dark border-warning" style="background: rgba(245, 196, 0, 0.05); color: #F5C400; font-size: 14px; padding: 20px; border-left: 5px solid #F5C400;">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 18px;"></i> 
                        <strong>AVISO DE SEGURIDAD:</strong> Los archivos originales, la fecha de entrega y el URL proporcionado por el cliente no pueden ser modificados.
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Valida que no se seleccionen más de 3 canales
 */
window.validarMaxCanales = function (checkbox) {
    const seleccionados = document.querySelectorAll('.check-canal:checked');
    if (seleccionados.length > 3) {
        checkbox.checked = false;
        Swal.fire({
            icon: 'warning',
            title: 'Límite alcanzado',
            text: 'Solo puedes seleccionar un máximo de 3 canales de difusión.',
            background: '#161616',
            color: '#fff',
            confirmButtonColor: '#f5c400',
            timer: 2000,
            showConfirmButton: false
        });
    }
}

/**
 * Guarda los cambios desde el panel de en proceso
 */
function guardarEdicionRequerimientoEnProceso() {
    const req = window.requerimientoActualEnProceso;
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = 'GUARDANDO...';

    const formData = new FormData();
    formData.append('idrequerimiento', req.idrequerimiento || req.id); // Asegurar ID correcto
    formData.append('idservicio', document.getElementById('edit-pro-servicio').value);
    formData.append('tipo_requerimiento', document.getElementById('edit-pro-tipo-req').value);
    formData.append('titulo', document.getElementById('edit-pro-titulo').value);
    formData.append('descripcion', document.getElementById('edit-pro-descripcion').value);
    formData.append('objetivo_comunicacion', document.getElementById('edit-pro-objetivo').value);
    formData.append('publico_objetivo', document.getElementById('edit-pro-publico').value);

    // Usar valores originales ya que no se editan
    formData.append('fecharequerida', req.fecharequerida ? req.fecharequerida.split(' ')[0] : '');
    formData.append('url_subida', req.url_subida || '');

    // Canales seleccionados (Máximo 3)
    let canales = Array.from(document.querySelectorAll('.check-canal:checked')).map(c => c.value);
    formData.append('canales_difusion', canales.join(', '));

    // Formatos seleccionados
    let formatos = Array.from(document.querySelectorAll('.check-formato:checked')).map(f => f.value);
    const checkOtrosFormatos = document.querySelector('.check-formato-otros');
    if (checkOtrosFormatos && checkOtrosFormatos.checked) {
        const otrosVal = document.getElementById('edit-pro-otros-formatos').value;
        const otrosArray = otrosVal.split(',').map(s => s.trim()).filter(s => s !== '');
        otrosArray.forEach(val => {
            if (!formatos.includes(val)) formatos.push(val);
        });
    }
    formData.append('formatos_solicitados', formatos.join(', '));

    // Envío sin archivos (Deshabilitado por seguridad)

    fetch(`${window.base_url}responsable/pedidos/actualizar`, {
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
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
                // Recargar detalles
                verDetalleTarea(req.idatencion || req.id);
                // Recargar listas de fondo
                if (typeof cargarTareasEmpleado === 'function') cargarTareasEmpleado(req.idempleado);
                if (typeof listarPedidosEnProceso === 'function') listarPedidosEnProceso();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, background: '#161616', color: '#fff', allowOutsideClick: false, allowEscapeKey: false });
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', background: '#161616', color: '#fff', allowOutsideClick: false, allowEscapeKey: false });
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
}

/**
 * Función para iniciar oficialmente el trabajo en un requerimiento
 */
function iniciarTrabajo(idAtencion) {
    Swal.fire({
        title: '¿Iniciar trabajo?',
        text: "Se registrará la fecha y hora oficial de inicio para este requerimiento.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f5c400',
        cancelButtonColor: '#333',
        confirmButtonText: 'Sí, ¡empezar!',
        cancelButtonText: 'Cancelar',
        background: '#161616',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${window.base_url}responsable/pedido-iniciar/${idAtencion}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Trabajo Iniciado!',
                            text: data.message,
                            background: '#161616',
                            color: '#fff',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        // Recargar tareas para actualizar la UI (quitar botón iniciar, poner entregar)
                        cargarTareasEmpleado(window.currentUserId);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            background: '#161616',
                            color: '#fff'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo conectar con el servidor',
                        background: '#161616',
                        color: '#fff'
                    });
                });
        }
    });
}