<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<style>
    .reports-container {
        padding: 20px 0;
    }

    .report-placeholder-card {
        background: #0a0a0a;
        border: 1px solid #1a1a1a;
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .report-placeholder-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #F5C400, #ff9f43);
        opacity: 0.7;
    }

    .report-icon {
        font-size: 48px;
        color: #F5C400;
        margin-bottom: 20px;
        display: block;
    }

    .report-status {
        display: inline-block;
        background: rgba(245, 196, 0, 0.1);
        color: #F5C400;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }

    .report-title {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 32px;
        color: #fff;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .report-desc {
        color: #666;
        font-size: 14px;
        max-width: 500px;
        margin: 0 auto 25px;
        line-height: 1.6;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="reports-container">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 style="font-family: 'Bebas Neue'; font-size: 42px; color: #fff; letter-spacing: 1px; margin: 0;">
                <?= $tituloPagina ?>
            </h1>
            <p style="color: #666; font-size: 14px; margin-top: 5px;">Módulo de análisis avanzado y monitoreo de
                rendimiento.</p>
        </div>
    </div>

    <!-- Main Card Placeholder -->
    <div class="report-placeholder-card">
        <span class="report-status">Próximamente</span>
        <i class="bi bi-graph-up-arrow report-icon"></i>
        <h2 class="report-title">ESTADÍSTICAS EN TIEMPO REAL</h2>
    </div>


    <?= $this->endSection() ?>