<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/usuarios.css') ?>" rel="stylesheet">
<style>
    .historial-area-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(245, 196, 0, 0.1);
        border: 1px solid rgba(245, 196, 0, 0.2);
        padding: 6px 14px;
        border-radius: 8px;
        color: #F5C400;
        font-weight: 800;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .historial-title {
        font-size: 16px;
        font-weight: 800;
        color: #ffffff !important;
        margin-bottom: 2px;
    }
    .historial-sub-info {
        font-size: 10px;
        color: #F5C400;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    /* Mejora de visualización de Empresa con Avatar */
    .historial-empresa-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .empresa-avatar-mini {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 14px;
        color: #000;
        flex-shrink: 0;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .empresa-nombre-text {
        font-size: 14px;
        font-weight: 700;
        color: #f0f0f0;
    }
    
    .btn-expediente {
        background: #F5C400;
        border: none;
        color: #000 !important;
        font-size: 11px;
        font-weight: 900;
        padding: 8px 18px;
        border-radius: 6px;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 10px rgba(245, 196, 0, 0.2);
    }
    .btn-expediente:hover {
        background: #fff;
        color: #000 !important;
        transform: translateY(-2px);
    }
    .input-calendario {
        background: #0a0a0a !important;
        border: 1.5px solid #222 !important;
        color: #fff !important;
        border-radius: 6px;
        font-size: 13px;
        padding: 8px 14px;
        height: 40px;
        transition: all 0.2s;
    }
    
    .tabla-usuarios thead th {
        color: #F5C400 !important;
        background: #000 !important;
        font-size: 11px !important;
    }
    .tabla-usuarios tbody td {
        border-bottom: 1px solid #111 !important;
        padding: 15px 15px !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/kanban.js') ?>"></script>
<script>
    $(document).ready(function() {
        $("#busquedaHistorial").on("keyup", function() { filtrarTabla(); });
        $("#filtroFecha").on("change", function() { filtrarTabla(); });

        function filtrarTabla() {
            var search = $("#busquedaHistorial").val().toLowerCase();
            var date = $("#filtroFecha").val();
            $("#tablaHistorial tbody tr").each(function() {
                var text = $(this).text().toLowerCase();
                var rowDate = $(this).data('fecha');
                var matchesSearch = text.indexOf(search) > -1;
                var matchesDate = (date === "" || rowDate === date);
                $(this).toggle(matchesSearch && matchesDate);
            });
        }
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <p style="font-size: 12px; color: #888; margin: 0;">Registro visual de todos los proyectos finalizados.</p>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <label style="font-size: 10px; color: #555; font-weight: 800; text-transform: uppercase; margin: 0;">Filtrar día:</label>
            <input type="date" id="filtroFecha" class="form-control input-calendario" style="width: 170px;">
        </div>
        <input type="text" id="busquedaHistorial" class="input-busqueda" placeholder="Buscar proyecto..." style="max-width: 200px;">
    </div>
</div>

<div class="tabla-contenedor">
    <table class="tabla-usuarios" id="tablaHistorial">
        <thead>
            <tr>
                <th style="width: 170px;">Área</th>
                <th>Proyecto</th>
                <th>Cliente / Empresa</th>
                <th style="text-align: center;">Finalización</th>
                <th>Ejecutor</th>
                <th style="text-align: center; width: 180px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pedidos)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 80px; color: #444;">No hay registros</td></tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p): ?>
                    <?php $inicial = mb_strtoupper(mb_substr($p['empresa_nombre'], 0, 1)); ?>
                    <tr data-fecha="<?= date('Y-m-d', strtotime($p['fechacompletado'])) ?>">
                        <td>
                            <div class="historial-area-badge">
                                <i class="bi bi-palette-fill"></i> <?= esc($p['area_nombre'] ?? 'General') ?>
                            </div>
                        </td>
                        <td>
                            <div class="historial-title"><?= esc($p['titulo']) ?></div>
                            <div class="historial-sub-info"><?= esc($p['servicio_nombre']) ?></div>
                        </td>
                        <td>
                            <div class="historial-empresa-wrapper">
                                <div class="empresa-avatar-mini" style="background: <?= $p['empresa_color'] ?>;">
                                    <?= $inicial ?>
                                </div>
                                <div class="empresa-nombre-text"><?= esc($p['empresa_nombre']) ?></div>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="color: #fff; font-weight: 800;"><?= date('d/m/Y', strtotime($p['fechacompletado'])) ?></div>
                            <div style="font-size: 10px; color: #555;"><?= date('H:i A', strtotime($p['fechacompletado'])) ?></div>
                        </td>
                        <td>
                            <div style="font-size: 14px; font-weight: 800; color: #fff;">
                                <?= esc(strtoupper($p['empleado_nombre'])) ?>
                            </div>
                            <div style="font-size: 10px; color: #F5C400; font-weight: 700;">FINALIZADO</div>
                        </td>
                        <td style="text-align: center;">
                            <button class="btn-expediente" onclick="verDetalle(<?= $p['id'] ?>)">
                                VER EXPEDIENTE
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="height: 50px;"></div>

<?= $this->endSection() ?>
