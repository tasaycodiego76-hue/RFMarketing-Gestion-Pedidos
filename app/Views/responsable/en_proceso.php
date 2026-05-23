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

<!-- Modal para realizar entrega de tarea (Idéntico al del empleado) -->
<div class="modal fade" id="modal-entregar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-entrega-content">
            <div class="modal-header modal-entrega-header">
                <h5 class="modal-title text-white">
                    <i class="bi bi-cloud-arrow-up me-2 text-warning"></i>ENVIAR TRABAJO TERMINADO
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-entrega-responsable">
                    <input type="hidden" id="entrega-idatencion" value="">
                    
                    <div class="mb-4">
                        <label class="form-label text-white-50 text-uppercase fw-bold" style="font-size:10px; letter-spacing:1px; display:block; margin-bottom:8px;">Enlace del entregable (Drive, Canva, Wetransfer, etc.)</label>
                        <div class="input-group input-group-entrega">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" id="entrega-url" class="form-control text-white border-0 bg-transparent" placeholder="https://..." style="box-shadow:none !important; padding-left: 0;">
                        </div>
                        <small class="text-white-50 d-block mt-2" style="font-size:10px;"><i class="bi bi-info-circle me-1"></i> El enlace debe comenzar con http:// o https://</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white-50 text-uppercase fw-bold" style="font-size:10px; letter-spacing:1px; display:block; margin-bottom:8px;">Cargar Archivos Directos (Opcional)</label>
                        <div class="upload-area-simple w-100" id="area-subida-entrega" style="cursor:pointer;">
                            <i class="bi bi-cloud-plus-fill mb-2 text-warning fs-3 d-block"></i>
                            <span class="fw-bold text-white fs-7">Click para agregar archivos</span>
                            <p class="text-white-50 fs-8 mb-0 mt-1">Puedes seleccionar varios archivos (Imágenes, PDF, etc.)</p>
                        </div>
                        <input type="file" id="entrega-archivos" class="d-none" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.zip">
                        <div id="lista-archivos-entrega" class="mt-3 d-flex flex-column gap-2"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-white-50 text-uppercase fw-bold" style="font-size:10px; letter-spacing:1px; display:block; margin-bottom:8px;">Mensaje para el administrador</label>
                        <textarea id="entrega-notas" class="form-control text-white" rows="3" placeholder="Describe detalles sobre la entrega o instrucciones especiales..." style="box-shadow:none !important; resize:none;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer modal-entrega-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-cancelar fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-entregar fw-bold" onclick="confirmarEntregaResponsable()">FINALIZAR Y ENTREGAR</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de entrega -->
<div class="modal fade" id="modal-confirmar-entrega" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #161616; border: 1px solid #333; color: #ccc; border-radius: 20px;">
            <div class="modal-header" style="background: linear-gradient(90deg, #141414 0%, #111 100%); border-bottom: 1px solid #222; padding: 20px 28px;">
                <h5 class="modal-title" style="font-family: 'Bebas Neue', sans-serif; font-size: 24px; letter-spacing: 1.5px; color: #f5c400;">
                    <i class="bi bi-question-circle me-2"></i>CONFIRMAR ENTREGA
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p style="color: #ccc; font-size: 16px;">¿Estás seguro de que deseas enviar esta entrega? Asegúrate de que todos los datos sean correctos.</p>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2" style="background: #111; border-top: 1px solid #222; padding: 15px 20px;">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal" style="background: #333; border: 1px solid #444; color: #fff; padding: 8px 24px; border-radius: 8px;">CANCELAR</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="ejecutarEntregaConfirmada()" style="background: #f5c400; border: none; color: #000; padding: 8px 24px; border-radius: 8px;">CONFIRMAR</button>
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