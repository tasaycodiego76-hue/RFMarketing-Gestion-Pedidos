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
    const trackingData = res.tracking || [];
    const pausasData = res.pausas || [];
    const historialAsigData = res.historial_asig || [];

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
    const renderTags = (json, otrosStr = '') => {
      const list = _parseList(json);
      if (otrosStr && otrosStr.trim()) {
        const otrosList = otrosStr.split(',').map(s => s.trim()).filter(s => s);
        list.push(...otrosList);
      }
      if (!list.length) return '<span class="tag-empty">SIN ESPECIFICAR</span>';
      return `<div class="tags-container">` + list.map(t => `<span class="tag-item">${String(t).toUpperCase()}</span>`).join("") + `</div>`;
    };
    clone.querySelector('.tpl-canales').innerHTML = renderTags(d.canales_difusion);
    clone.querySelector('.tpl-formatos').innerHTML = renderTags(d.formatos_solicitados, d.formato_otros);

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

    // ── Motivo de Pausa ──
    if (d.ultimo_motivo_pausa) {
      clone.querySelector('.tpl-pausa-container').innerHTML = `
        <div class="exp-card" style="border-color:#f97316; background:rgba(249,115,22,0.02); margin-top:25px;">
            <div class="exp-card-header" style="background:rgba(249,115,22,0.05); border-bottom-color:rgba(249,115,22,0.1);">
                <i class="bi bi-pause-circle-fill" style="color:#f97316;"></i> <span style="color:#f97316;">ÚLTIMO MOTIVO DE PAUSA</span>
            </div>
            <div class="exp-card-body">
                <div class="data-value" style="font-size:13px; color:#aaa; font-style:italic;">"${d.ultimo_motivo_pausa}"</div>
            </div>
        </div>
      `;
    }

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

    // ── Historial de Pausas ──
    const pausasContainer = cuerpo.querySelector('.tpl-pausas-historial-container');
    if (pausasContainer) {
      pausasContainer.innerHTML = _renderAdminPausas(pausasData, d.estado);
    }

    // ── Timeline de Tracking ──
    const timelineContainer = cuerpo.querySelector('.tpl-timeline-container');
    if (timelineContainer) {
      timelineContainer.innerHTML = _renderAdminTimeline(trackingData, historialAsigData);
    }
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

    // 1. Actualizar modal si está abierto y es de la misma tarea
    const tplId = document.querySelector('#modalDetalle .tpl-idatencion');
    if (tplId && parseInt(tplId.textContent, 10) === parseInt(id, 10)) {
      document.querySelectorAll('#modalDetalle .btn-prio').forEach(btn => {
        btn.classList.remove('active', 'prio-baja', 'prio-media', 'prio-alta');
        if (btn.getAttribute('data-prio') === valor) {
          btn.classList.add('active', 'prio-' + valor.toLowerCase());
        }
      });
    }

    // 2. Actualizar la tarjeta en el tablero Kanban visualmente
    const tarjeta = document.querySelector(`.kb-card[data-id="${id}"]`);
    if (tarjeta) {
      try {
        const res = await fetch(BASE_URL + 'admin/kanban/tarjetaHTML/' + id);
        const html = await res.text();
        if (html.trim()) {
          const temp = document.createElement('div');
          temp.innerHTML = html.trim();
          const nuevaTarjeta = temp.firstElementChild;
          const columnaParent = tarjeta.closest('.kb-col-body');
          tarjeta.replaceWith(nuevaTarjeta);
          if (columnaParent) _ordenarColumnaPorPrioridad(columnaParent);
        }
      } catch (e) {
        console.error("Error al recargar tarjeta", e);
      }
    }
  } else {
    alert(data.msg);
  }
}

// ── ORDENAR COLUMNA SEGÚN PRIORIDAD (ALTA > MEDIA > BAJA) ──
function _ordenarColumnaPorPrioridad(columnaBody) {
  const prioridades = { 'alta': 3, 'media': 2, 'baja': 1 };
  const tarjetas = Array.from(columnaBody.querySelectorAll('.kb-card'));

  tarjetas.sort((a, b) => {
    const pA = prioridades[a.getAttribute('data-prio')] || 2;
    const pB = prioridades[b.getAttribute('data-prio')] || 2;
    return pB - pA;
  });

  tarjetas.forEach(t => columnaBody.appendChild(t));
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
// ═══ DRAG & DROP (SORTABLE.JS) & COUNTS ══
// ═══════════════════════════════════════
function _actualizarBadgeArea(areaId, delta) {
  const tab = document.querySelector(`.kb-area-tab[data-area-id="${areaId}"]`);
  if (!tab) return;
  let badge = tab.querySelector('.area-badge-notif');
  if (delta > 0) {
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'area-badge-notif';
      tab.appendChild(badge);
    }
    badge.textContent = parseInt(badge.textContent || '0', 10) + delta;
  } else if (badge) {
    const current = parseInt(badge.textContent || '0', 10) + delta;
    if (current > 0) badge.textContent = current;
    else badge.remove();
  }
}

function _actualizarConteosColumnas() {
  document.querySelectorAll('.kb-col').forEach(col => {
    const estado = col.getAttribute('data-estado');
    const body = col.querySelector(`.kb-col-body[data-estado="${estado}"]`);
    const countSpan = col.querySelector('.kb-col-count');
    if (body && countSpan) {
      const cards = body.querySelectorAll('.kb-card');
      countSpan.textContent = cards.length;

      let emptyDiv = body.querySelector('.kb-empty');
      if (cards.length > 0) {
        if (emptyDiv) emptyDiv.style.display = 'none';
      } else {
        if (!emptyDiv) {
          emptyDiv = document.createElement('div');
          emptyDiv.className = 'kb-empty';
          emptyDiv.innerHTML = '<i class="bi bi-inbox"></i><span>No hay requerimientos en esta etapa</span>';
          body.appendChild(emptyDiv);
        } else {
          emptyDiv.style.display = 'flex';
        }
      }
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const colAprobar = document.querySelector('.kb-col-body[data-estado="pendiente_sin_asignar"]');
  const colProceso = document.querySelector('.kb-col-body[data-estado="en_proceso"]');

  if (colAprobar && colProceso) {
    const style = document.createElement("style");
    style.innerHTML = `
            .kb-col { display: flex !important; flex-direction: column !important; }
            .kb-col-body { flex-grow: 1 !important; min-height: 500px !important; }
        `;
    document.head.appendChild(style);

    new Sortable(colAprobar, {
      group: { name: "kanban", pull: true, put: true },
      draggable: ".js-draggable",
      animation: 150,
      onAdd(evt) {
        const idAtencion = evt.item.getAttribute("data-id");
        _post("admin/kanban/regresarAPendiente", {
          idatencion: idAtencion
        }).catch(() => { });

        // Aumentar en 1 la notificación del área actual visible arriba del kanban
        _actualizarBadgeArea(AREA_ACTUAL, 1);
        _actualizarConteosColumnas();
      }
    });

    new Sortable(colProceso, {
      group: { name: "kanban", pull: true, put: true },
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
        }).catch(() => { });

        // Disminuir en 1 la notificación del área actual visible arriba del kanban
        _actualizarBadgeArea(AREA_ACTUAL, -1);
        _actualizarConteosColumnas();
      },
    });

    _actualizarConteosColumnas();
  }
});

// ═══════════════════════════════════════
// ═══ PUSHER — TIEMPO REAL            ══
// ═══════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  if (typeof RFPusher === 'undefined') return;

  async function _getTarjetaHTML(id) {
    const res = await fetch(BASE_URL + 'admin/kanban/tarjetaHTML/' + id);
    return await res.text();
  }

  // Nueva solicitud → columna Nuevas Solicitudes en tiempo real
  RFPusher.on('solicitud.nueva', async function (data) {
    if (data.idarea_agencia) {
      if (typeof AREA_ACTUAL !== 'undefined' && parseInt(data.idarea_agencia) !== parseInt(AREA_ACTUAL)) {
        _actualizarBadgeArea(data.idarea_agencia, 1);
        return;
      }
    } else {
      // Servicio personalizado sin área asignada: incrementar en todas las pestañas excepto la actual
      document.querySelectorAll('.kb-area-tab').forEach(tab => {
        const tabAreaId = tab.getAttribute('data-area-id');
        if (tabAreaId && typeof AREA_ACTUAL !== 'undefined' && parseInt(tabAreaId) !== parseInt(AREA_ACTUAL)) {
          _actualizarBadgeArea(tabAreaId, 1);
        }
      });
    }

    const columna = document.querySelector('.kb-col-body[data-estado="pendiente_sin_asignar"]');
    if (!columna) return;

    const html = await _getTarjetaHTML(data.id);
    const temp = document.createElement('div');
    temp.innerHTML = html.trim();
    const nuevaTarjeta = temp.firstElementChild;
    if (!nuevaTarjeta) return;

    // Validación extra sobre el atributo data-area de la tarjeta inyectada
    const tarjetaAreaId = nuevaTarjeta.getAttribute('data-area');
    // Permitir inyectar si tarjetaAreaId es null/vacío (es servicio personalizado) O si coincide con AREA_ACTUAL
    if (tarjetaAreaId && tarjetaAreaId !== 'null' && tarjetaAreaId !== '' && typeof AREA_ACTUAL !== 'undefined' && parseInt(tarjetaAreaId) !== parseInt(AREA_ACTUAL)) {
      return;
    }

    nuevaTarjeta.style.animation = 'fadeIn 0.4s ease';
    columna.prepend(nuevaTarjeta);
    if (typeof _ordenarColumnaPorPrioridad === 'function') _ordenarColumnaPorPrioridad(columna);
    _actualizarConteosColumnas();

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
  RFPusher.on('solicitud.actualizada', async function (data) {
    const tarjeta = document.querySelector(`.kb-card[data-id="${data.id}"]`);
    let estadoDestino = data.estado_nuevo;
    if (estadoDestino === 'pendiente_asignado') estadoDestino = 'en_proceso';

    // Si la tarjeta era un servicio personalizado (sin área asignada) y ahora se asignó o canceló
    const eraPersonalizado = tarjeta && (!tarjeta.getAttribute('data-area') || tarjeta.getAttribute('data-area') === 'null' || tarjeta.getAttribute('data-area') === '');

    if (eraPersonalizado) {
      // Disminuir la notificación en todas las pestañas de área excepto en la que ahora es dueña
      document.querySelectorAll('.kb-area-tab').forEach(tab => {
        const tabAreaId = tab.getAttribute('data-area-id');
        if (tabAreaId && parseInt(tabAreaId) !== parseInt(data.idarea_agencia)) {
          _actualizarBadgeArea(tabAreaId, -1);
        }
      });
      // Si la tarjeta estaba en la columna "pendiente_sin_asignar" (Nuevas Solicitudes) del tablero actual y ya no es de esta área,
      // también restamos su notificación local
      if (typeof AREA_ACTUAL !== 'undefined' && parseInt(data.idarea_agencia) !== parseInt(AREA_ACTUAL)) {
        if (tarjeta && tarjeta.closest('.kb-col-body[data-estado="pendiente_sin_asignar"]')) {
          _actualizarBadgeArea(AREA_ACTUAL, -1);
        }
      }
    }

    if (estadoDestino === 'cancelado' || (typeof AREA_ACTUAL !== 'undefined' && data.idarea_agencia && parseInt(data.idarea_agencia) !== parseInt(AREA_ACTUAL))) {
      if (tarjeta) {
        tarjeta.remove();
        _actualizarConteosColumnas();
      }
      return;
    }

    const columna = document.querySelector(`.kb-col-body[data-estado="${estadoDestino}"]`);
    if (!columna) {
      if (tarjeta) {
        tarjeta.remove();
        _actualizarConteosColumnas();
      }
      return;
    }

    const html = await _getTarjetaHTML(data.id);
    const temp = document.createElement('div');
    temp.innerHTML = html.trim();
    const nuevaTarjeta = temp.firstElementChild;
    if (!nuevaTarjeta) {
      if (tarjeta) {
        tarjeta.remove();
        _actualizarConteosColumnas();
      }
      return;
    }

    const tarjetaAreaId = nuevaTarjeta.getAttribute('data-area');
    if (tarjetaAreaId && tarjetaAreaId !== 'null' && tarjetaAreaId !== '' && typeof AREA_ACTUAL !== 'undefined' && parseInt(tarjetaAreaId) !== parseInt(AREA_ACTUAL)) {
      if (tarjeta) {
        tarjeta.remove();
        _actualizarConteosColumnas();
      }
      return;
    }

   nuevaTarjeta.style.animation = 'fadeIn 0.4s ease';
if (tarjeta) tarjeta.remove();
columna.prepend(nuevaTarjeta);

// Si el admin regresó el pedido a proceso, queda en pausa visual
if (data.pausado === true) {
    _inyectarBadgePausa(nuevaTarjeta);
}

if (typeof _ordenarColumnaPorPrioridad === 'function') _ordenarColumnaPorPrioridad(columna);
_actualizarConteosColumnas();
  });

  RFPusher.on('sesion.pausada', function (data) {
    Swal.fire({
      icon: 'warning',
      title: `Tarea Pausada: ${data.titulo}`,
      text: `Motivo: ${data.motivo_pausa}`,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 6000,
      timerProgressBar: true,
      background: '#0a0a0a',
      color: '#fff'
    });
    // Si el modal de la tarjeta está abierto y es de la misma tarea, actualizamos
    const tplId = document.querySelector('#modalDetalle .tpl-idatencion');
    if (tplId && parseInt(tplId.textContent, 10) === parseInt(data.id, 10)) {
      const container = document.querySelector('#modalDetalle .tpl-pausa-container');
      if (container) {
        container.innerHTML = `
          <div class="exp-card" style="border-color:#f97316; background:rgba(249,115,22,0.02); margin-top:25px;">
              <div class="exp-card-header" style="background:rgba(249,115,22,0.05); border-bottom-color:rgba(249,115,22,0.1);">
                  <i class="bi bi-pause-circle-fill" style="color:#f97316;"></i> <span style="color:#f97316;">ÚLTIMO MOTIVO DE PAUSA</span>
              </div>
              <div class="exp-card-body">
                  <div class="data-value" style="font-size:13px; color:#aaa; font-style:italic;">"${data.motivo_pausa}"</div>
              </div>
          </div>
        `;
      }
    }
  });

});

// ═══════════════════════════════════════════════════════════════════
// ═══ HELPER — HISTORIAL DE PAUSAS (Admin Kanban)                 ══
// ═══════════════════════════════════════════════════════════════════

/**
 * Renderiza el historial completo de pausas de un requerimiento.
 * Se muestra solo si hay pausas registradas.
 * Si el requerimiento está finalizado, incluye el resumen total.
 *
 * @param {Array} pausas  - Array de sesiones pausadas con motivo_pausa
 * @param {string} estado - Estado actual del requerimiento
 * @returns {string} HTML
 */
function _renderAdminPausas(pausas, estado) {
  if (!pausas || pausas.length === 0) return '';

  const fmtFecha = (s) => {
    if (!s) return '---';
    s = String(s).trim();
    if (s.length >= 16 && s.includes(' ')) {
      const [fecha, hora] = s.split(' ');
      return fecha.split('-').reverse().join('/') + ' ' + hora.substring(0, 5);
    }
    if (s.length >= 10) return s.substring(0, 10).split('-').reverse().join('/');
    return s;
  };

  const items = pausas.map((p, i) => {
    const motivo = p.motivo_pausa || 'Sin motivo registrado';
    const fechaInicio = fmtFecha(p.hora_inicio);
    const fechaFin = fmtFecha(p.hora_fin);

    let duracionHtml = '';
    if (p.hora_inicio && p.hora_fin) {
      const diffSeg = Math.max(0, Math.floor((new Date(p.hora_fin.replace(' ', 'T')) - new Date(p.hora_inicio.replace(' ', 'T'))) / 1000));
      const hh = Math.floor(diffSeg / 3600);
      const mm = Math.floor((diffSeg % 3600) / 60);
      const durStr = hh > 0 ? `${hh}h ${mm}m` : `${mm}m`;
      duracionHtml = `<span style="color:#888; font-size:11px; font-weight:600; font-variant-numeric:tabular-nums;">${durStr}</span>`;
    }

    return `
      <div style="display:flex; align-items:flex-start; gap:12px; padding:10px 0; ${i < pausas.length - 1 ? 'border-bottom:1px solid #151515;' : ''}">
        <span style="color:#555; font-size:11px; font-weight:700; min-width:18px; font-variant-numeric:tabular-nums;">${i + 1}.</span>
        <div style="flex:1; min-width:0;">
          <div style="font-size:12px; color:#aaa; line-height:1.5; margin-bottom:4px;">${_escHtml(motivo)}</div>
          <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <span style="font-size:10px; color:#444;">${fechaInicio} → ${fechaFin}</span>
            ${duracionHtml}
          </div>
        </div>
      </div>`;
  }).join('');

  return `
    <div style="margin-top:20px; padding:16px 18px; background:#0a0a0a; border:1px solid #1a1a1a; border-radius:10px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #151515;">
        <span style="font-size:11px; font-weight:700; color:#666; letter-spacing:1px; text-transform:uppercase;">Historial de Pausas</span>
        <span style="font-size:11px; font-weight:700; color:#888; font-variant-numeric:tabular-nums;">${pausas.length}</span>
      </div>
      <div style="max-height:200px; overflow-y:auto;">${items}</div>
    </div>`;
}

// ═══════════════════════════════════════════════════════════════════
// ═══ HELPER — TIMELINE DE TRACKING (Admin Kanban)                ══
// ═══════════════════════════════════════════════════════════════════

/**
 * Renderiza la línea de tiempo del tracking de un requerimiento.
 * Incluye el historial de reasignaciones integrado cronológicamente.
 * Cada evento muestra icono, badge de acción, texto y fecha.
 *
 * @param {Array} tracking        - Array de eventos del tracking
 * @param {Array} historialAsig   - Array de reasignaciones
 * @returns {string} HTML
 */
function _renderAdminTimeline(tracking, historialAsig) {
  if (!tracking || tracking.length === 0) return '';

  const fmtFecha = (s) => {
    if (!s) return '---';
    s = String(s).trim();
    if (s.length >= 16 && s.includes(' ')) {
      const [fecha, hora] = s.split(' ');
      return fecha.split('-').reverse().join('/') + ' ' + hora.substring(0, 5);
    }
    if (s.length >= 10) return s.substring(0, 10).split('-').reverse().join('/');
    return s;
  };

  const estadoLabel = {
    pendiente_sin_asignar: 'Solicitud',
    pendiente_asignado: 'Asignado',
    en_proceso: 'En Proceso',
    en_revision: 'Revisión',
    finalizado: 'Finalizado',
    cancelado: 'Cancelado',
  };

  const items = tracking.map((t, i) => {
    const accion = t.accion || '';
    const estado = t.estado || 'pendiente_sin_asignar';
    const fecha = fmtFecha(t.fecha_registro);
    const label = estadoLabel[estado] || estado;
    const isLast = i === tracking.length - 1;

    return `
      <div style="display:flex; gap:12px; position:relative;">
        <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0;">
          <div style="width:8px; height:8px; border-radius:50%; background:${i === 0 ? '#F5C400' : '#333'}; flex-shrink:0; margin-top:5px;"></div>
          ${!isLast ? `<div style="width:1px; flex:1; min-height:20px; background:#1a1a1a; margin-top:4px;"></div>` : ''}
        </div>
        <div style="flex:1; min-width:0; padding-bottom:${isLast ? '0' : '14px'};">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px;">
            <span style="font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.5px;">${label}</span>
            <span style="font-size:10px; color:#333;">·</span>
            <span style="font-size:10px; color:#444; font-variant-numeric:tabular-nums;">${fecha}</span>
          </div>
          <div style="font-size:12px; color:#666; line-height:1.5;">${_escHtml(accion)}</div>
        </div>
      </div>`;
  }).join('');

  // Historial de reasignaciones
  let reasigHtml = '';
  if (historialAsig && historialAsig.length > 0) {
    const reasigItems = historialAsig.map((h, i) => {
      const desde = h.nombre_anterior ? `${_escHtml(h.nombre_anterior)} ${_escHtml(h.apellidos_anterior || '')}` : 'Sin asignar';
      const hacia = `${_escHtml(h.nombre_nuevo || '')} ${_escHtml(h.apellidos_nuevo || '')}`.trim() || '---';
      const quien = `${_escHtml(h.nombre_responsable || '')} ${_escHtml(h.apellidos_responsable || '')}`.trim() || '---';
      const fecha = fmtFecha(h.fecha_asignacion);
      const motivo = h.motivo_cambio || 'Sin motivo registrado';

      return `
        <div style="padding:10px 0; ${i < historialAsig.length - 1 ? 'border-bottom:1px solid #151515;' : ''}">
          <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px; flex-wrap:wrap;">
            <span style="color:#666; font-size:12px;">${desde}</span>
            <span style="color:#444; font-size:10px;">→</span>
            <span style="color:#ccc; font-size:12px; font-weight:600;">${hacia}</span>
          </div>
          <div style="font-size:11px; color:#555; margin-bottom:3px;">${_escHtml(motivo)}</div>
          <div style="font-size:10px; color:#333;">Por: ${quien} · ${fecha}</div>
        </div>`;
    }).join('');

    reasigHtml = `
      <div style="margin-top:14px; padding-top:14px; border-top:1px solid #151515;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
          <span style="font-size:10px; font-weight:700; color:#555; letter-spacing:1px; text-transform:uppercase;">Reasignaciones</span>
          <span style="font-size:10px; font-weight:700; color:#888; font-variant-numeric:tabular-nums;">${historialAsig.length}</span>
        </div>
        ${reasigItems}
      </div>`;
  }

  return `
    <div style="margin-top:20px; padding:16px 18px; background:#0a0a0a; border:1px solid #1a1a1a; border-radius:10px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #151515;">
        <span style="font-size:11px; font-weight:700; color:#666; letter-spacing:1px; text-transform:uppercase;">Línea de Tiempo</span>
        <span style="font-size:10px; font-weight:600; color:#444; font-variant-numeric:tabular-nums;">${tracking.length} eventos</span>
      </div>
      <div style="max-height:280px; overflow-y:auto;">
        ${items}
        ${reasigHtml}
      </div>
    </div>`;
}

/**
 * Sanitiza texto para prevenir XSS en el template del admin.
 * @param {string} texto
 * @returns {string}
 */
function _escHtml(texto) {
  if (!texto) return '';
  const d = document.createElement('div');
  d.textContent = String(texto);
  return d.innerHTML;
}
// ── BADGE VISUAL DE PAUSA EN LA TARJETA KANBAN ──
// ── BADGE VISUAL DE PAUSA EN LA TARJETA KANBAN ──
function _inyectarBadgePausa(tarjeta) {
    if (!tarjeta) return;

    // Buscar el badge de "TRABAJANDO" y reemplazarlo por "PAUSADO" con el mismo estilo del PHP
    const badgeTrabajando = tarjeta.querySelector('.kb-badge-developing');
    if (badgeTrabajando) {
        badgeTrabajando.style.background = 'rgba(249, 115, 22, 0.1)';
        badgeTrabajando.style.color = '#f97316';
        badgeTrabajando.style.border = '1px solid rgba(249, 115, 22, 0.2)';
        badgeTrabajando.innerHTML = '<i class="bi bi-pause-fill"></i> PAUSADO';
    }
}