<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('styles') ?>
<style>
    @keyframes pulse-yellow {
        0% { box-shadow: 0 0 0 0 rgba(245, 196, 0, 0.7); border-color: rgba(245, 196, 0, 1); }
        70% { box-shadow: 0 0 0 15px rgba(245, 196, 0, 0); border-color: rgba(245, 196, 0, 1); }
        100% { box-shadow: 0 0 0 0 rgba(245, 196, 0, 0); border-color: rgba(245, 196, 0, 0.2); }
    }
    .highlight-task {
        animation: pulse-yellow 2s infinite;
        border: 2px solid var(--amarillo) !important;
        transform: scale(1.02);
        z-index: 10;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/empleado/misPedidos.js') ?>"></script>
<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const highlightId = urlParams.get('highlight');
        
        if (highlightId) {
            const target = $('#pedido-' + highlightId);
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
                
                target.addClass('highlight-task');
                
                // Quitar el highlight después de un tiempo para que no sea molesto
                setTimeout(() => {
                    target.removeClass('highlight-task');
                }, 6000);
            }
        }
    });
</script>
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
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 16px;">
            <i class="bi bi-inbox" style="font-size: 40px; color: var(--texto-3); opacity: 0.3;"></i>
            <p class="mt-3" style="font-size: 11px; color: var(--texto-3); text-transform: uppercase; letter-spacing: 2px;">No tienes tareas pendientes</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($pedidos as $pedido): ?>
                <?php 
                    $claseStatus = ($pedido['estado'] == 'pendiente_asignado') ? 'task-new' : 
                                  (($pedido['estado'] == 'en_proceso') ? 'task-process' : 'task-revision');
                    
                    $pillStatus = ($pedido['estado'] == 'pendiente_asignado') ? 'pill-new' : 
                                 (($pedido['estado'] == 'en_proceso') ? 'pill-process' : 'pill-revision');
                    
                    $textoStatus = ($pedido['estado'] == 'pendiente_asignado') ? 'POR INICIAR' : 
                                  (($pedido['estado'] == 'en_proceso') ? 'EN CURSO' : 'REVISIÓN');
                ?>
                <div class="col-12 col-xl-6 mb-4">
                    <div class="emp-task-card <?= $claseStatus ?>" id="pedido-<?= $pedido['id'] ?>">
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="task-client">
                                <i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?>
                            </div>
                            <span class="task-status-pill <?= $pillStatus ?>">
                                <?= $textoStatus ?>
                            </span>
                        </div>

                        <div class="task-title"><?= esc($pedido['titulo']) ?></div>

                        <div class="task-meta">
                            <div class="task-meta-item">
                                <i class="bi bi-tag-fill"></i> <?= esc($pedido['servicio_nombre']) ?>
                            </div>
                            <div class="task-meta-item">
                                <i class="bi bi-calendar-event"></i> <?= isset($pedido['fechafin']) ? date('d M Y', strtotime($pedido['fechafin'])) : '---' ?>
                            </div>
                            <div class="task-meta-item">
                                <i class="bi bi-hash"></i> REQ-<?= $pedido['id_requerimiento'] ?>
                            </div>
                        </div>

                        <div class="task-actions">
                            <button class="task-primary-btn btn-view" onclick="verDetalleSolicitud(<?= $pedido['id'] ?>)">
                                <i class="bi bi-eye"></i> BRIEF
                            </button>
                            
                            <div class="d-flex gap-2">
                                <?php if($pedido['estado'] == 'pendiente_asignado'): ?>
                                    <button class="task-primary-btn btn-start" onclick="abrirModalAccion(<?= $pedido['id'] ?>, 'iniciar')">
                                        <i class="bi bi-play-fill"></i> COMENZAR
                                    </button>
                                <?php elseif($pedido['estado'] == 'en_proceso'): ?>
                                    <button class="task-primary-btn btn-deliver" onclick="abrirModalAccion(<?= $pedido['id'] ?>, 'entregar')">
                                        <i class="bi bi-cloud-arrow-up-fill"></i> ENTREGAR
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: 10px; font-weight: 700; color: var(--texto-3); letter-spacing: 1px; text-transform: uppercase;">
                                        En manos del admin <i class="bi bi-hourglass-split ml-1"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
