<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
 <link href="<?= base_url('recursos/styles/admin/paginas/dashboard.css') ?>" rel="stylesheet">
    <link href="<?= base_url('recursos/styles/admin/paginas/usuarios.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/dashboard.js') ?>"></script>
<?= $this->endSection() ?>
<?= $this->section('contenido') ?>

<p class="seccion-titulo">Resumen</p>
<div class="row g-3 mb-3">

    <div class="col-6 col-md-3">
        <div class="card met-card met-morado h-100">
            <div class="met-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="met-label">Por Aprobar</div>
            <div class="met-num morado" data-count="<?= $porAprobar ?? 0 ?>">0</div>
            <div class="met-sub">Esperando tu decisión</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card met-amarillo h-100">
            <div class="met-icon"><i class="bi bi-lightning-charge-fill"></i></div>
            <div class="met-label">Activos</div>
            <div class="met-num amarillo" data-count="<?= $activos ?? 0 ?>">0</div>
            <div class="met-sub">En manos del empleado</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card met-naranja h-100">
            <div class="met-icon"><i class="bi bi-search"></i></div>
            <div class="met-label">En Revisión</div>
            <div class="met-num naranja" data-count="<?= $enRevision ?? 0 ?>">0</div>
            <div class="met-sub">Esperando tu revisión</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card met-verde h-100">
            <div class="met-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="met-label">Completados</div>
            <div class="met-num verde" data-count="<?= $completados ?? 0 ?>">0</div>
            <div class="met-sub">Total histórico</div>
        </div>
    </div>

</div>

<!-- PEDIDOS EN REVISIÓN -->
<?php if (!empty($pedidos_revision)): ?>
<p class="seccion-titulo">Pendientes de Revisión</p>
<div class="row g-3 mb-4">
    <?php foreach ($pedidos_revision as $idx => $pedido): ?>
        <div class="col-12">
            <div class="card revision-card" style="animation-delay: <?= $idx * 0.08 ?>s;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="revision-badge badge" style="background: #8b5cf6; color: white;">#REQ-<?= $pedido['id_requerimiento'] ?></span>
                                <span class="revision-badge badge bg-warning text-dark">EN REVISIÓN</span>
                            </div>
                            <h6 class="revision-titulo mb-2"><?= esc($pedido['titulo_requerimiento']) ?></h6>
                            <div class="d-flex flex-wrap gap-3 revision-meta">
                                <span><i class="bi bi-person-fill"></i> <?= esc($pedido['empleado_nombre'] . ' ' . $pedido['empleado_apellidos']) ?></span>
                                <span><i class="bi bi-briefcase-fill"></i> <?= esc($pedido['area_nombre'] ?? 'Sin área') ?></span>
                                <span><i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($pedido['fechafin'])) ?></span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <button class="btn btn-sm btn-revisar" onclick="window.location.href='<?= base_url('admin/kanban') ?>'">
                                <i class="bi bi-eye-fill"></i> Revisar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- EMPRESAS -->
<p class="seccion-titulo">Empresas</p>

<?php if (empty($empresas)): ?>
    <div class="estado-vacio">
        <i class="bi bi-building"></i>
        <p>No hay empresas registradas todavía.</p>
    </div>
<?php else: ?>

    <div class="emp-scroll-wrap mb-1" id="empScroll">
        <?php foreach ($empresas as $idx => $empresa): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="emp-card h-100" <?= $empresa['color'] ?>;" style="animation-delay: <?= $idx * 0.08 ?>s;">
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
                            <div class="emp-stat-label">Por Aprobar</div>
                        </div>
                        <div class="emp-stat">
                            <div class="emp-stat-num amarillo"><?= $empresa['activos'] ?></div>
                            <div class="emp-stat-label">Activos</div>
                        </div>
                        <div class="emp-stat">
                            <div class="emp-stat-num naranja"><?= $empresa['en_revision'] ?></div>
                            <div class="emp-stat-label">En Revisión</div>
                        </div>
                        <div class="emp-stat">
                            <div class="emp-stat-num verde"><?= $empresa['completados'] ?></div>
                            <div class="emp-stat-label">Completados</div>
                        </div>
                    </div>

                    <div class="emp-areas">
                        <?php foreach ($areas as $area): ?>
                            <!-- Indicador de pasos  Al hacer click en un área, manda el id de la empresa y el id del área-->
                            <button class="area-btn"
                                onclick="window.location.href='<?= site_url('admin/kanban/' . $empresa['id'] . '/' . $area['id']) ?>'">
                                <?= esc($area['nombre']) ?>
                            </button>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>


<p class="seccion-titulo">Estadísticas</p>

<div class="row g-3 pb-4 stats-row">

    <!-- Barras por empresa -->
    <div class="col-12 col-lg-7">
        <div class="card graf-card h-100 d-flex flex-column">
            <div class="graf-titulo">Pedidos por empresa</div>
            <div class="graf-subtitulo">Distribución total de requerimientos</div>
            <div class="barras-container">
                <div class="barras-grid">
                    <div class="barras-grid-line"></div>
                    <div class="barras-grid-line"></div>
                    <div class="barras-grid-line"></div>
                    <div class="barras-grid-line"></div>
                </div>
                <div class="barras-wrap" style="overflow-x:auto; flex:1; align-items:flex-end; position:relative; z-index:1;">
                    <?php
                    $totales = array_map(fn($e) => $e['por_aprobar'] + $e['activos'] + $e['completados'], $empresas);
                    $max     = max(1, ...$totales);
                    foreach ($empresas as $i => $e):
                        $h = round($totales[$i] / $max * 100);
                        $delay = 0.15 + ($i * 0.1);
                    ?>
                    <div class="barra-col">
                        <div class="barra-num" style="color:<?= $e['color'] ?>; animation-delay: <?= $delay + 0.3 ?>s;"><?= $totales[$i] ?></div>
                        <div class="barra-fill" style="height:<?= $h ?>%;background: linear-gradient(180deg, <?= $e['color'] ?>, <?= $e['color'] ?>88); opacity:.9; animation-delay: <?= $delay ?>s;"></div>
                        <div class="barra-label"><?= esc($e['nombreempresa']) ?></div>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Donut estado general -->
    <div class="col-12 col-lg-5">
        <div class="card graf-card h-100 d-flex flex-column justify-content-center">
            <div class="graf-titulo">Estado general</div>
            <div class="graf-subtitulo">Proporción de pedidos por estado</div>
            <div class="donut-wrap">
                <?php
                $offset    = 0;
                $segmentos = [
                    ['color' => '#22c55e', 'pct' => $pctCompletados, 'label' => 'Completados'],
                    ['color' => '#F5C400', 'pct' => $pctActivos,     'label' => 'Activos'],
                    ['color' => '#c084fc', 'pct' => $pctPorAprobar,  'label' => 'Por Aprobar'],
                ];
                ?>
                <div class="donut-container">
                    <svg class="donut-svg" width="120" height="120" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#1a1a1a" stroke-width="14"/>
                        <?php foreach ($segmentos as $si => $s): ?>
                            <circle class="donut-segment" cx="50" cy="50" r="38" fill="none"
                                stroke="<?= $s['color'] ?>" stroke-width="14"
                                stroke-linecap="round"
                                stroke-dasharray="<?= $s['pct'] * 2.39 ?> 239"
                                stroke-dashoffset="-<?= $offset ?>"
                                transform="rotate(-90 50 50)"
                                style="animation-delay: <?= 0.3 + ($si * 0.15) ?>s;"/>
                            <?php $offset += $s['pct'] * 2.39 ?>
                        <?php endforeach ?>
                        <text x="50" y="45" text-anchor="middle" fill="#fff" class="donut-center-text" font-size="20"><?= $totalPedidos ?></text>
                        <text x="50" y="58" text-anchor="middle" fill="#777" font-size="8" font-weight="600" letter-spacing="2">PEDIDOS</text>
                    </svg>
                </div>
                <div class="donut-leyenda">
                    <?php foreach ($segmentos as $s): ?>
                    <div class="leyenda-fila">
                        <span class="leyenda-punto" style="background:<?= $s['color'] ?>"></span>
                        <span class="leyenda-label" style="color:#ccc"><?= $s['label'] ?></span>
                        <span class="leyenda-pct"><?= $s['pct'] ?>%</span>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>