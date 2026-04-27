<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/notificaciones.css') ?>">
<?= $this->endSection() ?>

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
                                
                                // Determinar clases de estado
                                $estadoBadgeClass = '';
                                $estadoIconClass = '';
                                
                                switch($notif['estado']) {
                                    case 'en_proceso':
                                        $estadoBadgeClass = 'badge-en_proceso';
                                        $estadoIconClass = 'icono-en_proceso bi-gear';
                                        break;
                                    case 'finalizado':
                                        $estadoBadgeClass = 'badge-finalizada';
                                        $estadoIconClass = 'icono-finalizado bi-check-circle';
                                        break;
                                    case 'en_revision':
                                        $estadoBadgeClass = 'badge-en_revision';
                                        $estadoIconClass = 'icono-en_revision bi-eye';
                                        break;
                                    case 'pendiente_asignado':
                                        $estadoBadgeClass = 'badge-pendiente_asignado';
                                        $estadoIconClass = 'icono-pendiente bi-clock';
                                        break;
                                    case 'pendiente_sin_asignar':
                                        $estadoBadgeClass = 'badge-pendiente_sin_asignar';
                                        $estadoIconClass = 'icono-pendiente bi-clock';
                                        break;
                                    case 'cancelado':
                                        $estadoBadgeClass = 'badge-cancelado';
                                        $estadoIconClass = 'icono-cancelado bi-x-circle';
                                        break;
                                    default:
                                        $estadoBadgeClass = 'badge-pendiente_sin_asignar';
                                        $estadoIconClass = 'icono-pendiente bi-info-circle';
                                }
                                ?>
                                <div class="list-group-item">
                                    <div class="notificacion-layout">
                                        <div class="notificacion-contenido">
                                            <h6 class="notificacion-titulo">
                                                <i class="bi <?= $estadoIconClass ?> notificacion-icono"></i>
                                                <?= esc($notif['atencion_titulo'] ?? 'Sin título') ?>
                                            </h6>
                                            <p class="notificacion-accion"><?= esc($notif['accion']) ?></p>
                                            <small class="notificacion-fecha">
                                                <i class="bi bi-person"></i>
                                                Por: <?= esc($notif['realizado_por'] ?? 'Sistema') ?>
                                            </small>
                                        </div>
                                        <div class="notificacion-meta">
                                            <span class="badge badge-estado <?= $estadoBadgeClass ?>">
                                                <?= esc(str_replace('_', ' ', $notif['estado'] ?? 'Desconocido')) ?>
                                            </span>
                                            <small class="notificacion-fecha">
                                                <i class="bi bi-clock"></i>
                                                <?= $fechaFormateada ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-bell-slash"></i>
                            <h5>No tienes notificaciones</h5>
                            <p>No se encontraron notificaciones recientes.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
