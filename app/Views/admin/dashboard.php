<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
    <link href="<?= base_url('recursos/styles/admin/paginas/dashboard.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- ── SECCIÓN 1: MÉTRICAS DE GESTIÓN ── -->
<div class="dashboard-header mb-4">
    <p class="seccion-titulo">Gestión de Plataforma</p>
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card mini-card">
                <div class="mini-card-icon mini-icon-blue"><i class="bi bi-building"></i></div>
                <div class="mini-card-info">
                    <div class="mini-card-num" data-count="<?= $totalEmpresas ?? 0 ?>">0</div>
                    <div class="mini-card-label">Empresas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card mini-card">
                <div class="mini-card-icon mini-icon-cyan"><i class="bi bi-people"></i></div>
                <div class="mini-card-info">
                    <div class="mini-card-num" data-count="<?= $totalEmpleados ?? 0 ?>">0</div>
                    <div class="mini-card-label">Empleados</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card mini-card">
                <div class="mini-card-icon mini-icon-yellow"><i class="bi bi-person-badge"></i></div>
                <div class="mini-card-info">
                    <div class="mini-card-num" data-count="<?= $totalResponsables ?? 0 ?>">0</div>
                    <div class="mini-card-label">Responsables</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card mini-card">
                <div class="mini-card-icon mini-icon-green"><i class="bi bi-clipboard-check"></i></div>
                <div class="mini-card-info">
                    <div class="mini-card-num" data-count="<?= $totalPedidos ?? 0 ?>">0</div>
                    <div class="mini-card-label">Pedidos Totales</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── SECCIÓN 2: ESTADO OPERATIVO ── -->
<p class="seccion-titulo">Estado Operativo</p>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card met-card met-morado h-100">
            <div class="met-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="met-label">Por Aprobar</div>
            <div class="met-num morado" data-count="<?= $porAprobar ?? 0 ?>">0</div>
            <div class="met-sub">En espera</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card met-card met-amarillo h-100">
            <div class="met-icon"><i class="bi bi-lightning-charge-fill"></i></div>
            <div class="met-label">Activos</div>
            <div class="met-num amarillo" data-count="<?= $activos ?? 0 ?>">0</div>
            <div class="met-sub">En proceso</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card met-card met-naranja h-100">
            <div class="met-icon"><i class="bi bi-search"></i></div>
            <div class="met-label">En Revisión</div>
            <div class="met-num naranja" data-count="<?= $enRevision ?? 0 ?>">0</div>
            <div class="met-sub">Por validar</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card met-card met-verde h-100">
            <div class="met-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="met-label">Completados</div>
            <div class="met-num verde" data-count="<?= $completados ?? 0 ?>">0</div>
            <div class="met-sub">Entregados</div>
        </div>
    </div>
</div>

<!-- ── SECCIÓN 3: EMPRESAS ACTIVAS (Centro) ── -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="seccion-titulo m-0">Empresas Activas</p>
    <div class="scroll-nav">
        <button id="btnPrev" class="nav-btn" title="Anterior"><i class="bi bi-arrow-left-circle-fill"></i></button>
        <button id="btnNext" class="nav-btn" title="Siguiente"><i class="bi bi-arrow-right-circle-fill"></i></button>
    </div>
</div>

<?php if (empty($empresas)): ?>
    <div class="estado-vacio">
        <i class="bi bi-building"></i>
        <p>No hay empresas registradas todavía.</p>
    </div>
<?php else: ?>
    <div class="emp-scroll-wrap mb-4" id="empScroll">
        <?php foreach ($empresas as $idx => $empresa): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="emp-card h-100" style="animation-delay: <?= $idx * 0.08 ?>s;">
                    <div class="emp-head">
                        <div class="emp-inicial" style="background: <?= $empresa['color'] ?>; color: #000;">
                            <?= $empresa['inicial'] ?>
                        </div>
                        <div class="emp-info">
                            <div class="emp-nombre"><?= esc($empresa['nombreempresa']) ?></div>
                            <div class="emp-ruc">RUC <?= esc($empresa['ruc']) ?></div>
                        </div>
                        <?php if ($empresa['por_aprobar'] > 0): ?>
                            <div class="emp-badge ms-auto">
                                <span class="badge-punto" style="background: <?= $empresa['color'] ?>;"></span>
                                <?= $empresa['por_aprobar'] ?> nueva<?= $empresa['por_aprobar'] > 1 ? 's' : '' ?>
                            </div>
                        <?php endif ?>
                    </div>

                    <div class="emp-stats">
                        <div class="emp-stat">
                            <div class="emp-stat-num morado"><?= $empresa['por_aprobar'] ?></div>
                            <div class="emp-stat-label">Pendiente</div>
                        </div>
                        <div class="emp-stat">
                            <div class="emp-stat-num amarillo"><?= $empresa['activos'] ?></div>
                            <div class="emp-stat-label">Activos</div>
                        </div>
                        <div class="emp-stat">
                            <div class="emp-stat-num naranja"><?= $empresa['en_revision'] ?></div>
                            <div class="emp-stat-label">Revisión</div>
                        </div>
                        <div class="emp-stat">
                            <div class="emp-stat-num verde"><?= $empresa['completados'] ?></div>
                            <div class="emp-stat-label">Finalizado</div>
                        </div>
                    </div>

                    <div class="emp-areas">
                        <?php foreach ($areas as $area): ?>
                            <?php $countNuevas = $empresa['stats_areas'][$area['id']] ?? 0; ?>
                            <button class="area-btn"
                                onclick="window.location.href='<?= site_url('admin/kanban/' . $empresa['id'] . '/' . $area['id']) ?>'">
                                <?= esc($area['nombre']) ?>
                                <?php if ($countNuevas > 0): ?>
                                    <span class="area-badge-notif"><?= $countNuevas ?></span>
                                <?php endif ?>
                            </button>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>

<!-- ── SECCIÓN 4: ESTADÍSTICAS VISUALES (Pie) ── -->
<p class="seccion-titulo">Estadísticas Visuales</p>
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card graf-card h-100">
            <div class="graf-head">
                <div class="graf-titulo">Carga de Trabajo</div>
                <div class="graf-subtitulo">Requerimientos por cliente</div>
            </div>
            <div class="graf-body">
                <canvas id="chartEmpresas"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card graf-card h-100">
            <div class="graf-head">
                <div class="graf-titulo">Estado de Pedidos</div>
                <div class="graf-subtitulo">Balance operativo global</div>
            </div>
            <div class="graf-body">
                <canvas id="chartEstados"></canvas>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables globales para Chart.js
    const dataEmpresas = {
        labels: <?= json_encode(array_values(array_map(fn($e) => $e['nombreempresa'], $empresas))) ?>,
        datasets: [{
            label: 'Pedidos',
            data: <?= json_encode(array_values(array_map(fn($e) => $e['por_aprobar'] + $e['activos'] + $e['completados'], $empresas))) ?>,
            backgroundColor: <?= json_encode(array_values(array_map(fn($e) => $e['color'], $empresas))) ?>,
            borderRadius: 6,
            borderWidth: 0,
            barThickness: 25
        }]
    };

    const dataEstados = {
        labels: ['Completados', 'Activos', 'Por Aprobar', 'Revisión'],
        datasets: [{
            data: [<?= (int)$completados ?>, <?= (int)$activos ?>, <?= (int)$porAprobar ?>, <?= (int)$enRevision ?>],
            backgroundColor: ['#22c55e', '#F5C400', '#c084fc', '#f97316'],
            borderWidth: 0,
            hoverOffset: 12
        }]
    };
</script>
<script src="<?= base_url('recursos/scripts/admin/dashboard.js') ?>"></script>
<?= $this->endSection() ?>