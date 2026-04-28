<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<p class="seccion-titulo">Resumen de Rendimiento</p>

<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon"><i class="bi bi-lightning-charge-fill"></i></div>
            <div class="met-label">Nuevos Pedidos</div>
            <div class="met-num naranja"><?= $stats['nuevos'] ?? 0 ?></div>
            <div class="met-sub">Por aceptar</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="met-label">En Desarrollo</div>
            <div class="met-num morado"><?= $stats['proceso'] ?? 0 ?></div>
            <div class="met-sub">Tareas activas</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon"><i class="bi bi-search"></i></div>
            <div class="met-label">En Revisión</div>
            <div class="met-num blanco"><?= $stats['revision'] ?? 0 ?></div>
            <div class="met-sub">Esperando feedback</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="met-label">Completados</div>
            <div class="met-num verde"><?= $stats['historial'] ?? 0 ?></div>
            <div class="met-sub">Total éxitos</div>
        </div>
    </div>

</div>

<!-- SECCIÓN DE ACCIÓN INMEDIATA -->
<div class="row mb-5">
    <div class="col-lg-8">
        <p class="seccion-titulo">Tareas en Ejecución</p>
        
        <?php if(empty($pedidos_recientes)): ?>
            <div class="card p-5 text-center" style="background: rgba(0,0,0,0.1); border: 2px dashed #222; border-radius: 16px;">
                <i class="bi bi-rocket-takeoff mb-3" style="font-size: 40px; color: var(--amarillo); opacity: 0.3;"></i>
                <h6 style="letter-spacing: 2px; text-transform: uppercase; font-size: 11px; color: var(--texto-3);">No tienes tareas activas ahora</h6>
                <a href="<?= base_url('empleado/mis_pedidos') ?>" class="btn-yellow mt-3 d-inline-block" style="text-decoration:none;">EXPLORAR PROYECTOS</a>
            </div>
        <?php else: ?>
            <?php foreach(array_slice($pedidos_recientes, 0, 2) as $pedido): ?>
                <div class="emp-task-card task-process">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="task-client"><i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?></div>
                        <span class="task-status-pill pill-process">TRABAJANDO</span>
                    </div>
                    <div class="task-title" style="font-size: 20px;"><?= esc($pedido['titulo']) ?></div>
                    <div class="task-actions">
                        <div class="task-meta-item"><i class="bi bi-calendar-event"></i> Límite: <?= isset($pedido['fechafin']) ? date('d M', strtotime($pedido['fechafin'])) : '---' ?></div>
                        <button class="task-primary-btn btn-start" onclick="window.location.href='<?= base_url('empleado/mis_pedidos') ?>'">
                            CONTINUAR <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if(count($pedidos_recientes) > 2): ?>
                <div class="text-right">
                    <a href="<?= base_url('empleado/mis_pedidos') ?>" style="color:var(--amarillo); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Ver todas mis tareas (<?= count($pedidos_recientes) ?>) →</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <p class="seccion-titulo">Pendientes de Revisión</p>
        <?php if(empty($pedidos_revision)): ?>
            <div class="p-4 text-center" style="background: rgba(0,0,0,0.05); border: 1px solid #1a1a1a; border-radius: 16px;">
                <p style="font-size: 11px; color: #555; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Todo al día <i class="bi bi-check-all ml-1"></i></p>
            </div>
        <?php else: ?>
            <?php foreach(array_slice($pedidos_revision, 0, 3) as $pedido): ?>
                <div style="background: #0d0d0d; border: 1px solid #1e1e1e; border-radius: 12px; padding: 15px; margin-bottom: 12px;">
                    <div style="font-size: 10px; color: var(--texto-3); font-weight: 800; margin-bottom: 4px;"><?= esc($pedido['empresa_nombre']) ?></div>
                    <div style="font-size: 13px; color: #fff; font-weight: 600; margin-bottom: 8px;"><?= esc($pedido['titulo']) ?></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="task-status-pill pill-revision">EN REVISIÓN</span>
                        <i class="bi bi-clock-history" style="color: #444;"></i>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
