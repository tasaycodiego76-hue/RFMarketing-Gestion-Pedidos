<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/equipo.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Encabezado -->
<div class="header">
    <div class="header-top">
        <div>
            <h1>MI EQUIPO</h1>
            <p>Gestiona los miembros de tu área y su carga de trabajo</p>
        </div>
        <div class="team-count" id="contador-equipo">
            👥 0 miembros
        </div>
    </div>
</div>

<!-- Grid de miembros del equipo -->
<div id="contenedor-equipo" class="team-grid">
    <!-- Miembros cargados dinámicamente -->
</div>

<!-- Estado vacío -->
<div id="estado-vacio" class="estado-vacio d-none">
    <i class="bi bi-people"></i>
    <p>No hay miembros registrados en tu área</p>
</div>

<!-- Modal Detalle Miembro -->
<div class="modal fade" id="modalDetalleMiembro" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-dark-rf">
            <div class="modal-header modal-header-rf">
                <h5 class="modal-title modal-title-rf">
                    <span id="nombreMiembroModal" class="text-oro">Tareas del Miembro</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaTareasMiembro">
                        <thead class="thead-dark-soft">
                            <tr>
                                <th class="p-3 border-secondary">Requerimiento</th>
                                <th class="p-3 border-secondary">Servicio</th>
                                <th class="p-3 border-secondary">Estado</th>
                                <th class="p-3 border-secondary">Prioridad</th>
                                <th class="p-3 border-secondary">Inicio</th>
                            </tr>
                        </thead>
                        <tbody id="bodyTareasMiembro" class="border-none">
                            <!-- Filas de tareas -->
                        </tbody>
                    </table>
                </div>
                <div id="sinTareasMiembro" class="text-center p-5 d-none text-muted">
                    <i class="bi bi-check-circle icon-large-muted"></i>
                    <p class="mt-3 mb-0 text-large-muted">No hay tareas asignadas actualmente.</p>
                </div>
            </div>
            <div class="modal-footer modal-footer-rf">
                <button type="button" class="btn btn-secondary btn-dark-rf" data-bs-dismiss="modal">Cerrar</button>
            </div>
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