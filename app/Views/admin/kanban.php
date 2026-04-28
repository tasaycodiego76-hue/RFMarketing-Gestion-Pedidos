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
                RUC <?= esc($empresa['ruc'] ?? '—') ?>
                <?php if (!empty($empresa['correo'])): ?> · <?= esc($empresa['correo']) ?><?php endif ?>
                <?php if (!empty($empresa['telefono'])): ?> · <?= esc($empresa['telefono']) ?><?php endif ?>
            </div>
        </div>
    </div>
    <div class="kb-head-stats">
        <div class="kb-stat"><span class="st-morado"><?= $stats['por_aprobar'] ?? 0 ?></span><small>POR APROBAR</small>
        </div>
        <div class="kb-stat"><span class="st-amarillo"><?= $stats['activos'] ?? 0 ?></span><small>ACTIVOS</small></div>
        <div class="kb-stat"><span class="st-blanco"><?= $stats['en_revision'] ?? 0 ?></span><small>EN REVISIÓN</small>
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
        <div class="kb-col">
            <div class="kb-col-head" style="border-top: 3px solid <?= $col['color'] ?>">
                <span class="kb-col-title" style="color: <?= $col['color'] ?>"><?= $col['label'] ?></span>
                <span class="kb-col-count"><?= count($col['items']) ?></span>
            </div>

            <div class="kb-col-body" data-estado="<?= $estado ?>">
                <?php if (empty($col['items'])): ?>
                    <div class="kb-empty">Sin requerimientos</div>
                <?php else: ?>
                    <?php 
                    // Ordenar TODOS los pedidos por prioridad: Alta > Media > Baja
                    usort($col['items'], function($a, $b) {
                        $prios = ['Alta' => 1, 'Media' => 2, 'Baja' => 3];
                        $vA = $prios[$a['prioridad_admin'] ?? ($a['prioridad'] ?? 'Media')] ?? 2;
                        $vB = $prios[$b['prioridad_admin'] ?? ($b['prioridad'] ?? 'Media')] ?? 2;
                        return $vA <=> $vB;
                    });
                    ?>
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
                                <span class="kb-tag-servicio"
                                    style="background:#222; border-color:#333; color:#ccc;"><?= esc($p['tipo_requerimiento'] ?? 'Estándar') ?></span>
                                <?php if ($estado === 'pendiente_sin_asignar'): ?>
                                    <?php $pri = strtolower($p['prioridad'] ?? 'media'); ?>
                                    <span class="kb-tag-pri kb-pri-<?= $pri ?>">
                                        <?= $pri === 'alta' ? '▲ Alta' : ($pri === 'baja' ? '▼ Baja' : '● Media') ?>
                                    </span>
                                <?php endif ?>
                            </div>

                            <?php if (!empty($p['fechafin'])): ?>
                                <div class="kb-card-fecha"><i class="bi bi-calendar-check mr-1"></i> Entrega: <?= date('d M Y', strtotime($p['fechafin'])) ?></div>
                            <?php elseif (!empty($p['fecharequerida'])): ?>
                                <div class="kb-card-fecha"><i class="bi bi-calendar-event mr-1"></i> Requerida: <?= date('d M Y', strtotime($p['fecharequerida'])) ?></div>
                            <?php endif ?>

                            <div class="kb-card-footer">
                                <?php if ($estado !== 'pendiente_sin_asignar'): ?>
                                    <?php if ($p['idempleado']): ?>
                                        <div class="kb-card-user">
                                            <span
                                                class="kb-user-avatar"><?= mb_strtoupper(mb_substr($p['empleado_nombre'], 0, 1) . mb_substr($p['empleado_apellidos'], 0, 1)) ?></span>
                                            <?php if ($estado === 'finalizado'): ?>
                                                <span class="kb-user-name text-success">Realizado por: <?= esc($p['empleado_nombre']) ?></span>
                                            <?php elseif ($estado === 'en_proceso'): ?>
                                                <span class="kb-user-name text-success">Desarrollando: <?= esc($p['empleado_nombre']) ?></span>
                                            <?php else: ?>
                                                <span class="kb-user-name text-success">Asignado a: <?= esc($p['empleado_nombre']) ?></span>
                                            <?php endif ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="kb-card-user">
                                            <span class="kb-user-avatar sin-asignar"
                                                style="background:rgba(234, 179, 8, 0.2); color:#eab308;">!</span>
                                            <span class="kb-user-name text-warning">Falta asignar responsable</span>
                                        </div>
                                    <?php endif ?>
                                <?php endif ?>
                            </div>

                            <div class="kb-card-actions">
                                <?php if ($estado === 'pendiente_sin_asignar'): ?>
                                    <button class="kb-btn kb-btn-ver" onclick="verDetalle(<?= $p['id'] ?>)">Ver</button>
                                    <button class="kb-btn kb-btn-cancel" onclick="cancelarAtencion(<?= $p['id'] ?>)">✕</button>
                                <?php elseif ($estado === 'en_proceso'): ?>
                                    <button class="kb-btn kb-btn-detalle" onclick="verDetalle(<?= $p['id'] ?>)">Ver detalle</button>

                                <?php elseif ($estado === 'en_revision'): ?>
                                    <button class="kb-btn kb-btn-aprobar"
                                        onclick="cambiarEstado(<?= $p['id'] ?>, 'finalizado', 'SU Requerimiento ha sido Completado Satisfactoriamente')">✓
                                        Aprobar</button>
                                    <button class="kb-btn kb-btn-regresar"
                                        onclick="solicitarRetroalimentacion(<?= $p['id'] ?>)">↶
                                        Regresar</button>
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

<!-- ═══ MODAL VER DETALLE (Bootstrap 4) ═══ -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
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

<!-- ═══ MODAL RETROALIMENTACIÓN (Admin -> Empleado) ═══ -->
<div class="modal fade" id="modalRetro" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content kb-modal">
            <div class="modal-header kb-modal-header">
                <h6 class="modal-title" style="color: var(--amarillo);"><i class="bi bi-chat-left-text mr-2"></i>Enviar a Corrección</h6>
                <button type="button" class="close kb-modal-close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="retro-idatencion">
                <p style="font-size: 11px; color: #888; margin-bottom: 15px; line-height: 1.4;">
                    Por favor, indica los puntos específicos que el empleado debe mejorar o corregir en este pedido.
                </p>
                <div class="form-group">
                    <label class="kb-modal-label">Mensaje de mejora:</label>
                    <textarea id="retro-mensaje" class="form-control kb-modal-select" rows="5" placeholder="Escribe aquí las observaciones..."></textarea>
                </div>
            </div>
            <div class="modal-footer kb-modal-footer">
                <button class="btn kb-btn-confirmar-asignar" style="background: #ef4444; color: #fff;" onclick="enviarRetroalimentacion()">Enviar a Corrección</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>