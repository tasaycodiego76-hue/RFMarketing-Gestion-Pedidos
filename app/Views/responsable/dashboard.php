<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/dashboard.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Metricas - Prueba Vista -->
<div class="seccion-titulo">Resumen del Área</div>
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-naranja"><?= $porAsignar ?? 0 ?></div>
            <div class="metrica-sub">Por Asignar</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-amarillo"><?= $enProceso ?? 0 ?></div>
            <div class="metrica-sub">En Proceso</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-violeta"><?= $enRevision ?? 0 ?></div>
            <div class="metrica-sub">En Revisión</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-verde"><?= $completados ?? 0 ?></div>
            <div class="metrica-sub">Completados</div>
        </div>
    </div>

</div>

<!-- Mensaje de ¡Bienvenida (Prueba) -->
<div class="card-rf">
    <h5 class="mb-16">¡Bienvenido <?= esc($user['nombre']) ?>!</h5>
    <p class="text-muted-rf">
        Área: <strong
            class="color-amarillo"><?= esc($user['nombre_area'] ?? $user['nombre_areaagencia'] ?? 'Sin área') ?></strong>
    </p>
    <hr class="divider-rf">
    <p class="text-small-muted">
        Desde aquí podrás gestionar los requerimientos asignados a tu área y distribuir el trabajo entre tu equipo.
    </p>
</div>

<!-- Link del Chart.js (Graficos) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Metricas, Diagramas, etc sobre el Equipo -->
<div class="row g-4 mt-2">
    <!-- Productividad por empleado -->
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Productividad por Empleado</h6>
            <canvas id="graficoProductividad"></canvas>
            <p class="text-small-muted mt-3 mb-0">Compara tareas en proceso vs completadas por cada técnico.</p>
        </div>
    </div>
    <!-- Distribución de carga-->
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Distribución de Carga</h6>
            <div style="height: 350px; position: relative; width: 100%;">
                <canvas id="graficoDistribucion"></canvas>
            </div>
            <p class="text-small-muted mt-3 mb-0">Porcentaje de tareas activas asignadas actualmente.</p>
        </div>
    </div>

    <!-- Tendencia semanal-->
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Tendencia Semanal</h6>
            <canvas id="graficoTendencia"></canvas>
            <p class="text-small-muted mt-3 mb-0">Numero de tareas finalizadas en los últimos 7 días.</p>
        </div>
    </div>

    <!-- Tiempo promedio por empleado -->
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Tiempo Promedio de Resolución</h6>
            <canvas id="graficoTiempo"></canvas>
            <p class="text-small-muted mt-3 mb-0">Promedio de horas invertidas en completar cada pedido.</p>
        </div>
    </div>
</div>


<!-- Pasar variables globales al JS -->
<script>
    const BASE_URL = "<?= base_url() ?>";
    const RESPONSABLE_ID = "<?= esc($user['id']) ?>";
    const RESPONSABLE_NOMBRE = "<?= esc($user['nombre'] . ' ' . $user['apellidos']) ?>";
    const RESPONSABLE_AREA = "<?= esc($user['nombre_area'] ?? $user['nombre_areaagencia'] ?? 'Sin área') ?>";
    const RESPONSABLE_ROL = "<?= esc($user['rol']) ?>";
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/responsable/paginas/dashboard.js') ?>"></script>
<?= $this->endSection() ?>