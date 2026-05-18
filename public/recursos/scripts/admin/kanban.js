// ══════════════════════════════════════════════════════
// ══ FORZAR RECORTE DE TÍTULOS LARGOS EN LAS TARJETAS ══
// ══════════════════════════════════════════════════════

// ═══════════════════════════════════════════════
// ═══ KANBAN.JS — Flujo completo y sin errores ══
// ═══════════════════════════════════════════════

const KB_ICONS = {
  pdf: "bi-file-earmark-pdf",
  doc: "bi-file-earmark-word",
  docx: "bi-file-earmark-word",
  xls: "bi-file-earmark-excel",
  xlsx: "bi-file-earmark-excel",
  png: "bi-file-earmark-image",
  jpg: "bi-file-earmark-image",
  jpeg: "bi-file-earmark-image",
  zip: "bi-file-earmark-zip",
  default: "bi-file-earmark",
};

// ── SISTEMA DE NOTIFICACIONES POST-CARGA ──
$(document).ready(function () {
  const msg = localStorage.getItem('kanban_msg');
  if (msg) {
    Swal.fire({
      icon: 'success',
      title: msg,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      background: '#0a0a0a',
      color: '#fff'
    });
    localStorage.removeItem('kanban_msg');
  }

  // ── ABRIR DETALLE AUTOMÁTICAMENTE SI VIENE EN LA URL ──
  const urlParams = new URLSearchParams(window.location.search);
  const idAVer = urlParams.get('ver');
  if (idAVer) {
    verDetalle(idAVer);
    // Limpiar el parámetro de la URL sin recargar para que no se reabra al refrescar
    const newUrl = window.location.pathname;
    window.history.replaceState({}, document.title, newUrl);
  }
});


// ════════════════════════════════════════════════════════
// ═══ ADMIN — Solo envía el pedido a un ÁREA          ═══
//     El empleado queda vacío. El responsable asigna. ═══
// ════════════════════════════════════════════════════════
async function abrirModalAsignar(idAtencion) {
  _resetModal(
    "Enviar Pedido al Área",
    "Enviar al Área",
    "confirmarAsignacion()",
  );
  document.getElementById("asignar-idatencion").value = idAtencion;

  const select = document.getElementById("asignar-empleado");
  select.innerHTML = '<option value="">Cargando áreas...</option>';

  try {
    const r = await fetch(BASE_URL + "admin/kanban/areas");
    const data = await r.json();
    select.innerHTML = '<option value="">— Seleccionar área —</option>';
    data.forEach((a) => {
      select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
    });
  } catch {
    select.innerHTML = '<option value="">Error al cargar áreas</option>';
  }
  $("#modalAsignar").modal("show");
}

async function confirmarAsignacion() {
  const idAtencion = document.getElementById("asignar-idatencion").value;
  const idArea = document.getElementById("asignar-empleado").value;
  if (!idArea) {
    alert("Selecciona un área");
    return;
  }

  const data = await _post("admin/kanban/asignarArea", {
    idatencion: idAtencion,
    idareaagencia: idArea,
  });
  if (data.status === "success") {
    $("#modalAsignar").modal("hide");
    Swal.fire({ icon: 'success', title: '¡Pedido enviado al área!', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true, background: '#0a0a0a', color: '#fff' });
  } else alert(data.msg);
}

// ═══════════════════════════════════════════════════════════════════════════════
// ═══ RESPONSABLE — Asigna empleado O se autoasigna                          ═══
//      Asignar empleado  desarrollando.                                    ═══
//       Solo cuando alguien pulsa "Iniciar Trabajo" se pone fechainicio.     ═══
// ═══════════════════════════════════════════════════════════════════════════════
async function abrirModalAsignarEmpleado(idAtencion, idArea) {
  _resetModal(
    "Asignar Responsable del Pedido",
    "Confirmar Asignación",
    "confirmarAsignacionEmpleado()",
  );
  document.getElementById("asignar-idatencion").value = idAtencion;

  const select = document.getElementById("asignar-empleado");
  select.innerHTML = '<option value="">Cargando empleados...</option>';

  try {
    const r = await fetch(BASE_URL + "admin/kanban/empleados/" + idArea);
    const data = await r.json();

    if (!data.length) {
      select.innerHTML =
        '<option value="">No hay empleados en esta área</option>';
      $("#modalAsignar").modal("show");
      return;
    }

    select.innerHTML = '<option value="">— Seleccionar empleado —</option>';

    // Auto-asignación del responsable
    if (typeof EMPLEADO_ACTUAL_ID !== "undefined" && EMPLEADO_ACTUAL_ID) {
      select.innerHTML += `<option value="${EMPLEADO_ACTUAL_ID}" style="font-weight:700;color:#F5C400;"> Asignarme a mí mismo</option>`;
    }

    data.forEach((u) => {
      if (u.id == EMPLEADO_ACTUAL_ID) return;
      select.innerHTML += `<option value="${u.id}">${u.nombre} ${u.apellidos}</option>`;
    });
  } catch {
    select.innerHTML = '<option value="">Error al cargar empleados</option>';
  }
  $("#modalAsignar").modal("show");
}

async function confirmarAsignacionEmpleado() {
  const idAtencion = document.getElementById("asignar-idatencion").value;
  const idEmpleado = document.getElementById("asignar-empleado").value;
  if (!idEmpleado) {
    alert("Selecciona un empleado");
    return;
  }

  const data = await _post("admin/kanban/asignarEmpleado", {
    idatencion: idAtencion,
    idempleado: idEmpleado,
  });
  if (data.status === "success") {
    $("#modalAsignar").modal("hide");
    Swal.fire({ icon: 'success', title: '¡Empleado asignado!', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true, background: '#0a0a0a', color: '#fff' });
  } else alert(data.msg);
}

async function verDetalle(idAtencion) {
  const cuerpo = document.getElementById("detalle-cuerpo");
  if (!cuerpo) return;

  // Loader inicial con estilo premium
  cuerpo.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:450px;background:#080808;border-radius:0 0 16px 16px;">
            <div class="spinner-border" style="color:#F5C400;width:3.5rem;height:3.5rem;border-width:3px;margin-bottom:24px;"></div>
            <span style="color:#F5C400;font-size:11px;letter-spacing:4px;font-weight:900;text-transform:uppercase;animation:pulse 2s infinite;">Preparando expediente digital...</span>
        </div>`;

  $("#modalDetalle").modal("show");

  try {
    const r = await fetch(BASE_URL + "admin/kanban/detalle/" + idAtencion);
    const res = await r.json();

    if (res.status !== "success") {
      cuerpo.innerHTML = _errorHtml(
        "No se pudo obtener la información detallada.",
      );
      return;
    }

    const d = res.data;
    const archivosCliente = res.archivos_cliente || [];
    const archivosEmpleado = res.archivos_empleado || [];

    // Título del Modal
    document.getElementById("detalle-titulo").innerText = (d.nombreempresa || "DETALLE DE PEDIDO").toUpperCase();

    // Clonar el template
    const template = document.getElementById("template-detalle-kanban");
    if (!template) throw new Error("Template no encontrado en la vista.");
    const clone = template.content.cloneNode(true);
    const set = (sel, val) => { const el = clone.querySelector(sel); if (el) el.textContent = val; };
    const setHtml = (sel, val) => { const el = clone.querySelector(sel); if (el) el.innerHTML = val; };

    // ── Fechas y Tiempos ─────
    const fReq = d.fecharequerida || "---";
    const fSol = d.r_fechacreacion || "---";
    const fIni = d.fechainicio || "---";
    const fFin = d.fechacompletado || "---";

    // Formato de fecha con hora: YYYY-MM-DD HH:MM → DD/MM/YYYY HH:MM
    const fmtRaw = (s) => {
      if (!s || s === "---" || s === "—") return s;
      s = String(s).trim();
      if (s.length === 0) return "---";

      // Si ya tiene el formato "YYYY-MM-DD HH:MM"
      if (s.length >= 16 && s.includes(' ')) {
        const partes = s.split(' ');
        if (partes.length >= 2) {
          const fecha = partes[0].split('-').reverse().join('/');
          const hora = partes[1];
          return `${fecha} ${hora}`;
        }
      }
      // Si solo tiene fecha "YYYY-MM-DD"
      else if (s.length >= 10) {
        const fecha = s.substring(0, 10).split("-").reverse().join("/");
        return fecha;
      }
      return s;
    };
    const fSolFmt = fmtRaw(fSol);

    // ── Pill de Estado ──────────────────────────────────────
    let statusConfig = {
      label: "ESTADO DESCONOCIDO",
      color: "#888",
      icon: "bi-question-circle",
    };
    if (d.estado === "finalizado")
      statusConfig = {
        label: "COMPLETADO",
        color: "#10b981",
        icon: "bi-check2-all",
      };
    else if (!d.idempleado)
      statusConfig = {
        label: "SIN ASIGNAR",
        color: "#F5C400",
        icon: "bi-person-exclamation",
      };
    else if (d.estado === "pendiente_asignado")
      statusConfig = {
        label: "ASIGNADO",
        color: "#a855f7",
        icon: "bi-person-check",
      };
    else if (d.estado === "en_proceso")
      statusConfig = {
        label: "EN DESARROLLO",
        color: "#3b82f6",
        icon: "bi-lightning-charge",
      };
    else if (d.estado === "en_revision")
      statusConfig = { label: "EN REVISIÓN", color: "#f97316", icon: "bi-eye" };

    clone.querySelector('.tpl-status-pill').innerHTML = `
        <div class="status-pill-premium" style="--pill-color: ${statusConfig.color}">
            <i class="bi ${statusConfig.icon}"></i>
            <span>${statusConfig.label}</span>
        </div>
    `;

    // ── Lógica de Pasos (Stepper) ──────────────────────────
    const estados = [
      "pendiente_sin_asignar",
      "pendiente_asignado",
      "en_proceso",
      "en_revision",
      "finalizado",
    ];
    const actualIdx = estados.indexOf(d.estado);

    const renderStep = (idx, icon, label) => {
      const isDone = actualIdx >= idx;
      const isCurrent = actualIdx === idx;
      const color = isDone ? "#F5C400" : "#222";
      const iconColor = isDone ? "#000" : "#444";
      const bgColor = isDone ? "#F5C400" : "#0f0f0f";

      return `
            <div class="step-item ${isCurrent ? "current" : ""} ${isDone ? "done" : ""}">
                <div class="step-icon" style="background:${bgColor}; color:${iconColor}; border:1px solid ${isDone ? "#F5C400" : "#222"};">
                    <i class="bi ${icon}"></i>
                </div>
                <div class="step-label" style="color:${isDone ? "#fff" : "#444"};">${label}</div>
            </div>
        `;
    };

    clone.querySelector('.tpl-stepper-container').innerHTML = `
        <div class="workflow-stepper">
            ${renderStep(0, "bi-plus-lg", "SOLICITUD")}
            <div class="step-line ${actualIdx > 0 ? "active" : ""}"></div>
            ${renderStep(2, "bi-play-fill", "PROCESO")}
            <div class="step-line ${actualIdx > 2 ? "active" : ""}"></div>
            ${renderStep(3, "bi-eye-fill", "REVISIÓN")}
            <div class="step-line ${actualIdx > 3 ? "active" : ""}"></div>
            ${renderStep(4, "bi-check-all", "ENTREGA")}
        </div>
    `;

    // ── Llenar Datos Básicos ──
    clone.querySelector('.tpl-id').textContent = d.idrequerimiento || "---";
    clone.querySelector('.tpl-titulo').textContent = d.titulo || "SIN TÍTULO";
    clone.querySelector('.tpl-empresa').textContent = d.nombreempresa || "";
    clone.querySelector('.tpl-area').textContent = d.area_solicitante_nombre || "---";
    clone.querySelector('.tpl-servicio').textContent = d.servicio || "SERVICIO GENERAL";
    clone.querySelector('.tpl-idatencion').textContent = d.id.toString().padStart(5, "0");
    clone.querySelector('.tpl-descripcion').textContent = d.descripcion || "Sin descripción adicional.";

    // ── Datos del Cliente Responsable (Contacto) ──
    set('.tpl-cliente-nombre', (d.cliente_nombre + " " + (d.cliente_apellidos || "")).toUpperCase());
    set('.tpl-cliente-telefono', d.cliente_telefono || "SIN NÚMERO");
    set('.tpl-cliente-correo', (d.cliente_correo || "SIN CORREO").toLowerCase());
    // ── Estrategia ──
    clone.querySelector('.tpl-objetivo').textContent = d.objetivo_comunicacion && d.objetivo_comunicacion.trim() !== '' ? d.objetivo_comunicacion : '---';
    clone.querySelector('.tpl-publico').textContent = d.publico_objetivo && d.publico_objetivo.trim() !== '' ? d.publico_objetivo : '---';

    // ── Tags ──
    const renderTags = (json) => {
      const list = _parseList(json);
      if (!list.length) return '<span class="tag-empty">SIN ESPECIFICAR</span>';
      return `<div class="tags-container">` + list.map(t => `<span class="tag-item">${String(t).toUpperCase()}</span>`).join("") + `</div>`;
    };
    clone.querySelector('.tpl-canales').innerHTML = renderTags(d.canales_difusion);
    clone.querySelector('.tpl-formatos').innerHTML = renderTags(d.formatos_solicitados);

    // ── Archivos Cliente ──
    const renderArchivos = (lista, color = "#F5C400") => {
      if (!lista.length) return '<div class="no-files">No se encontraron archivos adjuntos.</div>';
      return `<div class="files-grid">` + lista.map((a) => {
        const ext = a.nombre.split(".").pop().toLowerCase();
        const icon = KB_ICONS[ext] ?? KB_ICONS.default;
        return `
            <a href="${a.url_completa}" target="_blank" class="file-card">
                <div class="file-icon" style="color:${color}"><i class="bi ${icon}"></i></div>
                <div class="file-info">
                    <span class="file-name">${a.nombre}</span>
                    <span class="file-ext">${ext.toUpperCase()}</span>
                </div>
                <i class="bi bi-download download-icon"></i>
            </a>`;
      }).join("") + "</div>";
    };

    let arcHtml = renderArchivos(archivosCliente);
    if (d.url_subida) {
      arcHtml += `
        <a href="${d.url_subida}" target="_blank" style="margin-top:20px; display:flex; align-items:center; justify-content:center; gap:10px; background:#F5C400; color:#000; padding:15px; border-radius:12px; font-weight:900; text-decoration:none; font-size:12px; letter-spacing:1px;">
            <i class="bi bi-cloud-arrow-down-fill" style="font-size:20px;"></i> URL DEL CLIENTE
        </a>`;
    }
    clone.querySelector('.tpl-archivos-cliente').innerHTML = arcHtml;

    // ── Entrega Empleado ──
    if (d.estado === "en_revision" || d.estado === "finalizado") {
      clone.querySelector('.tpl-entrega-container').innerHTML = `
        <div class="exp-card" style="border-color:#10b981; background:rgba(16,185,129,0.02); margin-top:25px;">
            <div class="exp-card-header" style="background:rgba(16,185,129,0.05); border-bottom-color:rgba(16,185,129,0.1);">
                <i class="bi bi-send-check" style="color:#10b981;"></i> <span style="color:#10b981;">ENTREGA Y RESULTADOS</span>
            </div>
            <div class="exp-card-body">
                <div class="data-box" style="margin-bottom:20px; border-color:rgba(16,185,129,0.2);">
                    <span class="data-label" style="color:#10b981;">Link de Entrega</span>
                    <div class="data-value">
                        ${d.url_entrega ? `<a href="${d.url_entrega}" target="_blank" style="color:#fff; text-decoration:underline; font-weight:800; word-break:break-all;">${d.url_entrega} <i class="bi bi-box-arrow-up-right ms-2"></i></a>` : 'No se adjuntó link.'}
                    </div>
                </div>
                <div class="data-box" style="margin-bottom:20px; border-color:rgba(16,185,129,0.2);">
                    <span class="data-label" style="color:#10b981;">Notas del Desarrollador</span>
                    <div class="data-value" style="font-size:13px; color:#aaa;">${d.observacion_revision || "Sin notas adicionales."}</div>
                </div>
                <span class="data-label" style="color:#10b981; margin-bottom:10px;">Archivos de Entrega</span>
                ${renderArchivos(archivosEmpleado, "#10b981")}
            </div>
        </div>
      `;
    }

    // ── Responsable ──
    if (d.empleado_nombre) {
      const ini = (
        d.empleado_nombre[0] + (d.empleado_apellidos?.[0] ?? "")
      ).toUpperCase();
      clone.querySelector('.tpl-empleado').innerHTML = `
        <div class="user-card-premium">
            <div class="user-avatar-premium">${ini}</div>
            <div class="user-details">
                <span class="user-name">${d.empleado_nombre} ${d.empleado_apellidos}</span>
                <span class="user-role">RESPONSABLE ASIGNADO</span>
            </div>
        </div>`;
    } else {
      clone.querySelector('.tpl-empleado').innerHTML = `
        <div class="user-card-premium unassigned">
            <div class="user-avatar-premium"><i class="bi bi-person-dash"></i></div>
            <div class="user-details">
                <span class="user-name">PENDIENTE</span>
                <span class="user-role">ESPERANDO RESPONSABLE</span>
            </div>
        </div>`;
    }

    // ── Cronología y Auditoría ──
    clone.querySelector('.tpl-f-solicitud').textContent = fSolFmt;
    clone.querySelector('.tpl-f-limite').textContent = fmtRaw(fReq);

    if (d.fechainicio !== "---" && d.fechainicio !== "—") {
      clone.querySelector('.tpl-f-inicio-container').innerHTML = `
            <div class="data-box">
                <span class="data-label">Inicio de Trabajo</span>
                <div class="data-value">${fmtRaw(fIni)}</div>
            </div>`;
    }

    if (d.fechacompletado !== "---" && d.fechacompletado !== "—") {
      clone.querySelector('.tpl-f-fin-container').innerHTML = `
            <div class="data-box" style="border-color:#10b98133;">
                <span class="data-label" style="color:#10b981;">Completado</span>
                <div class="data-value">${fmtRaw(fFin)}</div>
            </div>`;
    }

    clone.querySelector('.tpl-modificaciones').textContent = d.num_modificaciones || 0;

    // ── Prioridad ──
    const prioManager = clone.querySelector('.priority-manager');
    if (d.estado === 'en_revision' || d.estado === 'finalizado') {
      if (prioManager) prioManager.style.display = 'none';
    } else {
      const currentPrio = d.prioridad_admin || d.prioridad || "Media";
      clone.querySelectorAll('.btn-prio').forEach(btn => {
        const pVal = btn.getAttribute('data-prio');
        if (pVal === currentPrio) {
          btn.classList.add('active');
          btn.classList.add('prio-' + pVal.toLowerCase());
        }
        btn.onclick = () => cambiarPrioridad(d.id, pVal);
      });
    }

    // Finalmente inyectar en el DOM
    cuerpo.innerHTML = "";
    cuerpo.appendChild(clone);
  } catch (e) {
    console.error("ERROR EN DETALLE:", e);
    cuerpo.innerHTML = _errorHtml(
      "Hubo un error al procesar el expediente. Por favor, intenta de nuevo.",
    );
  }
}

// ═══════════════════════════════════════
// ═══ OTRAS ACCIONES                   ══
// ═══════════════════════════════════════
async function cambiarPrioridad(id, valor) {
  const data = await _post("admin/kanban/cambiarPrioridad", {
    idatencion: id,
    prioridad: valor,
  });
  if (data.status === "success") {
    Swal.fire({ icon: 'success', title: 'Prioridad actualizada', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: '#0a0a0a', color: '#fff' });
  } else alert(data.msg);
}


async function cambiarEstado(id, est, acc) {
  const esFinalizar = (est === 'finalizado');

  const result = await Swal.fire({
    title: esFinalizar ? '¿Aprobar requerimiento?' : '¿Confirmar cambio de estado?',
    text: esFinalizar
      ? 'Se registrará como entregado y se notificará al cliente.'
      : `¿Estás seguro de que deseas cambiar el estado a ${acc.toLowerCase()}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: esFinalizar ? '#10b981' : '#F5C400',
    cancelButtonColor: '#333',
    confirmButtonText: esFinalizar ? 'Sí, aprobar' : 'Sí, confirmar',
    cancelButtonText: 'Cancelar',
    background: '#0a0a0a',
    color: '#fff'
  });

  if (!result.isConfirmed) return;

  // Feedback inmediato de procesamiento
  Swal.fire({
    title: 'Procesando...',
    text: 'Actualizando el tablero, por favor espera.',
    allowOutsideClick: false,
    showConfirmButton: false,
    background: '#0a0a0a',
    color: '#fff',
    didOpen: () => {
      Swal.showLoading();
    }
  });

  const data = await _post("admin/kanban/cambiarEstado", {
    idatencion: id,
    estado: est,
    accion: esFinalizar ? 'Requerimiento finalizado y entregado con éxito.' : acc,
  });

  if (data.status === "success") {
    const msg = esFinalizar
      ? '¡Pedido aprobado y entregado con éxito!'
      : `¡Pedido marcado como ${acc.toLowerCase()} con éxito!`;
    $("#modalDetalle").modal("hide");
    Swal.fire({ icon: 'success', title: msg, toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true, background: '#0a0a0a', color: '#fff' });
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#0a0a0a', color: '#fff' });
  }
}

// ── SOLICITAR RETROALIMENTACIÓN AL REGRESAR A PROCESO ──
function solicitarRetroalimentacion(id) {
  document.getElementById("retro-idatencion").value = id;
  document.getElementById("retro-mensaje").value = "";
  $("#modalRetro").modal("show");
}

async function enviarRetroalimentacion() {
  const id = document.getElementById("retro-idatencion").value;
  const msg = document.getElementById("retro-mensaje").value;

  if (!msg.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'Campo vacío',
      text: 'Por favor, escribe un mensaje de mejora.',
      background: '#0a0a0a',
      color: '#fff'
    });
    return;
  }

  // Feedback inmediato
  Swal.fire({
    title: 'Enviando corrección...',
    allowOutsideClick: false,
    showConfirmButton: false,
    background: '#0a0a0a',
    color: '#fff',
    didOpen: () => {
      Swal.showLoading();
    }
  });

  const data = await _post("admin/kanban/regresarAProceso", {
    idatencion: id,
    mensaje: msg,
  });

  if (data.status === "success") {
    $("#modalRetro").modal("hide");
    Swal.fire({ icon: 'success', title: '¡Corrección enviada!', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true, background: '#0a0a0a', color: '#fff' });
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#0a0a0a', color: '#fff' });
  }
}

async function cancelarAtencion(id) {
  const { value: motivo } = await Swal.fire({
    title: 'Cancelar Pedido',
    text: 'Indica el motivo de la cancelación para informar al cliente:',
    input: 'textarea',
    inputPlaceholder: 'Escribe el motivo aquí...',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#333',
    confirmButtonText: 'Sí, cancelar pedido',
    cancelButtonText: 'Cerrar',
    background: '#0a0a0a',
    color: '#fff',
    inputAttributes: {
      'aria-label': 'Escribe el motivo aquí'
    },
    preConfirm: (value) => {
      if (!value) {
        Swal.showValidationMessage('Debes ingresar un motivo');
      }
      return value;
    }
  });

  if (!motivo) return;

  Swal.fire({
    title: 'Cancelando...',
    text: 'Por favor espera.',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });

  const data = await _post("admin/kanban/cancelar", {
    idatencion: id,
    motivo: motivo,
  });

  if (data.status === "success") {
    Swal.fire({ icon: 'success', title: 'Pedido cancelado', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true, background: '#0a0a0a', color: '#fff' });
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: data.msg, background: '#0a0a0a', color: '#fff' });
  }
}

// ═══════════════════════════════════════
// ═══ HELPERS PRIVADOS                 ══
// ═══════════════════════════════════════

async function _post(endpoint, body) {
  const r = await fetch(BASE_URL + endpoint, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
  return r.json();
}

function _resetModal(titulo, btnTexto, btnOnclick) {
  document.querySelector(".kb-modal-title-asignar").textContent = titulo;
  const btn = document.querySelector(".kb-btn-confirmar-asignar");
  btn.textContent = btnTexto;
  btn.setAttribute("onclick", btnOnclick);
}

function _pill(icon, label, color, bg) {
  return `<span style="background:${bg};color:${color};border:1px solid ${color}33;
        padding:6px 14px;border-radius:20px;font-size:12px;font-weight:800;letter-spacing:0.5px;
        text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;">
        <i class="bi ${icon}"></i>${label}</span>`;
}

function _seccion(icon, titulo, color, innerHtml) {
  return `<div class="kd-sec" style="border-left-color:${color};">
        <div class="kd-sec-title" style="color:${color};">
            <i class="bi ${icon}"></i>${titulo}
        </div>
        ${innerHtml}
    </div>`;
}

function _label(texto) {
  return `<span style="color:#F5C400; font-size:12px; font-weight:900; letter-spacing:1.5px; text-transform:uppercase; display:block; margin-bottom:8px; border-bottom:2px solid #F5C400; padding-bottom:4px; display:inline-block; font-family:'Bebas Neue', sans-serif;">${texto}</span>`;
}

function _errorHtml(msg) {
  return `<div style="color:#ef4444;padding:40px;text-align:center;">
        <i class="bi bi-exclamation-triangle" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        <p style="font-size:13px;">${msg}</p>
    </div>`;
}

function _parseList(json) {
  if (!json) return [];
  try {
    const l = JSON.parse(json);
    return Array.isArray(l) ? l : [json];
  } catch {
    if (typeof json === "string" && json.includes(",")) {
      return json
        .split(",")
        .map((s) => s.trim())
        .filter((s) => s);
    }
    return [json];
  }
}

// ═══════════════════════════════════════
// ═══ DRAG & DROP (SORTABLE.JS)        ══
// ═══════════════════════════════════════
document.addEventListener("DOMContentLoaded", () => {
  const colAprobar = document.querySelector(
    '.kb-col-body[data-estado="pendiente_sin_asignar"]',
  );
  const colProceso = document.querySelector(
    '.kb-col-body[data-estado="en_proceso"]',
  );

  if (colAprobar && colProceso) {
    const style = document.createElement("style");
    style.innerHTML = `
            .kb-col { display: flex !important; flex-direction: column !important; }
            .kb-col-body { flex-grow: 1 !important; min-height: 500px !important; }
        `;
    document.head.appendChild(style);

    new Sortable(colAprobar, {
      group: { name: "kanban", pull: true, put: false },
      draggable: ".js-draggable",
      animation: 150,
    });

    // SOLO SE PUEDE SOLTAR AQUÍ (NO REGRESAR)
    new Sortable(colProceso, {
      group: { name: "kanban", pull: false, put: true },
      draggable: ".js-draggable",
      sort: false,
      animation: 150,
      onAdd(evt) {
        const idAtencion = evt.item.getAttribute("data-id");
        _post("admin/kanban/cambiarEstado", {
          idatencion: idAtencion,
          estado: "pendiente_asignado",
          accion: "Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.",
          idareaagencia: AREA_ACTUAL,
        }).catch(() => {});
      },
    });
  }
});

// ═══════════════════════════════════════
// ═══ PUSHER — TIEMPO REAL            ══
// ═══════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  if (typeof PUSHER_KEY === 'undefined') return;

  const pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });
  const canal = pusher.subscribe(PUSHER_CANAL);

  async function _getTarjetaHTML(id) {
    const res = await fetch(BASE_URL + 'admin/kanban/tarjetaHTML/' + id);
    return await res.text();
  }

  // Nueva solicitud → columna Nuevas Solicitudes en tiempo real
  canal.bind('solicitud.nueva', async function (data) {
    const columna = document.querySelector('.kb-col-body[data-estado="pendiente_sin_asignar"]');
    if (!columna) return;

    const html = await _getTarjetaHTML(data.id);
    if (!html.trim()) return;

    const temp = document.createElement('div');
    temp.innerHTML = html.trim();
    const nuevaTarjeta = temp.firstElementChild;
    nuevaTarjeta.style.animation = 'fadeIn 0.4s ease';
    columna.prepend(nuevaTarjeta);

    Swal.fire({
      icon: 'info',
      title: `Nuevo pedido #${data.id} recibido`,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 5000,
      timerProgressBar: true,
      background: '#0a0a0a',
      color: '#fff'
    });
  });

  // Cambio de estado → mueve la tarjeta a la columna correcta en tiempo real
  canal.bind('solicitud.actualizada', async function (data) {
    const tarjeta = document.querySelector(`.kb-card[data-id="${data.id}"]`);

    let estadoDestino = data.estado_nuevo;
    if (estadoDestino === 'pendiente_asignado') estadoDestino = 'en_proceso';

    if (estadoDestino === 'cancelado') {
      if (tarjeta) tarjeta.remove();
      return;
    }

    const columna = document.querySelector(`.kb-col-body[data-estado="${estadoDestino}"]`);
    if (!columna) {
      if (tarjeta) tarjeta.remove();
      return;
    }

    const html = await _getTarjetaHTML(data.id);
    if (!html.trim()) {
      if (tarjeta) tarjeta.remove();
      return;
    }

    const temp = document.createElement('div');
    temp.innerHTML = html.trim();
    const nuevaTarjeta = temp.firstElementChild;
    nuevaTarjeta.style.animation = 'fadeIn 0.4s ease';

    // Quitar la vieja (en cualquier columna) e insertar en la correcta
    if (tarjeta) tarjeta.remove();
    columna.prepend(nuevaTarjeta);
  });

});