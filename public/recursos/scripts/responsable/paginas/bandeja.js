/**
 * Bandeja de Entrada - Responsable de Área
 * Consume endpoints:
 * - GET responsable/pedidos/bandeja-json
 * - GET responsable/empleados/mi-area-json
 * - POST responsable/pedidos/asignar
 */

// Variables globales
let empleadosData = [];
let requerimientosData = [];
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

/**
 * Cargar datos de la bandeja
 */
function cargarBandeja() {
  const tbody = document.getElementById("contenido-bandeja");
  tbody.innerHTML = generarSkeletonFilas(5);

  fetch(`${base_url}responsable/pedidos/bandeja-json`)
    .then((response) => response.json())
    .then((data) => {
      console.log("Datos recibidos de la bandeja:", data);
      if (data.success) {
        requerimientosData = data.data || [];
        actualizarContador(requerimientosData.length);

        if (requerimientosData.length === 0) {
          mostrarEstadoVacio();
        } else {
          renderizarBandeja(requerimientosData);
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

/**
 * Cargar empleados del área
 */
function cargarEmpleados() {
  fetch(`${base_url}responsable/empleados/mi-area-json`)
    .then((response) => response.json())
    .then((data) => {
      console.log("Empleados recibidos:", data);
      if (data.success) {
        empleadosData = data.data || [];
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
            <td>#${item.idatencion}</td>
            <td>
                <div style="font-weight:600;">${escaparHtml(item.titulo || "Sin título")}</div>
                ${item.cliente_nombre ? `<div style="font-size:11px;color:#a1a1aa;">Cliente: ${escaparHtml(item.cliente_nombre)}</div>` : ""}
            </td>
            <td>${escaparHtml(item.servicio || "N/A")}</td>
            <td>${escaparHtml(item.nombreempresa || "N/A")}</td>
            <td>
                <span class="prioridad-${(item.prioridad || "media").toLowerCase()}">
                    ${item.prioridad || "Media"}
                </span>
            </td>
            <td>${formatearFecha(item.fechacreacion)}</td>
            <td>
                <span class="estado-por-asignar">
                    Por Asignar
                </span>
            </td>
            <td>
                <button class="btn-asignar" onclick="abrirModalAsignar(${item.idatencion})" title="Asignar a miembro del equipo">
                    <i class="bi bi-person-plus"></i> Asignar
                </button>
            </td>
        </tr>
    `,
    )
    .join("");
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
    });
    return;
  }

  console.log("Requerimiento seleccionado:", requerimiento);
  requerimientoSeleccionado = requerimiento;

  // Llenar info del modal
  document.getElementById("idatencion-seleccionado").value = idAtencion;
  document.getElementById("modal-titulo-requerimiento").textContent =
    escaparHtml(requerimiento.titulo || "Sin título");
  document.getElementById("info-servicio").textContent = escaparHtml(
    requerimiento.servicio || "N/A",
  );
  document.getElementById("info-empresa").textContent = escaparHtml(
    requerimiento.nombreempresa || "N/A",
  );

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

  if (empleadosData.length === 0) {
    contenedor.innerHTML = `
            <div class="text-center py-4" style="color:#a1a1aa;">
                <i class="bi bi-exclamation-circle mb-2" style="font-size:24px;display:block;"></i>
                No hay miembros disponibles en tu área
            </div>
        `;
    return;
  }

  contenedor.innerHTML = empleadosData
    .map(
      (emp) => `
        <div class="empleado-item ${empleadoSeleccionado === emp.id ? "seleccionado" : ""}"
             onclick="seleccionarEmpleado(${emp.id})"
             data-id="${emp.id}">
            <div class="empleado-avatar ${emp.esresponsable ? "responsable" : ""}">
                ${emp.esresponsable ? '<i class="bi bi-shield-check"></i>' : obtenerIniciales(emp.nombre_completo)}
            </div>
            <div class="empleado-info">
                <div class="empleado-nombre">${escaparHtml(emp.nombre_completo)}</div>
                <div class="empleado-rol">
                    ${emp.esresponsable ? '<span class="badge-jefe">Jefe de Área</span>' : '<span class="badge-miembro">Miembro del Equipo</span>'}
                </div>
            </div>
            <div class="empleado-check">
                ${empleadoSeleccionado === emp.id ? '<i class="bi bi-check-lg"></i>' : ""}
            </div>
        </div>
    `,
    )
    .join("");
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

  // Obtener el token (ej: csrf_test_name)
  const csrfTokenName = "csrf_test_name"; // Nombre por defecto en CodeIgniter 4
  const csrfHash = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

  if (csrfHash) {
    formData.append(csrfTokenName, csrfHash);
  }
  // ------------------------

  fetch(`${base_url}responsable/pedidos/asignar`, {
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Respuesta de asignación:", data);
      if (data.success) {
        // Cerrar modal
        bootstrap.Modal.getInstance(
          document.getElementById("modal-asignar"),
        ).hide();

        Swal.fire({
          icon: "success",
          title: "¡Asignado!",
          text: data.message,
          background: "#161616",
          color: "#fff",
          confirmButtonColor: "#f5c400",
          timer: 2000,
          showConfirmButton: false,
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
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error de conexión al asignar",
        background: "#161616",
        color: "#fff",
        confirmButtonColor: "#f5c400",
      });
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
    });
}

/**
 * Filtrar bandeja
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
      (item.servicio || "").toLowerCase().includes(busqueda) ||
      (item.nombreempresa || "").toLowerCase().includes(busqueda) ||
      (item.cliente_nombre || "").toLowerCase().includes(busqueda) ||
      String(item.idatencion).includes(busqueda),
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
            <td colspan="8" class="text-center py-4" style="color:#ef4444;">
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
  return Array(cantidad)
    .fill(0)
    .map(
      () => `
        <tr>
            <td><div class="skeleton" style="width:30px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:150px;height:16px;margin-bottom:4px;"></div><div class="skeleton" style="width:80px;height:12px;"></div></td>
            <td><div class="skeleton" style="width:100px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:120px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:60px;height:20px;border-radius:4px;"></div></td>
            <td><div class="skeleton" style="width:80px;height:16px;"></div></td>
            <td><div class="skeleton" style="width:70px;height:20px;border-radius:4px;"></div></td>
            <td><div class="skeleton" style="width:80px;height:32px;border-radius:6px;"></div></td>
        </tr>
    `,
    )
    .join("");
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
    year: "numeric",
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

// Exponer función al scope global
window.abrirModalAsignar = abrirModalAsignar;
window.seleccionarEmpleado = seleccionarEmpleado;
