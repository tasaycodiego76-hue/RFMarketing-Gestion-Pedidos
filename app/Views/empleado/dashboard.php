<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<p class="seccion-titulo">Resumen de Rendimiento</p>

<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon naranja"><i class="bi bi-lightning-charge-fill"></i></div>
            <div class="met-label">Nuevos Pedidos</div>
            <div class="met-num naranja"><?= $stats['nuevos'] ?? 0 ?></div>
            <div class="met-sub">Por aceptar</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon morado"><i class="bi bi-hourglass-split"></i></div>
            <div class="met-label">En Desarrollo</div>
            <div class="met-num morado"><?= $stats['proceso'] ?? 0 ?></div>
            <div class="met-sub">Tareas activas</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon blanco"><i class="bi bi-search"></i></div>
            <div class="met-label">En Revisión</div>
            <div class="met-num blanco"><?= $stats['revision'] ?? 0 ?></div>
            <div class="met-sub">Esperando feedback</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card met-card h-100">
            <div class="met-icon verde"><i class="bi bi-check-circle-fill"></i></div>
            <div class="met-label">Completados</div>
            <div class="met-num verde"><?= $stats['historial'] ?? 0 ?></div>
            <div class="met-sub">Total éxitos</div>
        </div>
    </div>

</div>

<!-- SECCIÓN DE ACCIÓN INMEDIATA -->
<div class="row mb-5">
    <div class="col-lg-8">
        <p class="seccion-titulo">Tareas en Ejecución</p>

        <?php if (empty($pedidos_recientes)): ?>
            <div class="card p-5 text-center"
                style="background: var(--panel); border: 1px dashed var(--borde); border-radius: 16px;">
                <div class="mb-3">
                    <i class="bi bi-stars" style="font-size: 42px; color: var(--amarillo); opacity: 0.8;"></i>
                </div>
                <h6 style="letter-spacing: 2px; text-transform: uppercase; font-size: 13px; color: var(--texto); font-weight: 700;">¡Todo al día por ahora!</h6>
                <p style="font-size: 11px; color: var(--texto-3); max-width: 250px; margin: 10px auto 0;">No tienes tareas pendientes de inicio o en proceso. Descansa o revisa tu historial.</p>
            </div>
        <?php else: ?>
            <?php foreach (array_slice($pedidos_recientes, 0, 3) as $pedido): ?>
                <?php 
                    $esNuevo = ($pedido['estado'] == 'pendiente_asignado');
                    $cardCls = $esNuevo ? 'task-new' : 'task-process';
                    $pillCls = $esNuevo ? 'pill-new' : 'pill-process';
                    $pillTxt = $esNuevo ? 'POR INICIAR' : 'TRABAJANDO';
                ?>
                <div class="emp-task-card <?= $cardCls ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="task-client"><i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?></div>
                        <span class="task-status-pill <?= $pillCls ?>"><?= $pillTxt ?></span>
                    </div>
                    <div class="task-title"><?= esc($pedido['titulo']) ?></div>
                    
                    <div class="task-meta">
                        <div class="task-meta-item">
                            <i class="bi bi-calendar-event"></i> 
                            Límite: <?= date('d M Y', strtotime($pedido['fechafin'] ?? $pedido['fecharequerida'])) ?>
                        </div>
                    </div>

                    <div class="task-actions">
                        <div>
                            <button class="kb-btn" onclick="verDetalleSolicitud(<?= $pedido['id'] ?>)">
                                <i class="bi bi-eye mr-1"></i> VER BRIEF
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($esNuevo): ?>
                                <button class="btn-yellow btn-start-quick" data-id="<?= $pedido['id'] ?>" style="font-size: 13px; padding: 6px 20px;">
                                    <i class="bi bi-play-fill"></i> COMENZAR
                                </button>
                            <?php else: ?>
                                <button class="btn-yellow btn-continue-quick" 
                                    onclick="window.location.href='<?= base_url('empleado/mis_pedidos') ?>'" 
                                    style="font-size: 13px; padding: 6px 20px; background: #fff !important; color: #000 !important;">
                                    CONTINUAR <i class="bi bi-arrow-right ml-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($pedidos_recientes) > 2): ?>
                <div class="text-right">
                    <a href="<?= base_url('empleado/mis_pedidos') ?>"
                        style="color:var(--amarillo); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Ver
                        todas mis tareas (<?= count($pedidos_recientes) ?>) →</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <p class="seccion-titulo">Pendientes de Revisión</p>
        <?php if (empty($pedidos_revision)): ?>
            <div class="p-4 text-center"
                style="background: rgba(0,0,0,0.05); border: 1px solid #1a1a1a; border-radius: 16px;">
                <p style="font-size: 11px; color: #555; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Todo al
                    día <i class="bi bi-check-all ml-1"></i></p>
            </div>
        <?php else: ?>
            <?php foreach (array_slice($pedidos_revision, 0, 3) as $pedido): ?>
                <div
                    style="background: #0d0d0d; border: 1px solid #1e1e1e; border-radius: 12px; padding: 15px; margin-bottom: 12px;">
                    <div style="font-size: 10px; color: var(--texto-3); font-weight: 800; margin-bottom: 4px;">
                        <?= esc($pedido['empresa_nombre']) ?></div>
                    <div style="font-size: 13px; color: #fff; font-weight: 600; margin-bottom: 8px;">
                        <?= esc($pedido['titulo']) ?></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="task-status-pill pill-revision">EN REVISIÓN</span>
                        <i class="bi bi-clock-history" style="color: #444;"></i>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('recursos/scripts/empleado/misPedidos.js') ?>"></script>
<script>
document.querySelectorAll('.btn-start-quick').forEach(btn => {
    btn.addEventListener('click', function() {
        const idAtencion = this.getAttribute('data-id');
        
        Swal.fire({
            title: '¿Iniciar esta tarea?',
            text: "Se marcará como 'En Proceso' y se notificará el inicio del trabajo.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f5c400',
            cancelButtonColor: '#333',
            confirmButtonText: 'Sí, comenzar ya',
            cancelButtonText: 'Ahora no',
            background: '#111',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                // Loader
                Swal.fire({
                    title: 'Iniciando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch(`<?= base_url('empleado/pedido-iniciar') ?>/${idAtencion}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        window.location.href = '<?= base_url('empleado/mis_pedidos') ?>';
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo iniciar', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>