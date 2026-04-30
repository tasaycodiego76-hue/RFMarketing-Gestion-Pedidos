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
<div class="modal fade" id="modalDetalleMiembro" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #1a1a1a; border: 1px solid #333; border-radius: 12px; color: #f5f5f5;">
            <div class="modal-header" style="border-bottom: 1px solid #333;">
                <h5 class="modal-title" style="font-weight: 600;">
                    <span id="nombreMiembroModal" style="color: #d4af37;">Tareas del Miembro</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0" id="tablaTareasMiembro" style="background: transparent;">
                        <thead style="background: rgba(255,255,255,0.05);">
                            <tr>
                                <th class="p-3 border-secondary">Requerimiento</th>
                                <th class="p-3 border-secondary">Servicio</th>
                                <th class="p-3 border-secondary">Estado</th>
                                <th class="p-3 border-secondary">Prioridad</th>
                                <th class="p-3 border-secondary">Inicio</th>
                            </tr>
                        </thead>
                        <tbody id="bodyTareasMiembro" style="border-top: 0;">
                            <!-- Filas de tareas -->
                        </tbody>
                    </table>
                </div>
                <div id="sinTareasMiembro" class="text-center p-5 d-none text-muted">
                    <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0" style="font-size: 1.1rem;">No hay tareas asignadas actualmente.</p>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #333;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #333; border: 1px solid #444; color: #fff;">Cerrar</button>
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
