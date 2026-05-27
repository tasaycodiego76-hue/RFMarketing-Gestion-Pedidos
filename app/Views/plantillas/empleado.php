<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>RF Marketing — <?= esc($titulo ?? 'Empleado') ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 4.6.2 (MATCH ADMIN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Estilos Base (MATCH ADMIN) -->
    <link href="<?= base_url('recursos/styles/admin/paginas/tema.css') ?>" rel="stylesheet">
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
            <a href="<?= base_url('empleado/dashboard') ?>"
                class="nav-enlace <?= ($paginaActual == 'dashboard') ? 'activo' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>

            <p class="nav-seccion">MI TRABAJO</p>
            <a href="<?= base_url('empleado/mis_pedidos') ?>"
                class="nav-enlace <?= ($paginaActual == 'mis_pedidos') ? 'activo' : '' ?>">
                <i class="bi bi-lightning-charge"></i> Mis Pedidos
                <?php if (isset($stats['nuevos']) && $stats['nuevos'] > 0): ?>
                    <span class="nav-badge"><?= $stats['nuevos'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= base_url('empleado/historial') ?>"
                class="nav-enlace <?= ($paginaActual == 'historial') ? 'activo' : '' ?>">
                <i class="bi bi-clock-history"></i> Historial
            </a>

            <p class="nav-seccion">COMUNICACIÓN</p>
            <a href="<?= base_url('empleado/retroalimentacion') ?>"
                class="nav-enlace <?= ($paginaActual == 'retroalimentacion') ? 'activo' : '' ?>">
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
            <a href="<?= base_url('logout') ?>" class="ms-auto"
                style="color:#999; font-size: 18px; transition: color .2s;" title="Salir">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>

    </aside>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="contenedor-principal">

        <!-- BARRA SUPERIOR -->
        <header class="topbar">
            <div style="display: flex; align-items: center;">
                <button class="btn-menu-toggle d-lg-none" id="btn-menu-toggle" aria-label="Abrir menú"
                    style="background:none; border:none; color:inherit; font-size:22px; margin-right:15px;">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-titulo"><?= esc($tituloPagina ?? 'PANEL') ?></div>
            </div>
            
            <div class="ml-auto d-flex align-items-center" style="gap: 15px;">
                <!-- TITULO ÁREA (RESTORED STYLE) -->
                <div class="d-none d-md-flex align-items-center"
                    style="font-size: 19px; color: inherit; text-transform: uppercase; font-weight: 400; letter-spacing: 3px; font-family: 'Bebas Neue', sans-serif;">
                    <i class="bi bi-palette mr-3" style="color: var(--amarillo); font-size: 22px;"></i>
                    <?= esc($user['nombre_areaagencia'] ?? 'Agencia') ?>
                </div>

                <!-- BOTÓN DE TEMA (NEXT TO TITLE LIKE IMAGE) -->
                <button class="theme-toggle-btn" id="theme-toggle-btn" title="Cambiar tema">
                    <i class="bi bi-sun-fill"></i>
                </button>
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
            var sidebar = document.getElementById('sidebar');

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
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const PUSHER_KEY = '<?= env('PUSHER_KEY') ?>';
        const PUSHER_CLUSTER = '<?= env('PUSHER_CLUSTER') ?>';
        const PUSHER_CANAL = 'kanban-empleados';
    </script>
    <script src="<?= base_url('recursos/scripts/pusher-global.js') ?>"></script>
    
    <!-- Sistema de Cambio de Tema -->
    <script src="<?= base_url('recursos/scripts/cambiador-tema.js') ?>"></script>

    <script>
        const BASE_URL = '<?= base_url() ?>';
        
        // Interceptor Global para inyectar token CSRF en Fetch y jQuery AJAX
        (function() {
            // 1. Interceptar jQuery AJAX
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // 2. Interceptar Fetch API
            const originalFetch = window.fetch;
            window.fetch = async function(...args) {
                let [resource, config] = args;
                if (config && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(config.method?.toUpperCase())) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (token) {
                        config.headers = config.headers || {};
                        if (config.headers instanceof Headers) {
                            config.headers.set('X-CSRF-TOKEN', token);
                        } else {
                            config.headers['X-CSRF-TOKEN'] = token;
                        }
                    }
                }
                return originalFetch.apply(this, args);
            };
        })();

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) { registration.unregister(); }
            });
        }
    </script>
    <?= $this->renderSection('scripts') ?>

</body>

</html>