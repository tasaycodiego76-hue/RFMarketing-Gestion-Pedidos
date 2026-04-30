// Variables globales
let empleadosBandejaData = [];
let requerimientosData = [];
let revisionData = [];
let empleadoSeleccionado = null;
let requerimientoSeleccionado = null;

// DOM Ready
document.addEventListener("DOMContentLoaded", function () {
  cargarBandeja();
  cargarEmpleados();

  // Buscador
  const buscador = document.getElementById("buscador-bandeja");
  if (buscador) {
    buscador.addEventListener("input", debounce(filtrarBandeja, 300));
  }


  // Botón confirmar asignación
  const btnConfirmar = document.getElementById("btn-confirmar-asignacion");
  if (btnConfirmar) {
    btnConfirmar.addEventListener("click", confirmarAsignacion);
  }

  // Limpiar selección al cerrar modal
  const modalAsignar = document.getElementById("modal-asignar");
  if (modalAsignar) {
    modalAsignar.addEventListener("hidden.bs.modal", function () {
      empleadoSeleccionado = null;
      requerimientoSeleccionado = null;
      document.getElementById("btn-confirmar-asignacion").disabled = true;
    });
  }
});

function cargarBandeja() {
  const tbody = document.getElementById("contenido-bandeja");
  const tbodyRev = document.getElementById("contenido-revision");

  if (tbody) tbody.innerHTML = generarSkeletonFilas(3);
  if (tbodyRev) tbodyRev.innerHTML = generarSkeletonFilas(2);

  fetch(`${base_url}responsable/pedidos/bandeja-json`)
    .then((response) => response.json())
    .then((data) => {
      console.log("Datos recibidos de la bandeja:", data);
      if (data.success) {
        // 1. Requerimientos por asignar
        requerimientosData = data.data || [];
        actualizarContador(requerimientosData.length);
        if (requerimientosData.length === 0) {
          mostrarEstadoVacio();
        } else {
          renderizarBandeja(requerimientosData);
        }

        // 2. Tareas en revisión
        if (data.data_revision) {
          revisionData = data.data_revision;
          renderizarRevision(revisionData);
        }
      } else {
        mostrarError(data.message || "Error al cargar la bandeja");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarError("Error de conexión al cargar la bandeja");
    });
}

function formatearFechaLimpia(fechaStr) {
  if (!fechaStr) return "---";
  try {
    const fecha = new Date(fechaStr);
    if (isNaN(fecha)) return fechaStr;
    const dia = fecha.getDate().toString().padStart(2, '0');
    const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
    const anio = fecha.getFullYear();
    return `${dia}/${mes}/${anio}`;
  } catch (e) {
    return fechaStr;
  }
}

/**
 * Renderizar tabla de revisión
 */
function renderizarRevision(data) {
  const tbody = document.getElementById("contenido-revision");
  const estadoVacio = document.getElementById("estado-vacio-revision");

  if (!tbody) return;

  if (data.length === 0) {
    tbody.innerHTML = "";
    estadoVacio?.classList.remove("d-none");
    return;
  }

  estadoVacio?.classList.add("d-none");
  tbody.innerHTML = data.map(item => `
        <tr>
            <td>
                <div style="font-weight:600;">${escaparHtml(item.titulo || "Sin título")}</div>
                <div style="font-size:10px; color:#666;">#REQ-${item.id_requerimiento}</div>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:24px; height:24px; border-radius:50%; background:#333; color:#fff; font-size:10px; display:flex; align-items:center; justify-content:center;">
                        ${obtenerIniciales(item.empleado_nombre)}
                    </div>
                    <span style="font-size:12px;">${escaparHtml(item.empleado_nombre || "---")}</span>
                </div>
            </td>
            <td>${escaparHtml(item.empresa_nombre || "N/A")}</td>
            <td>${escaparHtml(item.nombre_area || "General")}</td>
            <td>${escaparHtml(item.cliente_nombre || "Usuario")}</td>
            <td>${escaparHtml(item.servicio_nombre || "N/A")}</td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn-ver-detalle" onclick="verDetalleRequerimiento(${item.id})" style="background:rgba(34,197,94,0.1); color:#22c55e; border-color:rgba(34,197,94,0.2);">
                        <i class="bi bi-eye"></i> Ver Trabajo
                    </button>
                </div>
            </td>
        </tr>
    `).join("");
}

/**
 * Cargar empleados del área
 */
function cargarEmpleados() {
  fetch(`${base_url}responsable/empleados/mi-area-json`)
    .then((response) => response.json())
    .then((data) => {
      console.log("Empleados recibidos:", data);
      if (data.success) {
        empleadosBandejaData = data.data || [];
      }
    })
    .catch((error) => {
      console.error("Error al cargar empleados:", error);
    });
}

/**
 * Renderizar tabla de bandeja
 */
function renderizarBandeja(data) {
  const tbody = document.getElementById("contenido-bandeja");
  const estadoVacio = document.getElementById("estado-vacio");
  estadoVacio?.classList.add("d-none");
  tbody.innerHTML = data
    .map(
      (item) => `
        <tr>
            <td>
                <div style="font-weight:600;">${escaparHtml(item.titulo || "Sin título")}</div>
                ${item.observacion_revision ? '<span class="badge bg-danger" style="font-size:9px; letter-spacing:0.5px;">DEVUELTO</span>' : ''}
            </td>
            <td>${escaparHtml(item.servicio || "N/A")}</td>
            <td>${escaparHtml(item.nombreempresa || "N/A")}</td>
            <td>${escaparHtml(item.nombre_area || "General")}</td>
            <td>${escaparHtml(item.cliente_nombre || "Usuario")}</td>
            <td>
                <span class="prioridad-${(item.prioridad || "media").toLowerCase()}">
                    ${item.prioridad || "Media"}
                </span>
            </td>
            <td>${formatearFechaLimpia(item.fechacreacion)}</td>
            <td>
                <span class="estado-por-asignar">
                    Por Asignar
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn-ver-detalle" onclick="verDetalleRequerimiento(${item.idatencion})" title="Ver detalle del requerimiento">
                        <i class="bi bi-eye"></i> Ver Detalle
                    </button>
                    <button class="btn-asignar" onclick="abrirModalAsignar(${item.idatencion})" title="Asignar a miembro del equipo">
                        <i class="bi bi-person-plus"></i> Asignar
                    </button>
                </div>
            </td>
        </tr>
    `,
    ).join("");
}

/**
 * Abrir modal de asignación
 */
function abrirModalAsignar(idAtencion) {
  // Buscar por idatencion (convertir a número para comparación segura)
  const requerimiento = requerimientosData.find(
    (r) => parseInt(r.idatencion) === parseInt(idAtencion),
  );

  if (!requerimiento) {
    console.error("Requerimiento no encontrado. ID buscado:", idAtencion);
    console.log("Datos disponibles:", requerimientosData);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se encontró el requerimiento seleccionado",
      background: "#161616",
      color: "#fff",
      confirmButtonColor: "#f5c400",
      allowOutsideClick: false,
      allowEscapeKey: false
    });
    return;
  }

  console.log("Requerimiento seleccionado:", requerimiento);
  requerimientoSeleccionado = requerimiento;

  // Llenar info del modal
  document.getElementById("idatencion-seleccionado").value = idAtencion;
  document.getElementById("modal-titulo-requerimiento").textContent = escaparHtml(requerimiento.titulo || "Sin título");
  document.getElementById("info-servicio").textContent = escaparHtml(requerimiento.servicio || "N/A");
  document.getElementById("info-empresa").textContent = escaparHtml(requerimiento.nombreempresa || "N/A");

  const prioridadSpan = document.getElementById("info-prioridad");
  const prioridad = (requerimiento.prioridad || "media").toLowerCase();
  prioridadSpan.innerHTML = `<span class="prioridad-${prioridad}">${requerimiento.prioridad || "Media"}</span>`;

  // Resetear selección previa
  empleadoSeleccionado = null;

  // Renderizar lista de empleados
  renderizarListaEmpleados();

  // Abrir modal
  const modal = new bootstrap.Modal(document.getElementById("modal-asignar"));
  modal.show();
}

/**
 * Renderizar lista de empleados en el modal
 */
function renderizarListaEmpleados() {
  const contenedor = document.getElementById("lista-empleados");

  if (empleadosBandejaData.length === 0) {
    contenedor.innerHTML = `
            <div class="text-center py-4" style="color:#a1a1aa;">
                <i class="bi bi-exclamation-circle mb-2" style="font-size:24px;display:block;"></i>
                No hay miembros disponibles en tu área
            </div>
        `;
    return;
  }

  contenedor.innerHTML = empleadosBandejaData
    .map(
      (emp) => `
        <div class="empleado-item ${empleadoSeleccionado === emp.id ? "seleccionado" : ""} ${emp.en_proceso > 0 ? 'emp-ocupado' : ''}"
             onclick="seleccionarEmpleado(${emp.id})"
             data-id="${emp.id}">
            <div class="empleado-avatar ${emp.esresponsable ? "responsable" : ""}">
                ${emp.esresponsable ? '<i class="bi bi-shield-check"></i>' : obtenerIniciales(emp.nombre_completo)}
            </div>
            <div class="empleado-info">
                <div class="empleado-nombre" style="font-size:15px;">${escaparHtml(emp.nombre_completo)}</div>
                <div class="empleado-rol" style="font-size:12px;margin-top:2px;">
                    ${emp.esresponsable ? '<span class="badge-jefe">Jefe de Área</span>' : '<span class="badge-miembro">Miembro del Equipo</span>'}
                </div>
                <div class="empleado-workload" style="margin-top:4px;">
                    ${emp.en_proceso > 0
          ? `<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(245,196,0,0.15);color:#F5C400;border:1px solid rgba(245,196,0,0.3);padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;">${emp.en_proceso} tarea${emp.en_proceso > 1 ? 's' : ''} activa${emp.en_proceso > 1 ? 's' : ''}</span>`
          : `<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(34,197,94,0.1);color:#22c55e;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;">Disponible</span>`
        }
                </div>
            </div>
            <div class="empleado-check">
                ${empleadoSeleccionado === emp.id ? '<i class="bi bi-check-lg"></i>' : ""}
            </div>
        </div>
    `,
    ).join("");
}

/**
 * Seleccionar empleado
 */
function seleccionarEmpleado(idEmpleado) {
  empleadoSeleccionado = idEmpleado;

  // Actualizar visual
  document.querySelectorAll(".empleado-item").forEach((el) => {
    const esSeleccionado = parseInt(el.dataset.id) === idEmpleado;
    el.classList.toggle("seleccionado", esSeleccionado);
    el.querySelector(".empleado-check").innerHTML = esSeleccionado
      ? '<i class="bi bi-check-lg"></i>'
      : "";
  });

  // Habilitar botón
  document.getElementById("btn-confirmar-asignacion").disabled = false;
}
/**
 * Confirmar asignación
 */
function confirmarAsignacion() {
  if (!empleadoSeleccionado || !requerimientoSeleccionado) {
    console.error("Faltan datos para asignar");
    return;
  }
  const btn = document.getElementById("btn-confirmar-asignacion");
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Asignando...';
  const formData = new FormData();
  formData.append("idatencion", requerimientoSeleccionado.idatencion);
  formData.append("idusuario_asignado", empleadoSeleccionado);
  const csrfTokenName = "csrf_test_name";
  const csrfHash = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
  if (csrfHash) {
    formData.append(csrfTokenName, csrfHash);
  }
  fetch(`${base_url}responsable/pedidos/asignar`, {
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest"
    }
  })
    .then(response => response.json())
    .then(data => {
      console.log("Respuesta de asignación:", data);
      if (data.success) {
        // Cerrar modal
        bootstrap.Modal.getInstance(document.getElementById("modal-asignar")).hide();
        Swal.fire({
          icon: "success",
          title: "¡Asignado!",
          text: data.message,
          background: "#161616",
          color: "#fff",
          confirmButtonColor: "#f5c400",
          timer: 2000,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false
        });
        // Recargar bandeja
        setTimeout(() => cargarBandeja(), 500);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message || "No se pudo asignar el requerimiento",
          background: "#161616",
          color: "#fff",
          confirmButtonColor: "#f5c400",
          allowOutsideClick: false,
          allowEscapeKey: false
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
      }
    })
    .catch(error => {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error de conexión al asignar",
        background: "#161616",
        color: "#fff",
        confirmButtonColor: "#f5c400",
        allowOutsideClick: false,
        allowEscapeKey: false
      });
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
    });
}
/*
 * Filtrar bandeja
 */
function filtrarBandeja() {
  const busqueda = document.getElementById("buscador-bandeja").value.toLowerCase().trim();
  if (!busqueda) {
    renderizarBandeja(requerimientosData);
    return;
  }
  const filtrados = requerimientosData.filter(item =>
    (item.titulo || "").toLowerCase().includes(busqueda) ||
    (item.servicio || "").toLowerCase().includes(busqueda) ||
    (item.nombreempresa || "").toLowerCase().includes(busqueda) ||
    (item.cliente_nombre || "").toLowerCase().includes(busqueda) ||
    String(item.idatencion).includes(busqueda)
  );
  renderizarBandeja(filtrados);
}

/**
 * Mostrar estado vacío
 */
function mostrarEstadoVacio() {
  document.getElementById("contenido-bandeja").innerHTML = "";
  document.getElementById("estado-vacio").classList.remove("d-none");
  actualizarContador(0);
}

/**
 * Actualizar contador
 */
function actualizarContador(cantidad) {
  const contador = document.getElementById("contador-pendientes");
  if (contador) {
    contador.innerHTML = `<i class="bi bi-inbox"></i> ${cantidad} pendiente${cantidad !== 1 ? "s" : ""}`;
  }
}

/**
 * Mostrar error
 */
function mostrarError(mensaje) {
  const tbody = document.getElementById("contenido-bandeja");
  tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4" style="color:#ef4444;">
                <i class="bi bi-exclamation-triangle-fill mb-2" style="font-size:24px;display:block;"></i>
                ${escaparHtml(mensaje)}
            </td>
        </tr>
    `;
}



/**
 * Generar skeleton loading
 */
function generarSkeletonFilas(cantidad) {
  return Array(cantidad).fill(0).map(() => `
        <tr>
            <td><div class="skeleton" style="width:150px;height:16px;margin-bottom:4px;"></div><div class="skeleton" style="width:80px;height:12px;"></div></td>
            <td><div class="skeleton" style="width:100px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:120px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:60px;height:20px;border-radius:4px;"></div></td>
            <td><div class="skeleton" style="width:80px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:70px;height:20px;border-radius:4px;"></div></td>
            <td><div class="skeleton" style="width:160px;height:32px;border-radius:6px;"></div></td>
        </tr>
    `).join("");
}

/**
 * Utilidades
 */
function formatearFecha(fecha) {
  if (!fecha) return "N/A";
  const date = new Date(fecha);
  if (isNaN(date.getTime())) return fecha;
  return date.toLocaleDateString("es-PE", {
    day: "2-digit",
    month: "short",
    year: "numeric"
  });
}


function escaparHtml(texto) {
  if (!texto) return "";
  const div = document.createElement("div");
  div.textContent = texto;
  return div.innerHTML;
}

function obtenerIniciales(nombre) {
  if (!nombre) return "?";
  const partes = nombre.trim().split(" ");
  const primera = partes[0]?.[0] || "";
  const segunda = partes[1]?.[0] || "";
  return (primera + segunda).toUpperCase();
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

/**
 * Ver detalle completo del requerimiento
 */
function verDetalleRequerimiento(idAtencion) {
  // Buscar el requerimiento en ambos datasets
  let requerimiento = requerimientosData.find(r => parseInt(r.idatencion) === parseInt(idAtencion));
  if (!requerimiento) {
    requerimiento = revisionData.find(r => parseInt(r.id) === parseInt(idAtencion));
  }

  if (!requerimiento) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se encontró el requerimiento seleccionado",
      background: "#161616",
      color: "#fff",
      confirmButtonColor: "#f5c400",
      allowOutsideClick: false,
      allowEscapeKey: false
    });
    return;
  }

  // Mostrar loading en el modal
  const modal = new bootstrap.Modal(document.getElementById("modal-ver-detalle"));
  document.getElementById("detalle-contenido").innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border text-warning" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando detalles del requerimiento...</p>
    </div>
  `;

  // Actualizar título del modal
  document.getElementById("detalle-titulo-requerimiento").textContent =
    escaparHtml(requerimiento.titulo || "Sin título");
  modal.show();

  // Obtener detalles completos del requerimiento
  fetch(`${base_url}responsable/pedidos/detalle?id=${idAtencion}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        window.requerimientoActual = data.data; // Guardar globalmente para edición
        renderizarDetalleRequerimiento(data.data, data.archivos);
      } else {
        document.getElementById("detalle-contenido").innerHTML = `
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            ${data.message || "Error al cargar los detalles del requerimiento"}
          </div>
        `;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      document.getElementById("detalle-contenido").innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i>
          Error de conexión al cargar los detalles
        </div>
      `;
    });
}

/**
 * Renderizar el contenido del modal de detalles
 */
function renderizarDetalleRequerimiento(req, archivos) {
  const cuerpo = document.getElementById("detalle-contenido");

  // Procesar archivos
  const archivosCliente = archivos.filter(a => !a.idatencion);
  const archivosEmpleado = archivos.filter(a => a.idatencion);

  // Estados y Prioridades
  const estados = {
    'pendiente': { label: 'PENDIENTE', c: '#ef4444', i: 'bi-clock' },
    'pendiente_asignado': { label: 'ASIGNADO', c: '#3b82f6', i: 'bi-person-check' },
    'en_proceso': { label: 'EN PROCESO', c: '#f5c400', i: 'bi-play-circle' },
    'en_revision': { label: 'EN REVISIÓN', c: '#a855f7', i: 'bi-eye' },
    'finalizado': { label: 'FINALIZADO', c: '#22c55e', i: 'bi-check-circle' }
  };
  const es = estados[req.estado] || { label: (req.estado || '').toUpperCase(), c: '#999', i: 'bi-question' };

  const prios = {
    'alta': { label: 'ALTA', c: '#ef4444', i: 'bi-chevron-double-up' },
    'media': { label: 'MEDIA', c: '#f5c400', i: 'bi-chevron-up' },
    'baja': { label: 'BAJA', c: '#3b82f6', i: 'bi-chevron-down' }
  };
  const p = (req.prioridad || 'media').toLowerCase();
  const pri = prios[p] || { label: p.toUpperCase(), c: '#999', i: 'bi-dash' };

  // HTML de Entrega
  let entregaHtml = '';
  if (req.estado === 'en_revision' || req.estado === 'finalizado') {
    entregaHtml = `
      <div style="background:rgba(34,197,94,0.05); border:1px solid rgba(34,197,94,0.15); border-left-width:4px; border-left-color:#22c55e; border-radius:10px; padding:20px; margin-bottom:20px;">
        <div style="font-family:'Bebas Neue',sans-serif; font-size:17px; letter-spacing:1.5px; color:#22c55e; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-send-check-fill"></i> INFORMACIÓN DE LA ENTREGA
        </div>
        <div class="row g-3">
          <div class="col-md-12">
            <span class="hp-label">URL DE ENTREGA</span>
            ${req.url_entrega ? `<a href="${req.url_entrega}" target="_blank" class="btn btn-sm btn-outline-success" style="font-size:12px;"><i class="bi bi-link-45deg"></i> ABRIR ENTREGABLE</a>` : '<span style="color:#444; font-size:12px; font-style:italic;">No se proporcionó URL</span>'}
          </div>
          <div class="col-md-12">
            <span class="hp-label">NOTAS / OBSERVACIONES</span>
            <p style="color:#bbb; font-size:13px; line-height:1.6; margin:0;">${escaparHtml(req.observacion_revision || 'Sin observaciones adicionales.')}</p>
          </div>
          <div class="col-md-12">
            <span class="hp-label">ARCHIVOS ADJUNTOS</span>
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
              ${archivosEmpleado.length > 0 ? archivosEmpleado.map(a => `
                <a href="${base_url}responsable/archivos/vista-previa/${a.id}" target="_blank" style="display:flex; align-items:center; background:#111; border:1px solid #22c55e44; padding:8px 12px; border-radius:8px; color:#ddd; text-decoration:none; font-size:12px; gap:8px;">
                  <i class="bi bi-file-earmark-check-fill" style="color:#22c55e;"></i>
                  <span>${escaparHtml(a.nombre)}</span>
                </a>
              `).join('') : '<span style="color:#444; font-size:12px; font-style:italic;">No hay archivos físicos.</span>'}
            </div>
          </div>
        </div>
      </div>
    `;
  }

  cuerpo.innerHTML = `
    <style>
      .hp-label { font-size: 10px; font-weight: 800; letter-spacing: 1px; color: #555; text-transform: uppercase; display: block; margin-bottom: 5px; }
      .hp-val { color: #eee; font-size: 14px; line-height: 1.6; word-break: break-word; }
      .hp-sec { background: #0d0d0d; border: 1px solid #1e1e1e; border-radius: 12px; padding: 20px; margin-bottom: 15px; }
      .hp-sec-title { font-family: 'Bebas Neue', sans-serif; font-size: 16px; letter-spacing: 1px; color: #666; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
      .hp-pill { background: #111; border: 1px solid #333; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; color: #aaa; }
    </style>

    <div class="row g-4">
      <div class="col-lg-8">
        ${entregaHtml}

        <div class="hp-sec">
          <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
            <span class="hp-pill" style="border-color:${es.c}44; color:${es.c};"><i class="bi ${es.i} me-1"></i>${es.label}</span>
            <span class="hp-pill" style="border-color:${pri.c}44; color:${pri.c};"><i class="bi ${pri.i} me-1"></i>PRIORIDAD ${pri.label}</span>
            
            ${(req.servicio === 'Creación de Contenido' && (req.estado === 'pendiente_asignado' || req.estado === 'en_proceso' || req.estado === 'pendiente')) ? `
              <button class="btn btn-sm btn-warning ms-auto" id="btn-editar-req" onclick="activarEdicionRequerimiento()" style="font-weight:700; font-size:11px; height:24px; display:flex; align-items:center; gap:5px;">
                <i class="bi bi-pencil-square"></i> EDITAR INFORMACIÓN
              </button>
            ` : ''}
          </div>

          <div class="hp-sec-title">SOLICITUD ORIGINAL</div>
          <div class="row g-4">
            <div class="col-md-6">
              <span class="hp-label">Objetivo de Comunicación</span>
              <div class="hp-val">${escaparHtml(req.objetivo_comunicacion || '---')}</div>
            </div>
            <div class="col-md-6">
              <span class="hp-label">Público Objetivo</span>
              <div class="hp-val">${escaparHtml(req.publico_objetivo || '---')}</div>
            </div>
            <div class="col-12">
              <span class="hp-label">Descripción Detallada</span>
              <div class="hp-val" style="white-space:pre-wrap; max-height:200px; overflow-y:auto;">${escaparHtml(req.descripcion || 'Sin descripción.')}</div>
            </div>
            <div class="col-md-6">
              <span class="hp-label">Canales de Difusión</span>
              <div class="d-flex flex-wrap gap-2 mt-1">${formatearLista(req.canales_difusion)}</div>
            </div>
            <div class="col-md-6">
              <span class="hp-label">Formatos Solicitados</span>
              <div class="d-flex flex-wrap gap-2 mt-1">${formatearLista(req.formatos_solicitados)}</div>
            </div>
            <div class="col-12">
              <span class="hp-label">Archivos del Cliente</span>
              <div class="d-flex flex-wrap gap-2 mt-1">
                ${archivosCliente.length > 0 ? archivosCliente.map(a => `
                  <a href="${base_url}responsable/archivos/vista-previa/${a.id}" target="_blank" class="badge bg-dark border border-secondary text-secondary p-2 text-decoration-none" style="font-weight:400; font-size:10px;">
                    <i class="bi bi-paperclip me-1"></i> ${escaparHtml(a.nombre)}
                  </a>
                `).join('') : '<span style="color:#444; font-size:12px; font-style:italic;">Sin adjuntos.</span>'}
              </div>
            </div>

            ${req.url_subida ? `
            <div class="col-12 mt-3">
              <span class="hp-label">Materiales de Referencia (Link)</span>
              <div class="hp-val">
                <a href="${req.url_subida}" target="_blank" class="text-info" style="font-size:13px; text-decoration:underline; word-break:break-all;">
                  <i class="bi bi-link-45deg"></i> ${escaparHtml(req.url_subida)}
                </a>
              </div>
            </div>
            ` : ''}
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="hp-sec" style="background:#0a0a0a;">
          <div class="hp-sec-title">RESUMEN DEL PEDIDO</div>
          <div class="mb-3">
            <span class="hp-label">Solicitado por</span>
            <div style="font-weight:700; color:#fff;">${escaparHtml(req.nombre_cliente || '---')}</div>
          </div>
          <div class="mb-3">
            <span class="hp-label">Área</span>
            <div style="font-weight:700; color:#fff;">${escaparHtml(req.nombre_area || '---')}</div>
          </div>
           <div class="mb-3">
            <span class="hp-label">Empresa</span>
            <div style="font-weight:700; color:#fff; word-break: break-word;">${escaparHtml(req.nombre_empresa)}</div>
          </div>
          <div class="mb-3">
            <span class="hp-label">Servicio</span>
            <div style="font-weight:700; color:#fff; word-break: break-word;">${escaparHtml(req.nombre_servicio || req.servicio)}</div>
          </div>
          <div class="mb-3">
            <span class="hp-label">Especialista Asignado</span>
            <div style="font-weight:700; color:var(--amarillo);">${escaparHtml(req.empleado_nombre || 'No asignado todavía')}</div>
          </div>
          
          <hr style="border-color:#1e1e1e; margin:15px 0;">
          
          <div class="d-flex justify-content-between mb-2">
            <span class="hp-label" style="margin:0;">Solicitado:</span>
            <span style="font-size:12px; color:#aaa;">${formatearFecha(req.fechacreacion)}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="hp-label" style="margin:0;">Requerido:</span>
            <span style="font-size:12px; color:#f0f0f0; font-weight:700;">${formatearFecha(req.fecharequerida)}</span>
          </div>
        </div>
      </div>
    </div>
  `;
}

function formatearLista(valor) {
  if (!valor) return '';
  let items = [];
  try {
    const parsed = JSON.parse(valor);
    items = Array.isArray(parsed) ? parsed : [String(parsed)];
  } catch (e) {
    items = valor.split(',').map(s => s.trim()).filter(s => s);
  }
  return items.map(item => `<span style="display:inline-block;background:#1e1e1e;color:#ddd;border:1px solid #333;padding:5px 12px;border-radius:6px;font-size:11px;">${escaparHtml(item)}</span>`).join('');
}

function nl2br(str) {
  if (!str) return '';
  return str.replace(/\n/g, '<br>');
}

function mb_strtoupper(str) {
  if (!str) return '';
  return str.toUpperCase();
}

function escaparHtml(texto) {
  if (!texto) return '';
  const div = document.createElement('div');
  div.textContent = texto;
  return div.innerHTML;
}

function formatearFecha(fecha) {
  if (!fecha) return '---';
  const d = new Date(fecha);
  if (isNaN(d.getTime())) return fecha;

  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');

  return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function obtenerIniciales(nombre) {
  if (!nombre) return '??';
  const partes = nombre.trim().split(' ');
  if (partes.length >= 2) {
    return (partes[0][0] + partes[1][0]).toUpperCase();
  }
  return partes[0]?.[0].toUpperCase() || '?';
}

// Exponer función al scope global
window.abrirModalAsignar = abrirModalAsignar;
window.seleccionarEmpleado = seleccionarEmpleado;
/**
 * Activa el modo edición en el modal
 */
function activarEdicionRequerimiento() {
  const req = window.requerimientoActual;
  if (!req) return;

  // Cambiar el botón de Editar por Guardar y Cancelar
  const headerBtns = document.querySelector('.hp-sec div[style*="display:flex; flex-wrap:wrap; gap:8px"]');
  headerBtns.innerHTML = `
        <span class="hp-pill" style="border-color:#F5C40044; color:#F5C400;"><i class="bi bi-pencil-square me-1"></i>MODO EDICIÓN</span>
        <button class="btn btn-sm btn-success ms-auto" id="btn-guardar-edicion" onclick="guardarEdicionRequerimiento()" style="font-weight:700; font-size:11px; height:24px; display:flex; align-items:center; gap:5px;">
            <i class="bi bi-check-lg"></i> GUARDAR CAMBIOS
        </button>
        <button class="btn btn-sm btn-outline-secondary" onclick="verDetalleRequerimiento(window.requerimientoActual.idatencion || window.requerimientoActual.id)" style="font-weight:700; font-size:11px; height:24px;">
            CANCELAR
        </button>
    `;

  // Preparar Checkboxes
  const canalesLista = ['Facebook', 'Instagram', 'LinkedIn', 'TikTok', 'WhatsApp', 'Web', 'Correo Electrónico', 'Publicidad Digital (Ads)', 'Impreso', 'Otros'];
  const formatosLista = ['Imagen (Post/Story)', 'Video (Reel/TikTok)', 'Carrusel', 'PDF / Documento', 'GIF Animado', 'Motion Graphics', 'Fotografía', 'Ilustración', 'Texto / Copywriting', 'Otros'];

  const canalesActuales = (req.canales_difusion || '').split(',').map(s => s.trim());
  const formatosActuales = (req.formatos_solicitados || '').split(',').map(s => s.trim());

  const renderCheckboxes = (lista, actuales, name) => {
    return lista.map(item => `
            <div class="form-check form-check-inline" style="margin-bottom: 5px;">
                <input class="form-check-input check-premium" type="checkbox" name="${name}" value="${item}" id="chk-${name}-${item.replace(/\s+/g, '-')}" ${actuales.includes(item) ? 'checked' : ''}>
                <label class="form-check-label text-white" for="chk-${name}-${item.replace(/\s+/g, '-')}" style="font-size: 12px; cursor: pointer;">${item}</label>
            </div>
        `).join('');
  };

  // Transformar campos en inputs
  const container = document.querySelector('.hp-sec .row.g-4');

  container.innerHTML = `
        <div class="col-12">
            <span class="hp-label">Título del Requerimiento</span>
            <input type="text" id="edit-titulo" class="form-control form-control-sm bg-dark text-white border-secondary" value="${escaparHtml(req.titulo)}">
        </div>
        <div class="col-md-6">
            <span class="hp-label">Objetivo de Comunicación</span>
            <textarea id="edit-objetivo" class="form-control form-control-sm bg-dark text-white border-secondary" rows="2">${req.objetivo_comunicacion || ''}</textarea>
        </div>
        <div class="col-md-6">
            <span class="hp-label">Público Objetivo</span>
            <textarea id="edit-publico" class="form-control form-control-sm bg-dark text-white border-secondary" rows="2">${req.publico_objetivo || ''}</textarea>
        </div>
        <div class="col-12">
            <span class="hp-label">Descripción Detallada</span>
            <textarea id="edit-descripcion" class="form-control form-control-sm bg-dark text-white border-secondary" rows="4">${req.descripcion || ''}</textarea>
        </div>
        
        <div class="col-12">
            <span class="hp-label mb-2">Canales de Difusión</span>
            <div class="p-3 border border-secondary rounded bg-black-opacity">
                ${renderCheckboxes(canalesLista, canalesActuales, 'canales')}
            </div>
        </div>

        <div class="col-12">
            <span class="hp-label mb-2">Formatos Solicitados</span>
            <div class="p-3 border border-secondary rounded bg-black-opacity">
                ${renderCheckboxes(formatosLista, formatosActuales, 'formatos')}
            </div>
        </div>

        <div class="col-md-6">
            <span class="hp-label">Fecha Requerida</span>
            <input type="date" id="edit-fecha" class="form-control form-control-sm bg-dark text-white border-secondary" value="${req.fecharequerida ? req.fecharequerida.split(' ')[0] : ''}">
        </div>
        <div class="col-md-6">
            <span class="hp-label">URL / Enlace de Materiales</span>
            <input type="text" id="edit-url-subida" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Drive, Canva, etc." value="${req.url_subida || ''}">
        </div>
    `;

  // Estilo para los checkboxes
  if (!document.getElementById('style-edicion-premium')) {
    const style = document.createElement('style');
    style.id = 'style-edicion-premium';
    style.textContent = `
            .bg-black-opacity { background: rgba(0,0,0,0.3); }
            .bg-warning-opacity { background: rgba(245, 196, 0, 0.05); }
            .check-premium:checked { background-color: #F5C400; border-color: #F5C400; }
            .check-premium { background-color: #111; border-color: #444; }
        `;
    document.head.appendChild(style);
  }
}

/**
 * Guarda los cambios realizados en el modo edición
 */
function guardarEdicionRequerimiento() {
  const req = window.requerimientoActual;
  const btn = document.getElementById('btn-guardar-edicion');
  const originalHtml = btn.innerHTML;

  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

  // Obtener canales seleccionados
  const canales = Array.from(document.querySelectorAll('input[name="canales"]:checked')).map(el => el.value).join(', ');
  // Obtener formatos seleccionados
  const formatos = Array.from(document.querySelectorAll('input[name="formatos"]:checked')).map(el => el.value).join(', ');

  const formData = new FormData();
  formData.append('idrequerimiento', req.id);
  formData.append('titulo', document.getElementById('edit-titulo').value);
  formData.append('descripcion', document.getElementById('edit-descripcion').value);
  formData.append('objetivo_comunicacion', document.getElementById('edit-objetivo').value);
  formData.append('publico_objetivo', document.getElementById('edit-publico').value);
  formData.append('canales_difusion', canales);
  formData.append('formatos_solicitados', formatos);
  formData.append('fecharequerida', document.getElementById('edit-fecha').value);
  formData.append('url_subida', document.getElementById('edit-url-subida').value);

  fetch(`${base_url}responsable/pedidos/actualizar`, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: '¡Actualizado!',
          text: data.message,
          background: '#161616',
          color: '#fff',
          timer: 2000,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false
        });
        // Recargar detalles en el modal
        verDetalleRequerimiento(req.idatencion || req.id);
        // Si hay una función de refresco de tabla, llamarla
        if (typeof listarBandeja === 'function') listarBandeja();
        if (typeof cargarTareasEmpleado === 'function') cargarTareasEmpleado();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message, background: '#161616', color: '#fff', allowOutsideClick: false, allowEscapeKey: false });
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', background: '#161616', color: '#fff', allowOutsideClick: false, allowEscapeKey: false });
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    });
}