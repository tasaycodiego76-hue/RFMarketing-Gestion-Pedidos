<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<!-- FILTROS (ESTILO ADMIN) -->
<div class="row mb-4">
    <div class="col-md-12">
        <input type="text" id="busqueda" class="form-control" placeholder="Filtrar historial por cliente o título..." 
            style="background: var(--panel); border: 1px solid var(--borde); color: var(--texto); font-size: 12px; height: 36px; border-radius: 6px;">
    </div>
</div>

<!-- LISTADO (ESTILO ADMIN) -->
<p class="seccion-titulo">Tareas Finalizadas</p>

<div id="contenedor-historial">
    <?php if(empty($pedidos)): ?>
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 10px;">
            <i class="bi bi-clock-history" style="font-size: 30px; color: var(--texto-3);"></i>
            <p class="mt-2" style="font-size: 11px; color: var(--texto-3); text-transform: uppercase;">Sin registros históricos</p>
        </div>
    <?php else: ?>
        <?php foreach($pedidos as $pedido): ?>
            <div class="pedido-card-admin">
                <div class="pedido-header">
                    <div>
                        <div class="pedido-id">REGISTRO FINALIZADO — #REQ-<?= $pedido['id_requerimiento'] ?></div>
                        <div class="pedido-title"><?= esc($pedido['titulo']) ?></div>
                    </div>
                    <span class="pedido-status status-finalizado">
                        <i class="bi bi-check-circle-fill mr-1" style="font-size: 4px;"></i>
                        COMPLETADO
                    </span>
                </div>

                <div class="pedido-info">
                    <span><i class="bi bi-building"></i> <?= esc($pedido['empresa_nombre']) ?></span>
                    <span><i class="bi bi-gear-fill"></i> <?= esc($pedido['servicio_nombre']) ?></span>
                    <span><i class="bi bi-calendar-check"></i> <?= isset($pedido['fechacompletado']) ? date('d/m/Y', strtotime($pedido['fechacompletado'])) : '---' ?></span>
                </div>

                <div class="pedido-footer">
                    <span style="font-size: 9px; color: var(--texto-3); font-weight: 700; text-transform: uppercase; letter-spacing: .5px;">Almacenado en historial</span>
                    <button class="btn-outline" onclick="verDetalleSolicitud(<?= $pedido['id'] ?>)">
                        <i class="bi bi-eye"></i> VER SOLICITUD
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function verDetalleSolicitud(id) {
        // Misma lógica que en mis_pedidos.php
        const modal = $('#modal');
        const titulo = $('#modal-titulo');
        const cuerpo = $('#modal-cuerpo');
        const pie = $('#modal-pie');

        Swal.fire({ title: 'Cargando...', background: '#111', color: '#fff', didOpen: () => { Swal.showLoading(); } });

        $.get(`${BASE_URL}/empleado/pedido-detalle/${id}`, function(res) {
            Swal.close();
            if(res.status === 'success') {
                const d = res.data;
                titulo.text('DETALLE DE LA SOLICITUD - #REQ-' + d.idrequerimiento);
                
                let html = `
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">INFORMACIÓN DEL PROYECTO (HISTÓRICO)</h6>
                            <div style="background:#0d0d0d; padding:15px; border-radius:8px; border:1px solid #1e1e1e;">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Cliente</small>
                                        <p style="margin:0; font-weight:600; font-size:13px;">${d.nombreempresa}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Servicio</small>
                                        <p style="margin:0; font-weight:600; font-size:13px; color:var(--amarillo);">${d.servicio}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Estado</small>
                                        <p style="margin:0; font-weight:600; font-size:11px; color:var(--verde);">FINALIZADO</p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Título</small>
                                    <p style="margin:0; font-weight:600; font-size:14px; color:#fff;">${d.titulo}</p>
                                </div>
                                <div>
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Descripción / Brief</small>
                                    <p style="margin:0; font-size:12.5px; line-height:1.6; color:#bbb;">${d.descripcion || 'Sin descripción'}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">FORMATOS Y CANALES</h6>
                            <div style="background:#0d0d0d; padding:15px; border-radius:8px; border:1px solid #1e1e1e;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Canales</small>
                                        <p style="margin:0; font-size:12px; color:#bbb;">${d.canales_difusion ? JSON.parse(d.canales_difusion).join(', ') : '---'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Formatos</small>
                                        <p style="margin:0; font-size:12px; color:#bbb;">${d.formatos_solicitados ? JSON.parse(d.formatos_solicitados).join(', ') : '---'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                cuerpo.html(html);
                pie.html('<button class="btn btn-sm btn-dark" data-dismiss="modal" style="font-weight:700; border:1px solid #333;">CERRAR</button>');
                modal.modal('show');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#111', color: '#fff' });
            }
        });
    }
</script>
<?= $this->endSection() ?>
