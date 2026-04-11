// ═══ ASIGNAR ═══
function abrirModalAsignar(idAtencion) {
    document.getElementById('asignar-idatencion').value = idAtencion;
    const select = document.getElementById('asignar-empleado');
    select.innerHTML = '<option value="">Cargando...</option>';

    fetch(BASE_URL + 'admin/kanban/empleados/' + AREA_ACTUAL)
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                select.innerHTML = '<option value="">No hay empleados en esta área</option>';
            } else {
                select.innerHTML = '<option value="">-- Seleccionar --</option>';
                data.forEach(e => {
                    select.innerHTML += '<option value="' + e.id + '">' + e.nombre + ' ' + e.apellidos + '</option>';
                });
            }
        });

    $('#modalAsignar').modal('show');
}

function confirmarAsignacion() {
    const idAtencion = document.getElementById('asignar-idatencion').value;
    const idEmpleado = document.getElementById('asignar-empleado').value;

    if (!idEmpleado) { alert('Selecciona un empleado'); return; }

    fetch(BASE_URL + 'admin/kanban/asignar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: idAtencion, idempleado: idEmpleado })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            $('#modalAsignar').modal('hide');
            location.reload();
        } else {
            alert(res.msg);
        }
    });
}

// ═══ CAMBIAR ESTADO ═══
function cambiarEstado(idAtencion, nuevoEstado, accion) {
    if (!confirm('¿Confirmar acción: ' + accion + '?')) return;

    fetch(BASE_URL + 'admin/kanban/cambiarEstado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: idAtencion, estado: nuevoEstado, accion: accion })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            location.reload();
        } else {
            alert(res.msg);
        }
    });
}

// ═══ CANCELAR ═══
function cancelarAtencion(idAtencion) {
    const motivo = prompt('Motivo de cancelación:');
    if (motivo === null) return;

    fetch(BASE_URL + 'admin/kanban/cancelar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: idAtencion, motivo: motivo })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            location.reload();
        } else {
            alert(res.msg);
        }
    });
}

// ═══ VER DETALLE ═══
function verDetalle(idAtencion) {
    document.getElementById('detalle-cuerpo').innerHTML = 'Cargando...';

    fetch(BASE_URL + 'admin/kanban/detalle/' + idAtencion)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') { alert(res.msg); return; }
            const d = res.data;
            let html = '<div class="kb-detalle-grid">'
                + '<div><strong>Título</strong><br>' + d.titulo + '</div>'
                + '<div><strong>Servicio</strong><br>' + d.servicio + '</div>'
                + '<div><strong>Estado</strong><br>' + d.estado + '</div>'
                + '<div><strong>Prioridad</strong><br>' + d.prioridad + '</div>'
                + '<div><strong>Empresa</strong><br>' + d.nombreempresa + '</div>'
                + '<div><strong>Empleado</strong><br>' + (d.empleado_nombre ? d.empleado_nombre + ' ' + d.empleado_apellidos : 'Sin asignar') + '</div>'
                + '<div><strong>Fecha requerida</strong><br>' + (d.fecharequerida || '—') + '</div>'
                + '<div><strong>Fecha fin</strong><br>' + (d.fechafin || '—') + '</div>'
                + '</div>'
                + '<hr class="kb-detalle-hr">'
                + '<div><strong>Descripción</strong><br>' + (d.descripcion || '—') + '</div>'
                + '<div class="mt-2"><strong>Objetivo</strong><br>' + (d.objetivo_comunicacion || '—') + '</div>'
                + '<div class="mt-2"><strong>Canales</strong><br>' + (d.canales_difusion || '—') + '</div>'
                + '<div class="mt-2"><strong>Público objetivo</strong><br>' + (d.publico_objetivo || '—') + '</div>'
                + '<div class="mt-2"><strong>Formatos</strong><br>' + (d.formatos_solicitados || '—') + '</div>';

            if (res.archivos && res.archivos.length > 0) {
                html += '<hr class="kb-detalle-hr"><strong>Archivos adjuntos</strong><ul class="kb-detalle-archivos">';
                res.archivos.forEach(function(a) {
                    var nombre = a.ruta.split('/').pop();
                    html += '<li><a href="' + BASE_URL + 'cliente/requerimiento/archivo/' + nombre + '" target="_blank">' + a.nombre + '</a> (' + (a.tamano / 1024).toFixed(1) + ' KB)</li>';
                });
                html += '</ul>';
            }

            document.getElementById('detalle-titulo').textContent = d.titulo;
            document.getElementById('detalle-cuerpo').innerHTML = html;
        });

    $('#modalDetalle').modal('show');
}