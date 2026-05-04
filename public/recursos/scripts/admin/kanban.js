// ══════════════════════════════════════════════════════
// ══ FORZAR RECORTE DE TÍTULOS LARGOS EN LAS TARJETAS ══
// ══════════════════════════════════════════════════════
document.head.insertAdjacentHTML(
  "beforeend",
  `
<style>
    .kb-card {
        max-width: 100% !important;
        overflow: hidden !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
    }
    .kb-card h3, 
    .kb-card h4, 
    .kb-card h5, 
    .kb-card-title, 
    .kb-card p {
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        hyphens: auto;
    }
</style>
`,
);

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
  if (data.status === "success") location.reload();
  else alert(data.msg);
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
  if (data.status === "success") location.reload();
  else alert(data.msg);
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

    // ── Fechas y Tiempos ─────
    const fReq = d.fecharequerida || "---";
    const fSol = d.r_fechacreacion || "---";
    const fIni = d.fechainicio || "---";
    const fFin = d.fechacompletado || "---";
    const fmtRaw = (s) =>
      s && s.length > 10
        ? s.substring(0, 10).split("-").reverse().join("/")
        : s;
    const fSolFmt = fmtRaw(fSol);

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

    const stepperHtml = `
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

    const statusPill = `
            <div class="status-pill-premium" style="--pill-color: ${statusConfig.color}">
                <i class="bi ${statusConfig.icon}"></i>
                <span>${statusConfig.label}</span>
            </div>
        `;

    // ── Renderizado de Archivos ──────────────────────────────
    const renderArchivos = (lista, color = "#F5C400") => {
      if (!lista.length)
        return '<div class="no-files">No se encontraron archivos adjuntos.</div>';
      return (
        `<div class="files-grid">` +
        lista
          .map((a) => {
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
          })
          .join("") +
        "</div>"
      );
    };

    const arcClienteHtml = renderArchivos(archivosCliente);
    const arcEmpleadoHtml = renderArchivos(archivosEmpleado, "#10b981");

    // ── Tags ───────────────────────────────────────────────
    const renderTags = (json) => {
      const list = _parseList(json);
      if (!list.length) return '<span class="tag-empty">SIN ESPECIFICAR</span>';
      return (
        `<div class="tags-container">` +
        list
          .map(
            (t) => `<span class="tag-item">${String(t).toUpperCase()}</span>`,
          )
          .join("") +
        `</div>`
      );
    };

    // ── Bloque del Empleado ──────────────────────────────────
    let empleadoHtml;
    if (d.empleado_nombre) {
      const ini = (
        d.empleado_nombre[0] + (d.empleado_apellidos?.[0] ?? "")
      ).toUpperCase();
      empleadoHtml = `
                <div class="user-card-premium">
                    <div class="user-avatar-premium">${ini}</div>
                    <div class="user-details">
                        <span class="user-name">${d.empleado_nombre} ${d.empleado_apellidos}</span>
                        <span class="user-role">RESPONSABLE ASIGNADO</span>
                    </div>
                </div>`;
    } else {
      empleadoHtml = `
                <div class="user-card-premium unassigned">
                    <div class="user-avatar-premium"><i class="bi bi-person-dash"></i></div>
                    <div class="user-details">
                        <span class="user-name">PENDIENTE</span>
                        <span class="user-role">ESPERANDO RESPONSABLE</span>
                    </div>
                </div>`;
    }

    // ── Título del Modal ────────────────────────────────────
    document.getElementById("detalle-titulo").innerText = (
      d.nombreempresa || "DETALLE DE PEDIDO"
    ).toUpperCase();

    cuerpo.innerHTML = `
            <style>
                .exp-container { background: #080808; color: #fff; font-family: 'DM Sans', sans-serif; padding-bottom: 40px; }
                
                /* Stepper */
                .workflow-stepper { display: flex; align-items: center; justify-content: space-between; padding: 30px 60px; background: #0a0a0a; border-bottom: 1px solid #151515; margin-bottom: 30px; }
                .step-item { display: flex; flex-direction: column; align-items: center; gap: 10px; position: relative; z-index: 2; }
                .step-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.3s; }
                .step-label { font-size: 10px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
                .step-line { flex-grow: 1; height: 2px; background: #151515; margin: 0 -20px; position: relative; top: -12px; z-index: 1; }
                .step-line.active { background: #F5C400; }
                .step-item.current .step-icon { box-shadow: 0 0 20px rgba(245, 196, 0, 0.3); transform: scale(1.1); }
                
                /* Layout */
                .exp-grid { display: grid; grid-template-columns: 1fr 340px; gap: 30px; padding: 0 30px; }
                
                /* Cards */
                .exp-card { background: #0a0a0a; border: 1px solid #151515; border-radius: 16px; overflow: hidden; margin-bottom: 25px; transition: border-color 0.3s; }
                .exp-card:hover { border-color: #222; }
                .exp-card-header { padding: 16px 20px; background: #0c0c0c; border-bottom: 1px solid #151515; display: flex; align-items: center; gap: 12px; }
                .exp-card-header i { color: #F5C400; font-size: 18px; }
                .exp-card-header span { font-family: 'Bebas Neue'; font-size: 20px; letter-spacing: 1px; color: #fff; }
                .exp-card-body { padding: 25px; }
                
                /* Data Boxes */
                .data-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
                .data-box { background: #050505; border: 1px solid #111; padding: 15px; border-radius: 12px; }
                .data-label { color: #fff; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; display: block; opacity: 0.8; }
                .data-value { color: #fff; font-size: 14px; font-weight: 600; line-height: 1.5; }
                .data-value.highlight { color: #F5C400; }
                .data-label-large { color: #fff; font-family: 'Bebas Neue', sans-serif; font-size: 20px; letter-spacing: 1px; margin-bottom: 8px; display: block; }
                
                /* Tags */
                .tags-container { display: flex; flex-wrap: wrap; gap: 8px; }
                .tag-item { background: #111; color: #fff; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid #222; }
                .tag-empty { color: #444; font-size: 11px; font-style: italic; }
                
                /* Files */
                .files-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
                .file-card { background: #050505; border: 1px solid #111; padding: 12px; border-radius: 12px; display: flex; align-items: center; gap: 12px; text-decoration: none; transition: all 0.2s; }
                .file-card:hover { border-color: #F5C400; background: #0a0a0a; transform: translateY(-2px); }
                .file-icon { font-size: 20px; }
                .file-info { flex-grow: 1; min-width: 0; }
                .file-name { color: #fff; font-size: 12px; font-weight: 600; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .file-ext { color: #444; font-size: 9px; font-weight: 800; }
                .download-icon { color: #222; font-size: 14px; }
                .file-card:hover .download-icon { color: #F5C400; }
                .no-files { color: #444; font-size: 13px; text-align: center; padding: 20px; border: 1px dashed #111; border-radius: 12px; }
                
                /* User Card */
                .user-card-premium { display: flex; align-items: center; gap: 15px; background: linear-gradient(135deg, #0a0a0a, #050505); padding: 15px; border-radius: 12px; border: 1px solid #151515; }
                .user-avatar-premium { width: 48px; height: 48px; background: #F5C400; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 900; color: #000; border: 3px solid #151515; }
                .user-details { display: flex; flex-direction: column; }
                .user-name { color: #fff; font-size: 14px; font-weight: 800; }
                .user-role { color: #F5C400; font-size: 9px; font-weight: 800; letter-spacing: 1px; }
                .user-card-premium.unassigned .user-avatar-premium { background: #111; color: #444; border-style: dashed; }
                
                /* Status Pill */
                .status-pill-premium { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; border-radius: 20px; background: rgba(var(--pill-color-rgb), 0.1); border: 1px solid var(--pill-color); color: var(--pill-color); font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
                
                /* Responsive */
                @media (max-width: 992px) {
                    .exp-grid { grid-template-columns: 1fr; gap: 20px; padding: 0 15px; }
                    .workflow-stepper { padding: 20px; overflow-x: auto; justify-content: flex-start; gap: 30px; }
                    .step-line { display: none; }
                    .exp-card-header span { font-size: 18px; }
                }
                @media (max-width: 600px) {
                    .data-row { grid-template-columns: 1fr; gap: 10px; }
                    .exp-container h2 { font-size: 32px !important; }
                    .workflow-stepper { padding: 15px; }
                    .exp-card-body { padding: 15px; }
                }
            </style>

            <div class="exp-container">
                <!-- 1. HEADER SECCIÓN -->
                <div style="padding: 40px 30px 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                            ${statusPill}
                            <span style="color:#444; font-size:11px; font-weight:800;">ID: #REQ-${d.idrequerimiento || "---"}</span>
                        </div>
                        <h2 style="font-family:'Bebas Neue'; font-size:48px; color:#fff; letter-spacing:1px; margin:0; line-height:1.1; word-wrap:break-word; overflow-wrap:break-word;">${d.titulo || "SIN TÍTULO"}</h2>
                        <div style="margin-top:15px; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                            <span style="color:#F5C400; font-weight:800; font-size:14px;"><i class="bi bi-building"></i> ${d.nombreempresa}</span>
                            <span style="color:#222;">|</span>
                            <span style="color:#888; font-size:13px; font-weight:600;">ÁREA: ${d.area_solicitante_nombre || "---"}</span>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <div style="font-family:'Bebas Neue'; font-size:24px; color:#F5C400;">${d.servicio || "SERVICIO GENERAL"}</div>
                        <div style="color:#444; font-size:10px; font-weight:800; letter-spacing:1px; margin-top:5px;">ATENCIÓN #${d.id.toString().padStart(5, "0")}</div>
                    </div>
                </div>

                <!-- 2. STEPPER -->
                ${stepperHtml}

                <div class="exp-grid">
                    <!-- COLUMNA PRINCIPAL -->
                    <div class="exp-main-col">
                        
                        <!-- Descripción (siempre visible) -->
                        <div class="exp-card">
                            <div class="exp-card-header"><i class="bi bi-file-text"></i> <span>DESCRIPCIÓN DEL REQUERIMIENTO</span></div>
                            <div class="exp-card-body">
                                <div class="data-value" style="white-space:pre-wrap; font-size:13px; color:#ccc;">${d.descripcion || "Sin descripción adicional."}</div>
                            </div>
                        </div>

                        ${((d.servicio && d.servicio.toLowerCase().includes('contenido')) || (d.area_nombre && d.area_nombre.toLowerCase().includes('contenido'))) ? `
                        <!-- Estrategia (Solo Creación de Contenido) -->
                        <div class="exp-card">
                            <div class="exp-card-header"><i class="bi bi-compass"></i> <span>ESTRATEGIA DE CONTENIDO</span></div>
                            <div class="exp-card-body">
                                <div class="data-row">
                                    <div class="data-box">
                                        <span class="data-label-large">Objetivo Principal</span>
                                        <div class="data-value">${d.objetivo_comunicacion && d.objetivo_comunicacion.trim() !== '' ? d.objetivo_comunicacion : '---'}</div>
                                    </div>
                                    <div class="data-box">
                                        <span class="data-label-large">Público Objetivo</span>
                                        <div class="data-value">${d.publico_objetivo && d.publico_objetivo.trim() !== '' ? d.publico_objetivo : '---'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Canales y Formatos (Solo Creación de Contenido) -->
                        <div class="data-row">
                            <div class="exp-card" style="margin-bottom:0;">
                                <div class="exp-card-header"><i class="bi bi-broadcast"></i> <span>CANALES</span></div>
                                <div class="exp-card-body">${renderTags(d.canales_difusion)}</div>
                            </div>
                            <div class="exp-card" style="margin-bottom:0;">
                                <div class="exp-card-header"><i class="bi bi-layers"></i> <span>FORMATOS</span></div>
                                <div class="exp-card-body">${renderTags(d.formatos_solicitados)}</div>
                            </div>
                        </div>
                        ` : ''}

                        <!-- Recursos -->
                        <div class="exp-card" style="margin-top:25px;">
                            <div class="exp-card-header"><i class="bi bi-folder-symlink"></i> <span>RECURSOS DEL CLIENTE</span></div>
                            <div class="exp-card-body">
                                ${arcClienteHtml}
                                ${d.url_subida
        ? `
                                <a href="${d.url_subida}" target="_blank" style="margin-top:20px; display:flex; align-items:center; justify-content:center; gap:10px; background:#F5C400; color:#000; padding:15px; border-radius:12px; font-weight:900; text-decoration:none; font-size:12px; letter-spacing:1px;">
                                    <i class="bi bi-cloud-arrow-down-fill" style="font-size:20px;"></i> URL DEL CLIENTE
                                </a>`
        : ""
      }
                            </div>
                        </div>

                        <!-- Entrega si existe -->
                        ${d.estado === "en_revision" ||
        d.estado === "finalizado"
        ? `
                        <div class="exp-card" style="border-color:#10b981; background:rgba(16,185,129,0.02);">
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
                                ${arcEmpleadoHtml}
                            </div>
                        </div>`
        : ""
      }

                    </div>

                    <!-- SIDEBAR -->
                    <div class="exp-sidebar">
                        
                        <!-- Responsable -->
                        <div class="exp-card">
                            <div class="exp-card-header"><i class="bi bi-person-badge"></i> <span>RESPONSABLE</span></div>
                            <div class="exp-card-body" style="padding:15px;">
                                ${empleadoHtml}
                            </div>
                        </div>

                        <!-- Cronología -->
                        <div class="exp-card">
                            <div class="exp-card-header"><i class="bi bi-calendar3"></i> <span>CRONOLOGÍA</span></div>
                            <div class="exp-card-body">
                                <div style="display:flex; flex-direction:column; gap:15px;">
                                    <div class="data-box">
                                        <span class="data-label-large">Fecha de Solicitud</span>
                                        <div class="data-value">${fSolFmt}</div>
                                    </div>
                                    <div class="data-box" style="border-color:#F5C40033; background:rgba(245,196,0,0.02);">
                                        <span class="data-label" style="color:#F5C400;">Fecha Límite</span>
                                        <div class="data-value" style="font-weight:900;">${fReq}</div>
                                    </div>
                                    ${d.fechainicio !== "---" &&
        d.fechainicio !== "—"
        ? `
                                    <div class="data-box">
                                        <span class="data-label">Inicio de Trabajo</span>
                                        <div class="data-value">${fIni}</div>
                                    </div>`
        : ""
      }
                                    ${d.fechacompletado !== "---" &&
        d.fechacompletado !== "—"
        ? `
                                    <div class="data-box" style="border-color:#10b98133;">
                                        <span class="data-label" style="color:#10b981;">Completado</span>
                                        <div class="data-value">${fFin}</div>
                                    </div>`
        : ""
      }
                                </div>
                            </div>
                        </div>

                        <!-- Auditoría -->
                        <div class="exp-card">
                            <div class="exp-card-header"><i class="bi bi-shield-check"></i> <span>CONTROL</span></div>
                            <div class="exp-card-body">
                                <div style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:15px; border-radius:12px; border:1px solid #111;">
                                    <span class="data-label" style="margin:0;">MODIFICACIONES</span>
                                    <span style="background:#F5C400; color:#000; padding:4px 12px; border-radius:8px; font-weight:900; font-size:14px;">${d.num_modificaciones || 0}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- FOOTER ACCIONES -->
                <div style="margin-top:30px; padding:30px; border-top:1px solid #151515; display:flex; justify-content:center;">
                    <button class="btn" data-dismiss="modal" style="background:#111; border:1px solid #222; font-family:'Bebas Neue'; font-size:20px; letter-spacing:2px; padding:12px 60px; border-radius:12px; color:#F5C400; transition:all 0.3s;">
                        CERRAR EXPEDIENTE DIGITAL
                    </button>
                </div>
            </div>
        `;
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
async function cambiarPrioridad(id) {
  const p = document.getElementById("detalle-prioridad").value;
  const data = await _post("admin/kanban/cambiarPrioridad", {
    idatencion: id,
    prioridad: p,
  });
  if (data.status === "success") location.reload();
  else alert(data.msg);
}

async function cambiarEstado(id, est, acc) {
  if (!confirm("Confirmar acción: " + acc)) return;
  const data = await _post("admin/kanban/cambiarEstado", {
    idatencion: id,
    estado: est,
    accion: acc,
  });
  if (data.status === "success") location.reload();
  else alert(data.msg);
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
    alert("Por favor, escribe un mensaje de mejora.");
    return;
  }

  const data = await _post("admin/kanban/regresarAProceso", {
    idatencion: id,
    mensaje: msg,
  });

  if (data.status === "success") {
    location.reload();
  } else {
    alert(data.msg);
  }
}

async function cancelarAtencion(id) {
  const m = prompt("Motivo de cancelación:");
  if (!m) return;
  const data = await _post("admin/kanban/cancelar", {
    idatencion: id,
    motivo: m,
  });
  if (data.status === "success") location.reload();
  else alert(data.msg);
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
    // Si no es un JSON o arreglo mágico, pero tiene comas, lo separa e ignora espacios vacíos
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
    // Estilos para asegurar que toda la columna sea área de soltado
    const style = document.createElement("style");
    style.innerHTML = `
            .kb-col { display: flex !important; flex-direction: column !important; }
            .kb-col-body { flex-grow: 1 !important; min-height: 500px !important; }
        `;
    document.head.appendChild(style);

    // SOLO SE PUEDE SACAR DE AQUÍ
    new Sortable(colAprobar, {
      group: { name: "kanban", pull: true, put: false },
      draggable: ".js-draggable",
      animation: 150,
    });

    // SOLO SE PUEDE SOLTAR AQUÍ (NO REGRESAR)
    new Sortable(colProceso, {
      group: { name: "kanban", pull: false, put: true },
      draggable: ".js-draggable",
      sort: false, // Desactivar el reordenamiento interno
      animation: 150,
      onAdd(evt) {
        const idAtencion = evt.item.getAttribute("data-id");
        _post("admin/kanban/cambiarEstado", {
          idatencion: idAtencion,
          estado: "pendiente_asignado",
          accion: "Aprobado vía arrastre",
          idareaagencia: AREA_ACTUAL,
        })
          .then(() => location.reload())
          .catch(() => location.reload());
      },
    });
  }
});
