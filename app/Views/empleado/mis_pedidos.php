<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<!-- FILTROS (ESTILO ADMIN) -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-8">
        <input type="text" id="busqueda" class="form-control" placeholder="Buscar por título o empresa..." 
            style="background: var(--panel); border: 1px solid var(--borde); color: var(--texto); font-size: 12px; height: 36px; border-radius: 6px;">
    </div>
    <div class="col-md-6 col-lg-4 mt-2 mt-md-0">
        <select class="form-control" style="background: var(--panel); border: 1px solid var(--borde); color: var(--texto-2); font-size: 12px; height: 36px; border-radius: 6px;">
            <option value="">TODOS LOS ESTADOS</option>
            <option value="pendiente_asignado">POR INICIAR</option>
            <option value="en_proceso">EN DESARROLLO</option>
        </select>
    </div>
</div>

<!-- LISTADO (ESTILO ADMIN) -->
<p class="seccion-titulo">Mis Pedidos Asignados</p>

<div id="contenedor-pedidos">
    <?php if(empty($pedidos)): ?>
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 10px;">
            <i class="bi bi-inbox" style="font-size: 30px; color: var(--texto-3);"></i>
            <p class="mt-2" style="font-size: 11px; color: var(--texto-3); text-transform: uppercase;">Bandeja de entrada vacía</p>
        </div>
    <?php else: ?>
        <?php foreach($pedidos as $pedido): ?>
            <div class="pedido-card-admin" id="pedido-<?= $pedido['id'] ?>">
                <div class="pedido-header">
                    <div>
                        <div class="pedido-id"><?= esc(strtoupper($pedido['empresa_nombre'])) ?> — #REQ-<?= $pedido['id_requerimiento'] ?></div>
                        <div class="pedido-title"><?= esc($pedido['titulo']) ?></div>
                    </div>
                    <?php 
                        $statusClass = str_replace('_', '-', $pedido['estado']);
                    ?>
                    <span class="pedido-status status-<?= $statusClass ?>">
                        <i class="bi bi-circle-fill mr-1" style="font-size: 4px;"></i>
                        <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                    </span>
                </div>

                <div class="pedido-info">
                    <span><i class="bi bi-gear-fill"></i> <?= esc($pedido['servicio_nombre']) ?></span>
                    <span><i class="bi bi-flag-fill"></i> <?= strtoupper(esc($pedido['prioridad'])) ?></span>
                    <span><i class="bi bi-calendar-check"></i> <?= isset($pedido['fechafin']) ? date('d/m/Y', strtotime($pedido['fechafin'])) : '---' ?></span>
                </div>

                <div class="pedido-footer">
                    <div class="d-flex gap-2">
                        <button class="btn-outline" onclick="verDetalleSolicitud(<?= $pedido['id'] ?>)">
                            <i class="bi bi-eye mr-1"></i> VER SOLICITUD
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if($pedido['estado'] == 'pendiente_asignado'): ?>
                            <button class="btn-yellow" onclick="abrirModalAccion(<?= $pedido['id'] ?>, 'iniciar')">INICIAR TRABAJO</button>
                        <?php elseif($pedido['estado'] == 'en_proceso'): ?>
                            <button class="btn-green" onclick="abrirModalAccion(<?= $pedido['id'] ?>, 'entregar')">ENTREGAR TRABAJO</button>
                        <?php else: ?>
                            <button class="btn-outline" disabled>EN REVISIÓN</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function verDetalleSolicitud(id) {
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
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">INFORMACIÓN DEL PROYECTO</h6>
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
                                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Tipo Req.</small>
                                        <p style="margin:0; font-weight:600; font-size:13px;">${d.tipo_requerimiento || '---'}</p>
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

                        <div class="col-md-6 mb-4">
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">OBJETIVOS Y PÚBLICO</h6>
                            <div style="background:#0d0d0d; padding:15px; border-radius:8px; border:1px solid #1e1e1e;">
                                <div class="mb-3">
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Objetivo de Comunicación</small>
                                    <p style="margin:0; font-size:12px; color:#bbb;">${d.objetivo_comunicacion || '---'}</p>
                                </div>
                                <div>
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Público Objetivo</small>
                                    <p style="margin:0; font-size:12px; color:#bbb;">${d.publico_objetivo || '---'}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">FORMATOS Y CANALES</h6>
                            <div style="background:#0d0d0d; padding:15px; border-radius:8px; border:1px solid #1e1e1e;">
                                <div class="mb-3">
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Canales de Difusión</small>
                                    <p style="margin:0; font-size:12px; color:#bbb;">${d.canales_difusion ? JSON.parse(d.canales_difusion).join(', ') : '---'}</p>
                                </div>
                                <div>
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700;">Formatos Solicitados</small>
                                    <p style="margin:0; font-size:12px; color:#bbb;">${d.formatos_solicitados ? JSON.parse(d.formatos_solicitados).join(', ') : '---'}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">ARCHIVOS ADJUNTOS</h6>
                            <div id="lista-archivos-requerimiento" style="background:#0d0d0d; padding:10px; border-radius:8px; border:1px solid #1e1e1e;">
                                <p style="font-size:11px; color:var(--texto-3); margin:0;">Buscando adjuntos...</p>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h6 style="color:var(--amarillo); font-family:'Bebas Neue'; letter-spacing:1px; font-size:16px;">ENLACES DE REFERENCIA</h6>
                            <div id="lista-enlaces-requerimiento" style="background:#0d0d0d; padding:10px; border-radius:8px; border:1px solid #1e1e1e;">
                                <p style="font-size:11px; color:var(--texto-3); margin:0;">Buscando enlaces...</p>
                            </div>
                        </div>
                    </div>
                `;

                cuerpo.html(html);
                pie.html('<button class="btn btn-sm btn-dark" data-dismiss="modal" style="font-weight:700; border:1px solid #333;">CERRAR DETALLE</button>');
                
                // Cargar archivos si tiene
                if(res.archivos && res.archivos.length > 0) {
                    let arcHtml = '<div class="row">';
                    res.archivos.forEach(a => {
                        arcHtml += `
                            <div class="col-md-6 mb-2">
                                <a href="${BASE_URL}/${a.ruta}" target="_blank" class="d-flex align-items-center p-2" style="background:#111; border:1px solid #222; border-radius:6px; color:#bbb; text-decoration:none;">
                                    <i class="bi bi-file-earmark-text mr-2" style="color:var(--amarillo);"></i>
                                    <span class="text-truncate" style="font-size:11px;">${a.nombre}</span>
                                </a>
                            </div>
                        `;
                    });
                    arcHtml += '</div>';
                    $('#lista-archivos-requerimiento').html(arcHtml);
                } else {
                    $('#lista-archivos-requerimiento').html('<p style="font-size:11px; color:#555; text-align:center; padding:10px; margin:0;">No hay archivos adjuntos en esta solicitud.</p>');
                }

                // Cargar URLs si tiene
                let enlaceHtml = '<div class="row">';
                
                // URL del cliente (referencia)
                if(res.data && res.data.url_subida) {
                    enlaceHtml += `
                        <div class="col-md-12 mb-2">
                            <div style="margin-bottom:8px;">
                                <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700; font-size:10px;">URL DE REFERENCIA (CLIENTE)</small>
                            </div>
                            <a href="${res.data.url_subida}" target="_blank" class="d-flex align-items-center p-2" style="background:#111; border:1px solid #222; border-radius:6px; color:#bbb; text-decoration:none;">
                                <i class="bi bi-link-45deg mr-2" style="color:var(--amarillo);"></i>
                                <span class="text-truncate" style="font-size:11px;">${res.data.url_subida}</span>
                                <i class="bi bi-box-arrow-up-right ml-2" style="font-size:10px;"></i>
                            </a>
                        </div>
                    `;
                }
                
                // URL de entrega del empleado
                if(res.data && res.data.url_entrega) {
                    enlaceHtml += `
                        <div class="col-md-12 mb-2">
                            <div style="margin-bottom:8px;">
                                <small style="color:var(--texto-3); text-transform:uppercase; font-weight:700; font-size:10px;">URL DE ENTREGA (TÚ)</small>
                            </div>
                            <a href="${res.data.url_entrega}" target="_blank" class="d-flex align-items-center p-2" style="background:#111; border:1px solid #222; border-radius:6px; color:#bbb; text-decoration:none;">
                                <i class="bi bi-link-45deg mr-2" style="color:var(--verde);"></i>
                                <span class="text-truncate" style="font-size:11px;">${res.data.url_entrega}</span>
                                <i class="bi bi-box-arrow-up-right ml-2" style="font-size:10px;"></i>
                            </a>
                        </div>
                    `;
                }
                
                if(enlaceHtml === '<div class="row">') {
                    $('#lista-enlaces-requerimiento').html('<p style="font-size:11px; color:#555; text-align:center; padding:10px; margin:0;">No hay enlaces en esta solicitud.</p>');
                } else {
                    enlaceHtml += '</div>';
                    $('#lista-enlaces-requerimiento').html(enlaceHtml);
                }

                modal.modal('show');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#111', color: '#fff' });
            }
        });
    }

    function abrirModalAccion(id, tipo) {
        const modal = $('#modal');
        const titulo = $('#modal-titulo');
        const cuerpo = $('#modal-cuerpo');
        const pie = $('#modal-pie');

        pie.html('<button class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="font-size: 10px; font-weight: 700;">CANCELAR</button>');

        if(tipo === 'iniciar') {
            titulo.text('Confirmar Inicio');
            cuerpo.html(`
                <div class="text-center py-3">
                    <p style="font-size: 13px; color: #eee; margin-bottom: 0;">¿Deseas iniciar el desarrollo de este pedido? Se notificará al responsable.</p>
                </div>
            `);
            pie.append(`<button class="btn-yellow" onclick="ejecutarAccion(${id}, 'iniciar')">DALE, EMPEZA AHORA</button>`);
        } else if(tipo === 'entregar') {
            titulo.text('Realizar Entrega');
            cuerpo.html(`
                <form id="form-entrega">
                    <div class="form-group mb-3">
                        <label style="font-size: 10px; font-weight: 700; color: var(--texto-3); text-transform: uppercase;">URL del Entregable</label>
                        <input type="text" name="url_entrega" id="url_entrega" class="form-control" placeholder="Link de Drive, Canva, etc." 
                            style="background:#0a0a0a; border:1px solid #222; color:#fff; font-size: 12px; height: 38px;">
                    </div>
                    <div class="form-group mb-3">
                        <label style="font-size: 10px; font-weight: 700; color: var(--texto-3); text-transform: uppercase;">Adjuntar Archivos (PDF, PNG, PPT...)</label>
                        <input type="file" name="archivos_entrega[]" id="archivos_entrega" class="form-control-file" multiple 
                            style="color: var(--texto-3); font-size: 11px; margin-top: 5px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 700; color: var(--texto-3); text-transform: uppercase;">Observaciones</label>
                        <textarea name="notas" id="notas" class="form-control" placeholder="Escribe algo sobre tu entrega..." 
                            style="background:#0a0a0a; border:1px solid #222; color:#fff; font-size: 12px; min-height: 80px;"></textarea>
                    </div>
                </form>
            `);
            pie.append(`<button class="btn-green" onclick="ejecutarAccion(${id}, 'entregar')">ENVIAR ENTREGA</button>`);
        }

        modal.modal('show');
    }

    function ejecutarAccion(id, tipo) {
        let url = tipo === 'iniciar' ? `${BASE_URL}/empleado/pedido-iniciar/${id}` : `${BASE_URL}/empleado/pedido-entregar/${id}`;
        let formData = new FormData();

        if(tipo === 'entregar') {
            const link = $('#url_entrega').val();
            const files = $('#archivos_entrega')[0].files;
            const notas = $('#notas').val();

            if(!link && files.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debes proporcionar un enlace o archivos.', background: '#111', color: '#fff' });
                return;
            }

            formData.append('url_entrega', link);
            formData.append('notas', notas);
            for(let i=0; i<files.length; i++) {
                formData.append('archivos_entrega[]', files[i]);
            }
        }

        Swal.fire({
            title: '¿Confirmar acción?',
            background: '#111',
            color: '#fff',
            confirmButtonColor: '#F5C400',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'No',
            showCancelButton: true
        }).then((result) => {
            if(result.isConfirmed) {
                Swal.fire({ title: 'Procesando...', background: '#111', color: '#fff', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if(res.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Éxito', text: res.message, background: '#111', color: '#fff' }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#111', color: '#fff' });
                        }
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
