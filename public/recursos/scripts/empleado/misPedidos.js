function verDetalleSolicitud(id) {
  // Registrar el ID activo para que Pusher pueda refrescarlo sin recargar
  window._modalIdActual = id;

  const modal = $("#modal");
  const titulo = $("#modal-titulo");
  const cuerpo = $("#modal-cuerpo");
  const pie = $("#modal-pie");

  // Limpiar ID al cerrar el modal
  modal.off('hidden.bs.modal.emp').on('hidden.bs.modal.emp', function () {
    window._modalIdActual = null;
  });

  Swal.fire({
    title: "Cargando expediente...",
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.get(`${BASE_URL}/empleado/pedido-detalle/${id}`, function (res) {
    Swal.close();
    if (res.status === "success") {
      const d = res.data;
      titulo.html(
        `<i class="bi bi-file-earmark-text mr-2" style="color:var(--amarillo);"></i> EXPEDIENTE: #REQ-${d.id_requerimiento || d.idrequerimiento}`,
      );

      let html = `
                <div class="expediente-contenedor" style="font-family:'DM Sans', sans-serif; padding: 10px;">
                    <!-- CABECERA -->
                    <div class="mb-4 expediente-header-card" style="background:var(--sidebar-active-glow); border:1px solid var(--amarillo); border-radius:12px; padding:20px;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; letter-spacing:1px; font-size:10px;">PROYECTO ASIGNADO</small>
                                <h4 class="exp-titulo" style="color:var(--texto); font-weight:700; margin:5px 0 0; font-size:22px;">${d.titulo}</h4>
                                <p style="color:var(--amarillo); font-weight:600; margin:5px 0 0; font-size:13px; text-transform:uppercase;">${d.nombreempresa} — ${d.servicio}</p>
                                ${d.fechainicio ? `
                                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 6px; color: var(--texto-2); font-size: 11px;">
                                        <i class="bi bi-calendar-play" style="color: var(--amarillo);"></i>
                                        <span>INICIO TRABAJO: ${d.fechainicio.split(' ')[0].split('-').reverse().join('/')} ${d.fechainicio.split(' ')[1] ? d.fechainicio.split(' ')[1].substring(0, 5) : ''}</span>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                <span class="task-status-pill ${d.estado === 'pendiente_asignado' ? 'pill-new' : (d.estado === 'en_proceso' ? 'pill-process' : 'pill-revision')}" style="padding:8px 16px; font-size:12px;">${d.estado.replace("_", " ").toUpperCase()}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 1. DESCRIPCIÓN -->
                    <div class="mb-4">
                        <h6 class="exp-subseccion-titulo" style="color:var(--texto); font-family:'Bebas Neue'; letter-spacing:2px; font-size:18px; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                            <span class="exp-icon-bg" style="background:var(--amarillo); color:#000; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:16px;"><i class="bi bi-card-text"></i></span>
                            DESCRIPCIÓN DEL REQUERIMIENTO
                        </h6>
                        <div class="exp-card-info" style="background:var(--mini-card-bg); padding:25px; border-radius:12px; border:1px solid var(--borde); color:var(--texto-2); font-size:14px; line-height:1.7; white-space:pre-wrap;">${d.descripcion || "Sin descripción detallada."}</div>
                    </div>

                    <!-- 2. ESTRATEGIA -->
                    <div class="mb-4">
                        <h6 class="exp-subseccion-titulo" style="color:var(--texto); font-family:'Bebas Neue'; letter-spacing:2px; font-size:18px; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                            <span class="exp-icon-bg" style="background:var(--amarillo); color:#000; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:16px;"><i class="bi bi-compass"></i></span>
                            ESTRATEGIA DE COMUNICACIÓN
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="exp-card-info" style="background:var(--mini-card-bg); padding:20px; border-radius:12px; border:1px solid var(--borde); height:100%;">
                                    <small style="color:var(--amarillo); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:10px; letter-spacing:1px;">OBJETIVO PRINCIPAL</small>
                                    <p style="color:var(--texto); font-size:14px; font-weight:600; margin:0; line-height:1.5;">${d.objetivo_comunicacion || "No especificado"}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="exp-card-info" style="background:var(--mini-card-bg); padding:20px; border-radius:12px; border:1px solid var(--borde); height:100%;">
                                    <small style="color:var(--amarillo); text-transform:uppercase; font-weight:800; font-size:10px; display:block; margin-bottom:10px; letter-spacing:1px;">PÚBLICO OBJETIVO</small>
                                    <p style="color:var(--texto); font-size:14px; font-weight:600; margin:0; line-height:1.5;">${d.publico_objetivo || "No especificado"}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. TÉCNICO -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h6 class="exp-subseccion-titulo" style="color:var(--texto); font-family:'Bebas Neue'; letter-spacing:2px; font-size:18px; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                                <span class="exp-icon-bg" style="background:var(--amarillo); color:#000; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:16px;"><i class="bi bi-broadcast"></i></span>
                                CANALES
                            </h6>
                            <div id="canales-container" class="exp-card-info" style="background:var(--mini-card-bg); padding:20px; border-radius:12px; border:1px solid var(--borde); display:flex; flex-wrap:wrap; gap:8px;">
                                ${d.canales_difusion ? JSON.parse(d.canales_difusion).map(c => `<span style="background:var(--panel); color:var(--texto); border:1px solid var(--borde); padding:4px 12px; border-radius:6px; font-size:11px; font-weight:700; text-transform:uppercase;">${c}</span>`).join("") : '<span style="color:var(--texto-3); font-size:11px; font-style:italic;">No especificados</span>'}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="exp-subseccion-titulo" style="color:var(--texto); font-family:'Bebas Neue'; letter-spacing:2px; font-size:18px; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                                <span class="exp-icon-bg" style="background:var(--amarillo); color:#000; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:16px;"><i class="bi bi-layers"></i></span>
                                FORMATOS
                            </h6>
                            <div id="formatos-container" class="exp-card-info" style="background:var(--mini-card-bg); padding:20px; border-radius:12px; border:1px solid var(--borde); display:flex; flex-wrap:wrap; gap:8px;">
                                ${d.formatos_solicitados ? JSON.parse(d.formatos_solicitados).map(f => `<span style="background:var(--panel); color:var(--texto); border:1px solid var(--borde); padding:4px 12px; border-radius:6px; font-size:11px; font-weight:700; text-transform:uppercase;">${f}</span>`).join("") : '<span style="color:var(--texto-3); font-size:11px; font-style:italic;">No especificados</span>'}
                            </div>
                        </div>
                    </div>

                    <!-- 4. RECURSOS -->
                    <div class="mb-2">
                        <h6 class="exp-subseccion-titulo" style="color:var(--texto); font-family:'Bebas Neue'; letter-spacing:2px; font-size:18px; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                            <span class="exp-icon-bg" style="background:var(--amarillo); color:#000; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:16px;"><i class="bi bi-folder2-open"></i></span>
                            RECURSOS DEL CLIENTE
                        </h6>
                        <div class="exp-card-info" style="background:var(--mini-card-bg); padding:20px; border-radius:12px; border:1px solid var(--borde);">
                            <div id="lista-archivos-requerimiento" class="mb-3"></div>
                            <div id="lista-enlaces-requerimiento"></div>
                        </div>
                    </div>
                </div>
            `;

      cuerpo.html(html);
      pie.html(
        '<button class="btn-yellow" data-dismiss="modal" style="width:100%;">ENTENDIDO, VOLVER</button>',
      );

      // Archivos del cliente
      const archivosCliente = res.archivos_cliente || [];
      if (archivosCliente.length > 0) {
        let arcHtml =
          '<div style="display:flex; flex-direction:column; gap:8px;">';
        archivosCliente.forEach((a) => {
          arcHtml += `
                        <a href="${BASE_URL}/${a.ruta}" target="_blank" class="exp-archivo-item" style="display:flex; align-items:center; gap:10px; padding:12px; background:var(--panel); border:1px solid var(--borde); border-radius:10px; color:var(--texto-2); text-decoration:none; font-size:12px; transition:all .2s;">
                            <i class="bi bi-cloud-arrow-down" style="color:var(--amarillo); font-size:16px;"></i>
                            <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${a.nombre}</span>
                        </a>`;
        });
        arcHtml += "</div>";
        $("#lista-archivos-requerimiento").html(arcHtml);
      } else {
        $("#lista-archivos-requerimiento").html(
          '<p style="font-size:11px; color:#444; font-style:italic;">No hay archivos adjuntos.</p>',
        );
      }

      // Enlaces del cliente
      let linkHtml = "";
      if (d.url_subida) {
        linkHtml += `
                    <div style="margin-top:10px;">
                        <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:9px; display:block; margin-bottom:5px;">Link de referencia:</small>
                        <a href="${d.url_subida}" target="_blank" style="color:var(--amarillo); font-size:12px; text-decoration:underline; word-break:break-all;">${d.url_subida}</a>
                    </div>`;
      }
      $("#lista-enlaces-requerimiento").html(
        linkHtml ||
        '<p style="font-size:11px; color:#444; font-style:italic;">No hay enlaces externos.</p>',
      );

      // Entregas del Empleado (Muestra lo que envió el empleado en pedidos finalizados o en revisión)
      if (d.url_entrega || (res.archivos_empleado && res.archivos_empleado.length > 0) || d.observacion_revision) {
        let filesHtml = "";
        const archivosEmp = res.archivos_empleado || [];
        if (archivosEmp.length > 0) {
          filesHtml = '<div style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;">';
          archivosEmp.forEach((a) => {
            filesHtml += `
              <a href="${BASE_URL}/${a.ruta}" target="_blank" class="exp-archivo-item" style="display:flex; align-items:center; gap:10px; padding:12px; background:var(--panel); border:1px solid var(--borde); border-radius:10px; color:var(--texto-2); text-decoration:none; font-size:12px; transition:all .2s;">
                  <i class="bi bi-file-earmark-arrow-down" style="color:#10b981; font-size:16px;"></i>
                  <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${a.nombre}</span>
              </a>`;
          });
          filesHtml += "</div>";
        }

        let linkEntregaHtml = "";
        if (d.url_entrega) {
          linkEntregaHtml = `
            <div style="margin-bottom:10px;">
                <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:9px; display:block; margin-bottom:5px;">Enlace del Entregable:</small>
                <a href="${d.url_entrega}" target="_blank" style="color:#10b981; font-size:13px; font-weight:600; text-decoration:underline; word-break:break-all;">
                    <i class="bi bi-link-45deg"></i> ${d.url_entrega}
                </a>
            </div>`;
        }

        let notasHtml = "";
        if (d.observacion_revision) {
          notasHtml = `
            <div style="margin-top:10px; padding-top:10px; border-top:1px solid var(--borde);">
                <small style="color:var(--texto-3); text-transform:uppercase; font-weight:800; font-size:9px; display:block; margin-bottom:5px;">Notas de Entrega:</small>
                <p style="color:var(--texto-2); font-size:12px; margin:0; font-style:italic;">"${d.observacion_revision}"</p>
            </div>`;
        }

        const entregaHtml = `
          <!-- 5. ENTREGABLES DEL EMPLEADO -->
          <div class="mb-4 mt-3">
              <h6 class="exp-subseccion-titulo" style="color:var(--texto); font-family:'Bebas Neue'; letter-spacing:2px; font-size:18px; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                  <span class="exp-icon-bg" style="background:#10b981; color:#fff; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:16px;"><i class="bi bi-patch-check"></i></span>
                  TRABAJO ENTREGADO
              </h6>
              <div class="exp-card-info" style="background:var(--mini-card-bg); padding:20px; border-radius:12px; border:1px solid rgba(16, 185, 129, 0.2);">
                  ${linkEntregaHtml}
                  ${filesHtml}
                  ${notasHtml}
              </div>
          </div>
        `;
        cuerpo.append(entregaHtml);
      }

      // ── TRACKING DEL PEDIDO en tiempo real ────────────────────────────────
      const _trackHtml = (res.tracking && res.tracking.length > 0)
        ? _renderTrackingEmpleado(res.tracking)
        : '<p style="font-size:11px;color:#555;font-style:italic;">Sin historial registrado.</p>';
      cuerpo.append(
        '<div class="mt-4" style="border-top:1px solid var(--borde);padding-top:15px;">'
        + '<h6 style="color:var(--texto);font-family:\'Bebas Neue\';letter-spacing:2px;font-size:18px;margin-bottom:12px;">'
        + '<i class="bi bi-clock-history" style="color:var(--amarillo);margin-right:8px;"></i>HISTORIAL DEL PEDIDO</h6>'
        + '<div id="emp-tracking-container" style="max-height:200px;overflow-y:auto;">'
        + _trackHtml + '</div></div>'
      );

      modal.modal("show");
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: res.message
      });
    }
  });
}

function abrirModalAccion(id, tipo) {
  const modal = $("#modal");
  const titulo = $("#modal-titulo");
  const cuerpo = $("#modal-cuerpo");
  const pie = $("#modal-pie");

  pie.html(
    '<button class="task-primary-btn btn-view" data-dismiss="modal">CANCELAR</button>',
  );

  if (tipo === "iniciar") {
    Swal.fire({
      title: '¿Iniciar esta tarea?',
      text: "Se notificará el inicio del trabajo.",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f5c400',
      confirmButtonText: 'Sí, empezar ahora',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        ejecutarAccion(id, 'iniciar');
      }
    });
    return;
  } else if (tipo === "entregar") {
    titulo.html(
      '<i class="bi bi-cloud-arrow-up mr-2" style="color:var(--amarillo);"></i> <span style="font-family:\'Bebas Neue\'; letter-spacing:1px; font-size:24px;">ENVIAR TRABAJO TERMINADO</span>',
    );
    cuerpo.html(`
            <div class="p-3" style="font-family:'DM Sans', sans-serif;">
                <form id="form-entrega">
                    <div class="form-group mb-4">
                        <label style="color:var(--texto); font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:12px;">Enlace del entregable (Drive, Canva, Wetransfer, etc.)</label>
                        <div class="input-group" style="background:var(--mini-card-bg); border:1px solid var(--borde); border-radius:12px; overflow:hidden; transition:border-color 0.3s;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background:transparent; border:none; color:var(--amarillo); font-size:18px;"><i class="bi bi-link-45deg"></i></span>
                            </div>
                            <input type="url" name="url_entrega" id="url_entrega" class="form-control" placeholder="https://..." 
                                style="background:transparent; border:none; color:var(--texto); font-size:14px; height:45px; padding-left:0;">
                        </div>
                        <small style="color:var(--texto-3); font-size:10px; margin-top:8px; display:block;"><i class="bi bi-info-circle mr-1"></i> El enlace debe comenzar con http:// o https://</small>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label style="color:var(--texto); font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:12px;">Cargar Archivos Directos (Opcional)</label>
                        
                        <div class="upload-area-simple" id="area-subida-entrega" style="border:2px dashed var(--borde); border-radius:15px; padding:30px; text-align:center; background:var(--panel); cursor:pointer; transition:all 0.3s;">
                            <i class="bi bi-cloud-plus-fill mb-2" style="font-size:32px; color:var(--amarillo); display:block;"></i>
                            <span style="color:var(--texto); font-weight:600; font-size:13px;">Click para agregar archivos</span>
                            <p style="color:var(--texto-3); font-size:10px; margin:5px 0 0;">Puedes seleccionar varios archivos (Imágenes, PDF, etc.)</p>
                        </div>
                        
                        <input type="file" name="archivos_entrega[]" id="archivos_entrega" class="d-none" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.zip">
                        
                        <div id="lista-archivos-entrega" style="margin-top:15px; display:flex; flex-direction:column; gap:8px;"></div>
                    </div>

                    <div class="form-group">
                        <label style="color:var(--texto); font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:12px;">Mensaje para el administrador</label>
                        <textarea name="notas" id="notas" class="form-control" 
                            style="background:var(--mini-card-bg); border:1px solid var(--borde); color:var(--texto); border-radius:12px; padding:15px; font-size:14px; resize:none;" 
                            placeholder="Describe detalles sobre la entrega o instrucciones especiales..." rows="3"></textarea>
                    </div>
                </form>
            </div>
        `);

    // Lógica para el input de archivos
    const area = document.getElementById("area-subida-entrega");
    const input = document.getElementById("archivos_entrega");
    const lista = document.getElementById("lista-archivos-entrega");

    area.addEventListener("click", () => input.click());

    input.addEventListener("change", () => {
      lista.innerHTML = "";
      Array.from(input.files).forEach((f) => {
        lista.innerHTML += `
                    <div style="background:var(--panel); border:1px solid var(--borde); border-radius:8px; padding:10px 15px; display:flex; align-items:center; gap:12px; color:var(--texto-2); font-size:12px;">
                        <i class="bi bi-file-earmark-check" style="color:var(--amarillo); font-size:16px;"></i>
                        <span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${f.name}</span>
                        <small style="color:var(--texto-3);">${(f.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                `;
      });
    });

    pie.append(
      `<button class="task-primary-btn btn-deliver" onclick="ejecutarAccion(${id}, 'entregar')">FINALIZAR Y ENTREGAR</button>`,
    );
  }

  modal.modal("show");
}

async function ejecutarAccion(id, tipo) {
  let url =
    tipo === "iniciar"
      ? `${BASE_URL}/empleado/pedido-iniciar/${id}`
      : `${BASE_URL}/empleado/pedido-entregar/${id}`;
  let formData = new FormData();

  if (tipo === "iniciar") {
    Swal.fire({ title: "Procesando...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
      formData.append('csrf_test_name', $('meta[name="csrf-token"]').attr('content'));
      const response = await fetch(url, { method: "POST", body: formData });
      const res = await response.json();
      if (res.status === "success") {
        Swal.fire({ icon: "success", title: "¡Hecho!", text: res.message }).then(() => location.reload());
      } else {
        Swal.fire({ icon: "error", title: "Error", text: res.message });
      }
    } catch {
      Swal.fire({ icon: "error", title: "Error fatal", text: "No se pudo procesar la solicitud." });
    }
    return;
  }

  // Solo llega aquí si es "entregar"
  const link = $("#url_entrega").val();
  const files = $("#archivos_entrega")[0].files;
  const notas = $("#notas").val();

  if (link) {
    const urlPattern = /^(https?:\/\/)/i;
    if (!urlPattern.test(link)) {
      Swal.fire({ icon: "warning", title: "URL Inválida", text: "El enlace debe comenzar con http:// o https://" });
      return;
    }
  }

  if (!link && files.length === 0) {
    Swal.fire({ icon: "warning", title: "Falta información", text: "Por favor, proporciona un enlace o adjunta los archivos de tu trabajo." });
    return;
  }

  formData.append("url_entrega", link);
  formData.append("notas", notas);
  for (let i = 0; i < files.length; i++) {
    formData.append("archivos_entrega[]", files[i]);
  }

  formData.append('csrf_test_name', $('meta[name="csrf-token"]').attr('content'));
  const result = await Swal.fire({
    title: "¿Confirmar envío?",
    text: "Asegúrate de que todo esté correcto.",
    confirmButtonColor: "#F5C400",
    confirmButtonText: "SÍ, CONFIRMAR",
    cancelButtonText: "CANCELAR",
    showCancelButton: true,
  });

  if (result.isConfirmed) {
    Swal.fire({ title: "Procesando...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
      const response = await fetch(url, { method: "POST", body: formData });
      const res = await response.json();
      if (res.status === "success") {
        Swal.fire({ icon: "success", title: "¡Hecho!", text: res.message }).then(() => location.reload());
      } else {
        Swal.fire({ icon: "error", title: "Error", text: res.message });
      }
    } catch {
      Swal.fire({ icon: "error", title: "Error fatal", text: "No se pudo procesar la solicitud." });
    }
  }
}

// LÓGICA DE BÚSQUEDA Y FILTRADO
$(document).ready(function () {
  let timeoutBusqueda = null;

  $('#busqueda').on('input', function () {
    // Limpiar el timeout previo
    if (timeoutBusqueda) clearTimeout(timeoutBusqueda);

    // Si estamos en la vista de historial, esperar 1.5 segundos; si no, 300ms
    const delay = $('#contenedor-historial').length ? 1500 : 300;

    // Iniciar nuevo timeout
    timeoutBusqueda = setTimeout(function () {
      filtrarResultados();
    }, delay);
  });

  $('#filtro-estado').on('change', function () {
    filtrarResultados();
  });

  function filtrarResultados() {
    const query = $('#busqueda').val().toLowerCase().trim();
    const filterEl = $('#filtro-estado');
    const estado = filterEl.length ? filterEl.val() : '';

    $('.emp-task-card').each(function () {
      const card = $(this);
      const titulo = card.find('.task-title').text().toLowerCase();
      const cliente = card.find('.task-client').text().toLowerCase();
      const cardEstado = card.data('estado') || '';

      const coincideQuery = query === '' || titulo.includes(query) || cliente.includes(query);
      const coincideEstado = estado === '' || cardEstado === estado;

      if (coincideQuery && coincideEstado) {
        card.closest('.col-12').fadeIn(200);
      } else {
        card.closest('.col-12').fadeOut(200);
      }
    });
  }

  // ── PUSHER: TIEMPO REAL PARA EMPLEADO ──────────────────────────────────────
  if (typeof RFPusher !== 'undefined') {
    function _actualizarVista() {
      const modalAbierto = $('#modal').hasClass('show');

      if (modalAbierto && window._modalIdActual) {
        // Modal abierto → refrescar SOLO el contenido sin cerrar
        _refrescarModalEmpleado(window._modalIdActual);
      } else {
        // Modal cerrado → recargar la lista de tarjetas
        location.reload();
      }
    }

    RFPusher.on('solicitud.actualizada', _actualizarVista);
    RFPusher.on('solicitud.nueva', _actualizarVista);
  }
});

// ── REFRESCAR MODAL DEL EMPLEADO (tracking + datos en tiempo real) ──────────
function _refrescarModalEmpleado(id) {
  $.get(`${BASE_URL}/empleado/pedido-detalle/${id}`, function (res) {
    if (res.status !== 'success') return;

    const d = res.data;

    // Actualizar estado en el header del modal
    const pill = document.querySelector('.emp-estado-pill');
    if (pill) {
      const estadoLabel = { pendiente_asignado: 'PENDIENTE', en_proceso: 'EN PROCESO', en_revision: 'EN REVISIÓN', finalizado: 'FINALIZADO' };
      pill.textContent = estadoLabel[d.estado] || d.estado.toUpperCase();
    }

    // Actualizar sección de tracking si existe
    const trackingContainer = document.getElementById('emp-tracking-container');
    if (trackingContainer && res.tracking && res.tracking.length > 0) {
      trackingContainer.innerHTML = _renderTrackingEmpleado(res.tracking);
    }
  });
}

function _renderTrackingEmpleado(tracking) {
  const iconos = {
    pendiente_asignado: { icon: 'bi-person-check-fill', color: '#f59e0b' },
    en_proceso: { icon: 'bi-play-circle-fill', color: '#a855f7' },
    en_revision: { icon: 'bi-send-check-fill', color: '#f97316' },
    finalizado: { icon: 'bi-check-circle-fill', color: '#10b981' },
  };

  return tracking.map(t => {
    const cfg = iconos[t.estado] || { icon: 'bi-circle', color: '#888' };
    const fecha = t.fecha_registro
      ? new Date(t.fecha_registro).toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
      : '---';
    return `
            <div style="display:flex; gap:12px; align-items:flex-start; padding:10px 0; border-bottom:1px solid var(--borde);">
                <div style="flex-shrink:0; width:32px; height:32px; border-radius:50%; background:${cfg.color}22; display:flex; align-items:center; justify-content:center;">
                    <i class="bi ${cfg.icon}" style="color:${cfg.color}; font-size:14px;"></i>
                </div>
                <div style="flex:1;">
                    <p style="margin:0; font-size:12px; color:var(--texto); font-weight:600; line-height:1.4;">${t.accion}</p>
                    <small style="color:var(--texto-3); font-size:10px;">${fecha}</small>
                </div>
            </div>`;
  }).join('');
}

// =============================================================================
// CRONÓMETRO DE SESIONES (Play / Pausa)
// =============================================================================

// Registro global de intervals activos: { idAtencion: intervalId }
const _cronoIntervals = {};

// Segundos acumulados en memoria (base desde servidor + tick local)
const _cronoBase = {};

// Timestamp local en que empezó el tick actual (para no depender del reloj del servidor)
const _cronoTick = {};

/**
 * Consulta el servidor y arranca o muestra el cronómetro según el estado.
 * Llamado al cargar la página para cada tarjeta en_proceso.
 */
function cronoInicializarDesdeServidor(id) {
  fetch(`${BASE_URL}/empleado/sesion/estado/${id}`)
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') return;

      _cronoBase[id] = res.segundos_totales || 0;

      if (res.activa && res.hora_inicio_sesion) {
        // Calcular cuántos segundos han pasado desde que el servidor abrió la sesión
        const servidorInicio = new Date(res.hora_inicio_sesion.replace(' ', 'T')).getTime();
        const ahora = Date.now();
        const extraSeg = Math.max(0, Math.floor((ahora - servidorInicio) / 1000));

        // Restar esos segundos extra del total (porque getTotalSegundos ya incluye la sesión activa)
        // Usamos la base tal como viene y dejamos que el tick sume desde ahora.
        _cronoTick[id] = Date.now() - (extraSeg * 1000);
        _cronoActivar(id);
      } else {
        _cronoPausar(id, false);
      }

      _cronoActualizar(id);
    })
    .catch(err => console.error("Error al inicializar timer:", err));
}

/** Activa el ticker y cambia la UI a "ACTIVO" */
function _cronoActivar(id) {
  // Guardar el timestamp de inicio del tick
  if (!_cronoTick[id]) _cronoTick[id] = Date.now();

  // Limpiar interval anterior si existía
  if (_cronoIntervals[id]) clearInterval(_cronoIntervals[id]);

  // Tick cada segundo
  _cronoIntervals[id] = setInterval(function () {
    _cronoActualizar(id);
  }, 1000);

  // UI: mostrar "ACTIVO"
  const dot = document.getElementById(`crono-dot-${id}`);
  const label = document.getElementById(`crono-label-${id}`);
  const btnP = document.getElementById(`btn-play-${id}`);
  const btnPa = document.getElementById(`btn-pausa-${id}`);
  const btnE = document.getElementById(`btn-entregar-${id}`);

  if (dot) { dot.style.display = 'inline-block'; }
  if (label) { label.textContent = 'TIEMPO ACTIVO'; label.className = 'crono-activo'; }
  if (btnP) { btnP.style.display = 'none'; }
  if (btnPa) { btnPa.style.display = 'flex'; }
  if (btnE) { btnE.disabled = false; btnE.classList.remove('opacity-50'); btnE.style.cursor = 'pointer'; }
}

/** Detiene el ticker y cambia la UI a "PAUSADO" */
function _cronoPausar(id, stopInterval = true) {
  if (stopInterval && _cronoIntervals[id]) {
    clearInterval(_cronoIntervals[id]);
    delete _cronoIntervals[id];
  }
  delete _cronoTick[id];

  const dot = document.getElementById(`crono-dot-${id}`);
  const label = document.getElementById(`crono-label-${id}`);
  const btnP = document.getElementById(`btn-play-${id}`);
  const btnPa = document.getElementById(`btn-pausa-${id}`);
  const btnE = document.getElementById(`btn-entregar-${id}`);

  if (dot) { dot.style.display = 'none'; }
  if (label) { label.textContent = 'PAUSADO'; label.className = 'crono-pausado'; }
  if (btnP) { btnP.style.display = 'flex'; }
  if (btnPa) { btnPa.style.display = 'none'; }
  if (btnE) { btnE.disabled = true; btnE.classList.add('opacity-50'); btnE.style.cursor = 'not-allowed'; }
}

/** Calcula el tiempo actual y actualiza el display */
function _cronoActualizar(id) {
  let total = _cronoBase[id] || 0;

  // Si hay tick activo, sumar segundos transcurridos
  if (_cronoTick[id]) {
    const transcurrido = Math.floor((Date.now() - _cronoTick[id]) / 1000);
    total += transcurrido;
  }

  // Ya no actualizamos cronometro-display porque lo eliminamos
  // Sólo el label de estado
}

/**
 * Botón REANUDAR → llama al endpoint y activa el timer local.
 */
function cronPlay(id) {
  const btn = document.getElementById(`btn-play-${id}`);
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> INICIANDO...'; }

  const fd = new FormData();
  fd.append('csrf_test_name', $('meta[name="csrf-token"]').attr('content'));

  fetch(`${BASE_URL}/empleado/sesion/iniciar/${id}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        _cronoTick[id] = Date.now();
        _cronoActivar(id);
      } else {
        Swal.fire({ icon: 'warning', title: 'Atención', text: res.message, confirmButtonColor: '#f5c400' });
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-play-fill"></i> REANUDAR'; }
      }
    })
    .catch(function () {
      Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar con el servidor.' });
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-play-fill"></i> REANUDAR'; }
    });
}

/**
 * Botón PAUSAR → pide motivo con SweetAlert y llama al endpoint.
 */
function cronPausa(id) {
  Swal.fire({
    title: '¿Pausar sesión de trabajo?',
    html: `
      <p style="font-size:13px; color:#aaa; margin-bottom:6px;">El motivo es <strong style="color:#f5c400;">obligatorio</strong>. Describe brevemente por qué pausas (el responsable lo verá).</p>
      <textarea id="swal-motivo" class="swal2-textarea" placeholder="Ej: Reunión de equipo, almuerzo, esperando brief..." style="min-height:80px; font-size:13px; background:#222; color:#fff; border:1px solid #444; border-radius:8px; width:90%; padding:10px; box-sizing:border-box; resize:vertical;"></textarea>
      <div id="swal-motivo-error" style="color:#ef4444; font-size:11px; font-weight:700; margin-top:6px; display:none; text-align:left; padding-left:5%;">
        <i class="bi bi-exclamation-triangle-fill" style="margin-right:4px;"></i>Debes ingresar el motivo de la pausa para continuar.
      </div>
    `,
    background: '#1e1e1e',
    color: '#ffffff',
    confirmButtonText: 'PAUSAR AHORA',
    confirmButtonColor: '#f5c400',
    cancelButtonText: 'CANCELAR',
    cancelButtonColor: '#555',
    showCancelButton: true,
    focusConfirm: false,
    allowOutsideClick: false,
    preConfirm: () => {
      const motivo = document.getElementById('swal-motivo').value.trim();
      const errorDiv = document.getElementById('swal-motivo-error');
      if (!motivo) {
        errorDiv.style.display = 'block';
        document.getElementById('swal-motivo').style.borderColor = '#ef4444';
        document.getElementById('swal-motivo').focus();
        Swal.showValidationMessage('El motivo de la pausa es obligatorio. Por favor, indícalo.');
        return false;
      }
      errorDiv.style.display = 'none';
      return motivo;
    }
  }).then(result => {
    if (!result.isConfirmed) return;

    const motivo = result.value;
    const fd = new FormData();
    fd.append('motivo_pausa', motivo);
    fd.append('csrf_test_name', $('meta[name="csrf-token"]').attr('content'));

    Swal.fire({ title: 'Pausando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    fetch(`${BASE_URL}/empleado/sesion/pausar/${id}`, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        Swal.close();
        if (res.status === 'success') {
          // Actualizar la base con los segundos reales que devuelve el servidor
          _cronoBase[id] = res.segundos_totales || _cronoBase[id];
          _cronoPausar(id, true);
          _cronoActualizar(id);
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        }
      })
      .catch(function () {
        Swal.close();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo pausar la sesión.' });
      });
  });
}