<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>RF Marketing — <?= esc($titulo ?? 'Admin') ?></title>
 <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- BARRA LATERAL -->
<aside class="sidebar">

    <div class="sidebar-logo">
        <div class="marca">RF</div>
        <div class="subtitulo">Marketing S.A.C.</div>
    </div>

    <nav>
        <p class="nav-seccion">PRINCIPAL</p>
        <a href="<?= site_url('admin/dashboard') ?>" class="nav-enlace <?= ($paginaActual == 'dashboard') ? 'activo' : '' ?>">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <p class="nav-seccion">EMPRESAS</p>

        <div class="nav-item-dropdown">
            <div class="nav-enlace" id="btn-empresas-toggle" style="cursor:pointer;">
                <i class="bi bi-building"></i>
                <span>Gestionar Empresas</span>
                <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
            </div>

            <div class="nav-sub-menu <?= ($paginaActual == 'todas_empresas' || $paginaActual == 'kanban') ? 'show' : '' ?>" id="menu-empresas">
                <a href="<?= site_url('admin/empresas') ?>" class="nav-enlace sub-enlace <?= ($paginaActual == 'todas_empresas') ? 'activo' : '' ?>">
                    <i style="font-size: 10px;"></i> Todas las Empresas
                </a>

                <?php foreach ($empresas ?? [] as $emp): ?>
                    <a href="<?= site_url('admin/kanban/'.$emp['id'].'/1') ?>" class="nav-enlace sub-enlace">
                        <i class="bi bi-circle-fill nav-punto"></i>
                        <span class="text-truncate"><?= $emp['nombreempresa'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <p class="nav-seccion">GESTIÓN</p>
        <a href="<?= site_url('admin/usuarios') ?>" class="nav-enlace <?= ($paginaActual == 'usuarios') ? 'activo' : '' ?>">
            <i class="bi bi-people"></i> Usuarios
        </a>
        <a href="<?= site_url('admin/areas') ?>" class="nav-enlace <?= ($paginaActual == 'areas') ? 'activo' : '' ?>">
    <i class="bi bi-diagram-3"></i> Áreas
</a>
        </a>
        <a href="<?= site_url('admin/empresas') ?>" class="nav-enlace <?= ($paginaActual == 'empresas') ? 'activo' : '' ?>">
            <i class="bi bi-building"></i> Empresas
        </a>
    </nav>

    <div class="sidebar-usuario">
        <div class="usuario-avatar">AD</div>
        <div>
            <div class="usuario-nombre">Administrador</div>
            <div class="usuario-rol">Admin</div>
        </div>
        <a href="<?= site_url('logout') ?>" class="ms-auto" style="color:#444" title="Salir">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

</aside>

<!-- CONTENEDOR PRINCIPAL -->
<div class="contenedor-principal">

    <!-- BARRA SUPERIOR -->
    <header class="topbar">
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
</script>

</body>
</html>