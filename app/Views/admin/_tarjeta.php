<?php
// Cálculo de Prioridad
$prio = $p['prioridad_admin'] ?? ($p['prioridad'] ?? 'Media');
$prioCls = strtolower($prio);

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
    data-id="<?= $p['id'] ?>" data-area="<?= $p['idarea_agencia'] ?>" data-sla="<?= $slaType ?>" data-prio="<?= $prioCls ?>" style="display: block;">
    <div class="kb-card-top" style="display: flex; flex-direction: column; gap: 4px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="kb-card-info" style="flex: 1; min-width: 0;">
                <span class="kb-card-empresa"
                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; width: 100%; font-size: 8px; color: #F5C400; font-weight: 800; text-transform: uppercase;"><?= esc($p['nombreempresa'] ?? '') ?></span>
                <span class="kb-card-title"
                    style="white-space: normal; overflow-wrap: break-word; word-break: normal; hyphens: none; display: block; margin-top: 3px; font-size: 11.5px; font-weight: 600; line-height: 1.3; color: #fff;"><?= esc($p['titulo'] ?? 'Sin título') ?></span>
            </div>
            <div class="kb-card-id" style="font-size: 9px; color: #666; font-weight: 900; margin-left: 10px;">#<?= $p['id'] ?></div>
        </div>
        <?php if (!empty($p['fecharequerida'])): ?>
            <div class="sla-badge <?= $slaCls ?>" style="align-self: flex-start; font-size: 8px; padding: 3px 6px; border-radius: 4px; font-weight: 800; letter-spacing: 0.5px;">
                <i class="bi bi-clock-history"></i> <?= date('d/m', strtotime($p['fecharequerida'])) ?> - <?= $slaText ?>
            </div>
        <?php endif ?>
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
            <i class="bi bi-tag-fill"></i> <?= ($p['es_servicio_personalizado'] ?? 0) ? 'Serv. Personalizado' : esc($p['servicio'] ?? 'General') ?>
        </span>
        <span class="kb-tag-pri kb-pri-<?= $prioCls ?>">
            <i class="bi bi-flag-fill"></i> <?= $prio ?>
        </span>
    </div>

    <div class="kb-card-footer">
        <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
            <?php if (!empty($p['idempleado'])): ?>
                <?php
                $nombreComp = $p['empleado_nombre'] . ' ' . ($p['empleado_apellidos'] ?? '');
                $empIni = mb_strtoupper(mb_substr($p['empleado_nombre'], 0, 1) . (mb_substr($p['empleado_apellidos'] ?? '', 0, 1)));
                $enDesarrolloReal = (!empty($p['fechainicio']) && $estado === 'en_proceso' && empty($p['ultimo_motivo_pausa']));
                $estaPausado = (!empty($p['fechainicio']) && $estado === 'en_proceso' && !empty($p['ultimo_motivo_pausa']));
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
                        <?php elseif ($estaPausado): ?>
                            <span class="kb-badge-developing" style="background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2);">
                                <i class="bi bi-pause-fill"></i> PAUSADO
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

    <div class="kb-card-actions" style="margin-top: 2px;">
        <?php if ($estado === 'pendiente_sin_asignar'): ?>
            <div class="kb-action-group" style="width: 100%; display: flex; gap: 6px;">
                <button class="kb-btn kb-btn-primary" onclick="verDetalle(<?= $p['id'] ?>)"
                    style="flex: 1; min-width: 120px; padding: 6px 10px; font-size: 11px;">
                    <i class="bi bi-search"></i> REVISAR
                </button>
                <button class="kb-btn kb-btn-danger" onclick="cancelarAtencion(<?= $p['id'] ?>)"
                    title="Cancelar Requerimiento" style="width: 40px; flex-shrink: 0; padding: 6px 10px; font-size: 11px;">
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
