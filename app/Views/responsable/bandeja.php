<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/bandeja.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<meta name="csrf-token" content="<?= csrf_hash() ?>">

<div class="container-fluid p-0">
    <!-- Encabezado Principal -->
    <div class="bandeja-header-hero mb-5">
        <div class="bandeja-header-left">
            <div class="bandeja-icono-wrap">
                <i class="bi bi-inbox-fill"></i>
            </div>
            <div>
                <h1 class="bandeja-titulo-principal">Bandeja de Entrada</h1>
                <p class="bandeja-subtitulo">Gestión y asignación de nuevos requerimientos de clientes</p>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <span class="contador-hero" id="contador-pendientes">
                <i class="bi bi-inbox"></i>
                <span id="contador-num">0</span>
                <span class="contador-label">PENDIENTES</span>
            </span>
        </div>
    </div>

    <!-- Sección: Requerimientos por Asignar -->
    <div class="card-rf overflow-hidden mb-5">
        <div class="tabla-header-responsable d-flex justify-content-between align-items-center p-3">
            <div class="buscador-wrap-responsable">
                <i class="bi bi-search text-oro"></i>
                <input type="text" id="buscador-bandeja" placeholder="Filtrar por título o empresa..."
                    class="input-buscar-responsable">
            </div>
        </div>

        <div class="table-responsive">
            <table class="tabla-rf-responsable w-100" id="tablaBandeja">
                <thead>
                    <tr>
                        <th class="ps-4">Título del Requerimiento</th>
                        <th>Empresa</th>
                        <th>Usuario</th>
                        <th>Prioridad</th>
                        <th>Solicitado</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>    
                <tbody id="contenido-bandeja">
                    <!-- Dinámico -->
                </tbody>
            </table>
        </div>

        <div id="estado-vacio" class="estado-vacio py-5 d-none">
            <div class="text-center opacity-50">
                <i class="bi bi-inbox-fill display-4 mb-3 d-block text-oro"></i>
                <p class="font-bebas letter-spacing-2 font-size-18">BANDEJA VACÍA</p>
            </div>
        </div>
    </div>

    <!-- Sección: Pendientes de Revisión -->
    <div class="mt-5 pt-2">
        <div class="bandeja-header-hero bandeja-revision-header mb-4">
            <div class="bandeja-header-left">
                <div class="bandeja-icono-wrap bandeja-icono-revision">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <h2 class="bandeja-titulo-principal">Esperando Revisión</h2>
                    <p class="bandeja-subtitulo">Entregas de especialistas que requieren tu validación final</p>
                </div>
            </div>
        </div>

        <div class="card-rf overflow-hidden">
            <div class="table-responsive">
                <table class="tabla-rf-responsable w-100" id="tablaRevision">
                    <thead>
                        <tr>
                            <th class="ps-4">Proyecto / Requerimiento</th>
                            <th>Ejecutor</th>
                            <th>Empresa</th>
                            <th>Usuario</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="contenido-revision">
                        <!-- Dinámico -->
                    </tbody>
                </table>
            </div>
            <div id="estado-vacio-revision" class="estado-vacio py-5 d-none">
                <div class="text-center opacity-50">
                    <i class="bi bi-send-check display-4 mb-3 d-block"></i>
                    <p class="font-bebas letter-spacing-2 font-size-18">TODO AL DÍA</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Requerimiento -->
<div class="modal fade" id="modal-asignar" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-rf">
            <div class="modal-header modal-rf-header">
                <div>
                    <p class="hp-label text-oro mb-1">GESTIÓN DE ASIGNACIÓN</p>
                    <h5 class="modal-title font-bebas letter-spacing-1" id="modal-titulo-requerimiento">Asignar
                        Especialista</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-rf-body p-4">
                <input type="hidden" id="idatencion-seleccionado">

                <div class="assign-info-grid mb-4">
                    <div class="assign-info-item">
                        <span class="assign-info-label"><i class="bi bi-building me-1"></i>Empresa</span>
                        <span class="assign-info-val" id="info-empresa">---</span>
                    </div>
                    <div class="assign-info-item">
                        <span class="assign-info-label"><i class="bi bi-lightning-fill me-1"></i>Prioridad</span>
                        <span id="info-prioridad">---</span>
                    </div>
                    <div class="assign-info-item">
                        <span class="assign-info-label"><i class="bi bi-diagram-3 me-1"></i>Área</span>
                        <span class="assign-info-val" id="info-area">---</span>
                    </div>
                    <div class="assign-info-item">
                        <span class="assign-info-label"><i class="bi bi-person me-1"></i>Solicitado por</span>
                        <span class="assign-info-val" id="info-solicitante">---</span>
                    </div>
                </div>

                <label class="hp-label mb-3">Seleccionar Miembro del Equipo</label>
                <div id="lista-empleados" class="lista-empleados custom-scrollbar pe-1">
                    <!-- Dinámico -->
                </div>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-link text-decoration-none font-size-12"
                    data-bs-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn-rf px-4 py-2" id="btn-confirmar-asignacion" disabled>
                    <i class="bi bi-person-plus-fill me-2"></i> CONFIRMAR ASIGNACIÓN
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalle Completo -->
<div class="modal fade" id="modal-ver-detalle" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-rf">
            <div class="modal-header modal-rf-header">
                <div>
                    <p class="hp-label text-oro mb-1">EXPEDIENTE DIGITAL</p>
                    <h5 class="modal-title font-bebas letter-spacing-1" id="detalle-titulo-requerimiento">Detalle</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-rf-body p-0 custom-scrollbar" id="detalle-contenido">
                <!-- Renderizado dinámico -->
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-outline-light font-size-12 px-4" data-bs-dismiss="modal">CERRAR
                    EXPEDIENTE</button>
            </div>
        </div>
    </div>
</div>

<script>
    const base_url = "<?= base_url() ?>";
    window.base_url = base_url;
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/responsable/paginas/bandeja.js') ?>"></script>
<?= $this->endSection() ?>