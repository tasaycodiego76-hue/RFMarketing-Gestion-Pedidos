<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/kanban.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>const AREA_ACTUAL = <?= $idArea ?>;</script>
<script src="<?= base_url('recursos/scripts/admin/kanban.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- ═══ CABECERA ÁREA ═══ -->
<div class="kb-head mb-4">
    <div class="kb-head-left">
        <div class="kb-emp-avatar" style="background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%); color:#fff;">
            <?= mb_strtoupper(mb_substr($areaNombre, 0, 2)) ?>
        </div>
        <div>
            <div class="kb-emp-nombre">ÁREA: <?= esc(strtoupper($areaNombre)) ?></div>
            <div class="kb-emp-meta">
                Gestión operativa del equipo y asignación de tareas
            </div>
        </div>
    </div>
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
                <div class="kb-empty">Sin tareas en este estado</div>
            <?php else: ?>
                <?php foreach ($col['items'] as $p): ?>
                <div class="kb-card" data-id="<?= $p['id'] ?>">
                    <div class="kb-card-top">
                        <span class="kb-card-title"><?= esc($p['titulo'] ?? 'Sin título') ?></span>
                        <span class="kb-badge kb-badge-<?= $estado ?>">
                            <?= $estado === 'pendiente_asignado' ? 'Nuevo Asignado' : ($estado === 'en_proceso' ? 'Desarrollo' : ($estado === 'en_revision' ? 'Esperando Aprob.' : 'Finalizado')) ?>
                        </span>
                    </div>

                    <div class="kb-card-empresa">Cliente: <?= esc($p['nombreempresa']) ?></div>

                    <div class="kb-card-tags">
                        <span class="kb-tag-servicio"><?= esc($p['servicio'] ?? 'Sin servicio') ?></span>
                        <span class="kb-tag-servicio" style="background:#222; border-color:#333; color:#ccc;"><?= esc($p['tipo_requerimiento'] ?? 'Estándar') ?></span>
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
                                <span class="kb-user-name sin-asignar-text" style="color: #eab308; font-weight:600;">⚠️ Por asignar empleado</span>
                            </div>
                        <?php endif ?>
                    </div>

                    <div class="kb-card-actions">
                        <button class="kb-btn kb-btn-detalle" onclick="verDetalle(<?= $p['id'] ?>)"><i class="bi bi-eye"></i> Detalle Full</button>
                        <?php if ($estado === 'pendiente_asignado' || empty($p['idempleado'])): ?>
                            <button class="kb-btn kb-btn-assign-emp" onclick="abrirModalAsignarEmpleado(<?= $p['id'] ?>, <?= $idArea ?>)"><i class="bi bi-person-plus"></i> Asignar</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>
    <?php endforeach ?>
</div>

<!-- ═══ MODAL ASIGNAR EMPLEADO (Mismo que admin) ═══ -->
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
                <label class="kb-modal-label">Seleccionar empleado operativo:</label>
                <select id="asignar-empleado" class="form-control kb-modal-select">
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div class="modal-footer kb-modal-footer">
                <button class="btn kb-btn-confirmar-asignar" onclick="confirmarAsignacionEmpleado()">Asignar</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL VER DETALLE ═══ -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content kb-modal">
            <div class="modal-header kb-modal-header">
                <h6 class="modal-title" id="detalle-titulo">EXPEDIENTE DEL PROYECTO</h6>
                <button type="button" class="close kb-modal-close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="detalle-cuerpo">
                Cargando...
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
