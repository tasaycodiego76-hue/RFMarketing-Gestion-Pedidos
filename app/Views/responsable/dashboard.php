<?= $this->extend('plantillas/responsable') ?>

<?= $this->section('estilos') ?>
<style>
.estado-pendiente_especificacion {
    background: #f59e0b;
    color: #000;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

/* Estilos específicos para tarjetas de revisión */
.card-rf[style*="border-left"] {
    position: relative;
    overflow: hidden;
}

.card-rf[style*="border-left"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(139, 92, 246, 0.15);
    transition: all 0.3s ease;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Metricas - Prueba Vista -->
<div class="seccion-titulo">Resumen del Área</div>
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor" style="color:#f59e0b"><?= $porAsignar ?? 0 ?></div>
            <div style="font-size:13px;color:#a1a1aa">Por Asignar</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor" style="color:#F5C400"><?= $enProceso ?? 0 ?></div>
            <div style="font-size:13px;color:#a1a1aa">En Proceso</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor" style="color:#8b5cf6"><?= $enRevision ?? 0 ?></div>
            <div style="font-size:13px;color:#a1a1aa">En Revisión</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor" style="color:#22c55e"><?= $completados ?? 0 ?></div>
            <div style="font-size:13px;color:#a1a1aa">Completados</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card-rf text-center">
            <div class="metrica-valor" style="color:#3b82f6"><?= $totalMiembros ?? 0 ?></div>
            <div style="font-size:13px;color:#a1a1aa">Mi Equipo</div>
        </div>
    </div>

</div>

<!-- Mensaje de ¡Bienvenida (Prueba) -->
<div class="card-rf">
    <h5 style="margin-bottom:16px">¡Bienvenido <?= esc($user['nombre']) ?>!</h5>
    <p style="color:#a1a1aa">
        Área: <strong style="color:#F5C400"><?= esc($user['nombre_area'] ?? 'Sin área') ?></strong>
    </p>
    <hr style="border-color:#27272a;margin:16px 0">
    <p style="color:#71717a;font-size:14px">
        Desde aquí podrás gestionar los requerimientos asignados a tu área y distribuir el trabajo entre tu equipo.
    </p>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->endSection() ?>