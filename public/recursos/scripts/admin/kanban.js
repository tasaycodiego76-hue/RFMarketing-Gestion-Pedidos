
// ═══ GLOBAL CONFIG ═══
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

// ═══ ASIGNAR ÁREA (ADMIN) ═══
async function abrirModalAsignar(idAtencion) {
    const inputId = document.getElementById('asignar-idatencion');
    const select = document.getElementById('asignar-empleado');
    inputId.value = idAtencion;
    select.innerHTML = '<option value="">Cargando...</option>';
    try {
        const response = await fetch(BASE_URL + 'admin/kanban/areas');
        const data = await response.json();
        select.innerHTML = '<option value="">-- Seleccionar área --</option>';
        data.forEach(area => {
            select.innerHTML += `<option value="${area.id}">${area.nombre}</option>`;
        });
    } catch (e) {
        select.innerHTML = '<option value="">Error al cargar áreas</option>';
    }
    $('#modalAsignar').modal('show');
}

async function confirmarAsignacion() {
    const idAtencion = document.getElementById('asignar-idatencion').value;
    const idArea = document.getElementById('asignar-empleado').value;
    if (!idArea) return alert('Selecciona un área');
    const res = await fetch(BASE_URL + 'admin/kanban/asignarArea', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: idAtencion, idareaagencia: idArea })
    });
    const data = await res.json();
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ═══ ASIGNAR EMPLEADO (RESPONSABLE/ADMIN) ═══
async function abrirModalAsignarEmpleado(idAtencion, idArea) {
    // Reutilizamos modalAsignar pero cambiamos textos
    const inputId = document.getElementById('asignar-idatencion');
    const select = document.getElementById('asignar-empleado');
    const titulo = document.querySelector('.kb-modal-title-asignar');
    const btn = document.querySelector('.kb-btn-confirmar-asignar');

    titulo.textContent = 'Asignar Empleado Responsable';
    btn.textContent = 'Asignar al Proyecto';
    btn.setAttribute('onclick', 'confirmarAsignacionEmpleado()');

    inputId.value = idAtencion;
    select.innerHTML = '<option value="">Cargando empleados...</option>';
    
    try {
        const response = await fetch(BASE_URL + 'admin/kanban/empleados/' + idArea);
        const data = await response.json();
        if(data.length === 0) {
            select.innerHTML = '<option value="">No hay empleados disponibles en esta área</option>';
        } else {
            select.innerHTML = '<option value="">-- Seleccionar empleado --</option>';
            data.forEach(u => {
                select.innerHTML += `<option value="${u.id}">${u.nombre} ${u.apellidos}</option>`;
            });
        }
    } catch (e) {
        select.innerHTML = '<option value="">Error carga</option>';
    }
    $('#modalAsignar').modal('show');
}

async function confirmarAsignacionEmpleado() {
    const idAtencion = document.getElementById('asignar-idatencion').value;
    const idEmpleado = document.getElementById('asignar-empleado').value;
    if (!idEmpleado) return alert('Selecciona un empleado');

    const res = await fetch(BASE_URL + 'admin/kanban/asignarEmpleado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: idAtencion, idempleado: idEmpleado })
    });
    const data = await res.json();
    if (data.status === 'success') location.reload();
    else alert(data.msg);
}

// ═══ VER DETALLE FULL FORM ═══
async function verDetalle(idAtencion) {
    const cuerpo = document.getElementById('detalle-cuerpo');
    const titulo = document.getElementById('detalle-titulo');
    cuerpo.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-warning"></div><p class="mt-2" style="font-size:11px;">PROCESANDO EXPEDIENTE...</p></div>';
    $('#modalDetalle').modal('show');

    try {
        const response = await fetch(BASE_URL + 'admin/kanban/detalle/' + idAtencion);
        const res = await response.json();
        if (res.status !== 'success') return cuerpo.innerHTML = 'Error';

        const d = res.data;
        const archivos = res.archivos;

        let arcHtml = '';
        if(archivos && archivos.length > 0) {
            arcHtml = '<div class="row mt-2">';
            archivos.forEach(a => {
                const ext = a.nombre.split('.').pop().toLowerCase();
                const icon = KB_ICONS[ext] || KB_ICONS.default;
                arcHtml += `
                    <div class="col-md-6 mb-2">
                        <a href="${BASE_URL}/cliente/archivos/${a.ruta.split('/').pop()}" target="_blank" class="d-flex align-items-center p-2" 
                           style="background:#0a0a0a; border:1px solid #222; border-radius:6px; color:#aaa; text-decoration:none; font-size:11px;">
                            <i class="bi ${icon} mr-2" style="color:#eab308; font-size:14px;"></i>
                            <span class="text-truncate">${a.nombre}</span>
                        </a>
                    </div>`;
            });
            arcHtml += '</div>';
        } else {
            arcHtml = '<p style="color:#444; font-size:11px; font-style:italic;">No se adjuntaron archivos.</p>';
        }

        const html = `
            <div class="kb-full-detail">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <h6 class="det-sec-title">RESUMEN DEL FORMULARIO CLIENTE</h6>
                        <div class="det-panel">
                            <div class="row">
                                <div class="col-md-4"><small>CLIENTE</small><p>${d.nombreempresa}</p></div>
                                <div class="col-md-4"><small>SERVICIO</small><p style="color:#eab308;">${d.servicio}</p></div>
                                <div class="col-md-4"><small>TIPO REQ.</small><p>${d.tipo_requerimiento || 'ESTÁNDAR'}</p></div>
                            </div>
                            <hr style="border-color:#1a1a1a; margin:10px 0;">
                            <small>SINOPSIS / DESCRIPCIÓN</small>
                            <p style="font-size:12px; color:#bbb; line-height:1.6; margin:5px 0 0;">${d.descripcion || 'Sin descripción'}</p>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <h6 class="det-sec-title">OBJETIVOS Y AUDIENCIA</h6>
                        <div class="det-panel">
                            <small>OBJETIVO PRINCIPAL</small>
                            <p>${d.objetivo_comunicacion || '---'}</p>
                            <div class="mt-2">
                                <small>PÚBLICO TARGET</small>
                                <p>${d.publico_objetivo || '---'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <h6 class="det-sec-title">FORMATOS Y DIFUSIÓN</h6>
                        <div class="det-panel">
                            <small>CANALES DE SALIDA</small>
                            <p>${parseJsonList(d.canales_difusion)}</p>
                            <div class="mt-2">
                                <small>FORMATOS SOLICITADOS</small>
                                <p>${parseJsonList(d.formatos_solicitados)}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-4">
                        <h6 class="det-sec-title">ARCHIVOS DEL REQUERIMIENTO</h6>
                        <div class="det-panel">${arcHtml}</div>
                    </div>

                    <div class="col-md-12">
                        <h6 class="det-sec-title">GESTIÓN ADMINISTRATIVA</h6>
                        <div class="det-panel" style="border-left: 2px solid #eab308;">
                             <div class="row">
                                <div class="col-md-6">
                                    <small>ESTADO ACTUAL</small>
                                    <p class="text-uppercase">${d.estado}</p>
                                </div>
                                <div class="col-md-6">
                                    <small>PRIORIDAD ADMIN</small>
                                    <div class="d-flex gap-2">
                                        <select id="detalle-prioridad" class="form-control form-control-sm det-select">
                                            <option value="Baja"  ${d.prioridad_admin === 'Baja'  ? 'selected' : ''}>Baja</option>
                                            <option value="Media" ${d.prioridad_admin === 'Media' ? 'selected' : ''}>Media</option>
                                            <option value="Alta"  ${d.prioridad_admin === 'Alta'  ? 'selected' : ''}>Alta</option>
                                        </select>
                                        <button class="btn btn-sm btn-outline-warning" onclick="cambiarPrioridad(${d.id})">OK</button>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        titulo.innerText = 'EXPEDIENTE REQ-' + d.idrequerimiento;
        cuerpo.innerHTML = html;
    } catch (e) {
        cuerpo.innerHTML = 'Error fatal al cargar datos.';
    }
}

function parseJsonList(jsonStr) {
    if(!jsonStr) return '---';
    try {
        const list = JSON.parse(jsonStr);
        return Array.isArray(list) ? list.join(', ') : jsonStr;
    } catch (e) { return jsonStr; }
}

async function cambiarPrioridad(id) {
    const p = document.getElementById('detalle-prioridad').value;
    await fetch(BASE_URL + 'admin/kanban/cambiarPrioridad', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: id, prioridad: p })
    });
    location.reload();
}

async function cambiarEstado(id, est, acc) {
    if(!confirm(`Confirmar acción: ${acc}`)) return;
    const res = await fetch(BASE_URL + 'admin/kanban/cambiarEstado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: id, estado: est, accion: acc })
    });
    location.reload();
}

async function cancelarAtencion(id) {
    const m = prompt('Motivo de cancelación:');
    if(!m) return;
    await fetch(BASE_URL + 'admin/kanban/cancelar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: id, motivo: m })
    });
    location.reload();
}
