<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link href="<?= base_url('recursos/styles/responsable/paginas/retroalimentacion.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="page-title mb-0">PEDIDOS DEVUELTOS / CORRECCIONES</h1>
        <p class="text-muted small mb-0">Revisa las observaciones y ajusta los requerimientos devueltos.</p>
    </div>
</div>

<?php if (empty($data)): ?>
    <div class="card-rf text-center py-5">
        <i class="bi bi-check2-circle icon-xl-success mb-3"></i>
        <h5>¡Todo en orden!</h5>
        <p class="text-muted">No tienes pedidos con retroalimentación pendiente por el momento.</p>
    </div>
<?php else: ?>
    <div class="retro-grid">
        <?php foreach ($data as $item): ?>
            <div class="retro-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="retro-badge"><i class="bi bi-exclamation-circle me-1"></i> Corrección Solicitada</span>
                    <span class="text-dim-small">
                        <i class="bi bi-clock-history"></i> <?= date('d/m/Y H:i', strtotime($item['fecha_retro'] ?? $item['fechacreacion'])) ?>
                    </span>
                </div>
                
                <h4 class="mb-1 title-bebas-retro">
                    <?= esc($item['titulo']) ?>
                </h4>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="badge-servicio-retro">
                        <?= esc($item['servicio_nombre']) ?>
                    </div>
                    <div class="text-muted-extra-small">
                        <i class="bi bi-building"></i> <?= esc($item['empresa_nombre']) ?>
                    </div>
                </div>

                <div class="retro-msg retro-msg-container">
                    <div class="retro-msg-label">
                        FEEDBACK DEL RESPONSABLE
                    </div>
                    <p class="retro-msg-text">
                        "<?= esc($item['observacion_revision']) ?>"
                    </p>
                </div>

                <div class="retro-footer">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle-retro">
                            <?= strtoupper(substr($item['empleado_nombre'] ?? 'S', 0, 1)) ?>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="specialist-label">Especialista</span>
                            <span class="specialist-name">
                                <?= esc($item['empleado_nombre'] ? ($item['empleado_nombre'] . ' ' . $item['empleado_apellidos']) : 'Sin asignar') ?>
                            </span>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-retro-action" onclick="verDetalle(<?= $item['id'] ?>)">
                        VER DETALLE <i class="bi bi-arrow-right-short ms-1"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Detalle (Reutilizamos el de bandeja/en_proceso si es compatible) -->
<div class="modal fade" id="modalDetalle" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-dark-retro">
            <div class="modal-body p-0" id="detalleCuerpo">
                <!-- Se carga vía JS -->
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/responsable/paginas/retroalimentacion.js') ?>"></script>
<?= $this->endSection() ?>
