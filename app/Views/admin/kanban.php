<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/kanban.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!-- Declarando variables AQUÍ -->
<script>
    const ADMIN_ID = "<?= session()->get('id') ?? 1 ?>";
    const ADMIN_ROL = "<?= session()->get('rol') ?? 'admin' ?>";
    const AREA_ACTUAL = <?= $idAreaAgencia ?>;
    const AREA_NOMBRE = "<?= esc($areaActual['nombre'] ?? '') ?>";
</script>
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
                RUC <span style="color:#fff;"><?= esc($empresa['ruc'] ?? '—') ?></span>
                <?php if (!empty($empresa['correo'])): ?> · <span style="color:#fff;"><?= esc($empresa['correo']) ?></span><?php endif ?>
                <?php if (!empty($empresa['telefono'])): ?> · <span style="color:#fff;"><?= esc($empresa['telefono']) ?></span><?php endif ?>
            </div>
        </div>
    </div>
    <div class="kb-head-stats">
        <div class="kb-stat"><span class="st-morado"><?= $stats['por_aprobar'] ?? 0 ?></span><small>REVISAR</small>
        </div>
        <div class="kb-stat"><span class="st-amarillo"><?= $stats['activos'] ?? 0 ?></span><small>ACTIVOS</small></div>
        <div class="kb-stat"><span class="st-naranja"><?= $stats['en_revision'] ?? 0 ?></span><small>EN REVISIÓN</small>
        </div>
        <div class="kb-stat"><span class="st-verde"><?= $stats['completados'] ?? 0 ?></span><small>COMPLETADOS</small>
        </div>
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
        <div class="kb-col" data-estado="<?= $estado ?>">
            <div class="kb-col-head">
                <span class="kb-col-title"><?= $col['label'] ?></span>
                <span class="kb-col-count"><?= count($col['items']) ?></span>
            </div>

            <div class="kb-col-body" data-estado="<?= $estado ?>">
                <?php if (empty($col['items'])): ?>
                    <div class="kb-empty">
                        <i class="bi bi-inbox"></i>
                        <span>No hay requerimientos en esta etapa</span>
                    </div>
                <?php else: ?>
                    <?php
                    usort($col['items'], function ($a, $b) {
                        $prios = ['Alta' => 1, 'Media' => 2, 'Baja' => 3];
                        $vA = $prios[$a['prioridad_admin'] ?? ($a['prioridad'] ?? 'Media')] ?? 2;
                        $vB = $prios[$b['prioridad_admin'] ?? ($b['prioridad'] ?? 'Media')] ?? 2;
                        return $vA <=> $vB;
                    });
                    ?>
                    <?php foreach ($col['items'] as $p): ?>
                        <div class="kb-card <?= ($estado === 'pendiente_sin_asignar') ? 'js-draggable' : '' ?>" data-id="<?= $p['id'] ?>">
                            <div class="kb-card-top">
                                <div class="kb-card-info">
                                    <span class="kb-card-empresa"><?= esc($p['nombreempresa']) ?></span>
                                    <span class="kb-card-title"><?= esc($p['titulo'] ?? 'Sin título') ?></span>
                                </div>
                                <div class="kb-card-id">#<?= $p['id'] ?></div>
                            </div>

                            <div class="kb-card-tags">
                                <span class="kb-tag-servicio">
                                    <i class="bi bi-tag-fill"></i> <?= esc($p['servicio'] ?? 'General') ?>
                                </span>
                                <?php
                                $prio = $p['prioridad_admin'] ?? ($p['prioridad'] ?? 'Media');
                                $prioCls = strtolower($prio);
                                ?>
                                <span class="kb-tag-pri kb-pri-<?= $prioCls ?>">
                                    <i class="bi bi-flag-fill"></i> <?= $prio ?>
                                </span>
                            </div>

                            <div class="kb-card-progress">
                                <?php 
                                    $prog = 0;
                                    if($estado === 'pendiente_sin_asignar') $prog = 25;
                                    elseif($estado === 'en_proceso') $prog = 50;
                                    elseif($estado === 'en_revision') $prog = 75;
                                    elseif($estado === 'finalizado') $prog = 100;
                                ?>
                                <div class="kb-progress-bar">
                                    <div class="kb-progress-fill" style="width: <?= $prog ?>%; background: <?= 
                                        ($estado === 'finalizado') ? '#10b981' : 
                                        (($estado === 'en_revision') ? '#f97316' : 
                                        (($estado === 'en_proceso') ? '#a855f7' : '#F5C400')) ?>"></div>
                                </div>
                            </div>

                            <div class="kb-card-footer">
                                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                                    <?php if ($p['idempleado']): ?>
                                        <div class="kb-card-user">
                                            <?php 
                                                $nombreComp = $p['empleado_nombre'] . ' ' . ($p['empleado_apellidos'] ?? '');
                                                $empIni = mb_strtoupper(mb_substr($p['empleado_nombre'], 0, 1) . (mb_substr($p['empleado_apellidos'] ?? '', 0, 1))); 
                                            ?>
                                            <div class="kb-user-avatar-wrapper">
                                                <span class="kb-user-avatar" title="<?= esc($nombreComp) ?>"><?= $empIni ?></span>
                                            </div>
                                            <span class="kb-user-name"><?= esc($p['empleado_nombre']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="kb-card-user">
                                            <div class="kb-user-avatar-wrapper sin-asignar">
                                                <span class="kb-user-avatar"><i class="bi bi-person-dash"></i></span>
                                            </div>
                                            <span class="kb-user-name sin-asignar-text">Pendiente</span>
                                        </div>
                                    <?php endif ?>

                                    <?php if (!empty($p['fecharequerida'])): ?>
                                        <div class="kb-card-fecha" style="margin-bottom:0; font-size:10px;">
                                            <i class="bi bi-clock"></i> Límite: <?= date('d/m', strtotime($p['fecharequerida'])) ?>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <div class="kb-card-actions">
                                <?php if ($estado === 'pendiente_sin_asignar'): ?>
                                    <button class="kb-btn kb-btn-primary" onclick="verDetalle(<?= $p['id'] ?>)">
                                        <i class="bi bi-search"></i> REVISAR
                                    </button>
                                <?php elseif ($estado === 'en_proceso'): ?>
                                    <button class="kb-btn kb-btn-secondary" onclick="verDetalle(<?= $p['id'] ?>)">
                                        <i class="bi bi-info-circle"></i> DETALLES
                                    </button>
                                <?php elseif ($estado === 'en_revision'): ?>
                                    <div class="kb-action-group">
                                        <button class="kb-btn kb-btn-view" onclick="verDetalle(<?= $p['id'] ?>)" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="kb-btn kb-btn-danger" onclick="solicitarRetroalimentacion(<?= $p['id'] ?>)" title="Regresar">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        <button class="kb-btn kb-btn-success" onclick="cambiarEstado(<?= $p['id'] ?>, 'finalizado', 'Aprobar')" title="Aprobar">
                                            <i class="bi bi-check-lg"></i> OK
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <button class="kb-btn kb-btn-info" onclick="verDetalle(<?= $p['id'] ?>)">
                                        <i class="bi bi-folder-check"></i> EXPEDIENTE
                                    </button>
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
                <h6 class="modal-title kb-modal-title-asignar">Asignar Área de Agencia</h6>
                <button type="button" class="close kb-modal-close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="asignar-idatencion">
                <label class="kb-modal-label">Seleccionar área de agencia:</label>
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

<?= $this->endSection() ?>