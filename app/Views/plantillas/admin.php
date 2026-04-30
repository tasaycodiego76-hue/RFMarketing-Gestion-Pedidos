<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>RF Marketing — <?= esc($titulo ?? 'Admin') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
</head>

<body>

    <!-- OVERLAY PARA SIDEBAR EN MÓVIL -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- BARRA LATERAL -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <div class="marca">RF</div>
            <div class="subtitulo">Marketing S.A.C.</div>
        </div>

        <nav>
            <p class="nav-seccion">PRINCIPAL</p>
            <a href="<?= site_url('admin/dashboard') ?>"
                class="nav-enlace <?= ($paginaActual == 'dashboard') ? 'activo' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>

            <p class="nav-seccion">EMPRESAS</p>

            <div class="nav-item-dropdown">
                <div class="nav-enlace" id="btn-empresas-toggle" style="cursor:pointer;">
                    <i class="bi bi-building"></i>
                    <span>Gestionar Empresas</span>
                    <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                </div>

                <div class="nav-sub-menu <?= ($paginaActual == 'todas_empresas' || $paginaActual == 'kanban') ? 'show' : '' ?>"
                    id="menu-empresas">
                    <a href="<?= site_url('admin/empresas') ?>"
                        class="nav-enlace sub-enlace <?= ($paginaActual == 'todas_empresas') ? 'activo' : '' ?>">
                        <i style="font-size: 10px;"></i> Todas las Empresas
                    </a>
                    <?php foreach ($empresas ?? [] as $emp): ?>
                        <?php
                        $estaActiva = ($emp['estado'] === true || $emp['estado'] === 't');
                        ?>
                        <a href="<?= site_url('admin/kanban/' . $emp['id'] . '/1') ?>" id="sidebar-item-<?= $emp['id'] ?>"
                            class="nav-enlace sub-enlace <?= (!$estaActiva) ? 'd-none' : '' ?>">
                            <i class="bi bi-circle-fill nav-punto"></i>
                            <span class="text-truncate"><?= esc($emp['nombreempresa']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="nav-seccion">GESTIÓN</p>
            <a href="<?= site_url('admin/usuarios') ?>"
                class="nav-enlace <?= ($paginaActual == 'usuarios') ? 'activo' : '' ?>">
                <i class="bi bi-people"></i> Usuarios
            </a>
            <a href="<?= site_url('admin/areas') ?>"
                class="nav-enlace <?= ($paginaActual == 'areas') ? 'activo' : '' ?>">
                <i class="bi bi-diagram-3"></i> Áreas
            </a>
            <a href="<?= site_url('admin/empresas') ?>"
                class="nav-enlace <?= ($paginaActual == 'empresas') ? 'activo' : '' ?>">
                <i class="bi bi-building"></i> Empresas
            </a>

            <p class="nav-seccion">ADMINISTRACIÓN</p>
            <a href="<?= site_url('admin/historial') ?>"
                class="nav-enlace <?= ($paginaActual == 'historial') ? 'activo' : '' ?>">
                <i class="bi bi-clock-history"></i> Historial
            </a>
        </nav>

        <div class="sidebar-usuario">
            <div class="usuario-avatar">AD</div>
            <div>
                <div class="usuario-nombre">Administrador</div>
                <div class="usuario-rol">Admin</div>
            </div>

            <a href="<?= site_url('logout') ?>" class="ms-auto"
                style="color:#999; font-size: 18px; transition: color .2s;" title="Salir">

                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>

    </aside>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="contenedor-principal">

        <!-- BARRA SUPERIOR -->
        <header class="topbar">
            <button class="btn-menu-toggle" id="btn-menu-toggle" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-titulo"><?= esc($tituloPagina ?? 'PANEL') ?></div>
        </header>

        <!-- CONTENIDO -->
        <main class="contenido">
            <?= $this->renderSection('contenido') ?>
        </main>

    </div>

    <!-- Modal genérico (se usa desde JS) -->
    <div class="modal fade" id="modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modal-titulo"></h6>
                    <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-cuerpo"></div>
                <div class="modal-footer gap-2" id="modal-pie"></div>
            </div>
        </div>
    </div>

    <!-- ═══ MODAL VER DETALLE (Bootstrap 4) — GLOBAL ═══ -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content kb-modal" style="background:#111; border:1px solid #222;">
                <div class="modal-header kb-modal-header"
                    style="background:#0a0a0a; border-bottom:1px solid #1a1a1a; padding: 15px 25px;">
                    <h6 class="modal-title" id="detalle-titulo"
                        style="font-family:'Bebas Neue'; letter-spacing:1px; color:#F5C400; font-size:22px;">DETALLE
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detalle-cuerpo" style="padding:0;">
                    Cargando...
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ MODAL RETROALIMENTACIÓN (Admin -> Empleado) — GLOBAL ═══ -->
    <div class="modal fade" id="modalRetro" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content kb-modal" style="background:#111; border:1px solid #222;">
                <div class="modal-header kb-modal-header" style="background:#0a0a0a; border-bottom:1px solid #1a1a1a;">
                    <h6 class="modal-title" style="color: #F5C400;"><i class="bi bi-chat-left-text mr-2"></i>Enviar a
                        Corrección</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="retro-idatencion">
                    <p style="font-size: 11px; color: #888; margin-bottom: 15px; line-height: 1.4;">
                        Por favor, indica los puntos específicos que el empleado debe mejorar o corregir en este pedido.
                    </p>
                    <div class="form-group">
                        <label
                            style="font-size:11px; color:#fff; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; display:block;">Mensaje
                            de mejora:</label>
                        <textarea id="retro-mensaje" class="form-control" rows="5"
                            style="background:#0a0a0a; border:1px solid #222; color:#fff;"
                            placeholder="Escribe aquí las observaciones..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #1a1a1a; background:#0a0a0a;">
                    <button class="btn"
                        style="background: #ef4444; color: #fff; font-weight:800; font-size:12px; padding:10px 20px;"
                        onclick="enviarRetroalimentacion()">Enviar a Corrección</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // URL base del proyecto — permite usarla en archivos JS externos sin depender de PHP
        const BASE_URL = '<?= base_url() ?>';
    </script>
    <?= $this->renderSection('scripts') ?>

    <script>
        // Toggle dropdown "Gestionar Empresas"
        document.getElementById('btn-empresas-toggle').addEventListener('click', function () {
            document.getElementById('menu-empresas').classList.toggle('show');
            this.querySelector('.arrow-icon').classList.toggle('rotado');
        });

        // ── Toggle sidebar en móvil ──
        (function () {
            var btnToggle = document.getElementById('btn-menu-toggle');
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebar-overlay');

            function abrirSidebar() {
                sidebar.classList.add('abierto');
                overlay.classList.add('activo');
                document.body.style.overflow = 'hidden';
            }

            function cerrarSidebar() {
                sidebar.classList.remove('abierto');
                overlay.classList.remove('activo');
                document.body.style.overflow = '';
            }

            if (btnToggle) {
                btnToggle.addEventListener('click', function () {
                    if (sidebar.classList.contains('abierto')) {
                        cerrarSidebar();
                    } else {
                        abrirSidebar();
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', cerrarSidebar);
            }

            // Cerrar sidebar si se agranda la ventana
            window.addEventListener('resize', function () {
                if (window.innerWidth > 992) {
                    cerrarSidebar();
                }
            });
        })();

        // Cerrar Sesión - (Confirmación Sweet Alert)
        const logoutLink = document.querySelector('.logout-link');
        if (logoutLink) {
            logoutLink.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Cerrar sesión?',
                    text: '¿Estás seguro de que deseas salir del sistema?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#F5C400',
                    cancelButtonColor: '#71717a',
                    confirmButtonText: 'Sí, salir',
                    cancelButtonText: 'Cancelar',
                    background: '#161616',
                    color: '#ffffff',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = this.href;
                    }
                });
            });
        }
    }) ();
    </script>

</body>

</html>