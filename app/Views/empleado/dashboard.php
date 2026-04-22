<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<!-- SECCIÓN: RESUMEN (ESTILO ADMIN) -->
<p class="seccion-titulo">Métricas de Rendimiento</p>

<div class="row no-gutters mb-4" style="margin: 0 -5px;">
    <div class="col-6 col-md-3 px-1 mb-2 mb-md-0">
        <div class="card p-3 h-100 dash-card">
            <div class="met-label">NUEVOS</div>
            <div class="met-num naranja"><?= $stats['nuevos'] ?? 0 ?></div>
            <div class="met-sub">Por aceptar inicio</div>
        </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-2 mb-md-0">
        <div class="card p-3 h-100 dash-card">
            <div class="met-label">EN DESARROLLO</div>
            <div class="met-num morado"><?= $stats['proceso'] ?? 0 ?></div>
            <div class="met-sub">Tareas activas</div>
        </div>
    </div>

    <div class="col-6 col-md-3 px-1">
        <div class="card p-3 h-100 dash-card">
            <div class="met-label">EN REVISIÓN</div>
            <div class="met-num blanco"><?= $stats['revision'] ?? 0 ?></div>
            <div class="met-sub">Esperando aprobación</div>
        </div>
    </div>

    <div class="col-6 col-md-3 px-1">
        <div class="card p-3 h-100 dash-card">
            <div class="met-label">COMPLETADOS</div>
            <div class="met-num verde"><?= $stats['historial'] ?? 0 ?></div>
            <div class="met-sub">Total histórico</div>
        </div>
    </div>
</div>

<!-- SECCIÓN: REVISIÓN -->
<p class="seccion-titulo">Pendientes de Revisión</p>

<div class="card p-3 mb-4">
    <?php if(empty($pedidos_revision)): ?>
        <div class="text-center py-3" style="color: var(--texto-3);">
            <i class="bi bi-check-circle" style="font-size: 24px; color: var(--amarillo); opacity: .2;"></i>
            <p class="mt-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">No hay entregas en revisión</p>
        </div>
    <?php else: ?>
        <div class="pedidos-list">
            <?php foreach($pedidos_revision as $pedido): ?>
                <div class="pedido-card-admin" style="border-left: 4px solid var(--amarillo);">
                    <div class="pedido-header">
                        <div>
                            <div class="pedido-id">#REQ-<?= $pedido['id_requerimiento'] ?></div>
                            <div class="pedido-title"><?= esc($pedido['titulo']) ?></div>
                        </div>
                        <span class="pedido-status status-en-revision">
                            <i class="bi bi-clock-fill mr-2" style="font-size: 5px;"></i>
                            EN REVISIÓN
                        </span>
                    </div>
                    <div class="pedido-info">
                        <span><i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?></span>
                        <span><i class="bi bi-calendar-check"></i> Entregado: <?= date('d/m/Y', strtotime($pedido['fechafin'])) ?></span>
                    </div>
                    <div class="pedido-footer">
                        <span style="color: var(--amarillo); font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;">
                            Esperando aprobación
                        </span>
                        <button class="btn-outline" onclick="window.location.href='<?= base_url('empleado/mis_pedidos') ?>'">
                            VER DETALLES <i class="bi bi-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- SECCIÓN: ACTIVIDAD (ESTILO ADMIN) -->
<p class="seccion-titulo">Tareas en Ejecución</p>

<div class="card p-3">
    <?php if(empty($pedidos_recientes)): ?>
        <div class="text-center py-5" style="color: var(--texto-3);">
            <i class="bi bi-clipboard-x" style="font-size: 32px; color: var(--amarillo); opacity: .2;"></i>
            <p class="mt-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Sin tareas activas</p>
        </div>
    <?php else: ?>
        <div class="pedidos-list">
            <?php foreach($pedidos_recientes as $pedido): ?>
                <div class="pedido-card-admin">
                    <div class="pedido-header">
                        <div>
                            <div class="pedido-id">#REQ-<?= $pedido['id_requerimiento'] ?></div>
                            <div class="pedido-title"><?= esc($pedido['titulo']) ?></div>
                        </div>
                        <?php 
                            $statusClass = str_replace('_', '-', $pedido['estado']);
                        ?>
                        <span class="pedido-status status-<?= $statusClass ?>">
                            <i class="bi bi-circle-fill mr-2" style="font-size: 5px;"></i>
                            <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                        </span>
                    </div>
                    <div class="pedido-info">
                        <span><i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?></span>
                        <span><i class="bi bi-calendar-event"></i> ENTREGA: <?= isset($pedido['fechafin']) ? date('d/m/Y', strtotime($pedido['fechafin'])) : '---' ?></span>
                    </div>
                    <div class="pedido-footer">
                        <span style="color: var(--amarillo); font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;">
                            Prioridad <?= esc($pedido['prioridad']) ?>
                        </span>
                        <button class="btn-yellow" onclick="window.location.href='<?= base_url('empleado/mis_pedidos') ?>'">
                            VER TAREA <i class="bi bi-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .naranja { color: #fff; } /* Admin style for 'naranja' var which is actually white/orange text */
</style>

<?= $this->endSection() ?>
