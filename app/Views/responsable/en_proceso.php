<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('title') ?>Tareas en Proceso - Mi Equipo<?= $this->endSection() ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/en_proceso.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="container-fluid py-4">
    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="stat-label mb-1">Total Tareas</p>
                        <h3 class="stat-value mb-0" id="total-tareas">0</h3>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="stat-label mb-1">Empleados Activos</p>
                        <h3 class="stat-value mb-0" id="total-empleados">0</h3>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="stat-label mb-1">Pendientes de Asignar</p>
                        <h3 class="stat-value mb-0" id="total-alta"><?= $pendientes_asignar ?? 0 ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Lista de Empleados con sus Tareas -->
    <div class="row" id="empleados-container">
        <!-- Se cargará dinámicamente -->
    </div>
</div>

<!-- Modal para ver detalles de tarea -->
<div class="modal fade" id="modal-detalle-tarea" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-detalle-content">
            <div class="modal-header modal-detalle-header">
                <h5 class="modal-title text-white">
                    Detalles de la Tarea
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-detalle-body" id="detalle-tarea-content">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer modal-detalle-footer">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reasignación de Tarea -->
<div class="modal fade" id="modal-reasignar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-detalle-content">
            <div class="modal-header modal-detalle-header">
                <h5 class="modal-title text-white">
                    <i class="bi bi-person-gear me-2 text-warning"></i>Reasignar Tarea
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" id="reasignar-titulo-tarea" style="font-size:13px;"></p>

                <div class="mb-3">
                    <label class="form-label text-white-50 text-uppercase fw-bold" style="font-size:10px; letter-spacing:1px;">Nuevo Especialista</label>
                    <select id="select-nuevo-empleado" class="form-select bg-dark text-white border-secondary">
                        <option value="">-- Selecciona un especialista --</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label text-white-50 text-uppercase fw-bold" style="font-size:10px; letter-spacing:1px;">Motivo del Cambio <span class="text-warning">*</span></label>
                    <textarea id="input-motivo-reasignacion" class="form-control bg-dark text-white border-secondary"
                              rows="3" placeholder="Ej: El especialista tiene sobrecarga de trabajo esta semana..."
                              style="font-size:13px; resize:none;"></textarea>
                </div>
                <small class="text-muted">El motivo queda registrado en el historial de la tarea.</small>
            </div>
            <div class="modal-footer modal-detalle-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold" onclick="confirmarReasignacion()">
                    <i class="bi bi-person-check-fill me-1"></i> CONFIRMAR REASIGNACIÓN
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Input oculto para guardar el ID de la tarea en curso -->
<input type="hidden" id="reasignar-idatencion" value="">
<input type="hidden" id="reasignar-idempleado-actual" value="">

<script>
    let tareasData = [];
    const base_url = '<?= base_url(); ?>';
    const empleadosData = <?= json_encode($empleados ?? []); ?>;

    // Hacer variables disponibles globalmente
    window.base_url = base_url;
    window.empleadosData = empleadosData;
    window.tareasData = tareasData;
    window.currentUserId = <?= json_encode($user['id'] ?? 0); ?>;
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/responsable/paginas/en_proceso.js') ?>"></script>
<?= $this->endSection() ?>