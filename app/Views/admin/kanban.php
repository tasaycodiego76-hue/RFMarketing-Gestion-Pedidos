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

                            <?php if (($p['num_modificaciones'] ?? 0) > 0): ?>
                                <div class="kb-returned-banner">
                                    <i class="bi bi-arrow-counterclockwise"></i> CORRECCIÓN SOLICITADA
                                </div>
                            <?php endif ?>

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
                                        <?php 
                                            $nombreComp = $p['empleado_nombre'] . ' ' . ($p['empleado_apellidos'] ?? '');
                                            $empIni = mb_strtoupper(mb_substr($p['empleado_nombre'], 0, 1) . (mb_substr($p['empleado_apellidos'] ?? '', 0, 1))); 
                                            $enDesarrolloReal = (!empty($p['fechainicio']) && $estado === 'en_proceso');
                                        ?>
                                        <div class="kb-card-user <?= $enDesarrolloReal ? 'en-desarrollo' : '' ?>">
                                            <div class="kb-user-avatar-wrapper">
                                                <span class="kb-user-avatar" title="<?= esc($nombreComp) ?>"><?= $empIni ?></span>
                                            </div>
                                            <div class="kb-user-info-col">
                                                <?php if ($enDesarrolloReal): ?>
                                                    <span class="kb-badge-developing">
                                                        <span class="kb-dot-pulse"></span> TRABAJANDO
                                                    </span>
                                                <?php endif ?>
                                                <span class="kb-user-name">
                                                    <?php 
                                                        $primerApellido = explode(' ', trim($p['empleado_apellidos'] ?? ''))[0];
                                                        echo esc($p['empleado_nombre'] . ' ' . $primerApellido);
                                                    ?>
                                                </span>
                                            </div>
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

<!-- ═══ TEMPLATE: DETALLE DE KANBAN ═══ -->
<template id="template-detalle-kanban">
    <div class="exp-container">
        <!-- HEADER SECCIÓN -->
        <div class="exp-header-layout" style="padding: 40px 30px 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
            <div style="flex: 1; min-width: 0;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <span class="tpl-status-pill"></span>
                    <span style="color:#444; font-size:11px; font-weight:800;">ID: <span class="tpl-id"></span></span>
                </div>
                <h2 class="tpl-titulo" style="font-family:'Bebas Neue'; font-size:48px; color:#fff; letter-spacing:1px; margin:0; line-height:1.1; word-wrap:break-word; overflow-wrap:break-word;"></h2>
                <div style="margin-top:15px; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                    <span style="color:#F5C400; font-weight:800; font-size:14px;"><i class="bi bi-building"></i> <span class="tpl-empresa"></span></span>
                    <span style="color:#222;">|</span>
                    <span style="color:#888; font-size:13px; font-weight:600;">ÁREA: <span class="tpl-area"></span></span>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div class="tpl-servicio" style="font-family:'Bebas Neue'; font-size:24px; color:#F5C400;"></div>
                <div style="color:#444; font-size:10px; font-weight:800; letter-spacing:1px; margin-top:5px;">ATENCIÓN #<span class="tpl-idatencion"></span></div>
            </div>
        </div>

        <!-- STEPPER -->
        <div class="tpl-stepper-container"></div>

        <div class="exp-grid">
            <!-- COLUMNA PRINCIPAL -->
            <div class="exp-main-col">
                
                <!-- Descripción -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-file-text"></i> <span>DESCRIPCIÓN DEL REQUERIMIENTO</span></div>
                    <div class="exp-card-body">
                        <div class="data-value tpl-descripcion" style="white-space:pre-wrap; font-size:13px; color:#ccc;"></div>
                    </div>
                </div>

                <!-- Estrategia -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-compass"></i> <span>ESTRATEGIA DE COMUNICACIÓN</span></div>
                    <div class="exp-card-body">
                        <div class="data-row">
                            <div class="data-box">
                                <span class="data-label-large">Objetivo Principal</span>
                                <div class="data-value tpl-objetivo"></div>
                            </div>
                            <div class="data-box">
                                <span class="data-label-large">Público Objetivo</span>
                                <div class="data-value tpl-publico"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canales y Formatos -->
                <div class="data-row">
                    <div class="exp-card" style="margin-bottom:0;">
                        <div class="exp-card-header"><i class="bi bi-broadcast"></i> <span>CANALES</span></div>
                        <div class="exp-card-body tpl-canales"></div>
                    </div>
                    <div class="exp-card" style="margin-bottom:0;">
                        <div class="exp-card-header"><i class="bi bi-layers"></i> <span>FORMATOS</span></div>
                        <div class="exp-card-body tpl-formatos"></div>
                    </div>
                </div>

                <!-- Recursos Cliente -->
                <div class="exp-card" style="margin-top:25px;">
                    <div class="exp-card-header"><i class="bi bi-folder-symlink"></i> <span>RECURSOS DEL CLIENTE</span></div>
                    <div class="exp-card-body tpl-archivos-cliente"></div>
                </div>

                <!-- Recursos Empleado (Entrega) -->
                <div class="tpl-entrega-container"></div>
            </div>

            <!-- SIDEBAR -->
            <div class="exp-sidebar">
                
                <!-- Responsable -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-person-badge"></i> <span>RESPONSABLE</span></div>
                    <div class="exp-card-body tpl-empleado" style="padding:15px;"></div>
                </div>

                <!-- Cronología -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-calendar3"></i> <span>CRONOLOGÍA</span></div>
                    <div class="exp-card-body">
                        <div style="display:flex; flex-direction:column; gap:15px;">
                            <div class="data-box">
                                <span class="data-label-large">Fecha de Solicitud</span>
                                <div class="data-value tpl-f-solicitud"></div>
                            </div>
                            <div class="data-box" style="border-color:#F5C40033; background:rgba(245,196,0,0.02);">
                                <span class="data-label" style="color:#F5C400;">Fecha Límite</span>
                                <div class="data-value tpl-f-limite" style="font-weight:900;"></div>
                            </div>
                            <div class="tpl-f-inicio-container"></div>
                            <div class="tpl-f-fin-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Auditoría -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-shield-check"></i> <span>CONTROL</span></div>
                    <div class="exp-card-body">
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:15px; border-radius:12px; border:1px solid #111;">
                            <span class="data-label" style="margin:0;">MODIFICACIONES</span>
                            <span class="tpl-modificaciones" style="background:#F5C400; color:#000; padding:4px 12px; border-radius:8px; font-weight:900; font-size:14px;"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- FOOTER ACCIONES -->
        <div style="margin-top:30px; padding:30px; border-top:1px solid #151515; display:flex; justify-content:center;">
            <button class="btn" data-dismiss="modal" style="background:#111; border:1px solid #222; font-family:'Bebas Neue'; font-size:20px; letter-spacing:2px; padding:12px 60px; border-radius:12px; color:#F5C400; transition:all 0.3s;">
                CERRAR EXPEDIENTE DIGITAL
            </button>
        </div>
    </div>
</template>

<?= $this->endSection() ?>