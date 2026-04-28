<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/empleado/misPedidos.js') ?>"></script>
<?= $this->endSection() ?>
<?= $this->section('contenido') ?>

<!-- FILTROS  -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-8">
        <input type="text" id="busqueda" class="form-control" placeholder="Buscar por título o empresa..." 
            style="background: var(--panel); border: 1px solid var(--borde); color: var(--texto); font-size: 12px; height: 36px; border-radius: 6px;">
    </div>
    <div class="col-md-6 col-lg-4 mt-2 mt-md-0">
        <select class="form-control" style="background: var(--panel); border: 1px solid var(--borde); color: var(--texto-2); font-size: 12px; height: 36px; border-radius: 6px;">
            <option value="">TODOS LOS ESTADOS</option>
            <option value="pendiente_asignado">POR INICIAR</option>
            <option value="en_proceso">EN DESARROLLO</option>
        </select>
    </div>
</div>

<!-- LISTADO  -->
<p class="seccion-titulo">Mis Pedidos Asignados</p>

<div id="contenedor-pedidos">
    <?php if(empty($pedidos)): ?>
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 10px;">
            <i class="bi bi-inbox" style="font-size: 30px; color: var(--texto-3);"></i>
            <p class="mt-2" style="font-size: 11px; color: var(--texto-3); text-transform: uppercase;">Bandeja de entrada vacía</p>
        </div>
    <?php else: ?>
        <?php foreach($pedidos as $pedido): ?>
            <div class="pedido-card-admin" id="pedido-<?= $pedido['id'] ?>">
                <div class="pedido-header">
                    <div>
                        <div class="pedido-id"><?= esc(strtoupper($pedido['empresa_nombre'])) ?> — #REQ-<?= $pedido['id_requerimiento'] ?></div>
                        <div class="pedido-title"><?= esc($pedido['titulo']) ?></div>
                    </div>
                    <?php 
                        $statusClass = str_replace('_', '-', $pedido['estado']);
                    ?>
                    <span class="pedido-status status-<?= $statusClass ?>">
                        <i class="bi bi-circle-fill mr-1" style="font-size: 4px;"></i>
                        <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                    </span>
                </div>

                <div class="pedido-info">
                    <span><i class="bi bi-gear-fill"></i> <?= esc($pedido['servicio_nombre']) ?></span>
                    <span><i class="bi bi-flag-fill"></i> <?= strtoupper(esc($pedido['prioridad'])) ?></span>
                    <span><i class="bi bi-calendar-check"></i> <?= isset($pedido['fechafin']) ? date('d/m/Y', strtotime($pedido['fechafin'])) : '---' ?></span>
                </div>

                <div class="pedido-footer">
                    <div class="d-flex gap-2">
                        <button class="btn-outline" onclick="verDetalleSolicitud(<?= $pedido['id'] ?>)">
                            <i class="bi bi-eye mr-1"></i> VER SOLICITUD
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if($pedido['estado'] == 'pendiente_asignado'): ?>
                            <button class="btn-yellow" onclick="abrirModalAccion(<?= $pedido['id'] ?>, 'iniciar')">INICIAR TRABAJO</button>
                        <?php elseif($pedido['estado'] == 'en_proceso'): ?>
                            <button class="btn-green" onclick="abrirModalAccion(<?= $pedido['id'] ?>, 'entregar')">ENTREGAR TRABAJO</button>
                        <?php else: ?>
                            <button class="btn-outline" disabled>EN REVISIÓN</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
