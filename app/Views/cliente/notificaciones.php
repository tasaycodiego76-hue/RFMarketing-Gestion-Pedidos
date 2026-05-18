<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/notificaciones.css') ?>">
<?= $this->endSection() ?>


<?= $this->section('contenido') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-dark-main">
                <!-- Header del Panel -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white bebas">
                        <i class="bi bi-bell text-warning"></i> Mis Notificaciones
                    </h5>
                    <small class="text-muted">Últimas 20 notificaciones</small>
                </div>

                <div class="card-body p-0">
                    <?php if (!empty($notificaciones)): ?>
                        <!-- LISTA DE NOTIFICACIONES -->
                        <div class="list-group list-group-flush bg-transparent">
                            <?php foreach ($notificaciones as $notif): ?>
                                <?php 
                                $fecha = new DateTime($notif['fecha_registro']);
                                $fechaFormateada = $fecha->format('d/m/Y H:i');
                                
                                // MAPEO DE ESTADOS: Define iconos y colores según el estado del requerimiento
                                $estadoBadgeClass = '';
                                $estadoIconClass = '';
                                
                                switch($notif['estado']) {
                                    case 'en_proceso':
                                        $estadoBadgeClass = 'badge-en_proceso';
                                        $estadoIconClass = 'icono-en_proceso bi-gear-fill';
                                        break;
                                    case 'finalizado':
                                        $estadoBadgeClass = 'badge-finalizada';
                                        $estadoIconClass = 'icono-finalizado bi-check-circle-fill';
                                        break;
                                    case 'en_revision':
                                        $estadoBadgeClass = 'badge-en_revision';
                                        $estadoIconClass = 'icono-en_revision bi-eye-fill';
                                        break;
                                    case 'pendiente_asignado':
                                    case 'pendiente_sin_asignar':
                                        $estadoBadgeClass = 'badge-pendiente_sin_asignar';
                                        $estadoIconClass = 'icono-pendiente bi-clock-fill';
                                        break;
                                    case 'cancelado':
                                        $estadoBadgeClass = 'badge-cancelado';
                                        $estadoIconClass = 'icono-cancelado bi-x-circle-fill';
                                        break;
                                    default:
                                        $estadoBadgeClass = 'badge-pendiente_sin_asignar';
                                        $estadoIconClass = 'icono-pendiente bi-info-circle-fill';
                                }
                                ?>
                                <!-- Item de Notificación -->
                                <div class="list-group-item bg-transparent border-dark py-3">
                                    <div class="notificacion-layout">
                                        <div class="notificacion-contenido">
                                            <h6 class="notificacion-titulo text-white mb-2">
                                                <i class="bi <?= $estadoIconClass ?> notificacion-icono me-2"></i>
                                                <?= esc($notif['atencion_titulo'] ?? 'Sin título') ?>
                                            </h6>
                                            <p class="notificacion-accion text-muted mb-2"><?= esc($notif['accion']) ?></p>
                                            <small class="notificacion-fecha text-secondary">
                                                <i class="bi bi-person"></i>
                                                Realizado por: <?= esc($notif['realizado_por'] ?? 'Sistema') ?>
                                            </small>
                                        </div>
                                        <div class="notificacion-meta text-end">
                                            <span class="badge badge-estado <?= $estadoBadgeClass ?> mb-2">
                                                <?= esc(str_replace('_', ' ', $notif['estado'] ?? 'Desconocido')) ?>
                                            </span>
                                            <br>
                                            <small class="notificacion-fecha text-secondary d-block mt-1">
                                                <i class="bi bi-clock"></i>
                                                <?= $fechaFormateada ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- ESTADO VACÍO: Si no hay notificaciones -->
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-bell-slash text-secondary" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No tienes notificaciones</h5>
                            <p class="text-secondary">No se encontraron movimientos recientes en tus requerimientos.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Paginacion -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="card-footer bg-transparent border-dark d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                        <small class="text-white">Mostrando página <?= $currentPage ?> de <?= $totalPages ?> (Total: <?= $totalRegistros ?> notificaciones)</small>
                        <nav aria-label="Paginación de notificaciones">
                            <ul class="pagination pagination-rf mb-0">
                                <li class="page-item <?= ($currentPage == 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= base_url('cliente/notificaciones?page=' . ($currentPage - 1)) ?>" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                                </li>
                                <?php 
                                $range = 2;
                                $startPage = max(1, $currentPage - $range);
                                $endPage = min($totalPages, $currentPage + $range);
                                
                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('cliente/notificaciones?page=1') ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= ($currentPage == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('cliente/notificaciones?page=' . $i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('cliente/notificaciones?page=' . $totalPages) ?>"><?= $totalPages ?></a>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item <?= ($currentPage == $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= base_url('cliente/notificaciones?page=' . ($currentPage + 1)) ?>" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
