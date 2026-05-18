<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/historial.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="historial-container">
    <!-- Buscador -->
    <div class="historial-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="buscador-historial" class="historial-search-input" placeholder="Buscar en historial (título, empresa, servicio, ejecutor, prioridad)...">
    </div>

    <!-- TABS PARA NAVEGAR ENTRE HISTORIAL PERSONAL Y DE ÁREA -->
    <ul class="nav nav-pills historial-tabs" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-mis-tareas-tab" data-bs-toggle="pill"
                data-bs-target="#pills-mis-tareas" type="button" role="tab" aria-controls="pills-mis-tareas"
                aria-selected="true">
                <i class="bi bi-person me-2"></i>MI HISTORIAL PERSONAL
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-area-tab" data-bs-toggle="pill" data-bs-target="#pills-area"
                type="button" role="tab" aria-controls="pills-area" aria-selected="false">
                <i class="bi bi-people me-2"></i>HISTORIAL DEL ÁREA
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- HISTORIAL PERSONAL -->
        <div class="tab-pane fade show active" id="pills-mis-tareas" role="tabpanel"
            aria-labelledby="pills-mis-tareas-tab">
            <p class="small mb-3" id="historial-counter-personal" style="color:var(--texto-dim,#888); font-weight: 600;">Cargando...</p>
            <div id="contenedor-mis-completados">
                <div class="spinner-hist">
                    <span class="spinner-border spinner-border-sm text-warning"></span>
                    Cargando historial personal...
                </div>
            </div>
            <div id="paginacion-mis-completados" class="mt-4"></div>
        </div>

        <!-- HISTORIAL DEL ÁREA -->
        <div class="tab-pane fade" id="pills-area" role="tabpanel" aria-labelledby="pills-area-tab">
            <p class="small mb-3" id="historial-counter-area" style="color:var(--texto-dim,#888); font-weight: 600;">Cargando...</p>
            <div id="contenedor-area-completados">
                <div class="spinner-hist">
                    <span class="spinner-border spinner-border-sm text-warning"></span>
                    Cargando historial del área...
                </div>
            </div>
            <div id="paginacion-area-completados" class="mt-4"></div>
        </div>
    </div>
</div>

<!-- Modal Detalle Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content modal-historial-content modal-dark-rf">
            <div class="modal-header modal-header-historial modal-header-rf">
                <h5 class="modal-title modal-title-historial modal-title-rf" id="modal-titulo-historial">
                    DETALLE DE HISTORIAL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-historial modal-body" id="modal-cuerpo-historial">
                <!-- Contenido Renderizable -->
            </div>
            <div class="modal-footer modal-footer-historial modal-footer">
                <button class="btn btn-dark btn-close-historial" data-bs-dismiss="modal">
                    CERRAR
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.BASE_URL = '<?= base_url() ?>';
    window.MIS_COMPLETADOS = <?= json_encode($mis_completados) ?>;
    window.AREA_COMPLETADOS = <?= json_encode($area_completados) ?>;
</script>
<script src="<?= base_url('recursos/scripts/responsable/paginas/historial.js') ?>"></script>
<?= $this->endSection() ?>