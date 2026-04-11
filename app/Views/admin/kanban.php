<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/kanban.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>const AREA_ACTUAL = <?= $idAreaAgencia ?>;</script>
<script src="<?= base_url('recursos/scripts/admin/kanban.js') ?>"></script>
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
