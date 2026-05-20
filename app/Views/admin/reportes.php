<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<style>
    .report-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 35px;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .report-card:hover {
        border-color: var(--amarillo);
    }
    .filter-label {
        font-size: 11.5px;
        color: var(--texto-2);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-bottom: 10px;
        display: block;
    }
    .form-control-rf {
        background: var(--mini-card-bg) !important;
        border: 1px solid var(--card-border) !important;
        color: var(--texto) !important;
        border-radius: 10px !important;
        padding: 12px 15px !important;
        font-size: 13px !important;
        transition: all 0.3s ease;
    }
    .form-control-rf:focus {
        border-color: var(--amarillo) !important;
        background: var(--card-bg) !important;
        box-shadow: 0 0 0 0.2rem rgba(245, 196, 0, 0.1) !important;
    }
    .btn-generate {
        background: var(--amarillo);
        color: #000;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 20px;
        letter-spacing: 1.5px;
        padding: 12px 50px;
        border-radius: 12px;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .btn-generate:hover {
        background: var(--texto);
        color: var(--fondo);
        transform: translateY(-2px);
    }
    .checkbox-container {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--texto-2);
        font-size: 13px;
        cursor: pointer;
    }
    .checkbox-container input {
        width: 18px;
        height: 18px;
        accent-color: var(--amarillo);
    }
    
    /* Adaptación específica para modo claro */
    html[data-theme="light"] .report-title {
        color: #000 !important;
    }
    html[data-theme="light"] .filter-label {
        color: #000 !important;
        font-weight: 900;
    }
    html[data-theme="light"] .checkbox-container {
        color: #333 !important;
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="report-title" style="font-family: 'Bebas Neue'; letter-spacing: 1px; color: #fff; margin: 0;">
                    <i class="bi bi-file-earmark-bar-graph text-warning me-2"></i> PANEL DE REPORTES ESTRATÉGICOS
                </h2>
            </div>
            
            <div class="report-card">
                <div class="row g-4 align-items-end">
                    <!-- Filtros Principales -->
                    <div class="col-md-3">
                        <label class="filter-label">Fecha de Inicio</label>
                        <input type="date" id="rep_desde" class="form-control-rf w-100">
                    </div>
                    <div class="col-md-3">
                        <label class="filter-label">Fecha de Cierre</label>
                        <input type="date" id="rep_hasta" class="form-control-rf w-100" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label">Cliente / Empresa</label>
                        <select id="rep_idempresa" class="form-control-rf w-100">
                            <option value="">-- Todas las Empresas --</option>
                            <?php foreach ($empresas as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= esc($e['nombreempresa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label">Área de la Agencia</label>
                        <select id="rep_idarea_int" class="form-control-rf w-100">
                            <option value="">-- Todas las Áreas --</option>
                            <?php foreach ($areasAgencia as $aa): ?>
                                <option value="<?= $aa['id'] ?>"><?= esc($aa['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fila Inferior: Opciones y Botón -->
                    <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
                        <label class="checkbox-container">
                            <input type="checkbox" id="rep_solo_completados">
                            Generar reporte solo con pedidos finalizados
                        </label>
                        
                        <button onclick="descargarReporte()" class="btn-generate">
                            <i class="bi bi-file-earmark-pdf-fill"></i> GENERAR DOCUMENTO PDF
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="text-center" style="color: var(--texto-3); font-size: 12px; font-style: italic;">
                <i class="bi bi-info-circle me-1"></i> El documento PDF incluirá el detalle de atenciones, métricas de equipo y alertas críticas de gestión.
            </div>
        </div>
    </div>
</div>

<script>
    function descargarReporte() {
        const params = new URLSearchParams();
        const filtros = {
            desde: document.getElementById('rep_desde').value,
            hasta: document.getElementById('rep_hasta').value,
            idempresa: document.getElementById('rep_idempresa').value,
            idarea_int: document.getElementById('rep_idarea_int').value,
            solo_completados: document.getElementById('rep_solo_completados').checked ? 1 : 0
        };

        Object.keys(filtros).forEach(key => {
            if (filtros[key]) params.append(key, filtros[key]);
        });

        window.open("<?= base_url('admin/reporte-gestion') ?>?" + params.toString(), '_blank');
    }
</script>

<?= $this->endSection() ?>