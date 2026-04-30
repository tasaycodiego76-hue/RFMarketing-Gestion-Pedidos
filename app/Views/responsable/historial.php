<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/historial.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="historial-container">
    <!-- TABS PARA NAVEGAR ENTRE HISTORIAL PERSONAL Y DE ÁREA -->
    <ul class="nav nav-pills historial-tabs" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-mis-tareas-tab" data-bs-toggle="pill"
                data-bs-target="#pills-mis-tareas" type="button" role="tab" aria-controls="pills-mis-tareas"
                aria-selected="true">
                <i class="bi bi-person me-2"></i>MI HISTORIAL PERSONAL
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-area-tab" data-bs-toggle="pill" data-bs-target="#pills-area" type="button"
                role="tab" aria-controls="pills-area" aria-selected="false">
                <i class="bi bi-people me-2"></i>HISTORIAL DEL ÁREA
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- HISTORIAL PERSONAL -->
        <div class="tab-pane fade show active" id="pills-mis-tareas" role="tabpanel"
            aria-labelledby="pills-mis-tareas-tab">
            <div id="contenedor-mis-completados">
                <?php if(empty($mis_completados)): ?>
                <div class="historial-empty">
                    <i class="bi bi-clock-history"></i>
                    <p>Aún no tienes tareas personales finalizadas</p>
                </div>
                <?php else: ?>
                <?php foreach($mis_completados as $pedido): ?>
                <div class="pedido-card-historial">
                    <div class="historial-header">
                        <div>
                            <div class="historial-empresa">
                                <?= esc(strtoupper($pedido['empresa_nombre'])) ?> — #REQ-<?= $pedido['id_requerimiento'] ?>
                            </div>
                            <h3 class="historial-titulo"><?= esc($pedido['titulo']) ?></h3>
                        </div>
                        <span class="historial-status">
                            <i class="bi bi-check-circle-fill"></i> FINALIZADO
                        </span>
                    </div>

                    <div class="historial-body">
                        <div class="historial-info-item">
                            <span class="historial-info-label">Servicio</span>
                            <span class="historial-info-value"><i class="bi bi-gear-fill"></i>
                                <?= esc($pedido['servicio_nombre']) ?></span>
                        </div>
                        <div class="historial-info-item">
                            <span class="historial-info-label">Completado</span>
                            <span class="historial-info-value"><i class="bi bi-calendar-check-fill"></i>
                                <?= date('d/m/Y', strtotime($pedido['fechacompletado'])) ?></span>
                        </div>
                        <div class="historial-info-item">
                            <span class="historial-info-label">Prioridad</span>
                            <span class="historial-info-value"><i class="bi bi-flag-fill"></i>
                                <?= esc($pedido['prioridad']) ?></span>
                        </div>
                    </div>

                    <div class="historial-footer">
                        <button class="btn-historial-detalle" onclick="verDetalleHistorial(<?= $pedido['id'] ?>)">
                            <i class="bi bi-eye"></i> VER DETALLE COMPLETO
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- HISTORIAL DEL ÁREA -->
        <div class="tab-pane fade" id="pills-area" role="tabpanel" aria-labelledby="pills-area-tab">
            <div id="contenedor-area-completados">
                <?php if(empty($area_completados)): ?>
                <div class="historial-empty">
                    <i class="bi bi-people-fill"></i>
                    <p>No hay tareas finalizadas en el área todavía</p>
                </div>
                <?php else: ?>
                <?php foreach($area_completados as $pedido): ?>
                <div class="pedido-card-historial">
                    <div class="historial-header">
                        <div>
                            <div class="historial-empresa">
                                <?= esc(strtoupper($pedido['empresa_nombre'])) ?> — #REQ-<?= $pedido['id_requerimiento'] ?>
                            </div>
                            <h3 class="historial-titulo"><?= esc($pedido['titulo']) ?></h3>
                        </div>
                        <span class="historial-status">
                            <i class="bi bi-check-circle-fill"></i> FINALIZADO
                        </span>
                    </div>

                    <div class="historial-body">
                        <div class="historial-info-item">
                            <span class="historial-info-label">Ejecutor</span>
                            <span class="historial-info-value"><i class="bi bi-person-fill"></i>
                                <?= esc($pedido['empleado_nombre'] ?? 'Desconocido') ?></span>
                        </div>
                        <div class="historial-info-item">
                            <span class="historial-info-label">Servicio</span>
                            <span class="historial-info-value"><i class="bi bi-gear-fill"></i>
                                <?= esc($pedido['servicio_nombre']) ?></span>
                        </div>
                        <div class="historial-info-item">
                            <span class="historial-info-label">Finalización</span>
                            <span class="historial-info-value"><i class="bi bi-calendar-check-fill"></i>
                                <?= date('d/m/Y', strtotime($pedido['fechacompletado'])) ?></span>
                        </div>
                    </div>

                    <div class="historial-footer">
                        <button class="btn-historial-detalle" onclick="verDetalleHistorial(<?= $pedido['id'] ?>)">
                            <i class="bi bi-eye"></i> VER DETALLE COMPLETO
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content modal-historial-content">
            <div class="modal-header" style="border-bottom: 1px solid #1e1e1e; padding: 20px 25px;">
                <h5 class="modal-title" id="modal-titulo-historial"
                    style="font-family: 'Bebas Neue'; color: #fff; letter-spacing: 1px; font-size: 22px;">
                    DETALLE DE HISTORIAL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-cuerpo-historial" style="padding: 25px;">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer" style="border-top: 1px solid #1e1e1e; padding: 15px 25px;">
                <button class="btn btn-dark" data-bs-dismiss="modal"
                    style="font-family: 'Bebas Neue'; letter-spacing: 1px; font-weight:500; border:1px solid #333; padding: 8px 25px;">
                    CERRAR
                </button>
            </div>
        </div>
    </div>
</div>

<?php if(false): ?>
<!-- El siguiente bloque es para que el editor reconozca variables de PHP -->
<script>
window.BASE_URL = '<?= base_url() ?>';
</script>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.BASE_URL = '<?= base_url() ?>';
</script>
<script src="<?= base_url('recursos/scripts/responsable/paginas/historial.js') ?>"></script>
<?= $this->endSection() ?>
