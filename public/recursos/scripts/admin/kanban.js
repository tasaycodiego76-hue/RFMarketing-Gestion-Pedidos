// ═══ ASIGNAR ÁREA ═══

/**
 * Abre el modal de asignación y carga las áreas disponibles
 * @param {number} idAtencion - ID de la atención a asignar
 */
async function abrirModalAsignar(idAtencion) {
  const inputId = document.getElementById("asignar-idatencion");
  const select = document.getElementById("asignar-empleado");

  inputId.value = idAtencion;
  select.innerHTML = '<option value="">Cargando...</option>';

  try {
    const response = await fetch(BASE_URL + "admin/kanban/areas");

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();

    if (data.length === 0) {
      select.innerHTML = '<option value="">No hay áreas disponibles</option>';
    } else {
      select.innerHTML = '<option value="">-- Seleccionar área --</option>';
      data.forEach((area) => {
        select.innerHTML += `<option value="${area.id}">${area.nombre}</option>`;
      });
    }
  } catch (error) {
    console.error("Error al cargar áreas:", error);
    select.innerHTML = '<option value="">Error al cargar áreas</option>';
  }

  $("#modalAsignar").modal("show");
}

/**
 * Confirma la asignación de área a una atención
 */
async function confirmarAsignacion() {
  const idAtencion = document.getElementById("asignar-idatencion").value;
  const idArea = document.getElementById("asignar-empleado").value;

  if (!idArea) {
    alert("Selecciona un área");
    return;
  }

  try {
    const response = await fetch(BASE_URL + "admin/kanban/asignarArea", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        idatencion: idAtencion,
        idareaagencia: idArea,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const res = await response.json();

    if (res.status === "success") {
      $("#modalAsignar").modal("hide");
      location.reload();
    } else {
      alert(res.msg || "Error al asignar el área");
    }
  } catch (error) {
    console.error("Error en asignación:", error);
    alert("Error al asignar el área. Intenta nuevamente.");
  }
}

// ═══ CAMBIAR ESTADO ═══

/**
 * Cambia el estado de una atención
 * @param {number} idAtencion - ID de la atención
 * @param {string} nuevoEstado - Nuevo estado a asignar
 * @param {string} accion - Nombre de la acción para confirmación
 */
async function cambiarEstado(idAtencion, nuevoEstado, accion) {
  if (!confirm(`¿Confirmar: ${accion}?`)) {
    return;
  }

  try {
    const response = await fetch(BASE_URL + "admin/kanban/cambiarEstado", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        idatencion: idAtencion,
        estado: nuevoEstado,
        accion: accion,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const res = await response.json();

    if (res.status === "success") {
      location.reload();
    } else {
      alert(res.msg || "Error al cambiar el estado");
    }
  } catch (error) {
    console.error("Error al cambiar estado:", error);
    alert("Error al cambiar el estado. Intenta nuevamente.");
  }
}

// ═══ CANCELAR ═══

/**
 * Cancela una atención solicitando motivo
 * @param {number} idAtencion - ID de la atención a cancelar
 */
async function cancelarAtencion(idAtencion) {
  const motivo = prompt("Motivo de cancelación:");

  if (motivo === null || motivo.trim() === "") {
    return;
  }

  try {
    const response = await fetch(BASE_URL + "admin/kanban/cancelar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        idatencion: idAtencion,
        motivo: motivo.trim(),
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const res = await response.json();

    if (res.status === "success") {
      location.reload();
    } else {
      alert(res.msg || "Error al cancelar la atención");
    }
  } catch (error) {
    console.error("Error al cancelar:", error);
    alert("Error al cancelar la atención. Intenta nuevamente.");
  }
}

// ═══ VER DETALLE ═══

/**
 * Muestra el detalle completo de una atención
 * @param {number} idAtencion - ID de la atención a consultar
 */
async function verDetalle(idAtencion) {
  const cuerpo = document.getElementById("detalle-cuerpo");
  const titulo = document.getElementById("detalle-titulo");

  cuerpo.innerHTML =
    '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
  $("#modalDetalle").modal("show");

  try {
    const URL_SERVIDOR = window.location.origin + "/";
    const response = await fetch(
      URL_SERVIDOR + "admin/kanban/detalle/" + idAtencion,
    );

    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const res = await response.json();
    if (res.status !== "success") {
      alert(res.msg || "Error al cargar el detalle");
      return;
    }

    const d = res.data;
    const listaArchivos = res.archivos;

    const badges = (arr) =>
      Array.isArray(arr) && arr.length
        ? arr
            .map((v) => `<span class="kb-badge-item">${escapeHtml(v)}</span>`)
            .join(" ")
        : "<em>—</em>";

    const html = `
        <div class="kb-detalle-seccion">
            <h6 class="kb-detalle-seccion-titulo">📋 Información del requerimiento</h6>
            <div class="kb-detalle-grid">
                <div><strong>Título</strong><br>${escapeHtml(d.titulo)}</div>
                <div><strong>Tipo / Servicio</strong><br>${escapeHtml(d.tipo_requerimiento || d.servicio || "—")}</div>
                <div><strong>Objetivo</strong><br>${escapeHtml(d.objetivo_comunicacion || "—")}</div>
                <div><strong>Público</strong><br>${escapeHtml(d.publico_objetivo || "—")}</div>
                <div><strong>Canales</strong><br>${badges(d.canales_difusion)}</div>
                <div><strong>Formatos</strong><br>
                    ${badges(d.formatos_solicitados)}
                    ${d.formato_otros ? `<br><small>Otros: ${escapeHtml(d.formato_otros)}</small>` : ""}
                </div>
                <div><strong>Materiales</strong><br>${d.tiene_materiales ? "✅ Sí" : "❌ No"}</div>
                <div><strong>Fecha requerida</strong><br>${d.fecharequerida}</div>
            </div>
            <div class="mt-2"><strong>Descripción</strong><br>${escapeHtml(d.descripcion || "—")}</div>
        </div>

        <hr class="kb-detalle-hr">

        <div class="kb-detalle-seccion">
            <h6 class="kb-detalle-seccion-titulo">⚙️ Gestión interna</h6>
            <div class="kb-detalle-grid">
                <div><strong>Estado</strong><br>${escapeHtml(d.estado)}</div>
                <div><strong>Prioridad Asignada</strong><br>
                    <select id="detalle-prioridad" class="form-control form-control-sm" style="width:auto">
                        <option value="Baja"  ${d.prioridad_admin === "Baja" ? "selected" : ""}>▼ Baja</option>
                        <option value="Media" ${d.prioridad_admin === "Media" ? "selected" : ""}>● Media</option>
                        <option value="Alta"  ${d.prioridad_admin === "Alta" ? "selected" : ""}>▲ Alta</option>
                    </select>
                    <button class="btn btn-sm btn-primary mt-1" onclick="cambiarPrioridad(${d.id})">Guardar</button>
                </div>
                <div><strong>Empresa</strong><br>${escapeHtml(d.nombreempresa || "—")}</div>
                <div><strong>Área Asignada</strong><br>${escapeHtml(d.area_nombre || "Sin asignar")}</div>
                <div><strong>Empleado</strong><br>${escapeHtml(d.empleado_fullname)}</div>
                <div><strong>Fecha Inicio</strong><br>${d.fechainicio}</div>
                <div><strong>Fecha Fin</strong><br>${d.fechafin}</div>
                <div><strong>Modificaciones</strong><br>${d.num_modificaciones ?? 0}</div>
            </div>
        </div>

        <hr class="kb-detalle-hr">
        <div class="kb-detalle-seccion">
            <h6>📁 Archivos adjuntos</h6>
            <div class="d-flex flex-wrap mt-2">
                ${
                  listaArchivos.length === 0
                    ? '<em class="text-muted small">Sin archivos adjuntos</em>'
                    : listaArchivos
                        .map(
                          (a) => `
                        <a href="${URL_SERVIDOR}${a.ruta.replace("writable/", "")}" 
                           download="${a.nombre}" 
                           class="btn btn-sm btn-outline-primary m-1 shadow-sm">
                            <i class="fas fa-download"></i> ${a.nombre}
                        </a>
                    `,
                        )
                        .join("")
                }
            </div>
        </div>`;

    titulo.textContent = d.titulo;
    cuerpo.innerHTML = html;
  } catch (error) {
    console.error("Error:", error);
    cuerpo.innerHTML =
      '<div class="alert alert-danger text-center">Error al cargar el detalle.</div>';
  }
}
async function cambiarPrioridad(idAtencion) {
  const prioridad = document.getElementById("detalle-prioridad").value;
  const res = await fetch(BASE_URL + "admin/kanban/cambiarPrioridad", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ idatencion: idAtencion, prioridad: prioridad }),
  });
  const data = await res.json();
  if (data.status === "success") {
    $("#modalDetalle").modal("hide");
    location.reload();
  } else {
    alert(data.msg || "Error al cambiar prioridad");
  }
}
/**
 * Escapa caracteres HTML para prevenir XSS
 * @param string text  Texto a escapar
 * @returns string  Texto escapado
 */
function escapeHtml(text) {
  if (!text) return "";

  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
