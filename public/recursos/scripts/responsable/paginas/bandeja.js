// Variables Globales
let empleadosBandejaData = []; // Lista de Empleados del área + Carga Trabajo
let requerimientosData = []; // Pedidos sin asignar
let revisionData = []; // Pedidos en revision
let empleadoSeleccionado = null; // ID del Empleado elegido en el modal de asignación.
let requerimientoSeleccionado = null; // Rquerimiento el cual se opera actualmente

document.addEventListener("DOMContentLoaded", function () {
  cargarBandeja(); // Carga inicial de Requerimientos y revisión.
  cargarEmpleados(); // Carga la lista de Empleados disponibles para asignar.

  // El buscador
  const buscador = document.getElementById("buscador-bandeja");
  if (buscador) {
    buscador.addEventListener("input", debounce(filtrarBandeja, 300));
  }

  // Botón final del modal de asignación.
  const btnConfirmar = document.getElementById("btn-confirmar-asignacion");
  if (btnConfirmar) {
    btnConfirmar.addEventListener("click", confirmarAsignacion);
  }

  // Al cerrar el modal de asignación, reseteamos todo
  const modalAsignar = document.getElementById("modal-asignar");
  if (modalAsignar) {
    modalAsignar.addEventListener("hidden.bs.modal", function () {
      empleadoSeleccionado = null;
      requerimientoSeleccionado = null;
      const btn = document.getElementById("btn-confirmar-asignacion");
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-person-plus-fill me-2"></i> CONFIRMAR ASIGNACIÓN';
      }
    });
  }
});

/* FUNCIONES DE CARGA */

/**
 * Obtiene los Requerimientos pendientes y en revisión desde el servidor.
 * Maneja estados de carga y errores de conexión.
 */
function cargarBandeja() {
  const tbody = document.getElementById("contenido-bandeja");
  const tbodyRev = document.getElementById("contenido-revision");

  // Solo mostrar animación si es la primera carga o está vacío, para evitar parpadeos con Pusher
  if (requerimientosData.length === 0 && tbody) {
    tbody.innerHTML = generarSkeletonFilas();
  }
  if (revisionData.length === 0 && tbodyRev) {
    tbodyRev.innerHTML = generarSkeletonFilas();
  }

  fetch(`${base_url}responsable/pedidos/bandeja-json`)
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        // Guardamos los datos
        requerimientosData = data.data || [];
        actualizarContador(requerimientosData.length);

        // Mostramos "Bandeja Vacía" o Renderizamos en la Tabla
        if (requerimientosData.length === 0) {
          mostrarEstadoVacio();
        } else {
          renderizarBandeja(requerimientosData);
        }

        // Si el servidor envía datos de revisión, los pintamos en la segunda tabla.
        if (data.data_revision) {
          revisionData = data.data_revision;
          renderizarRevision(revisionData);
        }
      } else {
        mostrarError(data.message || "Error al cargar la bandeja");
      }
    })
    .catch((err) => {
      console.error("Error crítico en cargarBandeja:", err);
      mostrarError("Error de conexión con el servidor");
    });
}

/**
 * Obtiene la lista de Empleados pertenecientes al área del responsable.
 * Incluye sus métricas de "Carga de Trabajo" (cuántas tareas tienen actualmente).
 */
function cargarEmpleados() {
  fetch(`${base_url}responsable/empleados/mi-area-json`)
    .then((r) => r.json())
    .then((data) => {
      if (data.success) empleadosBandejaData = data.data || [];
    })
    .catch((err) => console.error("Error al cargar lista de técnicos:", err));
}

/* FUNCIONES DE RENDERIZADO (VISTA) */

/**
 * Genera el HTML de las filas para la tabla de "Pedidos Pendientes".
 * @param {*} data
 */
function renderizarBandeja(data) {
  const tbody = document.getElementById("contenido-bandeja");
  const estadoVacio = document.getElementById("estado-vacio");
  estadoVacio?.classList.add("d-none");

  // El método .map recorre el array y devuelve un bloque HTML por cada item.
  tbody.innerHTML = data
    .map(
      (item) => `
        <tr>
            <td>
                <div class="fw-semibold">${escaparHtml(item.titulo || "Sin título")}</div>
                ${item.observacion_revision ? '<span class="badge bg-danger mt-1" style="font-size:9px;">DEVUELTO</span>' : ""}
            </td>
            <td>${escaparHtml(item.nombreempresa || "N/A")}</td>
            <td>${escaparHtml(item.cliente_nombre || "Usuario")}</td>
            <td><span class="prioridad-${(item.prioridad || "media").toLowerCase()}">${item.prioridad || "Media"}</span></td>
            <td>${formatearFechaLimpia(item.fechacreacion)}</td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn-ver-detalle" onclick="verDetalleRequerimiento(${item.idatencion})">
                        <i class="bi bi-eye"></i> Ver Detalle
                    </button>
                    <button class="btn-asignar" onclick="abrirModalAsignar(${item.idatencion})">
                        <i class="bi bi-person-plus"></i> Asignar
                    </button>
                </div>
            </td>
        </tr>
    `,
    )
    .join(""); // Convierte el array de filas en un solo bloque de texto.
}

/**
 * Genera el HTML de las filas para la tabla de "Esperando Revisión".
 * @param {*} data
 * @returns
 */
function renderizarRevision(data) {
  const tbody = document.getElementById("contenido-revision");
  const estadoVacio = document.getElementById("estado-vacio-revision");
  if (!tbody) {
    return;
  }

  if (data.length === 0) {
    tbody.innerHTML = "";
    estadoVacio?.classList.remove("d-none");
    return;
  }

  estadoVacio?.classList.add("d-none");
  tbody.innerHTML = data
    .map(
      (item) => `
        <tr>
            <td>
                <div class="fw-semibold">${escaparHtml(item.titulo || "Sin título")}</div>
                <div style="font-size:10px; color:#666;">#REQ-${item.id_requerimiento}</div>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:12px;">${escaparHtml(item.empleado_nombre || "---")}</span>
                </div>
            </td>
            <td>${escaparHtml(item.empresa_nombre || "N/A")}</td>
            <td>${escaparHtml(item.cliente_nombre || "Usuario")}</td>
            <td>
                <button class="btn-ver-detalle" onclick="verDetalleRequerimiento(${item.id})" style="background:rgba(34,197,94,0.1); color:#22c55e; border-color:rgba(34,197,94,0.2);">
                    <i class="bi bi-eye"></i> Ver Trabajo
                </button>
            </td>
        </tr>
    `,
    )
    .join("");
}

// FUNCIONES PARA ASIGNACION DE TAREAS

/**
 * Prepara y muestra el modal para asignar un técnico a un pedido.
 * @param {number} idAtencion - ID único de la atención.
 */
function abrirModalAsignar(idAtencion) {
  // Buscamos el pedido
  const req = requerimientosData.find(
    (r) => parseInt(r.idatencion) === parseInt(idAtencion),
  );
  if (!req) {
    const esClaro = document.documentElement.getAttribute("data-theme") === "light";
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Pedido no localizado en memoria local.",
      background: esClaro ? "#fff" : "#161616",
      color: esClaro ? "#000" : "#fff",
    });
    return;
  }

  requerimientoSeleccionado = req;

  // Llenamos los datos fijos del modal (Banner de Proyecto).
  document.getElementById("idatencion-seleccionado").value = idAtencion;
  document.getElementById("modal-titulo-requerimiento").textContent =
    escaparHtml(req.titulo || "Sin título");
  document.getElementById("info-empresa").innerHTML =
    `<i class="bi bi-building me-1 text-oro"></i> ${escaparHtml(req.nombreempresa || "---")}`;

  // Área y solicitante (si los datos están disponibles)
  const areaEl = document.getElementById("info-area");
  if (areaEl) areaEl.textContent = req.area_nombre || req.nombre_area || "---";
  const solEl = document.getElementById("info-solicitante");
  if (solEl) solEl.textContent = req.cliente_nombre || req.nombre_cliente || "---";

  const p = (req.prioridad || "media").toLowerCase();
  document.getElementById("info-prioridad").innerHTML =
    `<span class="badge-prioridad-pro ${p}"><i class="bi bi-lightning-fill"></i> PRIORIDAD ${p.toUpperCase()}</span>`;

  empleadoSeleccionado = null; // Reiniciar selección.
  renderizarListaEmpleados(); // Pintar los técnicos disponibles.

  new bootstrap.Modal(document.getElementById("modal-asignar")).show();
}

/**
 * Renderiza las tarjetas de los técnicos dentro del modal de asignación.
 * Muestra visualmente quién está saturado y quién tiene tiempo disponible.
 * @returns
 */
function renderizarListaEmpleados() {
  const contenedor = document.getElementById("lista-empleados");
  if (!contenedor || empleadosBandejaData.length === 0) {
    contenedor.innerHTML = `<div class="text-center py-5 opacity-20"><i class="bi bi-people mb-3 d-block fs-1"></i><p>No hay técnicos disponibles</p></div>`;
    return;
  }

  contenedor.innerHTML =
    `<div class="row g-3">` +
    empleadosBandejaData
      .map((emp) => {
        const isSelected =
          empleadoSeleccionado === emp.id ? "seleccionado" : "";
        const totalTareas =
          (parseInt(emp.en_proceso) || 0) + (parseInt(emp.pendientes) || 0);

        // Decidimos el color de la etiqueta de carga (Rojo = saturado, Verde = libre).
        const workloadClass = totalTareas > 0 ? "workload-med" : "workload-low";
        const workloadText =
          totalTareas > 0 ? `${totalTareas} TAREAS` : "SIN TAREAS";

        return `
            <div class="col-12">
                <div class="empleado-card-premium ${isSelected}" onclick="seleccionarEmpleado(${emp.id})" data-id="${emp.id}">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="empleado-avatar-pro ${emp.esresponsable ? "jefe" : ""}">
                            ${obtenerIniciales(emp.nombre_completo)}
                            ${emp.esresponsable ? '<i class="bi bi-patch-check-fill badge-jefe"></i>' : ""}
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="emp-name-pro">${escaparHtml(emp.nombre_completo)}</div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="emp-role-pro">${emp.esresponsable ? "Líder de Equipo" : "Especialista"}</div>
                                <span class="workload-tag ${workloadClass} workload-compact">
                                    <i class="bi bi-briefcase-fill me-1"></i> ${workloadText}
                                </span>
                            </div>
                        </div>
                        <div class="selection-indicator"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                </div>
            </div>
        `;
      })
      .join("") +
    `</div>`;
}

/**
 * Marca visualmente al técnico elegido y habilita el botón de confirmar.
 * @param {*} idEmpleado
 */
function seleccionarEmpleado(idEmpleado) {
  empleadoSeleccionado = idEmpleado;
  document
    .querySelectorAll(".empleado-card-premium")
    .forEach((el) =>
      el.classList.toggle(
        "seleccionado",
        parseInt(el.dataset.id) === idEmpleado,
      ),
    );
  document.getElementById("btn-confirmar-asignacion").disabled = false;
}

/**
 * Envía la orden de asignación al servidor.
 * Registra quién hará el trabajo y libera el pedido de la bandeja.
 * @returns
 */
function confirmarAsignacion() {
  if (!empleadoSeleccionado || !requerimientoSeleccionado) {
    return;
  }

  const btn = document.getElementById("btn-confirmar-asignacion");
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

  const formData = new FormData();
  formData.append("idatencion", requerimientoSeleccionado.idatencion);
  formData.append("idusuario_asignado", empleadoSeleccionado);

  fetch(`${base_url}responsable/pedidos/asignar`, {
    method: "POST",
    body: formData,
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        bootstrap.Modal.getInstance(
          document.getElementById("modal-asignar"),
        ).hide();
        const esClaro = document.documentElement.getAttribute("data-theme") === "light";
        Swal.fire({
          icon: "success",
          title: "¡Tarea Delegada!",
          text: data.message,
          background: esClaro ? "#fff" : "#161616",
          color: esClaro ? "#000" : "#fff",
          timer: 2000,
          showConfirmButton: false,
        });
        cargarEmpleados();
        setTimeout(() => cargarBandeja(), 500); // Recargar bandeja para ver los cambios.
      } else {
        const esClaro = document.documentElement.getAttribute("data-theme") === "light";
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message,
          background: esClaro ? "#fff" : "#161616",
          color: esClaro ? "#000" : "#fff",
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
      }
    })
    .catch(() => {
      const esClaro = document.documentElement.getAttribute("data-theme") === "light";
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Fallo en la comunicación con el servidor.",
        background: esClaro ? "#fff" : "#161616",
        color: esClaro ? "#000" : "#fff",
      });
      btn.disabled = false;
    });
}

// DETALLE REQUERIMIENTO (VISTA)

/**
 * Trae los detalles extendidos de un pedido (archivos, descripción, tracking).
 * @param {*} idAtencion
 * @returns
 */
function verDetalleRequerimiento(idAtencion) {
  // Buscamos si el pedido está en Pendientes o en Revisión.
  let req =
    requerimientosData.find(
      (r) => parseInt(r.idatencion) === parseInt(idAtencion),
    ) || revisionData.find((r) => parseInt(r.id) === parseInt(idAtencion));
  if (!req) return;

  const modal = new bootstrap.Modal(
    document.getElementById("modal-ver-detalle"),
  );
  document.getElementById("detalle-contenido").innerHTML =
    `<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-3 text-muted">Construyendo expediente...</p></div>`;
  document.getElementById("detalle-titulo-requerimiento").textContent =
    escaparHtml(req.titulo || "Sin título");
  modal.show();

  // Solicitud para traer el detalle completo (incluyendo archivos adjuntos)
  fetch(`${base_url}responsable/pedidos/detalle?id=${idAtencion}`)
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        window.requerimientoActual = data.data; // Guardamos en memoria global para el modo edición.
        renderizarDetalleRequerimiento(data.data, data.archivos);
      } else
        document.getElementById("detalle-contenido").innerHTML =
          `<div class="alert alert-danger">${data.message}</div>`;
    })
    .catch(
      () =>
      (document.getElementById("detalle-contenido").innerHTML =
        `<div class="alert alert-danger">Error de conexión al obtener detalles</div>`),
    );
}

/**
 * Maqueta visualmente el "Expediente Digital" en dos columnas.
 * @param {*} req
 * @param {*} archivos
 */
function renderizarDetalleRequerimiento(req, archivos) {
  const archivosCliente = archivos.filter((a) => !a.idatencion); // Adjuntos originales.
  const archivosEmpleado = archivos.filter((a) => a.idatencion); // Adjuntos de la entrega final.

  // Mapeo de estilos según el estado actual.
  const esMap = {
    pendiente: { l: "PENDIENTE", c: "#ef4444", i: "bi-clock" },
    pendiente_asignado: { l: "ASIGNADO", c: "#3b82f6", i: "bi-person-check" },
    en_proceso: { l: "EN PROCESO", c: "#f5c400", i: "bi-play-circle" },
    en_revision: { l: "EN REVISIÓN", c: "#a855f7", i: "bi-eye" },
    finalizado: { l: "FINALIZADO", c: "#22c55e", i: "bi-check-circle" },
  };
  const es = esMap[req.estado] || {
    l: (req.estado || "").toUpperCase(),
    c: "#999",
    i: "bi-question",
  };

  const p = (req.prioridad || "media").toLowerCase();
  const prMap = {
    alta: { l: "ALTA", c: "#ef4444", i: "bi-chevron-double-up" },
    media: { l: "MEDIA", c: "#f5c400", i: "bi-chevron-up" },
    baja: { l: "BAJA", c: "#3b82f6", i: "bi-chevron-down" },
  };
  const pri = prMap[p] || { l: p.toUpperCase(), c: "#999", i: "bi-dash" };

  // Estructura principal del expediente.
  document.getElementById("detalle-contenido").innerHTML = `
        <div class="case-expediente">
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Cabecera de estados -->
                    <div class="d-flex gap-2 mb-3">
                        <span class="case-badge" style="background:${es.c}15; color:${es.c}; border-color:${es.c}44;"><i class="bi ${es.i} me-1"></i>${es.l}</span>
                        <span class="case-badge" style="background:${pri.c}15; color:${pri.c}; border-color:${pri.c}44;"><i class="bi ${pri.i} me-1"></i>PRIORIDAD ${pri.l}</span>
                    </div>

                    <div class="case-card">
                        <div class="case-section-title"><i class="bi bi-file-earmark-text"></i> REQUERIMIENTO ORIGINAL</div>
                        <div class="row g-4">
                            <div class="col-md-6"><span class="case-label">Objetivo</span><div class="case-value">${escaparHtml(req.objetivo_comunicacion || "---")}</div></div>
                            <div class="col-md-6"><span class="case-label">Público</span><div class="case-value">${escaparHtml(req.publico_objetivo || "---")}</div></div>
                            <div class="col-12"><span class="case-label">Descripcion</span><div class="case-value-box"><div class="case-value" style="white-space:pre-wrap;">${escaparHtml(req.descripcion || "Sin descripción.")}</div></div></div>
                            <div class="col-md-6"><span class="case-label">Canales</span><div class="d-flex flex-wrap gap-2 mt-2">${formatearLista(req.canales_difusion)}</div></div>
                            <div class="col-md-6"><span class="case-label">Formatos</span><div class="d-flex flex-wrap gap-2 mt-2">${formatearLista(req.formatos_solicitados)}</div></div>
                            <div class="col-12">
                                <span class="case-label">Materiales Adjuntos (Cliente)</span>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    ${archivosCliente.map((a) => `<a href="${base_url}responsable/archivos/vista-previa/${a.id}" target="_blank" class="case-file-link"><i class="bi bi-paperclip text-oro"></i> ${escaparHtml(a.nombre)}</a>`).join("") || '<span class="text-muted small">Sin adjuntos del cliente.</span>'}
                                </div>
                                <div class="col-12 mt-3">
                                  <span class="case-label">Link de Referencia (Cliente)</span>
                                  ${!req.url_subida ? `<span class="small">Sin link de referencia.</span>` : `<a href="${req.url_subida}" target="_blank" class="btn btn-sm btn-outline-warning"><i class="bi bi-link-45deg"></i>${req.url_subida}</a>`}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="case-card" style="height: 100%;">
                        <div class="case-section-title"><i class="bi bi-info-circle"></i> RESUMEN TÉCNICO</div>
                        <div class="case-sidebar-item">
                            <span class="case-label">Cliente / Solicitante</span>
                            <div class="case-value font-weight-700">${escaparHtml(req.nombre_cliente || "---")}</div>
                            <div class="text-muted small" style="font-size:11px;">${escaparHtml(req.nombre_area || "---")}</div>
                        </div>
                        <div class="case-sidebar-item"><span class="case-label">Empresa</span><div class="case-value font-weight-700 text-oro">${escaparHtml(req.nombre_empresa)}</div></div>
                        <div class="case-sidebar-item"><span class="case-label">Categoría</span><div class="case-value">${escaparHtml(req.nombre_servicio || req.servicio)}</div></div>
                        <div class="case-sidebar-item">
                            <span class="case-label">Técnico Asignado</span>
                            <div class="case-value" style="color:${req.empleado_nombre ? "var(--amarillo)" : "#666"};">
                                <i class="bi bi-person-badge me-1"></i> ${escaparHtml(req.empleado_nombre || "Pendiente")}
                            </div>
                        </div>
                        <div class="case-sidebar-item mt-3">
                            <div class="mb-3">
                                <span class="case-label mb-1">Solicitado:</span>
                                <div class="case-value" style="font-size:13px; line-height:1.4;">${formatearFecha(req.fechacreacion)}</div>
                            </div>
                            <div>
                                <span class="case-label mb-1">Límite:</span>
                                <div class="case-value font-weight-800 text-warning" style="font-size:14px; line-height:1.4;">${formatearFecha(req.fecharequerida)}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Entrega (Si existe) -->
                ${(req.estado === 'en_revision' || req.estado === 'finalizado') ? `
                <div class="col-12 mt-2">
                    <div class="case-card" style="border-left: 4px solid #22c55e; background: rgba(34, 197, 94, 0.03);">
                        <div class="case-section-title" style="color: #22c55e;"><i class="bi bi-send-check-fill"></i> INFORMACIÓN DE LA ENTREGA</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <span class="case-label">Enlace de Entrega</span>
                                <div class="mt-2">
                                    ${req.url_entrega ? `<a href="${req.url_entrega}" target="_blank" class="case-ref-link" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2); color: #22c55e;"><i class="bi bi-link-45deg"></i> Ver Trabajo en Línea</a>` : '<span class="text-muted small">No se proporcionó URL.</span>'}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <span class="case-label">Notas del Especialista</span>
                                <div class="case-value-box mt-2" style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px;">
                                    <div class="case-value" style="font-size: 13px;">${escaparHtml(req.notas_tecnicas || "Sin observaciones adicionales.")}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <span class="case-label">Archivos del Entregable</span>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    ${archivosEmpleado.map((a) => `<a href="${base_url}responsable/archivos/vista-previa/${a.id}" target="_blank" class="case-file-link" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2);"><i class="bi bi-file-earmark-check text-success"></i> ${escaparHtml(a.nombre)}</a>`).join("") || '<span class="text-muted small">Sin archivos adjuntos en la entrega.</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>`;
}

// UTILIDADES | FORMATEO

/**
 * Filtra los requerimientos según el texto ingresado en el buscador.
 * @returns
 */
function filtrarBandeja() {
  const busqueda = document
    .getElementById("buscador-bandeja")
    .value.toLowerCase()
    .trim();
  if (!busqueda) {
    renderizarBandeja(requerimientosData);
    return;
  }

  const filtrados = requerimientosData.filter(
    (item) =>
      (item.titulo || "").toLowerCase().includes(busqueda) ||
      (item.nombreempresa || "").toLowerCase().includes(busqueda) ||
      (item.cliente_nombre || "").toLowerCase().includes(busqueda) ||
      String(item.idatencion).includes(busqueda),
  );
  renderizarBandeja(filtrados);
}

/**
 * Formatea fechas cortas (DD/MM/YYYY)
 * @param {*} f
 * @returns
 */
function formatearFechaLimpia(f) {
  if (!f) {
    return "---";
  }
  const d = new Date(f);
  if (isNaN(d.getTime())) {
    return f;
  }
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}

/**
 * Formatea fechas con hora (DD/MM/YYYY HH:MM)
 * @param {*} f
 * @returns
 */
function formatearFecha(f) {
  if (!f) {
    return "---";
  }
  const d = new Date(f);
  if (isNaN(d.getTime())) {
    return f;
  }
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}<br><span style="opacity:0.6; font-size:0.9em;">${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}</span>`;
}

/**
 * Sanitiza texto para evitar ataques XSS e inyecciones
 * @param {*} t
 * @returns
 */
function escaparHtml(t) {
  if (!t) {
    return "";
  }
  const div = document.createElement("div");
  div.textContent = t;
  return div.innerHTML;
}

/**
 * Genera las iniciales de un nombre
 * @param {*} n
 * @returns
 */
function obtenerIniciales(n) {
  if (!n) {
    return "??";
  }
  const p = n.trim().split(" ");
  return (p.length >= 2 ? p[0][0] + p[1][0] : p[0]?.[0] || "?").toUpperCase();
}

/**
 * Técnica de optimización para retardar la ejecución de una función
 * @param {*} fn
 * @param {*} w
 * @returns
 */
function debounce(fn, w) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), w);
  };
}

/**
 * Actualiza el contador numérico de la cabecera
 * @param {*} c
 */
function actualizarContador(c) {
  const el = document.getElementById("contador-pendientes");
  const num = document.getElementById("contador-num");
  if (num) num.textContent = c;
  if (el) {
    el.innerHTML = `<i class="bi bi-inbox"></i> <span>${c}</span> <span class="contador-label">pendiente${c !== 1 ? "s" : ""}</span>`;
  }
}

/**
 * Limpia la tabla y muestra la ilustración de vacío
 */
function mostrarEstadoVacio() {
  document.getElementById("contenido-bandeja").innerHTML = "";
  document.getElementById("estado-vacio").classList.remove("d-none");
  actualizarContador(0);
}

/**
 * Muestra un mensaje de error visual dentro de la tabla
 * @param {*} m
 */
function mostrarError(m) {
  document.getElementById("contenido-bandeja").innerHTML =
    `<tr><td colspan="6" class="text-center py-4" style="color:#ef4444;"><i class="bi bi-exclamation-triangle-fill mb-2 d-block fs-3"></i>${escaparHtml(m)}</td></tr>`;
}

/**
 * Genera filas con spinners para indicar carga activa
 * @param {*} c
 * @returns
 */
function generarSkeletonFilas(c) {
  return Array(c)
    .fill(0)
    .map(
      () =>
        `<tr><td colspan="6" class="py-3 text-center"><div class="spinner-border spinner-border-sm text-warning"></div></td></tr>`,
    )
    .join("");
}

/**
 * Convierte una cadena separada por comas en etiquetas (pills) visuales
 * @param {*} v
 * @returns
 */
function formatearLista(v) {
  if (!v) return "";
  let items = [];
  try {
    const p = JSON.parse(v);
    items = Array.isArray(p) ? p : [String(p)];
  } catch (e) {
    items = v
      .split(",")
      .map((s) => s.trim())
      .filter((s) => s);
  }
  return items
    .map((i) => `<span class="badge-tag">${escaparHtml(i)}</span>`)
    .join("");
}

// FUNCIONES AL ÁMBITO GLOBAL (WINDOW)
// Necesario por los botones HTML usan onclick="nombreFuncion()"
window.abrirModalAsignar = abrirModalAsignar;
window.seleccionarEmpleado = seleccionarEmpleado;
window.verDetalleRequerimiento = verDetalleRequerimiento;
window.cargarEmpleados = cargarEmpleados;