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
        <i class="bi bi-check2-circle text-success mb-3" style="font-size: 48px;"></i>
        <h5>¡Todo en orden!</h5>
        <p class="text-muted">No tienes pedidos con retroalimentación pendiente por el momento.</p>
    </div>
<?php else: ?>
    <div class="retro-grid">
        <?php foreach ($data as $item): ?>
            <div class="retro-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="retro-badge">Corrección Solicitada</span>
                    <span style="font-size: 11px; color: #555;"><?= date('d/m/Y H:i', strtotime($item['fechacreacion'])) ?></span>
                </div>
                
                <h5 class="mb-1" style="font-family:'Bebas Neue',sans-serif; letter-spacing:1px; color:#fff;">
                    <?= esc($item['titulo']) ?>
                </h5>
                <div style="font-size: 12px; color: #f59e0b; font-weight: 600; margin-bottom: 4px;">
                    <?= esc($item['servicio_nombre']) ?>
                </div>
                <div style="font-size: 11px; color: #71717a;">
                    <?= esc($item['empresa_nombre']) ?>
                </div>

                <div class="retro-msg">
                    <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; margin-bottom: 4px; color: #f59e0b;">
                        MENSAJE DE REVISIÓN:
                    </div>
                    <?= esc($item['observacion_revision']) ?>
                </div>

                <div class="retro-footer">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:28px; height:28px; border-radius:50%; background:#222; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; color:#aaa;">
                            <?= strtoupper(substr($item['empleado_nombre'] ?? 'S', 0, 1)) ?>
                        </div>
                        <span style="font-size: 11px; color: #aaa;">
                            <?= esc($item['empleado_nombre'] ? ($item['empleado_nombre'] . ' ' . $item['empleado_apellidos']) : 'Sin asignar') ?>
                        </span>
                    </div>
                    <button class="btn btn-sm" style="background:#f59e0b; color:#000; font-family:'Bebas Neue',sans-serif; font-size:13px; letter-spacing:1px;" onclick="verDetalle(<?= $item['id'] ?>)">
                        VER DETALLE
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Detalle (Reutilizamos el de bandeja/en_proceso si es compatible) -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background:#0a0a0a; border:1px solid #1e1e1e; border-radius:12px;">
            <div class="modal-body p-0" id="detalleCuerpo">
                <!-- Se carga vía JS -->
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const BASE_URL = "<?= base_url() ?>";
</script>
<script src="<?= base_url('recursos/scripts/responsable/paginas/retroalimentacion.js') ?>"></script>
<?= $this->endSection() ?>
