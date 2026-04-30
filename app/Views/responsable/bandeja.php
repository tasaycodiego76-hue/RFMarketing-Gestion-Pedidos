<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/bandeja.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-header" content="<?= csrf_token() ?>">

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="seccion-titulo">BANDEJA DE ENTRADA</div>
        <p class="mb-0" style="color:#a1a1aa; font-size:14px;">Solicitudes de requerimientos pendientes por asignar</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge-estado" id="contador-pendientes" style="background:#f59e0b; color:#000;">
            <i class="bi bi-inbox"></i> 0 pendientes
        </span>
    </div>
</div>

<!-- Tabla de requerimientos -->
<div class="card-rf" style="overflow:hidden;">
    <div class="tabla-header-responsable">
        <div class="buscador-wrap-responsable">
            <i class="bi bi-search"></i>
            <input type="text" id="buscador-bandeja" placeholder="Buscar requerimiento..."
                class="input-buscar-responsable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="tabla-rf-responsable" id="tablaBandeja">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Servicio</th>
                    <th>Empresa</th>
                    <th>Área</th>
                    <th>Usuario</th>
                    <th>Prioridad</th>
                    <th>Fecha Solicitud</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="contenido-bandeja">
                <!-- Contenido cargado dinámicamente -->
            </tbody>
        </table>
    </div>
    <div id="estado-vacio" class="estado-vacio d-none">
        <i class="bi bi-inbox"></i>
        <p>No hay solicitudes pendientes en tu bandeja</p>
    </div>
</div>

<!-- NUEVA SECCIÓN: PENDIENTES DE REVISIÓN -->
<div class="mt-5 mb-4">
    <div class="seccion-titulo">TAREAS ENVIADAS (ESPERANDO REVISIÓN)</div>
    <p class="mb-0" style="color:#a1a1aa; font-size:14px;">Trabajos completados por el equipo que esperan aprobación final</p>
</div>

<div class="card-rf" style="overflow:hidden;">
    <div class="table-responsive">
        <table class="tabla-rf-responsable" id="tablaRevision">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Ejecutor</th>
                    <th>Empresa</th>
                    <th>Área</th>
                    <th>Usuario</th>
                    <th>Servicio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="contenido-revision">
                <!-- Contenido cargado dinámicamente -->
            </tbody>
        </table>
    </div>
    <div id="estado-vacio-revision" class="estado-vacio d-none">
        <i class="bi bi-send-check"></i>
        <p>No hay tareas pendientes de revisión en este momento</p>
    </div>
</div>

<!-- Modal Asignar Requerimiento -->
<div class="modal fade" id="modal-asignar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-rf">
            <div class="modal-header modal-rf-header">
                <div>
                    <p class="campo-label mb-1">ASIGNAR REQUERIMIENTO</p>
                    <h5 class="modal-title mb-0" id="modal-titulo-requerimiento">Asignar a miembro del equipo</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-rf-body p-4">
                <input type="hidden" id="idatencion-seleccionado">

                <!-- Info del requerimiento -->
                <div class="info-requerimiento mb-4">
                    <div class="info-req-item">
                        <span class="info-req-label">Servicio:</span>
                        <span class="info-req-valor" id="info-servicio">—</span>
                    </div>
                    <div class="info-req-item">
                        <span class="info-req-label">Empresa:</span>
                        <span class="info-req-valor" id="info-empresa">—</span>
                    </div>
                    <div class="info-req-item">
                        <span class="info-req-label">Prioridad:</span>
                        <span class="info-req-valor" id="info-prioridad">—</span>
                    </div>
                </div>

                <!-- Selector de empleado -->
                <div class="field mb-3">
                    <label class="fw-bold mb-2">SELECCIONAR MIEMBRO DEL EQUIPO</label>
                    <p class="campo-sublabel mb-3">Elige a quién asignar este requerimiento</p>

                    <div id="lista-empleados" class="lista-empleados">
                        <!-- Empleados cargados dinámicamente -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0" style="gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-rf" id="btn-confirmar-asignacion" disabled>
                    <i class="bi bi-check-lg"></i> Confirmar Asignación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalle Requerimiento -->
<div class="modal fade" id="modal-ver-detalle" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-rf">
            <div class="modal-header modal-rf-header">
                <div>
                    <p class="campo-label mb-1">DETALLE DEL REQUERIMIENTO</p>
                    <h5 class="modal-title mb-0" id="detalle-titulo-requerimiento">Detalles completos</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-rf-body p-4" id="detalle-contenido">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
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