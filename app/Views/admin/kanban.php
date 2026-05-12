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
                <?php if (!empty($empresa['correo'])): ?> · <span
                        style="color:#fff;"><?= esc($empresa['correo']) ?></span><?php endif ?>
                <?php if (!empty($empresa['telefono'])): ?> · <span
                        style="color:#fff;"><?= esc($empresa['telefono']) ?></span><?php endif ?>
            </div>
        </div>
    </div>
    <div class="kb-head-stats">
        <div class="kb-stat"><span class="st-amarillo"><?= $stats['por_aprobar'] ?? 0 ?></span><small>SOLICITUDES</small>
        </div>
        <div class="kb-stat"><span class="st-morado"><?= $stats['activos'] ?? 0 ?></span><small>PROCESO</small></div>
        <div class="kb-stat"><span class="st-naranja"><?= $stats['en_revision'] ?? 0 ?></span><small>REVISIÓN</small>
        </div>
        <div class="kb-stat"><span class="st-verde"><?= $stats['completados'] ?? 0 ?></span><small>COMPLETADOS</small>
        </div>
    </div>

    <!-- ═══ WIDGET DE SATURACIÓN  ═══ -->
    <div class="kb-saturation-widget">
        <div
            style="font-size: 9px; color: #fff; font-weight: 800; text-align: right; line-height: 1.2; letter-spacing: 0.5px;">
            CARGA DE<br>TRABAJO
        </div>
        <div class="kb-saturation-item">
            <?php
            $valHoy = $carga_diaria['hoy'] ?? 0;
            $clHoy = ($valHoy >= 40) ? 'sat-high' : (($valHoy >= 30) ? 'sat-mid' : 'sat-low');
            ?>
            <span class="kb-saturation-val <?= $clHoy ?>"><?= $valHoy ?></span>
            <span class="kb-saturation-label" style="color:#fff;">HOY</span>
        </div>
        <div style="width:1px; height:25px; background:#1a1a1a;"></div>
        <div class="kb-saturation-item">
            <?php
            $valMan = $carga_diaria['manana'] ?? 0;
            $clMan = ($valMan >= 40) ? 'sat-high' : (($valMan >= 30) ? 'sat-mid' : 'sat-low');
            ?>
            <span class="kb-saturation-val <?= $clMan ?>"><?= $valMan ?></span>
            <span class="kb-saturation-label" style="color:#fff;">MAÑANA</span>
        </div>
    </div>

    <!-- ═══ FILTROS RÁPIDOS  ═══ -->
    <div class="kb-quick-filters"
        style="display:flex; align-items:center; gap:8px; margin-left: 25px; border-left: 1px solid #1a1a1a; padding-left: 20px;">
        <button onclick="filterKanban('all')" class="kb-filter-btn active" id="btn-filter-all" >TODO</button>
        <button onclick="filterKanban('hoy')" class="kb-filter-btn" id="btn-filter-hoy">HOY</button>
        <button onclick="filterKanban('manana')" class="kb-filter-btn" id="btn-filter-manana">MAÑANA</button>

    </div>
</div>

<!-- ═══ LEYENDA + ALERTA ATRASADOS ═══ -->
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-bottom:18px; padding:10px 14px; background:#0a0a0a; border:1px solid #151515; border-radius:12px;">
    
    <!-- Leyenda de colores -->
    <div style="display:flex; align-items:center; gap:22px;">
        <span style="display:flex; align-items:center; gap:7px; font-size:12px; font-weight:700; color:#ff4d4d; letter-spacing:0.3px;">
            <span style="width:12px; height:12px; border-radius:4px; background:#ff4d4d; box-shadow:0 0 6px rgba(255,77,77,0.35);"></span>
            Atrasado
        </span>
        <span style="display:flex; align-items:center; gap:7px; font-size:12px; font-weight:700; color:#f97316; letter-spacing:0.3px;">
            <span style="width:12px; height:12px; border-radius:4px; background:#f97316; box-shadow:0 0 6px rgba(249,115,22,0.35);"></span>
            Vence Hoy
        </span>
        <span style="display:flex; align-items:center; gap:7px; font-size:12px; font-weight:700; color:#ffcc00; letter-spacing:0.3px;">
            <span style="width:12px; height:12px; border-radius:4px; background:#ffcc00; box-shadow:0 0 6px rgba(255,204,0,0.35);"></span>
            Vence Mañana
        </span>
        <span style="display:flex; align-items:center; gap:7px; font-size:12px; font-weight:700; color:#10b981; letter-spacing:0.3px;">
            <span style="width:12px; height:12px; border-radius:4px; background:#10b981; box-shadow:0 0 6px rgba(16,185,129,0.35);"></span>
            En Tiempo
        </span>
    </div>

    <!-- Botón Atrasados -->
    <?php if (!empty($atrasados)): ?>
    <button onclick="document.getElementById('modalAtrasados').style.display='flex'"
        style="display:flex; align-items:center; gap:8px; background:rgba(255,77,77,0.1); border:1px solid rgba(255,77,77,0.35); color:#ff4d4d; padding:7px 16px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer; letter-spacing:0.4px; transition:all 0.25s;"
        onmouseover="this.style.background='rgba(255,77,77,0.22)'; this.style.borderColor='#ff4d4d';"
        onmouseout="this.style.background='rgba(255,77,77,0.1)'; this.style.borderColor='rgba(255,77,77,0.35)';">
        <i class="bi bi-exclamation-triangle-fill" style="font-size:13px;"></i>
        VER ATRASADOS (<?= count($atrasados) ?>)
    </button>
    <?php endif ?>
</div>

<!-- ═══ TABS ÁREAS AGENCIA ═══ -->
<div class="kb-areas">
    <?php foreach ($areasAgencia as $area): ?>
        <?php $countNuevasArea = $stats_areas[$area['id']] ?? 0; ?>
        <a href="<?= site_url('admin/kanban/' . $idEmpresa . '/' . $area['id']) ?>"
            class="kb-area-tab <?= $idAreaAgencia == $area['id'] ? 'activo' : '' ?>">
            <?= esc($area['nombre']) ?>
            <?php if ($countNuevasArea > 0): ?>
                <span class="area-badge-notif"><?= $countNuevasArea ?></span>
            <?php endif ?>
        </a>
    <?php endforeach ?>
</div>

<!-- ═══ TABLERO KANBAN ═══ -->
<div class="kb-board">
    <?php foreach ($columnas as $estado => $col): ?>
        <?php
        // --- PROCESAMIENTO DE ITEMS POR COLUMNA ---
        if ($estado === 'finalizado') {
            // Ordenar por fecha de finalización (más reciente primero)
            usort($col['items'], function ($a, $b) {
                $tA = strtotime($a['fechacompletado'] ?? $a['fechacreacion']);
                $tB = strtotime($b['fechacompletado'] ?? $b['fechacreacion']);
                return $tB <=> $tA;
            });
            // Limitamos estrictamente a los últimos 5 para evitar acumulación
            $col['items'] = array_slice($col['items'], 0, 5);
        } else {
            // Orden por PRIORIDAD DEL ADMIN (Alta > Media > Baja), luego por fecha de creación (más antiguo primero)
            usort($col['items'], function ($a, $b) {
                $prios = ['Alta' => 1, 'Media' => 2, 'Baja' => 3];
                $vA = $prios[$a['prioridad_admin'] ?? ($a['prioridad'] ?? 'Media')] ?? 2;
                $vB = $prios[$b['prioridad_admin'] ?? ($b['prioridad'] ?? 'Media')] ?? 2;
                if ($vA !== $vB) return $vA <=> $vB;
                // Si misma prioridad, el más antiguo va primero
                $tA = strtotime($a['fechacreacion'] ?? '2099-01-01');
                $tB = strtotime($b['fechacreacion'] ?? '2099-01-01');
                return $tA <=> $tB;
            });
        }
        $countItems = count($col['items']);
        ?>
        <div class="kb-col" data-estado="<?= $estado ?>">
            <div class="kb-col-head">
                <span class="kb-col-title"><?= $col['label'] ?></span>
                <span class="kb-col-count"><?= $countItems ?></span>
            </div>

            <div class="kb-col-body" data-estado="<?= $estado ?>">
                <?php if (empty($col['items'])): ?>
                    <div class="kb-empty">
                        <i class="bi bi-inbox"></i>
                        <span>No hay requerimientos en esta etapa</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($col['items'] as $p): ?>
                        <?php
                        // Cálculo de SLA para Filtros y Colores
                        $slaCls = 'sla-normal';
                        $slaType = 'tiempo';
                        $slaText = ' • EN TIEMPO';
                        if (!empty($p['fecharequerida'])) {
                            $t_hoy = strtotime(date('Y-m-d'));
                            $t_vence = strtotime(date('Y-m-d', strtotime($p['fecharequerida'])));
                            $diff_dias = ($t_vence - $t_hoy) / 86400;
                            if ($diff_dias < 0) {
                                $slaCls = 'sla-critico';
                                $slaType = 'hoy';
                                $slaText = ' • ATRASADO';
                            } elseif ($diff_dias == 0) {
                                $slaCls = 'sla-urgente';
                                $slaType = 'hoy';
                                $slaText = ' • HOY';
                            } elseif ($diff_dias == 1) {
                                $slaCls = 'sla-advertencia';
                                $slaType = 'manana';
                                $slaText = ' • MAÑANA';
                            }
                        }
                        ?>
                        <div class="kb-card <?= ($estado === 'pendiente_sin_asignar') ? 'js-draggable' : '' ?>"
                            data-id="<?= $p['id'] ?>" data-sla="<?= $slaType ?>" style="display: block;">
                            <div class="kb-card-top">
                                <div class="kb-card-info" style="flex: 1; min-width: 0;">
                                    <span class="kb-card-empresa"
                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; width: 100%;"><?= esc($p['nombreempresa']) ?></span>
                                    <span class="kb-card-title"
                                        style="white-space: normal; word-break: break-word;"><?= esc($p['titulo'] ?? 'Sin título') ?></span>
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px; margin-left: 10px;">
                                    <div class="kb-card-id">#<?= $p['id'] ?></div>
                                    <?php if (!empty($p['fecharequerida'])): ?>
                                        <div class="sla-badge <?= $slaCls ?>">
                                            <?= date('d/m', strtotime($p['fecharequerida'])) ?>                 <?= $slaText ?>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <?php if (($p['num_modificaciones'] ?? 0) > 0): ?>
                                <?php if ($estado === 'en_proceso'): ?>
                                    <div class="kb-returned-banner">
                                        <i class="bi bi-arrow-counterclockwise"></i> CORRECCIÓN SOLICITADA
                                    </div>
                                <?php elseif ($estado === 'en_revision'): ?>
                                    <div class="kb-corrected-banner">
                                        <i class="bi bi-check-all"></i> CORRECCIÓN REALIZADA
                                    </div>
                                <?php endif ?>
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
                                    <?php elseif ($estado !== 'pendiente_sin_asignar'): ?>
                                        <div class="kb-card-user">
                                            <div class="kb-user-avatar-wrapper sin-asignar">
                                                <span class="kb-user-avatar"><i class="bi bi-person-dash"></i></span>
                                            </div>
                                            <span class="kb-user-name sin-asignar-text">Pendiente</span>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <div class="kb-card-actions">
                                <?php if ($estado === 'pendiente_sin_asignar'): ?>
                                    <div class="kb-action-group" style="width: 100%; display: flex; gap: 8px;">
                                        <button class="kb-btn kb-btn-primary" onclick="verDetalle(<?= $p['id'] ?>)"
                                            style="flex: 1; min-width: 120px;">
                                            <i class="bi bi-search"></i> REVISAR
                                        </button>
                                        <button class="kb-btn kb-btn-danger" onclick="cancelarAtencion(<?= $p['id'] ?>)"
                                            title="Cancelar Requerimiento" style="width: 40px; flex-shrink: 0;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                <?php elseif ($estado === 'en_proceso'): ?>
                                    <div class="kb-action-group" style="width: 100%; display: flex; gap: 8px;">
                                        <button class="kb-btn kb-btn-secondary" onclick="verDetalle(<?= $p['id'] ?>)"
                                            style="flex: 1; min-width: 120px;">
                                            <i class="bi bi-info-circle"></i> DETALLES
                                        </button>
                                    </div>
                                <?php elseif ($estado === 'en_revision'): ?>
                                    <div class="kb-action-group">
                                        <button class="kb-btn kb-btn-view" onclick="verDetalle(<?= $p['id'] ?>)" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="kb-btn kb-btn-danger" onclick="solicitarRetroalimentacion(<?= $p['id'] ?>)"
                                            title="Regresar">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        <button class="kb-btn kb-btn-success"
                                            onclick="cambiarEstado(<?= $p['id'] ?>, 'finalizado', 'Aprobar')" title="Aprobar">
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

                    <?php if ($estado === 'finalizado'): ?>
                        <div style="padding: 15px; text-align: center;">
                            <a href="<?= site_url('admin/historial') ?>" 
                               style="display: block; padding: 12px; border: 1px dashed #333; border-radius: 12px; color: #F5C400; font-family: 'Bebas Neue'; font-size: 16px; text-decoration: none; transition: all 0.3s; letter-spacing: 1px;"
                               onmouseover="this.style.borderColor='#F5C400'; this.style.background='rgba(245,196,0,0.05)';"
                               onmouseout="this.style.borderColor='#333'; this.style.background='transparent';">
                                <i class="bi bi-clock-history"></i> VER HISTORIAL COMPLETO
                            </a>
                        </div>
                    <?php endif ?>
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
        <div class="exp-header-layout"
            style="padding: 40px 30px 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
            <div style="flex: 1; min-width: 0;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <span class="tpl-status-pill"></span>
                    <span style="color:#444; font-size:11px; font-weight:800;">ID: <span class="tpl-id"></span></span>
                </div>
                <h2 class="tpl-titulo"
                    style="font-family:'Bebas Neue'; font-size:48px; color:#fff; letter-spacing:1px; margin:0; line-height:1.1; word-wrap:break-word; overflow-wrap:break-word;"></h2>
                
                <div style="margin-top:15px; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                    <!-- Empresa y Área -->
                    <span style="color:#F5C400; font-weight:800; font-size:14px;"><i class="bi bi-building"></i> <span class="tpl-empresa"></span></span>
                    <span style="color:#222;">|</span>
                    <span style="color:#888; font-size:13px; font-weight:600; letter-spacing:0.5px;">ÁREA: <span class="tpl-area"></span></span>
                    
                    <!-- Separador con Contacto -->
                    <span style="width:1px; height:15px; background:rgba(255,255,255,0.1); margin:0 5px;"></span>
                    
                    <!-- Contacto del Responsable -->
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <span style="color:#fff; font-size:12px; font-weight:700;">
                            <i class="bi bi-person-fill" style="color:#F5C400; margin-right:4px;"></i>
                            <span class="tpl-cliente-nombre"></span>
                        </span>
                        <span style="color:#222;">|</span>
                        <span style="color:#fff; font-size:12px; font-weight:700;">
                            <i class="bi bi-telephone-fill" style="color:#F5C400; margin-right:4px;"></i>
                            <span class="tpl-cliente-telefono"></span>
                        </span>
                        <span style="color:#222;">|</span>
                        <span style="color:#fff; font-size:12px; font-weight:700;">
                            <i class="bi bi-envelope-fill" style="color:#F5C400; margin-right:4px;"></i>
                            <span class="tpl-cliente-correo"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div class="tpl-servicio" style="font-family:'Bebas Neue'; font-size:28px; color:#F5C400; line-height:1;"></div>
                <div style="color:#444; font-size:11px; font-weight:800; letter-spacing:1px; margin-top:8px;">ATENCIÓN #<span class="tpl-idatencion"></span></div>
            </div>
        </div>

        <!-- STEPPER -->
        <div class="tpl-stepper-container"></div>

        <div class="exp-grid">
            <!-- COLUMNA PRINCIPAL -->
            <div class="exp-main-col">

                <!-- Descripción -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-file-text"></i> <span>DESCRIPCIÓN DEL
                            REQUERIMIENTO</span></div>
                    <div class="exp-card-body">
                        <div class="data-value tpl-descripcion" style="font-size:13px; color:#ccc;"></div>
                    </div>
                </div>

                <!-- Estrategia -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-compass"></i> <span>ESTRATEGIA DE COMUNICACIÓN</span>
                    </div>
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
                    <div class="exp-card-header"><i class="bi bi-folder-symlink"></i> <span>RECURSOS DEL CLIENTE</span>
                    </div>
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

                <!-- Auditoría y Gestión -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-shield-check"></i> <span>CONTROL Y GESTIÓN</span></div>
                    <div class="exp-card-body">

                        <!-- Prioridad  -->
                        <div class="priority-manager">
                            <span class="data-label">PRIORIDAD</span>
                            <div class="priority-pills">
                                <button type="button" class="prio-pill btn-prio" data-prio="Alta">
                                    <span class="prio-dot dot-alta"></span> ALTA
                                </button>
                                <button type="button" class="prio-pill btn-prio" data-prio="Media">
                                    <span class="prio-dot dot-media"></span> MEDIA
                                </button>
                                <button type="button" class="prio-pill btn-prio" data-prio="Baja">
                                    <span class="prio-dot dot-baja"></span> BAJA
                                </button>
                            </div>
                        </div>

                        <div
                            style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:15px; border-radius:12px; border:1px solid #111;">
                            <span class="data-label" style="margin:0;">MODIFICACIONES</span>
                            <span class="tpl-modificaciones"
                                style="background:#F5C400; color:#000; padding:4px 12px; border-radius:8px; font-weight:900; font-size:14px;"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- FOOTER ACCIONES -->
        <div style="margin-top:30px; padding:30px; border-top:1px solid #151515; display:flex; justify-content:center;">
            <button class="btn" data-dismiss="modal"
                style="background:#111; border:1px solid #222; font-family:'Bebas Neue'; font-size:20px; letter-spacing:2px; padding:12px 60px; border-radius:12px; color:#F5C400; transition:all 0.3s;">
                CERRAR EXPEDIENTE DIGITAL
            </button>
        </div>
    </div>
</template>

<script>
    /**
     * Filtra las tarjetas del Kanban según su estado de SLA
     */
    function filterKanban(type) {
        const cards = document.querySelectorAll('.kb-card');
        const buttons = document.querySelectorAll('.kb-filter-btn');

        // 1. Actualizar estado visual de los botones
        buttons.forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('btn-filter-' + type);
        if (activeBtn) activeBtn.classList.add('active');

        // 2. Filtrar tarjetas
        cards.forEach(card => {
            if (type === 'all') {
                card.style.display = 'block'; // Volver al estado original
                card.style.opacity = '1';
            } else {
                if (card.dataset.sla === type) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    }
</script>

<!-- ═══ MODAL: PEDIDOS ATRASADOS ═══ -->
<?php if (!empty($atrasados)): ?>
<div id="modalAtrasados" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; padding:20px;">
    <div style="background:#080808; width:95%; max-width:1100px; max-height:85vh; border:1px solid #222; border-radius:12px; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 15px 40px rgba(0,0,0,0.7);">
        
        <!-- Header -->
        <div style="padding:20px 30px; background:#0f0f0f; border-bottom:1px solid #222; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-family:'Bebas Neue'; font-size:24px; color:#fff; letter-spacing:1px; text-transform:uppercase;">
                <?= esc($empresa['nombreempresa']) ?> — <span style="color:#ff4d4d;"><?= count($atrasados) ?> PEDIDOS VENCIDOS</span>
            </h3>
            <button onclick="document.getElementById('modalAtrasados').style.display='none'" style="background:transparent; border:none; color:#555; font-size:24px; cursor:pointer; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#555'">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding:15px 25px; overflow-y:auto; flex:1; background:#080808;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #222;">
                        <th style="padding:12px 10px; text-align:left; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">ID</th>
                        <th style="padding:12px 10px; text-align:left; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">PEDIDO</th>
                        <th style="padding:12px 10px; text-align:left; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">ÁREA</th>
                        <th style="padding:12px 10px; text-align:center; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">PRIORIDAD</th>
                        <th style="padding:12px 10px; text-align:center; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">SOLICITUD</th>
                        <th style="padding:12px 10px; text-align:center; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">FECHA LÍMITE</th>
                        <th style="padding:12px 10px; text-align:center; font-size:11px; color:#F5C400; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">ATRASO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($atrasados as $at): ?>
                    <?php 
                        $diasAtraso = (int)((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($at['fecharequerida'])))) / 86400);
                        $prioColor = match($at['prioridad']) {
                            'Alta' => '#ff4d4d',
                            'Media' => '#f97316',
                            'Baja' => '#10b981',
                            default => '#fff'
                        };
                    ?>
                    <tr style="border-bottom:1px solid #1a1a1a; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:15px 10px; color:#888; font-weight:700; font-size:13px;">#<?= $at['id'] ?></td>
                        <td style="padding:15px 10px;">
                            <div style="color:#fff; font-weight:700; font-size:14px; margin-bottom:3px;"><?= esc($at['titulo']) ?></div>
                            <div style="color:#F5C400; font-size:10px; font-weight:700; text-transform:uppercase; opacity:0.8;"><?= str_replace('_', ' ', ucfirst($at['estado'])) ?></div>
                        </td>
                        <td style="padding:15px 10px; color:#fff; font-size:13px; font-weight:600; opacity:0.9;"><?= esc($at['nombre_area'] ?? '—') ?></td>
                        <td style="padding:15px 10px; text-align:center;">
                            <span style="background:<?= $prioColor ?>; color:#000; padding:3px 10px; border-radius:4px; font-size:10px; font-weight:800; text-transform:uppercase; display:inline-block; min-width:70px;"><?= $at['prioridad'] ?></span>
                        </td>
                        <td style="padding:15px 10px; text-align:center; color:#fff; font-weight:600; font-size:13px; opacity:0.7;">
                            <?= date('d/m/Y', strtotime($at['fechacreacion'])) ?>
                        </td>
                        <td style="padding:15px 10px; text-align:center; color:#fff; font-weight:800; font-size:13px;">
                            <?= date('d/m/Y', strtotime($at['fecharequerida'])) ?>
                        </td>
                        <td style="padding:15px 10px; text-align:center;">
                            <div style="background:#ff4d4d; color:#fff; padding:5px 12px; border-radius:4px; font-weight:800; font-size:12px; display:inline-block;">
                                <?= $diasAtraso ?> DÍAS
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div style="padding:15px 30px; border-top:1px solid #222; text-align:center; background:#0f0f0f;">
            <button onclick="document.getElementById('modalAtrasados').style.display='none'"
                style="background:#111; border:1px solid #222; font-family:'Bebas Neue'; font-size:16px; letter-spacing:1px; padding:10px 40px; border-radius:8px; color:#F5C400; cursor:pointer; transition:all 0.3s;"
                onmouseover="this.style.background='#F5C400'; this.style.color='#000';" onmouseout="this.style.background='#111'; this.style.color='#F5C400';">
                CERRAR LISTADO
            </button>
        </div>
    </div>
</div>
<?php endif ?>

<?= $this->endSection() ?>