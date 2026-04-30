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
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="retro-badge"><i class="bi bi-exclamation-circle me-1"></i> Corrección Solicitada</span>
                    <span style="font-size: 11px; color: #555; font-weight: 600;">
                        <i class="bi bi-clock-history"></i> <?= date('d/m/Y H:i', strtotime($item['fechacreacion'])) ?>
                    </span>
                </div>
                
                <h4 class="mb-1" style="font-family:'Bebas Neue',sans-serif; font-size: 24px; letter-spacing:1.5px; color:#fff; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    <?= esc($item['titulo']) ?>
                </h4>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="font-size: 11px; color: #f5c400; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(245, 196, 0, 0.1); padding: 2px 8px; border-radius: 4px;">
                        <?= esc($item['servicio_nombre']) ?>
                    </div>
                    <div style="font-size: 11px; color: #71717a; font-weight: 600;">
                        <i class="bi bi-building"></i> <?= esc($item['empresa_nombre']) ?>
                    </div>
                </div>

                <div class="retro-msg" style="background: rgba(245, 158, 11, 0.03); border-color: #f59e0b; padding: 15px; border-radius: 12px; position: relative;">
                    <div style="position: absolute; top: -10px; left: 15px; background: #f59e0b; color: #000; font-size: 9px; font-weight: 900; padding: 2px 8px; border-radius: 4px; letter-spacing: 1px;">
                        FEEDBACK DEL RESPONSABLE
                    </div>
                    <p style="margin: 0; font-size: 14px; color: #e4e4e7; line-height: 1.6; font-style: normal; font-weight: 500;">
                        "<?= esc($item['observacion_revision']) ?>"
                    </p>
                </div>

                <div class="retro-footer">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg, #27272a, #09090b); border: 1px solid #3f3f46; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; color:#f5c400; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                            <?= strtoupper(substr($item['empleado_nombre'] ?? 'S', 0, 1)) ?>
                        </div>
                        <div class="d-flex flex-column">
                            <span style="font-size: 10px; color: #52525b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Especialista</span>
                            <span style="font-size: 12px; color: #d4d4d8; font-weight: 600; line-height: 1;">
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
