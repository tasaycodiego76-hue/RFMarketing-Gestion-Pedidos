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
<div class="row g-2 mb-3">

    <div class="col-6 col-md-3">
        <div class="card p-3 h-100">
            <div class="met-label">Por Aprobar</div>
            <div class="met-num morado"><?= $porAprobar ?? 0 ?></div>
            <div class="met-sub">Esperando tu decisión</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card p-3 h-100">
            <div class="met-label">Activos</div>
            <div class="met-num amarillo"><?= $activos ?? 0 ?></div>
            <div class="met-sub">En manos del empleado</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card p-3 h-100">
            <div class="met-label">En Revisión</div>
            <div class="met-num naranja"><?= $enRevision ?? 0 ?></div>
            <div class="met-sub">Esperando tu revisión</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card p-3 h-100">
            <div class="met-label">Completados</div>
            <div class="met-num verde"><?= $completados ?? 0 ?></div>
            <div class="met-sub">Total histórico</div>
        </div>
    </div>

</div>

<!-- EMPRESAS -->
<p class="seccion-titulo">Empresas</p>

<?php if (empty($empresas)): ?>
    <div class="estado-vacio">
        <i class="bi bi-building"></i>
        <p>No hay empresas registradas todavía.</p>
    </div>
<?php else: ?>

    <div class="emp-scroll-wrap mb-1" id="empScroll">
        <?php foreach ($empresas as $empresa): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="emp-card h-100" <?= $empresa['color'] ?>;">
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

<div class="row g-2 pb-4">

    <!-- Barras por empresa -->
    <div class="col-7">
        <div class="card p-3 h-100 d-flex flex-column">
            <div class="graf-titulo">Pedidos por empresa</div>
            <div class="barras-wrap" style="overflow-x:auto; flex:1; align-items:flex-end;">
                <?php
                $totales = array_map(fn($e) => $e['por_aprobar'] + $e['activos'] + $e['completados'], $empresas);
                $max     = max(1, ...$totales);
                foreach ($empresas as $i => $e):
                    $h = round($totales[$i] / $max * 100);
                ?>
                <div class="barra-col" style="min-width:50px;">
                    <div class="barra-num" style="color:<?= $e['color'] ?>"><?= $totales[$i] ?></div>
                    <div class="barra-fill" style="height:<?= $h ?>%;background:<?= $e['color'] ?>"></div>
                    <div class="barra-label"><?= esc($e['nombreempresa']) ?></div>
                </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <!-- Donut estado general -->
    <div class="col-5">
        <div class="card p-3 h-100 d-flex flex-column justify-content-center">
            <div class="graf-titulo">Estado general</div>
            <div class="d-flex align-items-center justify-content-center gap-3">
                <?php
                $offset    = 0;
                $segmentos = [
                    ['color' => '#22c55e', 'pct' => $pctCompletados, 'label' => 'Completados'],
                    ['color' => '#F5C400', 'pct' => $pctActivos,     'label' => 'Activos'],
                    ['color' => '#c084fc', 'pct' => $pctPorAprobar,  'label' => 'Por Aprobar'],
                ];
                ?>
                <svg width="86" height="86" viewBox="0 0 100 100" style="flex-shrink:0">
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#1e1e1e" stroke-width="13"/>
                    <?php foreach ($segmentos as $s): ?>
                        <circle cx="50" cy="50" r="38" fill="none"
                            stroke="<?= $s['color'] ?>" stroke-width="13"
                            stroke-dasharray="<?= $s['pct'] * 2.39 ?> 239"
                            stroke-dashoffset="-<?= $offset ?>"
                            transform="rotate(-90 50 50)"/>
                        <?php $offset += $s['pct'] * 2.39 ?>
                    <?php endforeach ?>
                    <text x="50" y="46" text-anchor="middle" fill="#fff" font-family="Bebas Neue" font-size="15"><?= $totalPedidos ?></text>
                    <text x="50" y="57" text-anchor="middle" fill="#888" font-size="7">TOTAL</text>
                </svg>
                <div class="donut-leyenda">
                    <?php foreach ($segmentos as $s): ?>
                    <div class="leyenda-fila">
                        <span class="leyenda-punto" style="background:<?= $s['color'] ?>"></span>
                        <span style="color:#eee"><?= $s['label'] ?></span>
                        <span class="leyenda-pct"><?= $s['pct'] ?>%</span>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>