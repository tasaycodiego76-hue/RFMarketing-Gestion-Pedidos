<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/responsable/paginas/dashboard.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Metricas Superiores -->
<div class="seccion-titulo">Resumen del Área</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-naranja"><?= $porAsignar ?? 0 ?></div>
            <div class="metrica-sub">Por Asignar</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-amarillo"><?= $enProceso ?? 0 ?></div>
            <div class="metrica-sub">En Proceso</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-violeta"><?= $enRevision ?? 0 ?></div>
            <div class="metrica-sub">En Revisión</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor color-verde"><?= $completados ?? 0 ?></div>
            <div class="metrica-sub">Completados</div>
        </div>
    </div>
</div>

<!-- Gráficos de Dashboard (Restaurados) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Productividad por Empleado</h6>
            <canvas id="graficoProductividad"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Distribución de Carga</h6>
            <div style="height: 300px; position: relative;">
                <canvas id="graficoDistribucion"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Tendencia Semanal</h6>
            <canvas id="graficoTendencia"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-rf h-100">
            <h6 class="mb-3">Tiempo Promedio de Resolución</h6>
            <canvas id="graficoTiempo"></canvas>
        </div>
    </div>
</div>

<!-- SECCIÓN DE REPORTES AVANZADOS -->
<div class="seccion-titulo">Reportes de Gestión del Área</div>
<div class="card-rf mb-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="text-small-muted mb-1">Fecha Inicio</label>
            <input type="date" id="rep_desde" class="form-control-rf w-100">
        </div>
        <div class="col-md-6">
            <label class="text-small-muted mb-1">Fecha Fin</label>
            <input type="date" id="rep_hasta" class="form-control-rf w-100" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="col-md-6">
            <label class="text-small-muted mb-1">Filtrar por Empresa</label>
            <select id="rep_idempresa" class="form-control-rf w-100">
                <option value="">-- Todas las Empresas --</option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id'] ?>">
                        <?= esc($e['nombreempresa']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="text-small-muted mb-1">Filtrar por Técnico</label>
            <select id="rep_idempleado" class="form-control-rf w-100">
                <option value="">-- Todo el Equipo --</option>
                <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id'] ?>">
                        <?= esc($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-12 mt-2">
            <div class="d-flex flex-wrap gap-4">
                <label class="checkbox-container">
                    <input type="checkbox" id="rep_solo_completados"> Solo completados
                </label>
                <label class="checkbox-container">
                    <input type="checkbox" id="rep_solo_retrasos"> Solo pedidos con retraso
                </label>
                <label class="checkbox-container">
                    <input type="checkbox" id="rep_incluir_pausas_reasignaciones" checked> Incluir pausas y reasignaciones
                </label>
            </div>
        </div>

        <div class="col-md-12 mt-3 text-end">
            <div class="d-flex gap-2 justify-content-end">
                <button onclick="descargarCSV()" class="btn-rf btn-verde" style="padding: 10px 25px;">
                    <i class="fas fa-file-csv"></i> EXPORTAR CSV
                </button>
                <button onclick="descargarReporte()" class="btn-rf btn-naranja" style="padding: 10px 25px;">
                    <i class="fas fa-file-pdf"></i> GENERAR REPORTE PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = "<?= base_url() ?>";
    const RESPONSABLE_ID = "<?= esc($user['id']) ?>";

    function descargarReporte() {
        const params = new URLSearchParams();
        const filtros = {
            desde: document.getElementById('rep_desde').value,
            hasta: document.getElementById('rep_hasta').value,
            idempresa: document.getElementById('rep_idempresa').value,
            idempleado: document.getElementById('rep_idempleado').value,
            solo_completados: document.getElementById('rep_solo_completados').checked ? 1 : 0,
            solo_retrasos: document.getElementById('rep_solo_retrasos').checked ? 1 : 0,
            incluir_pausas_reasignaciones: document.getElementById('rep_incluir_pausas_reasignaciones').checked ? 1 : 0
        };

        Object.keys(filtros).forEach(key => {
            params.append(key, filtros[key]);
        });

        window.open("<?= base_url('responsable/reporte-gestion') ?>?" + params.toString(), '_blank');
    }

    function descargarCSV() {
        const params = new URLSearchParams();
        const filtros = {
            desde: document.getElementById('rep_desde').value,
            hasta: document.getElementById('rep_hasta').value,
            idempresa: document.getElementById('rep_idempresa').value,
            idempleado: document.getElementById('rep_idempleado').value,
            solo_completados: document.getElementById('rep_solo_completados').checked ? 1 : 0,
            solo_retrasos: document.getElementById('rep_solo_retrasos').checked ? 1 : 0,
            incluir_pausas_reasignaciones: document.getElementById('rep_incluir_pausas_reasignaciones').checked ? 1 : 0
        };

        Object.keys(filtros).forEach(key => {
            params.append(key, filtros[key]);
        });

        window.open("<?= base_url('responsable/reporte-csv') ?>?" + params.toString(), '_blank');
    }
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/responsable/paginas/dashboard.js') ?>"></script>
<?= $this->endSection() ?>