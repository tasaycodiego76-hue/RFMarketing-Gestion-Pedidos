<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/kanban.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<?php $inicial = mb_strtoupper(mb_substr($empresa['nombreempresa'], 0, 1)); ?>

<!-- ═══ CABECERA EMPRESA ═══ -->
<div class="kb-head">
    <div class="kb-head-left">
        <div class="kb-emp-avatar"><?= $inicial ?></div>
        <div>
            <div class="kb-emp-nombre"><?= esc(strtoupper($empresa['nombreempresa'])) ?></div>
            <div class="kb-emp-meta">
                RUC <?= esc($empresa['ruc'] ?? '—') ?>
                <?php if (!empty($empresa['correo'])): ?> · <?= esc($empresa['correo']) ?><?php endif ?>
                <?php if (!empty($empresa['telefono'])): ?> · <?= esc($empresa['telefono']) ?><?php endif ?>
            </div>
        </div>
    </div>
    <div class="kb-head-stats">
        <div class="kb-stat"><span class="st-amarillo"><?= $stats['activos'] ?? 0 ?></span><small>ACTIVOS</small></div>
        <div class="kb-stat"><span class="st-morado"><?= $stats['por_aprobar'] ?? 0 ?></span><small>POR APROBAR</small></div>
        <div class="kb-stat"><span class="st-verde"><?= $stats['completados'] ?? 0 ?></span><small>COMPLETADOS</small></div>
    </div>
</div>

<!-- ═══ TABS ÁREAS AGENCIA ═══ -->
<div class="kb-areas">
    <?php foreach ($areasAgencia as $a): ?>
        <a href="<?= site_url('admin/kanban/' . $idEmpresa . '/' . $a['id']) ?>"
           class="kb-area-tab <?= $a['id'] == $areaActual['id'] ? 'activo' : '' ?>">
            <?= esc($a['nombre']) ?>
        </a>
    <?php endforeach ?>
</div>

<!-- ═══ TABLERO KANBAN ═══ -->
<div class="kb-board">
    <?php foreach ($columnas as $estado => $col): ?>
    <div class="kb-col">
        <div class="kb-col-head" style="border-top: 3px solid <?= $col['color'] ?>">
            <span class="kb-col-title" style="color: <?= $col['color'] ?>"><?= $col['label'] ?></span>
            <span class="kb-col-count"><?= count($col['items']) ?></span>
        </div>

        <div class="kb-col-body">
            <?php if (empty($col['items'])): ?>
                <div class="kb-empty">Sin requerimientos</div>
            <?php else: ?>
                <?php foreach ($col['items'] as $p): ?>
                <div class="kb-card" data-id="<?= $p['id'] ?>">
                    <div class="kb-card-top">
                        <span class="kb-card-title"><?= esc($p['titulo'] ?? 'Sin título') ?></span>
                        <span class="kb-badge kb-badge-<?= $estado ?>">
                            <?= $estado === 'pendiente_sin_asignar' ? 'Nuevo' : ($estado === 'en_proceso' ? 'En curso' : ($estado === 'en_revision' ? 'Revisión' : 'Entregado')) ?>
                        </span>
                    </div>

                    <div class="kb-card-empresa">Cliente: <?= esc($p['nombreempresa']) ?></div>

                    <div class="kb-card-tags">
                        <span class="kb-tag-servicio"><?= esc($p['servicio'] ?? 'Sin servicio') ?></span>
                        <?php $pri = strtolower($p['prioridad'] ?? 'media'); ?>
                        <span class="kb-tag-pri kb-pri-<?= $pri ?>">
                            <?= $pri === 'alta' ? '▲ Alta' : ($pri === 'baja' ? '▼ Baja' : '● Media') ?>
                        </span>
                    </div>

                    <?php if (!empty($p['fechafin'])): ?>
                        <div class="kb-card-fecha">Entrega: <?= date('d M Y', strtotime($p['fechafin'])) ?></div>
                    <?php elseif (!empty($p['fecharequerida'])): ?>
                        <div class="kb-card-fecha">Requerida: <?= date('d M Y', strtotime($p['fecharequerida'])) ?></div>
                    <?php endif ?>

                    <div class="kb-card-footer">
                        <?php if ($p['idempleado']): ?>
                            <div class="kb-card-user">
                                <span class="kb-user-avatar"><?= mb_strtoupper(mb_substr($p['empleado_nombre'], 0, 1) . mb_substr($p['empleado_apellidos'], 0, 1)) ?></span>
                                <span class="kb-user-name"><?= esc($p['empleado_nombre'] . ' ' . $p['empleado_apellidos']) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="kb-card-user">
                                <span class="kb-user-avatar sin-asignar">?</span>
                                <span class="kb-user-name sin-asignar-text">Sin asignar</span>
                            </div>
                        <?php endif ?>
                    </div>

                    <div class="kb-card-actions">
                        <?php if ($estado === 'pendiente_sin_asignar'): ?>
                            <button class="kb-btn kb-btn-asignar" onclick="abrirModalAsignar(<?= $p['id'] ?>)">Asignar</button>
                            <button class="kb-btn kb-btn-ver" onclick="verDetalle(<?= $p['id'] ?>)">Ver</button>
                            <button class="kb-btn kb-btn-cancel" onclick="cancelarAtencion(<?= $p['id'] ?>)">✕</button>
                        <?php elseif ($estado === 'en_proceso'): ?>
                            <button class="kb-btn kb-btn-detalle" onclick="verDetalle(<?= $p['id'] ?>)">Ver detalle</button>
                        <?php elseif ($estado === 'en_revision'): ?>
                            <button class="kb-btn kb-btn-aprobar" onclick="cambiarEstado(<?= $p['id'] ?>, 'finalizado', 'Aprobado por admin')">✓ Aprobar</button>
                            <button class="kb-btn kb-btn-regresar" onclick="cambiarEstado(<?= $p['id'] ?>, 'en_proceso', 'Regresado a proceso')">↶ Regresar</button>
                            <button class="kb-btn kb-btn-ver-sm" onclick="verDetalle(<?= $p['id'] ?>)">Ver</button>
                        <?php else: ?>
                            <button class="kb-btn kb-btn-entregado" onclick="verDetalle(<?= $p['id'] ?>)">Ver entrega</button>
                        <?php endif ?>
                    </div>
                </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>
    <?php endforeach ?>
</div>

<!-- ═══ MODAL ASIGNAR EMPLEADO (Bootstrap 4) ═══ -->
<div class="modal fade" id="modalAsignar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content kb-modal">
            <div class="modal-header kb-modal-header">
                <h6 class="modal-title kb-modal-title-asignar">Asignar Empleado</h6>
                <button type="button" class="close kb-modal-close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="asignar-idatencion">
                <label class="kb-modal-label">Seleccionar empleado del área:</label>
                <select id="asignar-empleado" class="form-control kb-modal-select">
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div class="modal-footer kb-modal-footer">
                <button class="btn kb-btn-confirmar-asignar" onclick="confirmarAsignacion()">Asignar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL VER DETALLE (Bootstrap 4) ═══ -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content kb-modal">
            <div class="modal-header kb-modal-header">
                <h6 class="modal-title" id="detalle-titulo">Detalle</h6>
                <button type="button" class="close kb-modal-close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalle-cuerpo">
                Cargando...
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const AREA_ACTUAL = <?= $idAreaAgencia ?>;

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
</script>
<?= $this->endSection() ?>
