<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<!-- FILTROS (ESTILO ADMIN) -->
<div class="row mb-4">
    <div class="col-md-12">
        <input type="text" id="busqueda" class="form-control" placeholder="Filtrar historial por cliente o título..." 
            style="background: var(--panel); border: 1px solid var(--borde); color: var(--texto); font-size: 12px; height: 36px; border-radius: 6px;">
    </div>
</div>

<!-- LISTADO (ESTILO ADMIN) -->
<p class="seccion-titulo">Tareas Finalizadas</p>

<div id="contenedor-historial">
    <?php if(empty($pedidos)): ?>
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 16px;">
            <i class="bi bi-clock-history" style="font-size: 40px; color: var(--texto-3); opacity: 0.3;"></i>
            <p class="mt-3" style="font-size: 11px; color: var(--texto-3); text-transform: uppercase; letter-spacing: 2px;">Sin registros históricos</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($pedidos as $pedido): ?>
                <div class="col-12 col-xl-4 mb-4">
                    <div class="emp-task-card task-done">
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="task-client">
                                <i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?>
                            </div>
                            <span class="task-status-pill pill-done">
                                FINALIZADO
                            </span>
                        </div>

                        <div class="task-title" style="font-size: 15px; min-height: 40px;"><?= esc($pedido['titulo']) ?></div>

                        <div class="task-meta" style="margin-bottom: 10px; padding-bottom: 10px;">
                            <div class="task-meta-item">
                                <i class="bi bi-calendar-check"></i> <?= isset($pedido['fechacompletado']) ? date('d M Y', strtotime($pedido['fechacompletado'])) : '---' ?>
                            </div>
                        </div>

                        <div class="task-actions">
                            <span style="font-size: 10px; color: var(--texto-3); font-weight: 700; letter-spacing: 0.5px;">ALMACENADO</span>
                            <button class="task-primary-btn btn-view" style="padding: 6px 15px; font-size: 12px;" onclick="verDetalleSolicitud(<?= $pedido['id'] ?>)">
                                <i class="bi bi-eye"></i> VER
                            </button>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/empleado/misPedidos.js') ?>"></script>
<?= $this->endSection() ?>
