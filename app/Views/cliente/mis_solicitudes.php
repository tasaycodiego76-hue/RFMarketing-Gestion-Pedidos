<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/mis-pedidos.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- ═════════════════════════════════════════════════════════════════════════════
     VISTA: Mis Pedidos (Cliente Dashboard)
     
     CONTEXTO DE DATOS RECIBIDOS DEL CONTROLADOR:
     ─────────────────────────────────────────────
     El controlador MisPedidosController::index() pasa estos datos:
     • $user: Array con datos del usuario autenticado
       - id: Identificador único del usuario en BD
       - nombre, apellidos: Datos personales
       - rol: Tipo de usuario (siempre 'cliente' en este endpoint)
     • $titulo: Título de la página ("Mis Pedidos")
     • $pendientes: Conteo de pedidos por aprobar (se actualiza dinámicamente)
     • $notif_no_leidas: Conteo de notificaciones sin leer
     
     FLUJO EN TIEMPO REAL:
     ────────────────────
     1. Página carga → mis-pedidos.js ejecuta fetch() a /cliente/pedidos/listar
     2. Este endpoint llama a MisPedidosController::listar()
     3. El controlador usa AtencionModel::getPedidosPorCliente($usuarioId)
     4. El modelo ejecuta query con INNER JOINs multinivel:
        Usuario → Área → Empresa → Requerimiento → Atención
     5. Los pedidos filtrados se retornan como JSON
     6. JavaScript renderiza la tabla dinámicamente (sin reload de página)
     
     SEGURIDAD Y PRIVACIDAD:
     ──────────────────────
     ✓ Filtrado multinivel en BD: datos solo de su empresa
     ✓ Escaping con esc() previene inyección XSS
     ✓ ID de usuario se pasa seguro al JS global
     ✓ Estados ENUM validan integridad de datos
     ═════════════════════════════════════════════════════════════════════════════ -->

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
                <!-- Skeleton mientras carga -->
                <tr id="sk-tabla">
                    <td colspan="7">
                        <div class="sk-line sk-full mt-2"></div>
                        <div class="sk-line sk-full"></div>
                        <div class="sk-line sk-med"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Selección de Servicios -->
<div class="modal fade" id="modal-nuevo-pedido" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
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
                <!-- Skeleton -->
                <div id="sk-servicios">
                    <div class="sk-line sk-full mb-3" style="height:80px; border-radius:10px;"></div>
                    <div class="sk-line sk-full" style="height:80px; border-radius:10px;"></div>
                </div>
                <!-- Cards de servicios -->
                <div id="lista-servicios" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Pasar base_url al JS -->
<script>
    const base_url = "<?= base_url() ?>/";
    const userId = "<?= esc($user['id']) ?>";
    const userRol = "<?= esc($user['rol']) ?>";
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/cliente/mis-pedidos.js') ?>"></script>
<?= $this->endSection() ?>