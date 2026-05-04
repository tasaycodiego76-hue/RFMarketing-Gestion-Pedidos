<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/seguimiento_requerimiento.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- HEADER: Navegación y título de la sección -->
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
    <!-- COLUMNA PRINCIPAL: Historial cronológico -->
    <div class="col-lg-8">
        <div class="card-dark-main p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="m-0 text-accent-yellow">
                    <i class="bi bi-clock-history"></i> Historial de Cambios
                </h4>
                <span class="badge-type">#REQ: <?= esc($requerimiento['id']) ?></span>
            </div>

            <?php if (!empty($historial)): ?>
                <!-- LINEA DE TIEMPO -->
                <div class="timeline-seguimiento">
                    <?php foreach ($historial as $item): ?>
                        <div class="timeline-item-seg">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="timeline-estado estado-<?= str_replace(['pendiente_sin_asignar','pendiente_asignado','en_proceso','en_revision'], ['pendiente', 'pendiente', 'proceso', 'revision'], strtolower($item['estado'] ?? 'default')) ?>">
                                    <?= strtoupper(str_replace('_', ' ', esc($item['estado'] ?? 'Estado actualizado'))) ?>
                                </span>
                                <small class="text-muted-timeline">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y H:i', strtotime($item['fecha_registro'])) ?>
                                </small>
                            </div>
                            <p class="mb-1 text-white-timeline">
                                <?= nl2br(esc($item['accion'] ?? 'Sin descripción')) ?>
                            </p>
                            <small class="text-grey-timeline">
                                <i class="bi bi-person"></i> Por:
                                <?= esc(($item['usuario_nombre'] ?? '') . ' ' . ($item['usuario_apellido'] ?? 'Sistema')) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- ESTADO VACÍO -->
                <div class="text-center py-5">
                    <i class="bi bi-inbox empty-icon"></i>
                    <p class="mt-3 text-grey-timeline">No hay registros de seguimiento para este requerimiento.</p>
                    <small class="text-muted">El historial se actualizará cuando haya cambios de estado.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- COLUMNA LATERAL: Resumen rápido del requerimiento -->
    <div class="col-lg-4">
        <div class="card-dark-main p-4 mb-4">
            <label class="label-tiny mb-3 d-block">RESUMEN DEL REQUERIMIENTO</label>
            <!-- Título del proyecto -->
            <div class="timeline-item">
                <i class="bi bi-file-text"></i>
                <div>
                    <span class="t-label">TÍTULO</span>
                    <span class="t-value"><?= esc($requerimiento['titulo']) ?></span>
                </div>
            </div>
            <!-- Servicio seleccionado -->
            <div class="timeline-item">
                <i class="bi bi-tag"></i>
                <div>
                    <span class="t-label">SERVICIO</span>
                    <span class="t-value">
                        <?= esc($requerimiento['nombre_servicio'] ?? $requerimiento['servicio_personalizado'] ?? 'N/A') ?>
                    </span>
                </div>
            </div>
            <!-- Estado actual resumido -->
            <div class="timeline-item">
                <i class="bi bi-info-circle"></i>
                <div>
                    <span class="t-label">ESTADO ACTUAL</span>
                    <span class="t-value text-uppercase-accent">
                        <?= strtoupper(str_replace('_', ' ', esc($requerimiento['estado'] ?? 'Pendiente'))) ?>
                    </span>
                </div>
            </div>
            <!-- Fecha de entrega estimada -->
            <div class="timeline-item">
                <i class="bi bi-calendar"></i>
                <div>
                    <span class="t-label">FECHA DE ENTREGA</span>
                    <span class="t-value">
                        <?= date('d/m/Y H:i', strtotime($requerimiento['fechafin'] ?? $requerimiento['fecharequerida'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>