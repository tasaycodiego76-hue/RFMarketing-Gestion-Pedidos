<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/equipo.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="seccion-titulo">MI EQUIPO</div>
        <p class="mb-0" style="color:#a1a1aa; font-size:14px;">Gestiona los miembros de tu área y su carga de trabajo</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge-estado" id="contador-equipo" style="background:#3b82f6;">
            <i class="bi bi-people-fill"></i> 0 miembros
        </span>
    </div>
</div>

<!-- Grid de miembros del equipo -->
<div id="contenedor-equipo" class="row g-3">
    <!-- Miembros cargados dinámicamente -->
</div>

<!-- Estado vacío -->
<div id="estado-vacio" class="estado-vacio d-none">
    <i class="bi bi-people"></i>
    <p>No hay miembros registrados en tu área</p>
</div>

<!-- Tarjeta de info del responsable -->
<div class="card-rf mt-4">
    <div class="d-flex align-items-center gap-3">
        <div class="info-icon">
            <i class="bi bi-info-circle-fill"></i>
        </div>
        <div>
            <h6 class="mb-1">Información del Equipo</h6>
            <p class="mb-0" style="color:#a1a1aa; font-size:14px;">
                Como responsable de área, puedes asignar requerimientos a los miembros de tu equipo desde la
                <strong>Bandeja de Entrada</strong>. Los miembros marcados con
                <span class="badge-jefe">Jefe de Área</span> tienen privilegios administrativos.
            </p>
        </div>
    </div>
</div>

<script>
    const base_url = "<?= base_url() ?>";
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/responsable/paginas/equipo.js') ?>"></script>
<?= $this->endSection() ?>
