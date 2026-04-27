<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RF Marketing — Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('recursos/styles/login.css') ?>">
</head>

<body>
    <!-- Capa de Fondo -->
    <div class="bg-abstract">
        <div class="orb-glow"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <main class="login-wrapper">
        <div class="login-card">
            <div class="row g-0 h-100">
                <!-- Sección de Marca - Izquierda -->
                <div class="col-md-5 d-none d-md-flex side-branding">
                    <div class="branding-content">
                        <div class="mini-badge">SISTEMA OFICIAL</div>
                        <h1 class="display-1 logo-text">RF</h1>
                        <div class="branding-footer">
                            <p class="mb-0 fw-bold">RF Agencia de Marketing</p>
                            <small class="opacity-50">Gestión de Requerimientos</small>
                        </div>
                    </div>
                </div>
                <!-- Seccion de Seguridad - Derecha -->
                <div class="col-md-7 side-form">
                    <div class="form-wrapper">
                        <header class="mb-5">
                            <h2 class="fw-800 mb-1">BIENVENIDO</h2>
                            <p class="text-white-50 small">Por favor, introduce tus credenciales.</p>
                        </header>
                        <!-- Verifica si existe un mensaje de error temporal en la sesión -->
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger-custom mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>
                        <!-- Verifica si existe un mensaje de información -->
                        <?php if (session()->getFlashdata('info')): ?>
                            <div class="alert alert-info-custom mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                <?= session()->getFlashdata('info') ?>
                            </div>
                        <?php endif; ?>
                        <!-- Verifica si existen errores de validación -->
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger-custom mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Errores de validación:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <!-- Inicio del formulario-->
                        <form action="<?= base_url('auth/login') ?>" method="POST" autocomplete="off">
                            <?= csrf_field() ?>
                            <!-- Usuario Ingreso -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="usr" name="usuario" placeholder="Usuario"
                                    value="<?= old('usuario') ?>">
                                <label for="usr">
                                    <i class="fas fa-user me-2"></i>USUARIO
                                </label>
                            </div>
                            <!-- Contraseña -->
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="pwd" name="clave"
                                    placeholder="Contraseña">
                                <label for="pwd">
                                    <i class="fas fa-lock me-2"></i>CONTRASEÑA
                                </label>
                            </div>
                            <button type="submit" class="btn btn-rf-primary w-100 py-3 fw-bold">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                INGRESAR
                            </button>
                        </form>
                        <footer class="mt-5 pt-4 border-top border-dark">
                            <div class="d-flex justify-content-between align-items-center opacity-50 small">
                                <span>RF MARKETING SAC</span>
                                <span><?= date('Y') ?></span>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('recursos/scripts/login.js') ?>"></script>
</body>

</html>