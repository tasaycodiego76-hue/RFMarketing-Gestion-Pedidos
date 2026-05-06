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
        Área: <strong class="color-amarillo"><?= esc($user['nombre_area'] ?? $user['nombre_areaagencia'] ?? 'Sin área') ?></strong>
    </p>
    <hr class="divider-rf">
    <p class="text-small-muted">
        Desde aquí podrás gestionar los requerimientos asignados a tu área y distribuir el trabajo entre tu equipo.
    </p>
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
<?= $this->endSection() ?>