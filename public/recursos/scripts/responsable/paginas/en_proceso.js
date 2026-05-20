document.addEventListener("DOMContentLoaded", function () {
  cargarTareasEnProceso();
  setTimeout(verificarHighlight, 1500); // Dar tiempo a que las tareas se carguen por AJAX

  // Lógica para el input de archivos de entrega (Responsable)
  const area = document.getElementById("area-subida-entrega");
  const input = document.getElementById("entrega-archivos");
  const lista = document.getElementById("lista-archivos-entrega");

  if (area && input && lista) {
    area.addEventListener("click", () => input.click());

    // Drag and drop event listeners
    area.addEventListener("dragover", (e) => {
      e.preventDefault();
      area.classList.add("dragover");
    });

    area.addEventListener("dragleave", () => {
      area.classList.remove("dragover");
    });

    area.addEventListener("drop", (e) => {
      e.preventDefault();
      area.classList.remove("dragover");
      if (e.dataTransfer.files.length > 0) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event("change"));
      }
    });

    input.addEventListener("change", () => {
      const esClaro = document.documentElement.getAttribute("data-theme") === "light";
      lista.innerHTML = "";
      Array.from(input.files).forEach((f) => {
        lista.innerHTML += `
            <div style="background:${esClaro ? "#f8fafc" : "#111"}; border:1px solid ${esClaro ? "#cbd5e1" : "#222"}; border-radius:8px; padding:10px 15px; display:flex; align-items:center; gap:12px; color:${esClaro ? "#334155" : "#aaa"}; font-size:12px;">
                <i class="bi bi-file-earmark-check" style="color:#f5c400; font-size:16px;"></i>
                <span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${f.name}</span>
                <small style="color:${esClaro ? "#64748b" : "#444"};">${(f.size / 1024 / 1024).toFixed(2)} MB</small>
            </div>
        `;
      });
    });
  }
});

/* FUNCIONES DE CARGA (API) */

/**
 * Función principal: Descarga la lista de empleados y sus tareas asociadas desde el backend. Actualiza los contadores globales.
 */
function cargarTareasEnProceso() {
  renderizarEmpleados(empleadosData);
  document.getElementById("total-empleados").textContent = empleadosData.length;

  // Cargar tareas de todos los empleados
  window._totalTareas = 0;
  empleadosData.forEach((empleado) => {
    cargarTareasEmpleado(empleado.id);
  });
}

/* RENDERIZADO DE UI */

/**
 * Renderiza la lista visual de empleados y genera los contenedores (tarjetas) donde irán sus tareas.
 * Posiciona al usuario actual (Responsable) al inicio de la lista.
 * @param {*} empleados
 * @returns
 */
function renderizarEmpleados(empleados) {
  const container = document.getElementById("empleados-container");

  if (empleados.length === 0) {
    container.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-inbox ep-icon-empty"></i>
                    <p class="mt-3 ep-text-empty">No hay empleados en el área</p>
                </div>
            </div>
        `;
    return;
  }

  // Ordenar para poner al usuario actual (Responsable) primero
  const sortedEmpleados = [...empleados].sort((a, b) => {
    if (a.id == window.currentUserId) return -1;
    if (b.id == window.currentUserId) return 1;
    return 0;
  });

  container.innerHTML = sortedEmpleados
    .map((empleado) => {
      const isMe = empleado.id == window.currentUserId;
      const colClass = isMe ? "col-12" : "col-lg-6";

      return `
        <div class="${colClass} mb-4">
            <div class="card-dark-main p-4 ${isMe ? "border-me" : ""}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="empleado-avatar ${empleado.esresponsable ? "responsable" : ""} ${isMe ? "me" : ""}">
                            ${empleado.esresponsable ? '<i class="bi bi-shield-check"></i>' : obtenerIniciales(empleado.nombre_completo)}
                        </div>
                        <div class="ms-3">
                            <h6 class="text-white mb-1 ep-nombre">
                                ${escaparHtml(empleado.nombre_completo)}
                            </h6>
                            <span class="${empleado.esresponsable ? "ep-rol-jefe" : "ep-rol-miembro"}">
                                ${empleado.esresponsable ? "Jefe de Área" : "Miembro del Equipo"}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-white fw-bold fs-5" id="tareas-count-${empleado.id}">0</span>
                        <br>
                        <small class="text-muted ep-text-xs">tareas</small>
                    </div>
                </div>
                <div class="tareas-lista" id="tareas-container-${empleado.id}">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span class="ep-text-sm">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    })
    .join("");
}

/**
 * Consulta al servidor para obtener exclusivamente las tareas activas de un empleado.
 * @param {*} idEmpleado
 */
function cargarTareasEmpleado(idEmpleado) {
  const container = document.getElementById(`tareas-container-${idEmpleado}`);
  const countElement = document.getElementById(`tareas-count-${idEmpleado}`);

  fetch(`${window.base_url}responsable/tareas/empleado/${idEmpleado}`)
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        renderizarTareasEmpleado(container, data.data, idEmpleado);
        countElement.textContent = data.total_tareas;
        // Actualizar total global
        window._totalTareas += data.total_tareas;
        document.getElementById("total-tareas").textContent =
          window._totalTareas;
      } else {
        container.innerHTML = `<div class="text-center py-3 ep-text-empty ep-text-sm">Error al cargar</div>`;
        countElement.textContent = "0";
      }
    })
    .catch(() => {
      container.innerHTML = `<div class="text-center py-3 ep-text-empty ep-text-sm">Error de conexión</div>`;
    });
}

/**
 * Dibuja el listado compacto de tareas (título, prioridad y botones) dentro del contenedor de un empleado.
 * @param {*} container
 * @param {*} tareas
 * @param {*} idEmpleado
 * @returns
 */
function renderizarTareasEmpleado(container, tareas, idEmpleado) {
  if (tareas.length === 0) {
    container.innerHTML = `
            <div class="text-center py-3 ep-text-empty ep-text-sm">
                Sin tareas asignadas
            </div>
        `;
    return;
  }

  container.innerHTML = tareas
    .map((tarea) => {
      const isMe = parseInt(idEmpleado) === parseInt(window.currentUserId);
      const hasStarted =
        tarea.fechainicio &&
        tarea.fechainicio !== "0000-00-00 00:00:00" &&
        tarea.fechainicio !== "0000-00-00";
      const canDeliver = isMe && hasStarted && tarea.estado === "en_proceso";

      return `
        <div class="tarea-item mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1 ep-min-w0">
                    <span class="badge-prio ${(tarea.prioridad || "media").toLowerCase()}">${tarea.prioridad || "Media"}</span>
                    <div class="d-flex flex-column ep-min-w0">
                        <span class="tarea-titulo text-truncate">${escaparHtml(tarea.titulo || "Sin título")}</span>
                        <div class="ep-meta-text text-uppercase fw-bold">
                            <i class="bi bi-building me-1"></i> ${escaparHtml(tarea.nombre_empresa || "---")} | 
                            <i class="bi bi-geo-alt me-1"></i> ${escaparHtml(tarea.nombre_area || "---")}
                        </div>
                    </div>

                    ${parseInt(tarea.num_modificaciones) > 0 ||
          tarea.observacion_revision
          ? `
                        <span class="badge-returned" title="Tarea devuelta con observaciones">DEVUELTO</span>
                    `
          : ""
        }
                </div>
                <div class="d-flex gap-1">
                    ${isMe && !hasStarted
          ? `
                        <button class="btn-header-action bg-warning text-dark" onclick="iniciarTrabajo(${tarea.id})" title="Registrar inicio de trabajo">
                            <i class="bi bi-play-fill me-1"></i> INICIAR TRABAJO
                        </button>
                    `
          : `
                        ${canDeliver
            ? `
                            <button class="btn btn-sm btn-success ep-btn-entregar" onclick="abrirModalEntregar(${tarea.id})" title="Entregar mi trabajo">
                                <i class="bi bi-send-fill me-1"></i> ENTREGAR
                            </button>
                        `
            : ""
          }
                        <button class="btn btn-sm btn-outline-warning ep-btn-ver" onclick="verDetalleTarea(${tarea.id})" title="Ver detalles">
                            <i class="bi bi-eye ep-text-eye"></i>
                        </button>
                    `
        }
                        <button class="btn btn-sm btn-outline-secondary ep-btn-ver" 
                                onclick="abrirModalReasignar(${tarea.id}, ${tarea.idempleado || 0}, '${escaparHtml(tarea.titulo || 'Sin título').replace(/'/g, "\\'")}')"
                                title="Reasignar esta tarea a otro especialista">
                            <i class="bi bi-person-gear"></i>
                        </button>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between mt-1">
                <div class="d-flex align-items-center gap-2 flex-wrap ep-meta-container">
                    <span class="badge ep-meta-badge">
                        <i class="bi bi-clock me-1"></i> Entrega: ${formatearFechaLimpia(tarea.fecharequerida) || "Sin fecha"}
                    </span>
                    ${hasStarted
          ? `
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ep-text-xxs">
                            <i class="bi bi-calendar-check me-1"></i> Iniciado: ${formatearFechaLimpia(tarea.fechainicio)}
                        </span>
                    `
          : ""
        }
                </div>
            </div>
        </div>`;
    })
    .join("");
}

/**
 * Registra oficialmente en el sistema la fecha y hora en la que el usuario empieza a trabajar.
 * @param {*} idAtencion
 */
function iniciarTrabajo(idAtencion) {
  Swal.fire({
    title: "¿Iniciar trabajo?",
    text: "Se registrará la fecha y hora oficial de inicio para este requerimiento.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#f5c400",
    cancelButtonColor: "#333",
    confirmButtonText: "Sí, ¡empezar!",
    cancelButtonText: "Cancelar",
    background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
    color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`${window.base_url}responsable/pedido-iniciar/${idAtencion}`, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
        },
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.status === "success") {
            Swal.fire({
              icon: "success",
              title: "¡Trabajo Iniciado!",
              text: data.message,
              background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
              color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
              timer: 1500,
              showConfirmButton: false,
            });
            // Recargar tareas para actualizar la UI (quitar botón iniciar, poner entregar)
            cargarTareasEmpleado(window.currentUserId);
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: data.message,
              background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
              color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
            });
          }
        })
        .catch((err) => {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo conectar con el servidor",
            background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
            color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
          });
        });
    }
  });
}

/* DETALLE DEL REQUERIMIENTO (MODAL DETALLE) */

/**
 * Llama al servidor para obtener la información detallada, archivos y tracking de una tarea específica.
 * @param {*} idAtencion
 */
function verDetalleTarea(idAtencion) {
  // Cargamos detalle e historial de asignaciones en paralelo
  Promise.all([
    fetch(`${window.base_url}responsable/pedidos/detalle?id=${idAtencion}`).then(r => r.json()),
    fetch(`${window.base_url}responsable/empleados/historial-asignaciones?idatencion=${idAtencion}`).then(r => r.json()).catch(() => ({ success: false, data: [] }))
  ]).then(([data, histData]) => {
    if (data.success) {
      window.requerimientoActualEnProceso = data.data;
      mostrarModalDetalle(data.data, data.archivos, data.tracking, histData.data || []);
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: data.message || "No se pudieron cargar los detalles",
        background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
        color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
        confirmButtonColor: "#f5c400",
        allowOutsideClick: false,
        allowEscapeKey: false,
      });
    }
  })
    .catch(() => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error de conexión",
        background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
        color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
        confirmButtonColor: "#f5c400",
        allowOutsideClick: false,
        allowEscapeKey: false,
      });
    });
}

/**
 * Construye dinámicamente y muestra el Modal con el Detalle del requerimiento.
 * Pinta toda la información relevante (objetivos, públicos, formatos, canales, entregables).
 * @param {*} req
 * @param {*} archivos
 * @param {*} tracking
 */
function mostrarModalDetalle(req, archivos, tracking, historialAsignaciones = []) {
  const cuerpo = document.getElementById("detalle-tarea-content");

  // Fechas
  const fReq = req.fecha_requerida_formateada || req.fecharequerida;
  const fSol = req.fecha_formateada || req.fechacreacion;
  const fIni = req.fecha_inicio_formateada || req.fechainicio;

  // Trabajo HTML
  let trabajoHtml;
  if (req.estado === "finalizado" || req.estado === "completado") {
    trabajoHtml = _pill("bi-check2-circle", "Completado", "#22c55e", "#052e16");
  } else if (!req.empleado_asignado) {
    trabajoHtml = _pill(
      "bi-hourglass-split",
      "Pendiente de asignación",
      "#6b7280",
      "#111",
    );
  } else if (
    req.estado === "pendiente_asignado" ||
    req.estado === "pendiente"
  ) {
    trabajoHtml = _pill(
      "bi-person-check-fill",
      "Asignado — aún no iniciado",
      "#F5C400",
      "#1a1500",
    );
  } else {
    trabajoHtml = _pill(
      "bi-lightning-charge-fill",
      "Desarrollando",
      "#10b981",
      "#001a0f",
    );
  }

  // Estado Map
  const estadoMap = {
    pendiente_sin_asignar: {
      c: "#f59e0b",
      label: " Nuevo requerimiento",
      i: "bi-hourglass-split",
    },
    pendiente_asignado: {
      c: "#F5C400",
      label: "Asignado al diseñador",
      i: "bi-send-check-fill",
    },
    en_proceso: {
      c: "#10b981",
      label: "Trabajando en tu diseño",
      i: "bi-lightning-charge-fill",
    },
    en_revision: {
      c: "#3b82f6",
      label: " Listo para revisar",
      i: "bi-eye-fill",
    },
    finalizado: {
      c: "#22c55e",
      label: "Entregado con éxito",
      i: "bi-check2-circle",
    },
    cancelado: { c: "#ef4444", label: "Cancelado", i: "bi-x-circle-fill" },
  };
  const estKey = (req.estado || "").toLowerCase();
  const es = estadoMap[estKey] || {
    c: "#aaa",
    label: req.estado || "N/A",
    i: "bi-circle",
  };

  const pri = req.prioridad || "Media";
  const priC =
    pri === "Alta" ? "#ef4444" : pri === "Media" ? "#F5C400" : "#3b82f6";
  const priI =
    pri === "Alta"
      ? "bi-arrow-up-circle-fill"
      : pri === "Media"
        ? "bi-dash-circle-fill"
        : "bi-arrow-down-circle-fill";

  // Empleado
  let empleadoHtml;
  if (req.empleado_asignado) {
    const ini = obtenerIniciales(
      req.empleado_asignado.nombre + " " + req.empleado_asignado.apellidos,
    );
    empleadoHtml = `
            <div class="d-flex align-items-center gap-2 mt-2">
                <div class="avatar-sm bg-warning text-dark fw-bold">${ini}</div>
                <div>
                    <div class="text-light fw-bold ep-emp-name">${escaparHtml(req.empleado_asignado.nombre)} ${escaparHtml(req.empleado_asignado.apellidos)}</div>
                    <div class="text-muted text-uppercase ep-emp-role">${req.estado === "en_proceso" ? "En desarrollo" : "Asignado"}</div>
                </div>
            </div>`;
  } else {
    empleadoHtml = `
            <div class="d-flex align-items-center gap-2 mt-2">
                <div class="avatar-sm bg-dark border border-secondary text-muted"><i class="bi bi-person-dash"></i></div>
                <div>
                    <div class="text-warning fw-bold ep-emp-name">Sin asignar</div>
                    <div class="text-muted ep-emp-role">Esperando asignación</div>
                </div>
            </div>`;
  }

  // Entrega info (si existe)
  let entregaHtml = "";
  if (req.estado === "en_revision" || req.estado === "finalizado") {
    const urlEntrega = req.url_entrega
      ? `<a href="${escaparHtml(req.url_entrega)}" target="_blank" class="btn btn-sm btn-outline-success ep-text-xs"><i class="bi bi-box-arrow-up-right"></i> VER TRABAJO FINAL</a>`
      : '<span class="text-muted">No se registró URL</span>';

    let arcEntHtml = "";
    const archivosEntrega = archivos.filter((a) => a.idatencion);
    if (archivosEntrega.length > 0) {
      arcEntHtml = `<div class="d-flex flex-wrap gap-2 mt-2">`;
      archivosEntrega.forEach((a) => {
        arcEntHtml += `<button type="button" onclick="abrirArchivo(${a.id})" class="archivo-item btn btn-dark btn-sm text-start"><i class="bi ${getFileIcon(a.nombre_original || a.nombre)} text-success me-2"></i><span class="text-truncate d-inline-block align-middle ep-archivo-nombre">${escaparHtml(a.nombre_original || a.nombre)}</span></button>`;
      });
      arcEntHtml += "</div>";
    } else {
      arcEntHtml =
        '<p class="text-muted fst-italic mt-2 mb-0 ep-text-italic-sm">No se subieron archivos físicos en la entrega.</p>';
    }

    entregaHtml = _seccion(
      "bi-send-check",
      "Información de la Entrega",
      "#22c55e",
      `
            <div class="mb-3">${_label("URL de Entrega")}<div class="mt-1">${urlEntrega}</div></div>
            <div class="mb-3">${_label("Notas de Entrega / Observaciones")}<div class="kd-val text-muted">${escaparHtml(req.observacion_revision || "Sin observaciones")}</div></div>
            <div>${_label("Archivos de la Entrega")}${arcEntHtml}</div>
        `,
    );
  }

  // Archivos de la Solicitud
  const archivosSolicitud = archivos.filter(
    (a) => !a.idatencion || (a.ruta && a.ruta.includes("requerimientos")),
  );
  let arcSolHtml = "";
  if (archivosSolicitud.length) {
    arcSolHtml = `<div class="d-flex flex-wrap gap-2 mt-2">`;
    archivosSolicitud.forEach((a) => {
      arcSolHtml += `<button type="button" onclick="abrirArchivo(${a.id})" class="archivo-item btn btn-dark btn-sm text-start"><i class="bi ${getFileIcon(a.nombre_original || a.nombre)} text-warning me-2"></i><span class="text-truncate d-inline-block align-middle ep-archivo-nombre">${escaparHtml(a.nombre_original || a.nombre)}</span></button>`;
    });
    arcSolHtml += "</div>";
  } else {
    arcSolHtml =
      '<p class="fst-italic mt-2 mb-0 ep-text-italic-sm">No se adjuntaron archivos.</p>';
  }

  // URLs del Cliente
  let urlsClienteHtml = "";
  if (req.url_subida) {
    const link = `<a href="${escaparHtml(req.url_subida)}" target="_blank" class="text-primary text-decoration-underline ep-url-link">${escaparHtml(req.url_subida)}</a>`;
    urlsClienteHtml = _seccion(
      "",
      "URLs enviadas por el Cliente",
      "#60a5fa",
      `<div class="mt-1">${_label("Enlace / URLs")}<div class="mt-1">${link}</div></div>`,
    );
  }

  // HTML Principal
  const html = `
    <div class="modal-ver-detalle">
        <!-- CABECERA -->
        <div class="mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                ${_pill(es.i, es.label, es.c, es.c + "18")}
                ${_pill(priI, pri, priC, priC + "18")}
                ${req.tipo_requerimiento ? `<span class="badge bg-dark border border-secondary rounded-pill px-3 py-1 fw-normal ep-detail-badge">${escaparHtml(req.tipo_requerimiento)}</span>` : ""}
            </div>
            <h2 class="font-bebas text-white mb-1 d-flex align-items-center gap-3 ep-detail-title">
                ${escaparHtml(req.titulo || "Sin Título")}
            </h2>
            <p class="fw-bold text-uppercase mb-3 ep-detail-subtitle">
                ${escaparHtml(req.nombre_empresa || "Empresa no asignada")} | ${escaparHtml(req.nombre_servicio || req.servicio || "Servicio no especificado")}
            </p>
            ${trabajoHtml}
        </div>

        <!-- GRID PRINCIPAL -->
        <div class="kd-main">
            <!-- IZQUIERDA -->
            <div class="d-flex flex-column gap-1 ep-min-w0">
                
                ${req.observacion_revision &&
      (req.estado === "en_proceso" ||
        req.estado === "pendiente_asignado")
      ? `
                    <div class="feedback-box">
                        <div class="feedback-title"><i class="bi bi-exclamation-triangle-fill"></i> CORRECCIÓN SOLICITADA POR ADMINISTRACIÓN</div>
                        <div class="feedback-content">"${escaparHtml(req.observacion_revision)}"</div>
                    </div>
                `
      : ""
    }

                ${entregaHtml}

                ${_seccion("", "Tipo de Requerimiento", "#3b82f6", `<div class="kd-val text-white fw-bold">${escaparHtml(req.tipo_requerimiento || "Sin especificar")}</div>`)}
                ${_seccion("", "Objetivo de Comunicación", "#F5C400", `<div class="kd-val ep-val-prewrap">${escaparHtml(req.objetivo_comunicacion || "---")}</div>`)}
                ${_seccion("", "Público Objetivo", "#F5C400", `<div class="kd-val ep-val-prewrap">${escaparHtml(req.publico_objetivo || "---")}</div>`)}
                ${_seccion("", "Descripción", "#898989ff", `<div class="kd-val ep-val-scroll">${escaparHtml(req.descripcion || "Sin descripción.")}</div>`)}
                ${_seccion("", "Canales de Difusión", "#898989ff", `<div class="d-flex flex-wrap gap-2">${formatearLista(req.canales_difusion)}</div>`)}
                ${_seccion("", "Formatos Solicitados", "#898989ff", `<div class="d-flex flex-wrap gap-2">${formatearLista(req.formatos_solicitados)}</div>`)}
                ${_seccion("", "Archivos Adjuntos a la Solicitud", "#898989ff", arcSolHtml)}
                ${urlsClienteHtml}

            </div>

            <!-- DERECHA -->
            <div class="ep-sidebar">
                <div class="bg-black border border-dark rounded p-4 position-sticky top-0">
                    <div class=" text-white mb-4 ep-sidebar-title">INFORMACIÓN DEL PEDIDO</div>

                    <div class="mb-4">
                        ${_label("Solicitado por")}
                        <div class="text-white fw-bold mb-3 ep-nombre">${escaparHtml(req.nombre_cliente || "---")}</div>
                        <div class="mb-2"><span class="d-block text-uppercase fw-bold mb-1 ep-sidebar-meta-label">Área</span><div class="text-light fw-semibold ep-sidebar-meta-val">${escaparHtml(req.nombre_area || "Área no especificada")}</div></div>
                        <div class="mb-2"><span class="d-block text-uppercase fw-bold mb-1 ep-sidebar-meta-label">Empresa</span><div class="text-warning fw-bold ep-emp-name">${escaparHtml(req.nombre_empresa || "---")}</div></div>
                    </div>
                    <hr class="kd-hr">
                    <div class="mb-4">
                        ${_label("Empleado asignado")}
                        ${empleadoHtml}
                    </div>
                    <hr class="kd-hr">
                        <div class="kd-info-row"> <span class="label-text">${_label("Fecha requerida")}</span><div class="fw-bold ${fReq ? "ep-sidebar-date-active" : "ep-sidebar-date-inactive"}">${formatearFechaLimpia(fReq) || "No definida"}</div></div>
                        <div class="kd-info-row"><span class="label-text">${_label("Fecha de solicitud")}</span><div class="text-light fw-bold ep-sidebar-date"> ${formatearFechaLimpia(fSol) || "---"}</div></div>
                        <div class="kd-info-row"><span class="label-text">${_label("Inicio de trabajo")}</span><div class="fw-bold ${fIni ? "ep-sidebar-date-started" : "ep-sidebar-date-pending"}">${formatearFechaLimpia(fIni) || "Aún no iniciado"}</div></div>
                    <hr class="kd-hr">
                    <div>
                        ${_label("Estado actual")}
                        <div class="mt-2">${_pill(es.i, es.label, es.c, es.c + "18")}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORIAL DE ASIGNACIONES -->
        ${_renderHistorialAsignaciones(historialAsignaciones)}

    </div>`;

  cuerpo.innerHTML = html;

  // Gestionar Botones en la Cabecera
  const modalHeader = document.querySelector(".modal-detalle-header");
  const existingBtn = document.getElementById("btn-formalizar-header");
  if (existingBtn) existingBtn.remove();
  const existingStartBtn = document.getElementById("btn-iniciar-header");
  if (existingStartBtn) existingStartBtn.remove();
  const existingEditBtns = document.getElementById("container-botones-edicion");
  if (existingEditBtns) existingEditBtns.remove();

  const isContentArea = parseInt(req.idarea_agencia) === 3;
  const esServicioEditable =
    isContentArea &&
    (req.estado === "pendiente_asignado" ||
      req.estado === "en_proceso" ||
      req.estado === "pendiente");
  const isMeTask = req.idempleado == window.currentUserId;
  const needsStartHeader = isMeTask && !req.fechainicio;

  if (needsStartHeader) {
    const btnStart = document.createElement("button");
    btnStart.id = "btn-iniciar-header";
    btnStart.className =
      "btn btn-sm btn-success ms-auto me-3 ep-btn-iniciar-header";
    btnStart.innerHTML = '<i class="bi bi-play-fill me-2"></i> INICIAR TRABAJO';
    btnStart.onclick = () => {
      iniciarTrabajo(req.idatencion || req.id);
    };

    // Insertar antes del botón de cerrar
    const closeBtn = modalHeader.querySelector(".btn-close");
    modalHeader.insertBefore(btnStart, closeBtn);
  } else if (esServicioEditable && isMeTask) {
    const btn = document.createElement("button");
    btn.id = "btn-formalizar-header";
    btn.className = "btn btn-sm btn-warning ms-auto me-3 ep-btn-editar-header";
    btn.innerHTML =
      '<i class="bi bi-pencil-square me-2"></i> EDITAR REQUERIMIENTO';
    btn.onclick = () => activarEdicionRequerimientoEnProceso();

    // Insertar antes del botón de cerrar
    const closeBtn = modalHeader.querySelector(".btn-close");
    modalHeader.insertBefore(btn, closeBtn);
  }

  const modalElement = document.getElementById("modal-detalle-tarea");
  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
  modal.show();
}

/* Helpers internos del modal (mostrarModalDetalle) */

function abrirArchivo(idArchivo) {
  window.open(
    `${window.base_url}responsable/archivos/vista-previa/${idArchivo}`,
    "_blank",
  );
}

function _pill(icon, label, color, bg) {
  return `<span class="ep-pill" style="background:${bg};color:${color};border:1px solid ${color}33;">
        ${label}</span>`;
}

//Genera una sección de bloque con título e icono.
function _seccion(icon, titulo, color, innerHtml) {
  return `<div class="kd-sec" style="border-left-color:${color};">
        <div class="kd-sec-title" style="color:${color};">
            ${titulo}
        </div>
        ${innerHtml}
    </div>`;
}

//Etiqueta pequeña para los subtítulos del modal.
function _label(texto) {
  return `<span class="kd-label">${texto}</span>`;
}

/**
 * Renderiza la sección de historial de asignaciones como una línea de tiempo.
 * Si no hay historial, muestra un estado vacío limpio.
 * @param {Array} historial
 * @returns {string} HTML
 */
function _renderHistorialAsignaciones(historial) {
  if (!historial || historial.length === 0) return '';

  const items = historial.map((h, i) => {
    const nombreAnterior = h.nombre_anterior
      ? `${escaparHtml(h.nombre_anterior)} ${escaparHtml(h.apellidos_anterior || '')}`
      : '<em>Sin asignar</em>';
    const nombreNuevo = `${escaparHtml(h.nombre_nuevo || '')} ${escaparHtml(h.apellidos_nuevo || '')}`;
    const responsable = `${escaparHtml(h.nombre_responsable || '')} ${escaparHtml(h.apellidos_responsable || '')}`;
    const fecha = h.fecha_asignacion ? formatearFechaLimpia(h.fecha_asignacion) : '---';

    return `
      <div class="hist-asig-item ${i === 0 ? 'hist-asig-item--latest' : ''}">
        <div class="hist-asig-dot"></div>
        <div class="hist-asig-body">
          <div class="hist-asig-transfer">
            <span class="hist-asig-from">${nombreAnterior}</span>
            <i class="bi bi-arrow-right hist-asig-arrow"></i>
            <span class="hist-asig-to">${nombreNuevo}</span>
          </div>
          <div class="hist-asig-motivo">"${escaparHtml(h.motivo_cambio || 'Sin motivo registrado')}"</div>
          <div class="hist-asig-meta">
            <i class="bi bi-person-gear me-1"></i>${responsable}
            <span class="hist-asig-sep">·</span>
            <i class="bi bi-clock me-1"></i>${fecha}
          </div>
        </div>
      </div>`;
  }).join('');

  return `
    <div class="hist-asig-section">
      <div class="hist-asig-header">
        <i class="bi bi-arrow-left-right me-2"></i>HISTORIAL DE REASIGNACIONES
        <span class="hist-asig-count">${historial.length}</span>
      </div>
      <div class="hist-asig-timeline">
        ${items}
      </div>
    </div>`;
}

/* UTILIDADES Y HELPERS */

/**
 * Retorna la clase del icono de Bootstrap según la extensión del archivo.
 * @param {*} nombre
 * @returns
 */
function getFileIcon(nombre) {
  if (!nombre) return "bi-file-earmark";
  const ext = nombre.split(".").pop().toLowerCase();
  if (["jpg", "jpeg", "png", "gif", "webp", "svg"].includes(ext))
    return "bi-file-earmark-image";
  if (ext === "pdf") return "bi-file-earmark-pdf";
  if (["doc", "docx"].includes(ext)) return "bi-file-earmark-word";
  if (["xls", "xlsx"].includes(ext)) return "bi-file-earmark-excel";
  if (["ppt", "pptx"].includes(ext)) return "bi-file-earmark-slides";
  return "bi-file-earmark";
}

/**
 * Convierte un texto separado por comas en etiquetas (pills).
 * @param {*} valor
 * @returns
 */
function formatearLista(valor) {
  if (!valor) return "";
  let items = [];
  try {
    const p = JSON.parse(valor);
    items = Array.isArray(p) ? p : [String(p)];
  } catch (e) {
    items = valor
      .split(",")
      .map((s) => s.trim())
      .filter((s) => s);
  }
  return items
    .map((item) => `<span class="badge-tag">${escaparHtml(item)}</span>`)
    .join("");
}

/**
 * Sanitiza texto para prevenir ataques XSS.
 * @param {*} texto
 * @returns
 */
function escaparHtml(texto) {
  if (!texto) return "";
  const div = document.createElement("div");
  div.textContent = texto;
  return div.innerHTML;
}

/**
 * Formatea fechas largas a una vista limpia (DD/MM/YYYY HH:MM).
 * @param {*} fecha
 * @returns
 */
function formatearFechaLimpia(fecha) {
  if (!fecha) return "---";
  if (fecha.includes("/") && fecha.length <= 16) return fecha; // Ya está formateada
  try {
    const d = new Date(fecha);
    if (isNaN(d.getTime())) return fecha;
    return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()} ${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
  } catch (e) {
    return fecha;
  }
}

/**
 * Extrae las iniciales de un nombre
 * @param {*} nombre
 * @returns
 */
function obtenerIniciales(nombre) {
  if (!nombre) return "?";
  const p = nombre.trim().split(" ");
  return ((p[0]?.[0] || "") + (p[1]?.[0] || "")).toUpperCase();
}

/*  ACCIONES DE ENTREGABLE */

/**
 * Abre el formulario (SweetAlert) para que el diseñador envíe su trabajo final (link o archivos).
 * @param {number|string} idAtencion - ID de la atención a entregar.
 */
function abrirModalEntregar(idAtencion) {
  Swal.fire({
    title: '<i class="bi bi-cloud-arrow-up mr-2" style="color:#F5C400;"></i> <span style="font-family:\'Bebas Neue\'; letter-spacing:1px; font-size:24px;">REALIZAR ENTREGA</span>',
    html: `
      <div class="text-start" style="font-family: 'Inter', sans-serif;">
        <div class="mb-3">
          <label class="form-label text-white-50 text-uppercase fw-bold ep-swal-label">Link del Entregable</label>
          <input type="text" id="swal-url-entrega" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Google Drive, Canva, Figma...">
        </div>
        <div class="mb-3">
          <label class="form-label text-white-50 text-uppercase fw-bold ep-swal-label">Subir Archivos (Opcional)</label>
          <input type="file" id="swal-archivos-entrega" class="form-control form-control-sm bg-dark text-white border-secondary" multiple>
        </div>
        <div class="mb-3">
          <label class="form-label text-white-50 text-uppercase fw-bold ep-swal-label">Notas adicionales</label>
          <textarea id="swal-notas-entrega" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Escribe aquí algún detalle..." rows="3"></textarea>
        </div>
      </div>`,
    background: "#0a0a0a",
    color: "#fff",
    showCancelButton: true,
    confirmButtonText: "ENVIAR ENTREGA",
    cancelButtonText: "CANCELAR",
    confirmButtonColor: "#22c55e",
    cancelButtonColor: "#333",
    allowOutsideClick: false,
    allowEscapeKey: false,
    preConfirm: () => {
      const url = document.getElementById("swal-url-entrega").value;
      const files = document.getElementById("swal-archivos-entrega").files;
      const notas = document.getElementById("swal-notas-entrega").value;

      if (url) {
        if (!/^(https?:\/\/)/i.test(url)) {
          Swal.showValidationMessage("El enlace debe comenzar con http:// o https://");
          return false;
        }
      }

      if (!url && files.length === 0) {
        Swal.showValidationMessage("Proporciona un enlace o adjunta archivos de tu trabajo.");
        return false;
      }

      return { url, files, notas };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      ejecutarEntrega(idAtencion, result.value);
    }
  });
}

function ejecutarEntrega(idAtencion, data) {
  const formData = new FormData();
  formData.append("url_entrega", data.url);
  formData.append("notas", data.notas);

  for (let i = 0; i < data.files.length; i++) {
    formData.append("archivos_entrega[]", data.files[i]);
  }

  const esClaro = document.documentElement.getAttribute("data-theme") === "light";
  Swal.fire({
    title: "Enviando entrega...",
    didOpen: () => {
      Swal.showLoading();
    },
    background: esClaro ? "#fff" : "#161616",
    color: esClaro ? "#000" : "#fff",
    allowOutsideClick: false,
  });

  fetch(`${window.base_url}responsable/pedido-entregar/${idAtencion}`, {
    method: "POST",
    body: formData,
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.status === "success") {
        const esClaro = document.documentElement.getAttribute("data-theme") === "light";
        Swal.fire({
          icon: "success",
          title: "¡Éxito!",
          text: res.message,
          background: esClaro ? "#fff" : "#161616",
          color: esClaro ? "#000" : "#fff",
          confirmButtonColor: "#f5c400",
          allowOutsideClick: false,
          allowEscapeKey: false,
        }).then(() => {
          location.reload();
        });
      } else {
        const esClaro = document.documentElement.getAttribute("data-theme") === "light";
        Swal.fire({
          icon: "error",
          title: "Error",
          text: res.message,
          background: esClaro ? "#fff" : "#161616",
          color: esClaro ? "#000" : "#fff",
          confirmButtonColor: "#f5c400",
          allowOutsideClick: false,
          allowEscapeKey: false,
        });
      }
    })
    .catch(() => {
      const esClaro = document.documentElement.getAttribute("data-theme") === "light";
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error de conexión al servidor",
        background: esClaro ? "#fff" : "#161616",
        color: esClaro ? "#000" : "#fff",
        confirmButtonColor: "#f5c400",
        allowOutsideClick: false,
        allowEscapeKey: false,
      });
    });
}

/* MODO EDICIÓN (CREACION DE CONTENIDO) */

/**
 * Transforma la vista estática del Expediente Digital en un formulario editable.
 * Permite al responsable (y solo al responsable) afinar los requerimientos.
 * @returns
 */
function activarEdicionRequerimientoEnProceso() {
  const req = window.requerimientoActualEnProceso;
  if (!req) return;

  // Cargar servicios si no están disponibles
  if (!window.serviciosList) {
    fetch(`${window.base_url}responsable/servicios/listar`)
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          window.serviciosList = data.data;
          activarEdicionRequerimientoEnProceso(); // Re-ejecutar ahora con datos
        }
      });
    return;
  }

  // Cambiar botones en la cabecera
  const modalHeader = document.querySelector(".modal-detalle-header");
  const formalizarBtn = document.getElementById("btn-formalizar-header");
  if (formalizarBtn) formalizarBtn.remove();

  const containerBotones = document.createElement("div");
  containerBotones.id = "container-botones-edicion";
  containerBotones.className = "ms-auto me-3 d-flex gap-2";
  containerBotones.innerHTML = `
            <button class="btn btn-sm btn-success ep-edit-btn" onclick="guardarEdicionRequerimientoEnProceso()">GUARDAR CAMBIOS</button>
            <button class="btn btn-sm btn-outline-light ep-edit-cancel-btn" onclick="verDetalleTarea(${req.idatencion || req.id})">CANCELAR</button>
        `;
  const closeBtn = modalHeader.querySelector(".btn-close");
  modalHeader.insertBefore(containerBotones, closeBtn);

  // Mensaje de Modo Edición en el cuerpo
  const headerH2 = document.querySelector(".modal-ver-detalle h2");
  headerH2.innerHTML = `<span class="ep-edit-header-text">MODO FORMALIZACIÓN DE REQUERIMIENTO</span>`;

  // Transformar campos de la izquierda
  const leftContainer = document.querySelector(".kd-main > div:first-child");

  // Listas para checkboxes (Estándar - Actualizadas)
  const canalesStandard = [
    "Por correo",
    "Página web",
    "Redes sociales",
    "SIGU o Aula Virtual Estudiantes",
    "SIGU o Aula Virtual Docentes",
    "Impresión física de folletos",
    "Banner físico",
    "Letreros",
    "Merch para eventos específicos",
  ];
  const formatosStandard = [
    "Emailing (pieza para correo)",
    "Post de Facebook/Instagram",
    "Historia Facebook/Instagram",
    "Historia de Whatsapp",
    "Post de LinkedIn",
    "SIGU (comunicado)",
    "Aula Virtual (Pop up)",
    "Wallpaper – Computadoras",
    "Banner Web Portada",
    "Volante A5",
    "Afiche A4",
    "Afiche A3",
    "Credenciales",
    "Banner 2x1",
    "Tarjeta Personal",
    "Tríptico",
    "Díptico",
    "Folder A4",
    "Brochure",
    "Cartilla",
    "Banderola",
    "Módulos",
    "SMS",
    "IVR",
    "Marcos Selfie",
    "Boletín",
    "Guías (para proceso, trámites, pagos, etc)",
    "Imagen JPG - PNG",
  ];

  const canalesActuales = (req.canales_difusion || "")
    .split(",")
    .map((s) => s.trim())
    .filter((s) => s !== "");
  const formatosActuales = (req.formatos_solicitados || "")
    .split(",")
    .map((s) => s.trim())
    .filter((s) => s !== "");

  // Detectar si hay valores "Otros"
  const canalesOtrosValues = []; // Canales ya no tiene "Otros"
  const formatosOtrosValues = formatosActuales.filter(
    (f) => !formatosStandard.includes(f),
  );

  const tieneOtrosFormatos = formatosOtrosValues.length > 0;

  const esClaro = document.documentElement.getAttribute("data-theme") === "light";
  leftContainer.innerHTML = `
        <div class="kd-sec border-warning ${esClaro ? "bg-white" : "bg-black"} ep-edit-sec">
            <div class="kd-sec-title text-warning"><i class="bi bi-pencil-square"></i> EDITAR DATOS DEL REQUERIMIENTO</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="kd-label">Título del Requerimiento</label>
                    <input type="text" id="edit-pro-titulo" class="form-control form-control-sm ${esClaro ? "bg-white text-dark" : "bg-dark text-white"} border-secondary" value="${escaparHtml(req.titulo)}">
                </div>
                <input type="hidden" id="edit-pro-servicio" value="${req.idservicio}">
                <div class="col-md-6">
                    <label class="kd-label">Tipo de Requerimiento</label>
                    <select id="edit-pro-tipo-req" class="form-select form-select-sm ${esClaro ? "bg-white text-dark" : "bg-dark text-white"} border-secondary">
                        <option value="" ${!req.tipo_requerimiento ? "selected" : ""}>Seleccionar...</option>
                        <option value="Adaptación de Arte" ${req.tipo_requerimiento === "Adaptación de Arte" ? "selected" : ""}>Adaptación de Arte — 2 días hábiles</option>
                        <option value="Creación de Arte" ${req.tipo_requerimiento === "Creación de Arte" ? "selected" : ""}>Creación de Arte — 4 días hábiles</option>
                        <option value="Creación de editorial" ${req.tipo_requerimiento?.includes("editorial") && req.tipo_requerimiento?.includes("Creación") ? "selected" : ""}>Creación de editorial (revistas, boletines, guías, similares) — 7 días hábiles</option>
                        <option value="Adaptación de editorial" ${req.tipo_requerimiento?.includes("editorial") && req.tipo_requerimiento?.includes("Adaptación") ? "selected" : ""}>Adaptación de editorial (revistas, boletines, guías, similares) — 7 días hábiles</option>
                        <option value="Creación de Videos" ${req.tipo_requerimiento?.includes("Videos") ? "selected" : ""}>Creación de Vídeos (institucionales, reels, etc) — 7 días hábiles</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="kd-label">Objetivo de Comunicación</label>
                    <textarea id="edit-pro-objetivo" class="form-control form-control-sm ${esClaro ? "bg-white text-dark" : "bg-dark text-white"} border-secondary" rows="3">${req.objetivo_comunicacion || ""}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="kd-label">Público Objetivo</label>
                    <textarea id="edit-pro-publico" class="form-control form-control-sm ${esClaro ? "bg-white text-dark" : "bg-dark text-white"} border-secondary" rows="3">${req.publico_objetivo || ""}</textarea>
                </div>
                <div class="col-12">
                    <label class="kd-label">Descripción Detallada</label>
                    <textarea id="edit-pro-descripcion" class="form-control form-control-sm ${esClaro ? "bg-white text-dark" : "bg-dark text-white"} border-secondary" rows="5">${req.descripcion || ""}</textarea>
                </div>
                
                <div class="col-12">
                    <label class="kd-label mb-2">Canales de Difusión</label>
                    <div class="d-flex flex-wrap gap-3 p-3 border border-dark rounded ${esClaro ? "bg-white" : "bg-black"}">
                        ${canalesStandard
      .map(
        (c) => `
                            <div class="form-check custom-check">
                                <input class="form-check-input check-canal" type="checkbox" value="${c}" id="canal-${c.replace(/\s+/g, "")}" ${canalesActuales.includes(c) ? "checked" : ""} onchange="validarMaxCanales(this)">
                                <label class="form-check-label" for="canal-${c.replace(/\s+/g, "")}">${c}</label>
                            </div>
                        `,
      )
      .join("")}
                    </div>
                </div>

                <div class="col-12">
                    <label class="kd-label mb-2">Formatos Solicitados</label>
                    <div class="d-flex flex-wrap gap-3 p-3 border border-dark rounded ${esClaro ? "bg-white" : "bg-black"}">
                        ${formatosStandard
      .map(
        (f) => `
                            <div class="form-check custom-check">
                                <input class="form-check-input check-formato" type="checkbox" value="${f}" id="formato-${f.replace(/\s+/g, "")}" ${formatosActuales.includes(f) ? "checked" : ""}>
                                <label class="form-check-label ${esClaro ? "text-dark" : "text-white"}" for="formato-${f.replace(/\s+/g, "")}">${f}</label>
                            </div>
                        `,
      )
      .join("")}
                        <div class="form-check custom-check">
                            <input class="form-check-input check-formato-otros" type="checkbox" value="Otros" id="formato-Otros" onchange="document.getElementById('container-otros-formatos').classList.toggle('d-none', !this.checked)">
                            <label class="form-check-label" for="formato-Otros">Otros</label>
                        </div>
                        <div id="container-otros-formatos" class="w-100 mt-2 ${tieneOtrosFormatos ? "" : "d-none"}">
                            <input type="text" id="edit-pro-otros-formatos" class="form-control form-control-sm ${esClaro ? "bg-white text-dark" : "bg-dark text-white"} border-secondary" placeholder="Especifique otros formatos separados por coma..." value="${formatosOtrosValues.join(", ")}">
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="alert alert-dark border-warning text-warning p-4 ep-alert-seguridad">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> 
                        <strong>AVISO DE SEGURIDAD:</strong> Los archivos originales, la fecha de entrega y el URL proporcionado por el cliente no pueden ser modificados.
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Valida que no se seleccionen más de 3 canales
 * @param {*} checkbox
 */
window.validarMaxCanales = function (checkbox) {
  const seleccionados = document.querySelectorAll(".check-canal:checked");
  if (seleccionados.length > 3) {
    checkbox.checked = false;
    const esClaro = document.documentElement.getAttribute("data-theme") === "light";
    Swal.fire({
      icon: "warning",
      title: "Límite alcanzado",
      text: "Solo puedes seleccionar un máximo de 3 canales de difusión.",
      background: esClaro ? "#fff" : "#161616",
      color: esClaro ? "#000" : "#fff",
      confirmButtonColor: "#f5c400",
      timer: 2000,
      showConfirmButton: false,
    });
  }
};

/**
 * Guarda los cambios desde el panel de en proceso
 */
function guardarEdicionRequerimientoEnProceso() {
  const req = window.requerimientoActualEnProceso;
  const btn = event.currentTarget;
  const originalHtml = btn.innerHTML;

  btn.disabled = true;
  btn.innerHTML = "GUARDANDO...";

  const formData = new FormData();
  formData.append("idrequerimiento", req.idrequerimiento || req.id); // Asegurar ID correcto
  formData.append(
    "idservicio",
    document.getElementById("edit-pro-servicio").value,
  );
  formData.append(
    "tipo_requerimiento",
    document.getElementById("edit-pro-tipo-req").value,
  );
  formData.append("titulo", document.getElementById("edit-pro-titulo").value);
  formData.append(
    "descripcion",
    document.getElementById("edit-pro-descripcion").value,
  );
  formData.append(
    "objetivo_comunicacion",
    document.getElementById("edit-pro-objetivo").value,
  );
  formData.append(
    "publico_objetivo",
    document.getElementById("edit-pro-publico").value,
  );

  // Usar valores originales ya que no se editan
  formData.append(
    "fecharequerida",
    req.fecharequerida ? req.fecharequerida.split(" ")[0] : "",
  );
  formData.append("url_subida", req.url_subida || "");

  // Canales seleccionados (Máximo 3)
  let canales = Array.from(
    document.querySelectorAll(".check-canal:checked"),
  ).map((c) => c.value);
  formData.append("canales_difusion", canales.join(", "));

  // Formatos seleccionados
  let formatos = Array.from(
    document.querySelectorAll(".check-formato:checked"),
  ).map((f) => f.value);
  const checkOtrosFormatos = document.querySelector(".check-formato-otros");
  if (checkOtrosFormatos && checkOtrosFormatos.checked) {
    const otrosVal = document.getElementById("edit-pro-otros-formatos").value;
    const otrosArray = otrosVal
      .split(",")
      .map((s) => s.trim())
      .filter((s) => s !== "");
    otrosArray.forEach((val) => {
      if (!formatos.includes(val)) formatos.push(val);
    });
  }
  formData.append("formatos_solicitados", formatos.join(", "));

  // Envío sin archivos (Deshabilitado por seguridad)

  fetch(`${window.base_url}responsable/pedidos/actualizar`, {
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-TOKEN": document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content"),
    },
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "¡Actualizado!",
          text: data.message,
          background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
          color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
          timer: 1500,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
        });
        // Recargar detalles
        verDetalleTarea(req.idatencion || req.id);
        // Recargar listas de fondo
        if (typeof cargarTareasEmpleado === "function")
          cargarTareasEmpleado(req.idempleado);
        if (typeof listarPedidosEnProceso === "function")
          listarPedidosEnProceso();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message,
          background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
          color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
          allowOutsideClick: false,
          allowEscapeKey: false,
        });
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    })
    .catch((err) => {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error de conexión",
        background: document.documentElement.getAttribute("data-theme") === "light" ? "#fff" : "#161616",
        color: document.documentElement.getAttribute("data-theme") === "light" ? "#000" : "#fff",
        allowOutsideClick: false,
        allowEscapeKey: false,
      });
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    });
}

/**
 * Busca un parámetro en la URL y resalta la tarea si existe (con reintentos para carga asíncrona)
 */
function verificarHighlight() {
  const urlParams = new URLSearchParams(window.location.search);
  const highlightId = urlParams.get('highlight');
  if (!highlightId) return;

  let intentos = 0;
  const maxIntentos = 10; // Intentar durante 5 segundos (500ms * 10)

  const interval = setInterval(() => {
    intentos++;
    const items = document.querySelectorAll('.tarea-item');
    let target = null;

    items.forEach(item => {
      if (item.innerHTML.includes(`verDetalleTarea(${highlightId})`) ||
        item.innerHTML.includes(`iniciarTrabajo(${highlightId})`)) {
        target = item;
      }
    });

    if (target) {
      clearInterval(interval);
      setTimeout(() => {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        target.style.transition = 'all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        target.style.border = '3px solid #f5c400';
        target.style.boxShadow = '0 0 30px rgba(245, 196, 0, 0.6)';
        target.style.transform = 'scale(1.03)';
        target.style.zIndex = '100';

        setTimeout(() => {
          target.style.border = 'none';
          target.style.boxShadow = 'none';
          target.style.transform = 'scale(1)';
        }, 4000);
      }, 300);
    }

    if (intentos >= maxIntentos) {
      clearInterval(interval);
    }
  }, 500);
}

/* REASIGNACIÓN DE TAREAS*/

/**
 * Abre el modal de reasignación, cargando la lista de empleados disponibles
 * @param {*} idAtencion 
 * @param {*} idEmpleadoActual 
 * @param {*} titulo 
 */
async function abrirModalReasignar(idAtencion, idEmpleadoActual, titulo) {
  // Guardamos los IDs en inputs ocultos para el submit
  document.getElementById('reasignar-idatencion').value = idAtencion;
  document.getElementById('reasignar-idempleado-actual').value = idEmpleadoActual;

  // Mostramos el título de la tarea en el modal
  document.getElementById('reasignar-titulo-tarea').textContent = `Tarea: "${titulo}"`;

  // Limpiamos campos del formulario
  document.getElementById('input-motivo-reasignacion').value = '';
  const select = document.getElementById('select-nuevo-empleado');
  select.innerHTML = '<option value="">Cargando especialistas...</option>';

  // Abrir el modal
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-reasignar')).show();

  // Cargar la lista de empleados desde el servidor (excluyendo al actual)
  try {
    const url = `${window.base_url}responsable/empleados/para-reasignar?excluir=${idEmpleadoActual}`;
    const res = await fetch(url);
    const data = await res.json();

    if (data.success && data.data.length > 0) {
      select.innerHTML = '<option value="">-- Selecciona un especialista --</option>';
      data.data.forEach(emp => {
        const opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.nombre_completo;
        select.appendChild(opt);
      });
    } else {
      select.innerHTML = '<option value="">No hay otros especialistas disponibles</option>';
    }
  } catch (err) {
    select.innerHTML = '<option value="">Error al cargar la lista</option>';
    console.error('Error cargando empleados:', err);
  }
}

/**
 * Envía la solicitud de reasignación al servidor. Se llama desde el botón "Confirmar" dentro del modal.
 * @returns 
 */
async function confirmarReasignacion() {
  const idAtencion = document.getElementById('reasignar-idatencion').value;
  const idNuevoEmp = document.getElementById('select-nuevo-empleado').value;
  const motivo = document.getElementById('input-motivo-reasignacion').value.trim();
  const idEmpAnterior = document.getElementById('reasignar-idempleado-actual').value;

  // Validaciones en cliente
  if (!idNuevoEmp) {
    return Swal.fire({ icon: 'warning', title: 'Falta el especialista', text: 'Selecciona el nuevo especialista.', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400' });
  }
  if (!motivo) {
    return Swal.fire({ icon: 'warning', title: 'Motivo requerido', text: 'Escribe el motivo de la reasignación para que quede en el historial.', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400' });
  }

  const btn = document.querySelector('#modal-reasignar .btn-warning');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';

  try {
    const formData = new FormData();
    formData.append('idatencion', idAtencion);
    formData.append('idempleado_nuevo', idNuevoEmp);
    formData.append('motivo', motivo);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const res = await fetch(`${window.base_url}responsable/pedidos/reasignar`, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
      body: formData,
    });
    const data = await res.json();

    if (data.success) {
      // Cerrar modal
      bootstrap.Modal.getInstance(document.getElementById('modal-reasignar')).hide();

      Swal.fire({
        icon: 'success',
        title: '¡Reasignada!',
        text: data.message,
        background: '#161616', color: '#fff',
        timer: 2000, showConfirmButton: false,
      });

      // Refrescar las tarjetas de AMBOS empleados (el anterior y el nuevo)
      if (idEmpAnterior) cargarTareasEmpleado(idEmpAnterior);
      cargarTareasEmpleado(idNuevoEmp);

    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message, background: '#161616', color: '#fff', confirmButtonColor: '#f5c400' });
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  } catch (err) {
    console.error('Error en reasignación:', err);
    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar al servidor.', background: '#161616', color: '#fff', confirmButtonColor: '#f5c400' });
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}