<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RF Marketing —
        <?= $titulo ?? 'Panel Cliente' ?>
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <!-- CSS Plantilla -->
    <link rel="stylesheet" href="<?= base_url('recursos/styles/plantillas/cliente.css') ?>">
    <!-- Agregar CSS -->
    <?= $this->renderSection('estilos') ?>
</head>

<body>

    <!--  OVERLAY — cierra sidebar en mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
        <div class="sidebar-brand">
            <div class="brand-logo">
                <span class="brand-rf">RF</span>
                <span class="brand-name">MARKETING</span>
            </div>
            <button class="sidebar-close-btn" id="sidebarCloseBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Perfil del usuario -->
        <div class="sidebar-profile">
            <div class="profile-avatar">
                <?php
                // Usamos el array que viene del endpoint (controlador)
                $nombre = $user['nombre'] ?? 'Sin Nombre';
                $apellidos = $user['apellidos'] ?? '';
                $rol = $user['rol'] ?? 'CLIENTE';
                // Generamos las iniciales para el círculo amarillo
                $n_ini = substr($nombre, 0, 1);
                $a_ini = substr($apellidos, 0, 1);
                $iniciales = strtoupper($n_ini . $a_ini);
                ?>
                <span>
                    <?= $iniciales ?>
                </span>
                <div class="avatar-status"></div>
            </div>
            <div class="profile-info">
                <p class="profile-name">
                    <?= esc($nombre . ' ' . $apellidos) ?>
                </p>
                <span class="profile-rol">
                    <?= esc($rol) ?>
                </span>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="sidebar-nav">
            <p class="nav-section-label">MI CUENTA</p>
            <ul>
                <li>
                    <a href="<?= base_url('cliente/') ?>"
                        class="nav-link-item <?= (uri_string() == 'cliente/') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-briefcase"></i></span>
                        <span class="nav-text">Mis Pedidos</span>
                        <!-- Badge de pedidos pendientes — dinámico -->
                        <?php if (isset($pendientes) && $pendientes > 0): ?>
                            <span class="nav-badge">
                                <?= $pendientes ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('cliente/notificaciones') ?>"
                        class="nav-link-item <?= (uri_string() == 'cliente/notificaciones') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-bell"></i></span>
                        <span class="nav-text">Notificaciones</span>
                        <?php if (isset($notif_no_leidas) && $notif_no_leidas > 0): ?>
                            <span class="nav-badge notif">
                                <?= $notif_no_leidas ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <p class="nav-section-label mt-3">CONFIGURACIÓN</p>
            <ul>
                <li>
                    <a href="<?= base_url('cliente/perfil') ?>"
                        class="nav-link-item <?= (uri_string() == 'cliente/perfil') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-person-circle"></i></span>
                        <span class="nav-text">Mi Perfil</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Footer del sidebar -->
        <div class="sidebar-footer">
            <span>RF MARKETING SAC</span>
            <span>v1.0</span>
        </div>
    </aside>

    <!--  CONTENEDOR PRINCIPAL -->
    <div class="main-wrapper" id="mainWrapper">

        <!-- TOPBAR -->
        <header class="topbar" id="topbar">
            <div class="topbar-left">
                <button class="hamburger-btn" id="hamburgerBtn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h1 class="page-title">
                    <?= $titulo ?? 'Panel' ?>
                </h1>
            </div>

            <div class="topbar-right">
                <!-- Notificaciones -->
                <a href="<?= base_url('cliente/notificaciones') ?>" class="topbar-icon-btn notif-btn">
                    <i class="bi bi-bell"></i>
                    <?php if (isset($notif_no_leidas) && $notif_no_leidas > 0): ?>
                        <span class="notif-dot"></span>
                    <?php endif; ?>
                </a>

                <!-- Usuario -->
                <div class="topbar-user">
                    <div class="topbar-avatar">
                        <?= $iniciales ?>
                    </div>
                    <div class="topbar-user-info">
                        <span class="topbar-user-name">
                            <?= esc($nombre . ' ' . $apellidos) ?>
                        </span>
                        <!-- <?php if (isset($empresa)): ?>
                            <span class="topbar-user-empresa">
                                <?= esc($empresa) ?>
                            </span>
                        <?php endif; ?> -->
                    </div>
                </div>

                <!-- Logout -->
                <a href="<?= base_url('logout') ?>" class="topbar-icon-btn logout-btn" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </header>

        <!-- CONTENIDO -->
        <main class="page-content">
            <?= $this->renderSection('contenido') ?>
        </main>

    </div><!-- /main-wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS Plantilla -->
    <script src="<?= base_url('recursos/scripts/plantillas/cliente.js') ?>"></script>
    <!-- Agregar Scrips -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>