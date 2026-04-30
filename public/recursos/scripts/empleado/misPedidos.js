function verDetalleSolicitud(id) {
    const modal = $('#modal');
    const titulo = $('#modal-titulo');
    const cuerpo = $('#modal-cuerpo');
    const pie = $('#modal-pie');

    Swal.fire({ 
        title: 'Cargando expediente...', 
        background: '#0a0a0a', 
        color: '#fff', 
        didOpen: () => { Swal.showLoading(); } 
    });

    $.get(`${BASE_URL}/empleado/pedido-detalle/${id}`, function(res) {
        Swal.close();
        if(res.status === 'success') {
            const d = res.data;
            titulo.html(`<i class="bi bi-file-earmark-text mr-2" style="color:var(--amarillo);"></i> EXPEDIENTE: #REQ-${d.idrequerimiento}`);
            
            let html = `
                <div style="font-family:'DM Sans', sans-serif;">
                    <!-- CABECERA RÁPIDA -->
                    <div class="mb-4" style="background:rgba(245, 196, 0, 0.03); border:1px solid rgba(245, 196, 0, 0.1); border-radius:12px; padding:20px;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; letter-spacing:1px; font-size:10px;">PROYECTO</small>
                                <h4 style="color:#fff; font-weight:700; margin:5px 0 0; font-size:20px;">${d.titulo}</h4>
                                <p style="color:var(--amarillo); font-weight:600; margin:5px 0 0; font-size:13px; text-transform:uppercase;">${d.nombreempresa} — ${d.servicio}</p>
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                <span class="task-status-pill pill-process" style="padding:6px 12px; font-size:11px;">${d.estado.replace('_', ' ')}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CUERPO DETALLE -->
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h6 style="color:#fff; font-family:'Bebas Neue'; letter-spacing:2px; font-size:16px; border-left:3px solid var(--amarillo); padding-left:10px; margin-bottom:15px;">OBJETIVO Y DESCRIPCIÓN</h6>
                            <div style="background:#0d0d0d; padding:20px; border-radius:12px; border:1px solid #1e1e1e;">
                                <div class="mb-4">
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:8px;">Lo que el cliente busca:</small>
                                    <p style="color:#eee; font-size:14px; line-height:1.6; margin:0;">${d.objetivo_comunicacion || 'No especificado'}</p>
                                </div>
                                <hr style="border-top:1px solid #1a1a1a; margin:15px 0;">
                                <div>
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:8px;">Instrucciones / Brief:</small>
                                    <div style="color:#bbb; font-size:13px; line-height:1.7; white-space:pre-wrap;">${d.descripcion || 'Sin descripción detallada.'}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 style="color:#fff; font-family:'Bebas Neue'; letter-spacing:2px; font-size:16px; border-left:3px solid var(--amarillo); padding-left:10px; margin-bottom:15px;">DETALLES TÉCNICOS</h6>
                            <div style="background:#0d0d0d; padding:20px; border-radius:12px; border:1px solid #1e1e1e; height:calc(100% - 31px);">
                                <div class="mb-3">
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:4px;">Canales:</small>
                                    <p style="color:#eee; font-size:13px; margin:0;">${d.canales_difusion ? JSON.parse(d.canales_difusion).join(', ') : '---'}</p>
                                </div>
                                <div class="mb-3">
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:4px;">Formatos:</small>
                                    <p style="color:#eee; font-size:13px; margin:0;">${d.formatos_solicitados ? JSON.parse(d.formatos_solicitados).join(', ') : '---'}</p>
                                </div>
                                <div>
                                    <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:4px;">Público:</small>
                                    <p style="color:#eee; font-size:13px; margin:0;">${d.publico_objetivo || '---'}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 style="color:#fff; font-family:'Bebas Neue'; letter-spacing:2px; font-size:16px; border-left:3px solid var(--amarillo); padding-left:10px; margin-bottom:15px;">RECURSOS Y ADJUNTOS</h6>
                            <div style="background:#0d0d0d; padding:20px; border-radius:12px; border:1px solid #1e1e1e; height:calc(100% - 31px);">
                                <div id="lista-archivos-requerimiento" class="mb-3"></div>
                                <div id="lista-enlaces-requerimiento"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            cuerpo.html(html);
            pie.html('<button class="task-primary-btn btn-view" data-dismiss="modal">ENTENDIDO, VOLVER</button>');
            
            // Archivos
            if(res.archivos && res.archivos.length > 0) {
                let arcHtml = '<div style="display:flex; flex-direction:column; gap:8px;">';
                res.archivos.forEach(a => {
                    arcHtml += `
                        <a href="${BASE_URL}/${a.ruta}" target="_blank" style="display:flex; align-items:center; gap:10px; padding:10px; background:#161616; border:1px solid #222; border-radius:8px; color:#aaa; text-decoration:none; font-size:12px; transition:border-color .2s;" onmouseover="this.style.borderColor='var(--amarillo)'" onmouseout="this.style.borderColor='#222'">
                            <i class="bi bi-cloud-arrow-down" style="color:var(--amarillo); font-size:16px;"></i>
                            <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${a.nombre}</span>
                        </a>`;
                });
                arcHtml += '</div>';
                $('#lista-archivos-requerimiento').html(arcHtml);
            } else {
                $('#lista-archivos-requerimiento').html('<p style="font-size:11px; color:#444; font-style:italic;">No hay archivos adjuntos.</p>');
            }

            // Enlaces
            let linkHtml = '';
            if(d.url_subida) {
                linkHtml += `
                    <div style="margin-top:10px;">
                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:9px; display:block; margin-bottom:5px;">Link de referencia:</small>
                        <a href="${d.url_subida}" target="_blank" style="color:var(--amarillo); font-size:12px; text-decoration:underline; word-break:break-all;">${d.url_subida}</a>
                    </div>`;
            }
            $('#lista-enlaces-requerimiento').html(linkHtml || '<p style="font-size:11px; color:#444; font-style:italic;">No hay enlaces externos.</p>');

            modal.modal('show');
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#0a0a0a', color: '#fff' });
        }
    });
}

function abrirModalAccion(id, tipo) {
    const modal = $('#modal');
    const titulo = $('#modal-titulo');
    const cuerpo = $('#modal-cuerpo');
    const pie = $('#modal-pie');

    pie.html('<button class="task-primary-btn btn-view" data-dismiss="modal">CANCELAR</button>');

    if(tipo === 'iniciar') {
        titulo.html('<i class="bi bi-play-circle mr-2"></i> Confirmar Inicio de Trabajo');
        cuerpo.html(`
            <div class="text-center py-4">
                <div style="width:60px; height:60px; background:rgba(245, 196, 0, 0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <i class="bi bi-lightning-charge-fill" style="color:var(--amarillo); font-size:24px;"></i>
                </div>
                <h5 style="color:#fff; font-weight:700;">¿Estás listo para empezar?</h5>
                <p style="color:var(--texto-3); font-size:13px;">Se notificará al administrador que has tomado este pedido y comenzarás a trabajar en él ahora mismo.</p>
            </div>
        `);
        pie.append(`<button class="task-primary-btn btn-start" onclick="ejecutarAccion(${id}, 'iniciar')">SÍ, EMPEZAR AHORA</button>`);
    } else if(tipo === 'entregar') {
        titulo.html('<i class="bi bi-cloud-arrow-up mr-2"></i> Enviar Trabajo Terminado');
        cuerpo.html(`
            <div class="p-2">
                <form id="form-entrega">
                    <div class="form-group mb-4">
                        <label class="kb-modal-label">Enlace del entregable (Drive, Canva, Wetransfer, etc.)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background:#0d0d0d; border:1px solid #222; border-right:none; color:var(--amarillo);"><i class="bi bi-link-45deg"></i></span>
                            </div>
                            <input type="text" name="url_entrega" id="url_entrega" class="form-control" placeholder="Pega el link aquí..." style="border-left:none;">
                        </div>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="kb-modal-label">Cargar Archivos Directos (Opcional)</label>
                        <div style="border:1px dashed #333; border-radius:10px; padding:20px; text-align:center; background:#080808;">
                            <i class="bi bi-files mb-2" style="font-size:24px; color:#333; display:block;"></i>
                            <input type="file" name="archivos_entrega[]" id="archivos_entrega" class="form-control-file" multiple style="font-size:11px; color:#555;">
                            <small style="display:block; margin-top:10px; color:#444; font-size:10px;">Puedes seleccionar varios archivos</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="kb-modal-label">Mensaje para el administrador</label>
                        <textarea name="notas" id="notas" class="form-control" placeholder="Escribe detalles sobre la entrega o instrucciones especiales..." rows="3"></textarea>
                    </div>
                </form>
            </div>
        `);
        pie.append(`<button class="task-primary-btn btn-deliver" onclick="ejecutarAccion(${id}, 'entregar')">FINALIZAR Y ENTREGAR</button>`);
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
            Swal.fire({ icon: 'warning', title: 'Falta información', text: 'Por favor, proporciona un enlace o adjunta los archivos de tu trabajo.', background: '#0a0a0a', color: '#fff' });
            return;
        }

        formData.append('url_entrega', link);
        formData.append('notas', notas);
        for(let i=0; i<files.length; i++) {
            formData.append('archivos_entrega[]', files[i]);
        }
    }

    Swal.fire({
        title: '¿Confirmar envío?',
        text: 'Asegúrate de que todo esté correcto.',
        background: '#0a0a0a',
        color: '#fff',
        confirmButtonColor: '#F5C400',
        confirmButtonText: 'SÍ, CONFIRMAR',
        cancelButtonText: 'CANCELAR',
        showCancelButton: true
    }).then((result) => {
        if(result.isConfirmed) {
            Swal.fire({ title: 'Procesando...', background: '#0a0a0a', color: '#fff', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if(res.status === 'success') {
                        Swal.fire({ icon: 'success', title: '¡Hecho!', text: res.message, background: '#0a0a0a', color: '#fff' }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#0a0a0a', color: '#fff' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error fatal', text: 'No se pudo procesar la solicitud.', background: '#0a0a0a', color: '#fff' });
                }
            });
        }
    });
}