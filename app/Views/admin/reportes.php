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

    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .mini-report-card {
        background: #0f0f0f;
        border: 1px solid #1a1a1a;
        padding: 25px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 20px;
        opacity: 0.6;
        transition: opacity 0.3s ease;
    }

    .mini-report-card:hover {
        opacity: 1;
        border-color: #333;
    }

    .mini-report-icon {
        width: 50px;
        height: 50px;
        background: #151515;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #444;
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
            <p style="color: #666; font-size: 14px; margin-top: 5px;">Módulo de análisis avanzado y monitoreo de rendimiento.</p>
        </div>
    </div>

    <!-- Main Card Placeholder -->
    <div class="report-placeholder-card">
        <span class="report-status">Próximamente</span>
        <i class="bi bi-graph-up-arrow report-icon"></i>
        <h2 class="report-title">ESTADÍSTICAS EN TIEMPO REAL</h2>
        <p class="report-desc">
            Estamos preparando un panel interactivo donde podrás visualizar el rendimiento por áreas, 
            tiempos de respuesta (SLA), carga de trabajo y efectividad de las campañas por empresa.
        </p>
        <div style="height: 2px; background: #1a1a1a; width: 100px; margin: 0 auto;"></div>
    </div>

    <!-- Grid of Future Reports -->
    <div class="report-grid">
        <div class="mini-report-card">
            <div class="mini-report-icon"><i class="bi bi-pie-chart"></i></div>
            <div>
                <div style="color: #fff; font-weight: 800; font-size: 14px; text-transform: uppercase;">Rendimiento de Áreas</div>
                <div style="color: #555; font-size: 11px;">Métricas de entrega y calidad.</div>
            </div>
        </div>

        <div class="mini-report-card">
            <div class="mini-report-icon"><i class="bi bi-people"></i></div>
            <div>
                <div style="color: #fff; font-weight: 800; font-size: 14px; text-transform: uppercase;">Productividad de Equipo</div>
                <div style="color: #555; font-size: 11px;">Carga de trabajo por empleado.</div>
            </div>
        </div>

        <div class="mini-report-card">
            <div class="mini-report-icon"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div style="color: #fff; font-weight: 800; font-size: 14px; text-transform: uppercase;">Cumplimiento de SLA</div>
                <div style="color: #555; font-size: 11px;">Análisis de pedidos entregados a tiempo.</div>
            </div>
        </div>

        <div class="mini-report-card">
            <div class="mini-report-icon"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div style="color: #fff; font-weight: 800; font-size: 14px; text-transform: uppercase;">Balance por Empresa</div>
                <div style="color: #555; font-size: 11px;">Distribución de requerimientos pagados.</div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
