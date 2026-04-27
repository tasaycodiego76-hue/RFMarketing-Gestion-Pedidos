<?= $this->extend('plantillas/cliente') ?>

<?php
// Cargar notificaciones directamente desde el controlador
$trackingModel = new \App\Models\TrackingModel();
$notificaciones = $trackingModel->getNotificacionesPorUsuario($user['id']);
?>

<?= $this->section('contenido') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bell"></i> Mis Notificaciones
                    </h5>
                    <small class="text-muted">Últimas 20 notificaciones</small>
                </div>
                <div class="card-body">
                    <?php if (!empty($notificaciones)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notificaciones as $notif): ?>
                                <?php 
                                $fecha = new DateTime($notif['fecha_registro']);
                                $fechaFormateada = $fecha->format('d/m/Y H:i');
                                
                                // Determinar el color del estado
                                $estadoClass = '';
                                $estadoIcon = '';
                                
                                switch($notif['estado']) {
                                    case 'en_proceso':
                                        $estadoClass = 'text-primary';
                                        $estadoIcon = 'bi-gear';
                                        break;
                                    case 'finalizado':
                                        $estadoClass = 'text-success';
                                        $estadoIcon = 'bi-check-circle';
                                        break;
                                    case 'en_revision':
                                        $estadoClass = 'text-warning';
                                        $estadoIcon = 'bi-eye';
                                        break;
                                    default:
                                        $estadoClass = 'text-secondary';
                                        $estadoIcon = 'bi-info-circle';
                                }
                                ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi <?= $estadoIcon ?> <?= $estadoClass ?>"></i>
                                                <?= esc($notif['atencion_titulo'] ?? 'Sin título') ?>
                                            </h6>
                                            <p class="mb-1"><?= esc($notif['accion']) ?></p>
                                            <small class="text-muted">
                                                Por: <?= esc($notif['realizado_por'] ?? 'Sistema') ?> • 
                                                <?= $fechaFormateada ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill">
                                            <?= esc($notif['estado'] ?? 'Desconocido') ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No tienes notificaciones</h5>
                            <p class="text-muted">No se encontraron notificaciones recientes.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
