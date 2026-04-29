<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>RF Marketing — <?= esc($titulo ?? 'Empleado') ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 4.6.2 (MATCH ADMIN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Estilos Base (MATCH ADMIN) -->
    <link href="<?= base_url('recursos/styles/empleado/plantilla/empleado.css') ?>" rel="stylesheet">

    <?= $this->renderSection('styles') ?>
</head>

<body>

    <!-- BARRA LATERAL -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <div class="marca">RF</div>
            <div class="subtitulo">Marketing S.A.C.</div>
        </div>

        <nav>
            <p class="nav-seccion">PRINCIPAL</p>
            <a href="<?= base_url('empleado/dashboard') ?>" class="nav-enlace <?= ($paginaActual == 'dashboard') ? 'activo' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>

            <p class="nav-seccion">MI TRABAJO</p>
            <a href="<?= base_url('empleado/mis_pedidos') ?>" class="nav-enlace <?= ($paginaActual == 'mis_pedidos') ? 'activo' : '' ?>">
                <i class="bi bi-lightning-charge"></i> Mis Pedidos
                <?php if (isset($stats['nuevos']) && $stats['nuevos'] > 0): ?>
                    <span class="nav-badge"><?= $stats['nuevos'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= base_url('empleado/historial') ?>" class="nav-enlace <?= ($paginaActual == 'historial') ? 'activo' : '' ?>">
                <i class="bi bi-clock-history"></i> Historial
            </a>

            <p class="nav-seccion">COMUNICACIÓN</p>
            <a href="<?= base_url('empleado/retroalimentacion') ?>" class="nav-enlace <?= ($paginaActual == 'retroalimentacion') ? 'activo' : '' ?>">
                <i class="bi bi-chat-left-text"></i> Retroalimentación
                <?php if (isset($stats['retro_count']) && $stats['retro_count'] > 0): ?>
                    <span class="nav-badge" style="background:#ef4444;"><?= $stats['retro_count'] ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-usuario">
            <?php
            $nombre = $user['nombre'] ?? 'Empleado';
            $apellidos = $user['apellidos'] ?? '';
            $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellidos, 0, 1));
            ?>
            <div class="usuario-avatar"><?= $iniciales ?></div>
            <div>
                <div class="usuario-nombre"><?= esc($nombre) ?></div>
                <div class="usuario-rol">Empleado</div>
            </div>
            <a href="<?= base_url('logout') ?>" class="ms-auto" style="color:#999; font-size: 18px; transition: color .2s;" title="Salir">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>

    </aside>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="contenedor-principal">

        <!-- BARRA SUPERIOR -->
        <header class="topbar">
            <button class="btn-menu-toggle d-lg-none" id="btn-menu-toggle" aria-label="Abrir menú" style="background:none; border:none; color:#fff; font-size:22px; margin-right:15px;">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-titulo"><?= esc($tituloPagina ?? 'PANEL') ?></div>
            <div class="ml-auto d-flex align-items-center" style="font-size: 10px; color: var(--texto-3); text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
                <i class="bi bi-palette mr-2" style="color: var(--amarillo);"></i>
                <?= esc($user['nombre_areaagencia'] ?? 'Agencia') ?>
            </div>
        </header>

        <!-- CONTENIDO -->
        <main class="contenido">
            <?= $this->renderSection('contenido') ?>
        </main>

    </div>

    <!-- Script para Toggle Sidebar (Copied from Admin) -->
    <script>
        (function () {
            var btnToggle = document.getElementById('btn-menu-toggle');
            var sidebar   = document.getElementById('sidebar');

            if (btnToggle) {
                btnToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('abierto');
                });
            }
        })();
    </script>

    <!-- Modal genérico (ID "modal" - CLON ADMIN) -->
    <div class="modal fade" id="modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content kb-modal">
                <div class="modal-header kb-modal-header">
                    <h6 class="modal-title" id="modal-titulo"></h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="modal-cuerpo"></div>
                <div class="modal-footer kb-modal-footer gap-2" id="modal-pie"></div>
            </div>
        </div>
    </div>

    <!-- Scripts Base -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BASE_URL = '<?= base_url() ?>';
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    <?= $this->renderSection('scripts') ?>

</body>

</html>
