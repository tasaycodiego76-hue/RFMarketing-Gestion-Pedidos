<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/mis-pedidos.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Encabezado -->
<div class="seccion-titulo">MIS PEDIDOS</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0" style="font-size:2rem; font-weight:800;">
            <?= esc($user['nombre'] . ' ' . $user['apellidos']) ?>
        </h2>
        <p class="small mb-0" style="color:#aaa;">Cliente — Historial de requerimientos</p>
    </div>
    <button class="btn-rf" data-bs-toggle="modal" data-bs-target="#modal-nuevo-pedido">
        <i class="bi bi-plus-lg"></i> Nuevo Pedido
    </button>
</div>

<!-- Métricas -->
<div class="seccion-titulo">RESUMEN</div>
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">Por Aprobar</div>
            <div class="met-num amarillo" id="cnt-por-aprobar">—</div>
            <div class="met-sub">Pendientes de revisión</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">En Proceso</div>
            <div class="met-num azul" id="cnt-en-proceso">—</div>
            <div class="met-sub">En curso</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">Completados</div>
            <div class="met-num verde" id="cnt-completado">—</div>
            <div class="met-sub">Total histórico</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">Total</div>
            <div class="met-num" style="color:#f0f0f0" id="cnt-total">—</div>
            <div class="met-sub">Todos los pedidos</div>
        </div>
    </div>
</div>

<!-- Tabla de pedidos -->
<div class="seccion-titulo">TODOS LOS PEDIDOS</div>
<div class="card" style="overflow:hidden;">
    <div class="tabla-header">
        <div class="buscador-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="buscador" placeholder="Buscar pedido..." class="input-buscar">
        </div>
    </div>
    <div class="table-responsive">
        <table class="tabla-rf" id="tablaPedidos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="content-pedidos">
                <!-- Contenido de los Pedidos -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Selección de Servicios -->
<div class="modal fade" id="modal-nuevo-pedido" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-rf">
            <div class="modal-header modal-rf-header">
                <div>
                    <p class="campo-label mb-1">NUEVO PEDIDO</p>
                    <h5 class="modal-title mb-0">Selecciona el tipo de servicio</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-rf-body p-4">
                <!-- Cards de servicios -->
                <div id="lista-servicios"></div>
            </div>
        </div>
    </div>
</div>

<!-- Pasar base_url al JS -->
<script>
    const base_url = "<?= base_url() ?>";
    const userId = "<?= esc($user['id']) ?>";
    const userRol = "<?= esc($user['rol']) ?>";
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/cliente/mis-pedidos.js') ?>"></script>
<?= $this->endSection() ?>