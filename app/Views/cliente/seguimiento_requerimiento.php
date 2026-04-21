<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/seguimiento_requerimiento.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="header-detalle mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="breadcrumb-text">Mis Pedidos / Seguimiento</span>
            <h2 class="nombre-cliente-titulo bebas">SEGUIMIENTO DEL REQUERIMIENTO</h2>
            <p class="cliente-rol-sub">Cliente — Historial de estados</p>
        </div>
        <a href="<?= base_url('cliente/mis_solicitudes') ?>" class="btn-volver-custom">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-dark-main p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="m-0" style="color:#f5c400;">
                    <i class="bi bi-clock-history"></i> Historial de Cambios
                </h4>
                <span class="badge-type">#REQ:
                    <?= esc($requerimiento['id']) ?>
                </span>
            </div>

            <?php if (!empty($historial)): ?>
                <div class="timeline-seguimiento">
                    <?php foreach ($historial as $item): ?>
                        <div class="timeline-item-seg">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span
                                    class="timeline-estado estado-<?= str_replace([
                                        'pendiente_sin_asignar',
                                        'pendiente_asignado',
                                        'en_proceso',
                                        'en_revision'
                                    ], ['pendiente', 'pendiente', 'proceso', 'revision'], strtolower($item['estado'] ?? 'default')) ?>">
                                    <?= esc($item['estado'] ?? 'Estado actualizado') ?>
                                </span>
                                <small style="color:#888;">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y H:i', strtotime($item['fecha_registro'])) ?>
                                </small>
                            </div>
                            <p class="mb-1" style="color:#f0f0f0;">
                                <?= nl2br(esc($item['accion'] ?? 'Sin descripción')) ?>
                            </p>
                            <small style="color:#666;">
                                <i class="bi bi-person"></i> Por:
                                <?= esc(($item['usuario_nombre'] ?? '') . ' ' . ($item['usuario_apellido'] ??
                                    'Sistema')) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size:64px;color:#333;"></i>
                    <p class="mt-3" style="color:#666;">No hay registros de seguimiento para este requerimiento.</p>
                    <small style="color:#555;">El historial se actualizará cuando haya cambios de estado.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-dark-main p-4 mb-4">
            <label class="label-tiny mb-3 d-block">RESUMEN DEL REQUERIMIENTO</label>

            <div class="timeline-item">
                <i class="bi bi-file-text"></i>
                <div>
                    <span class="t-label">TÍTULO</span>
                    <span class="t-value">
                        <?= esc($requerimiento['titulo']) ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-tag"></i>
                <div>
                    <span class="t-label">SERVICIO</span>
                    <span class="t-value">
                        <?= esc($requerimiento['nombre_servicio'] ?? $requerimiento['servicio_personalizado'] ?? 'N/A') ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-info-circle"></i>
                <div>
                    <span class="t-label">ESTADO ACTUAL</span>
                    <span class="t-value" style="text-transform:uppercase;color:#f5c400;">
                        <?= esc($requerimiento['estado'] ?? 'Pendiente') ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-calendar"></i>
                <div>
                    <span class="t-label">FECHA DE ENTREGA</span>
                    <span class="t-value">
                        <?= date('d/m/Y', strtotime($requerimiento['fechafin'] ?? $requerimiento['fecharequerida'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>