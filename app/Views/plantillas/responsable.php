<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>RF Marketing — <?= esc($titulo ?? 'Responsable de Área') ?>
    </title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Estilos base del Responsable -->
    <link href="<?= base_url('recursos/styles/responsable/plantilla/responsable.css') ?>" rel="stylesheet">

    <?= $this->renderSection('estilos') ?>
</head>

<body>

    <!-- OVERLAY para cerrar sidebar en móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR - Navegación Lateral-->
    <aside class="sidebar" id="sidebar">

        <!-- Logo RF Marketing -->
        <div class="sidebar-brand">
            <div class="brand-logo">
                <span class="brand-rf">RF</span>
                <span class="brand-marketing">MARKETING</span>
            </div>
            <button class="sidebar-close-btn d-lg-none" id="sidebarCloseBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Info del Responsable -->
        <div class="sidebar-profile">
            <?php
            // Calculamos iniciales del responsable
            $nombre = $user['nombre'] ?? 'Responsable';
            $apellidos = $user['apellidos'] ?? '';
            $nombre_area = $user['area_nombre'] ?? $user['nombre_area'] ?? 'Área no asignada';
            $n_ini = substr($nombre, 0, 1);
            $a_ini = substr($apellidos, 0, 1);
            $iniciales = strtoupper($n_ini . $a_ini);
            ?>
            <div class="profile-avatar">
                <span>
                    <?= $iniciales ?>
                </span>
                <div class="avatar-status online" title="En línea"></div>
            </div>
            <div class="profile-info">
                <p class="profile-name">
                    <?= esc($nombre . ' ' . $apellidos) ?>
                </p>
                <span class="profile-rol">
                    <i class="bi bi-shield-check"></i> Jefe de Área
                </span>
                <span class="profile-area">
                    <i class="bi bi-diagram-3"></i>
                    <?= esc($nombre_area) ?>
                </span>
            </div>
        </div>

        <!-- Navegación Principal -->
        <nav class="sidebar-nav">

            <!-- Seccion: Gestion Operativa -->
            <p class="nav-section-label">GESTIÓN OPERATIVA</p>
            <ul>
                <!-- Dashboard - Métricas del área -->
                <li>
                    <a href="<?= base_url('responsable/dashboard') . '?test_user=' . ($user['id'] ?? '') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/dashboard') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <!-- Bandeja de Entrada - Requerimientos nuevos del Admin -->
                <li>
                    <a href="<?= base_url('responsable/bandeja') . '?test_user=' . ($user['id'] ?? '') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/bandeja') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-inbox-fill"></i></span>
                        <span class="nav-text">Bandeja de Entrada</span>
                        <?php if (isset($pendientes_asignar) && $pendientes_asignar > 0): ?>
                            <span class="nav-badge">
                                <?= $pendientes_asignar ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- En Proceso - Requerimientos activos -->
                <li>
                    <a href="<?= base_url('responsable/en-proceso') . '?test_user=' . ($user['id'] ?? '') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/en-proceso') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-kanban-fill"></i></span>
                        <span class="nav-text">En Proceso</span>
                        <?php if (isset($en_proceso) && $en_proceso > 0): ?>
                            <span class="nav-badge accent">
                                <?= $en_proceso ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Historial - Completados / devueltos -->
                <li>
                    <a href="<?= base_url('responsable/historial') . '?test_user=' . ($user['id'] ?? '') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/historial') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="nav-text">Historial</span>
                    </a>
                </li>
            </ul>

            <!-- Seccion: Equipos y Recursos -->
            <p class="nav-section-label mt-3">EQUIPO Y RECURSOS</p>
            <ul>
                <!-- Mi Equipo - Ver miembros y carga de trabajo -->
                <li>
                    <a href="<?= base_url('responsable/equipo') . '?test_user=' . ($user['id'] ?? '') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/equipo') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
                        <span class="nav-text">Mi Equipo</span>
                    </a>
                </li>

                <!-- Retroalimentación - Devoluciones y observaciones -->
                <li>
                    <a href="<?= base_url('responsable/retroalimentacion') . '?test_user=' . ($user['id'] ?? '') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/retroalimentacion') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-chat-square-text-fill"></i></span>
                        <span class="nav-text">Retroalimentación</span>
                        <?php if (isset($devoluciones) && $devoluciones > 0): ?>
                            <span class="nav-badge warning">
                                <?= $devoluciones ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <!-- Seccion: Alertas -->
            <p class="nav-section-label mt-3">ALERTAS</p>
            <ul>
                <!-- Notificaciones -->
                <li>
                    <a href="<?= base_url('responsable/notificaciones') ?>"
                        class="nav-link-item <?= (uri_string() == 'responsable/notificaciones') ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="bi bi-bell-fill"></i></span>
                        <span class="nav-text">Notificaciones</span>
                        <?php if (isset($notif_no_leidas) && $notif_no_leidas > 0): ?>
                            <span class="nav-badge notif">
                                <?= $notif_no_leidas ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

        </nav>

        <!-- Footer del Sidebar - Logout -->
        <div class="sidebar-footer">
            <a href="<?= base_url('auth/logout') ?>" class="logout-link">
                <i class="bi bi-box-arrow-left"></i>
                <span>Cerrar Sesión</span>
            </a>
            <div class="version-info">
                <span>RF Marketing v1.0</span>
                <span class="rol-tag">RESPONSABLE</span>
            </div>
        </div>

    </aside>

    <!-- Contenido Principal -->
    <div class="main-wrapper" id="mainWrapper">

        <!-- TOPBAR - Barra Superior -->
        <header class="topbar" id="topbar">
            <div class="topbar-left">
                <!-- Botón hamburguesa para móvil -->
                <button class="hamburger-btn d-lg-none" id="hamburgerBtn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Título de la página -->
                <div class="page-title-section">
                    <h1 class="page-title">
                        <?= esc($tituloPagina ?? $titulo ?? 'Panel') ?>
                    </h1>
                    <span class="page-subtitle">
                        <?= esc($nombre_area) ?>
                    </span>
                </div>
            </div>

            <div class="topbar-right">
                <!-- Perfil rápido -->
                <div class="topbar-user">
                    <div class="user-info d-none d-md-block">
                        <span class="user-name">
                            <?= esc($nombre) ?>
                        </span>
                        <span class="user-role">
                            <?= esc($nombre_area) ?>
                        </span>
                    </div>
                    <div class="user-avatar">
                        <?= $iniciales ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido Dinamico Insertado -->
        <main class="page-content">
            <?= $this->renderSection('contenido') ?>
        </main>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JS de la plantilla -->
    <script src="<?= base_url('recursos/scripts/responsable/plantilla/responsable.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>

</body>

</html>