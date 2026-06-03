<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/historial.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Encabezado -->
<div class="seccion-titulo">HISTORIAL</div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0 cliente-nombre">
            <?= esc($user['nombre'] . ' ' . $user['apellidos']) ?>
        </h2>
        <p class="small mb-0 cliente-subtitulo">Proyectos completados o cancelados</p>
    </div>
    <a href="<?= base_url('cliente/mis_solicitudes') ?>" class="btn-rf">
        <i class="bi bi-briefcase"></i> Pedidos Activos
    </a>
</div>

<!-- Buscador -->
<div class="historial-search-wrap">
    <i class="bi bi-search"></i>
    <input type="text" id="buscador-historial" class="historial-search-input" placeholder="Buscar en historial...">
</div>

<!-- Contador dinámico -->
<p class="small mb-3" id="historial-counter" style="color:var(--text-muted,#888);">Cargando...</p>

<!-- Contenedor de cards -->
<div class="historial-container" id="contenedor-historial">
    <div class="spinner-hist">
        <span class="spinner-border spinner-border-sm text-warning"></span> Cargando historial...
    </div>
</div>

<!-- Contenedor de paginación -->
<div id="paginacion-historial" class="mt-4"></div>

<script>
    const base_url = "<?= base_url() ?>";
    const userId = "<?= esc($user['id']) ?>";
    const userRol = "<?= esc($user['rol']) ?>";
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/cliente/paginas/historial.js') ?>"></script>
<?= $this->endSection() ?>