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
                <div class="empleado-workload">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> ${emp.en_proceso} en proceso
                    </small>
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
function confirmarAsignacion(){
  if(!empleadoSeleccionado||!requerimientoSeleccionado){
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
  if(csrfHash){
    formData.append(csrfTokenName, csrfHash);
  }
  fetch(`${base_url}responsable/pedidos/asignar`, {
    method:"POST",
    body:formData,
    headers:{
      "X-Requested-With":"XMLHttpRequest"
    }
  })
    .then(response=>response.json())
    .then(data=>{
      console.log("Respuesta de asignación:", data);
      if(data.success){
        // Cerrar modal
        bootstrap.Modal.getInstance(document.getElementById("modal-asignar")).hide();
        Swal.fire({
          icon:"success",
          title:"¡Asignado!",
          text:data.message,
          background:"#161616",
          color:"#fff",
          confirmButtonColor:"#f5c400",
          timer:2000,
          showConfirmButton:false
        });
        // Recargar bandeja
        setTimeout(()=>cargarBandeja(), 500);
      }else{
        Swal.fire({
          icon:"error",
          title:"Error",
          text:data.message||"No se pudo asignar el requerimiento",
          background:"#161616",
          color:"#fff",
          confirmButtonColor:"#f5c400"
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
      }
    })
    .catch(error=>{
      console.error("Error:", error);
      Swal.fire({
        icon:"error",
        title:"Error",
        text:"Error de conexión al asignar",
        background:"#161616",
        color:"#fff",
        confirmButtonColor:"#f5c400"
      });
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar Asignación';
    });
}
/*
 * Filtrar bandeja
 */
function filtrarBandeja(){
  const busqueda = document.getElementById("buscador-bandeja").value.toLowerCase().trim();
  if(!busqueda){
    renderizarBandeja(requerimientosData);
    return;
  }
  const filtrados = requerimientosData.filter(item=>
    (item.titulo||"").toLowerCase().includes(busqueda)||
    (item.servicio||"").toLowerCase().includes(busqueda)||
    (item.nombreempresa||"").toLowerCase().includes(busqueda)||
    (item.cliente_nombre||"").toLowerCase().includes(busqueda)||
    String(item.idatencion).includes(busqueda)
  );
  renderizarBandeja(filtrados);
}

/**
 * Mostrar estado vacío
 */
function mostrarEstadoVacio(){
  document.getElementById("contenido-bandeja").innerHTML = "";
  document.getElementById("estado-vacio").classList.remove("d-none");
  actualizarContador(0);
}

/**
 * Actualizar contador
 */
function actualizarContador(cantidad){
  const contador = document.getElementById("contador-pendientes");
  if(contador){
    contador.innerHTML = `<i class="bi bi-inbox"></i> ${cantidad} pendiente${cantidad!==1?"s":""}`;
  }
}

/**
 * Mostrar error
 */
function mostrarError(mensaje){
  const tbody = document.getElementById("contenido-bandeja");
  tbody.innerHTML=`
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
function generarSkeletonFilas(cantidad){
  return Array(cantidad).fill(0).map(()=>`
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
function formatearFecha(fecha){
  if(!fecha) return "N/A";
  const date = new Date(fecha);
  if(isNaN(date.getTime())) return fecha;
  return date.toLocaleDateString("es-PE",{
    day:"2-digit",
    month:"short",
    year:"numeric"
  });
}


function escaparHtml(texto){
  if(!texto) return "";
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
  // Buscar el requerimiento en los datos cargados
  const requerimiento = requerimientosData.find(
    (r) => parseInt(r.idatencion) === parseInt(idAtencion),
  );

  if (!requerimiento) {
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
        renderizarDetalleRequerimiento(data.requerimiento, data.archivos);
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
function renderizarDetalleRequerimiento(requerimiento, archivos) {
  const contenido = document.getElementById("detalle-contenido");

  // Procesar canales y formatos
  const canales = JSON.parse(requerimiento.canales_difusion || '[]');
  const formatos = JSON.parse(requerimiento.formatos_solicitados || '[]');
  const formatosOtros = requerimiento.formato_otros ?
    requerimiento.formato_otros.split(',').map(f => f.trim()).filter(f => f) : [];

  // Filtrar archivos del cliente y del empleado
  const archivosCliente = archivos.filter(a => !a.idatencion);
  const archivosEmpleado = archivos.filter(a => a.idatencion);

  contenido.innerHTML = `
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card-dark-main p-4">
          <!-- Header del Requerimiento -->
          <div class="d-flex justify-content-between align-items-start mb-4">
            <div class="flex-grow-1">
              <div class="badges-row mb-3">
                <span class="badge-type">
                  ${mb_strtoupper(requerimiento.servicio_personalizado || requerimiento.nombre_servicio || 'N/A')}
                </span>
                <span class="badge-priority prio-${(requerimiento.prioridad || 'media').toLowerCase()}">
                  ${requerimiento.prioridad || 'Media'}
                </span>
              </div>
              <h1 class="main-project-title mb-2">${escaparHtml(requerimiento.titulo)}</h1>
            </div>
          </div>
          <div class="divider-dark my-4"></div>
          <!-- Información Principal -->
          <div class="row g-4 mb-4">
            <div class="col-md-6">
              <div class="info-section">
                <label class="label-tiny mb-2">
                  <i class="bi bi-bullseye me-1"></i>OBJETIVO DE COMUNICACIÓN
                </label>
                <div class="content-box">
                  <p class="content-text mb-0">${escaparHtml(requerimiento.objetivo_comunicacion || 'No especificado')}</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-section">
                <label class="label-tiny mb-2">
                  <i class="bi bi-people me-1"></i>PÚBLICO OBJETIVO
                </label>
                <div class="content-box">
                  <p class="content-text mb-0">${escaparHtml(requerimiento.publico_objetivo || 'No especificado')}</p>
                </div>
              </div>
            </div>
          </div>

          

          <!-- Descripción -->

          <div class="mb-4">

            <label class="label-tiny mb-2">

              <i class="bi bi-file-text me-1"></i>DESCRIPCIÓN DETALLADA

            </label>

            <div class="content-box">

              <div class="content-text">

                                ${nl2br(escaparHtml(requerimiento.descripcion || ''))}

              </div>

            </div>

          </div>

        </div>

      </div>

      

      <div class="col-lg-4">

        <div class="card-dark-main p-4 mb-4">

          <label class="label-tiny mb-4 d-block">INFORMACIÓN DEL PEDIDO</label>

          

          <div class="timeline-item">

            <i class="bi bi-person-workspace"></i>

            <div>

              <span class="t-label">EMPLEADO ASIGNADO</span>

              <span class="t-value">${escaparHtml(requerimiento.empleado_nombre || 'Pendiente de asignar')}</span>

            </div>

          </div>

          

          <div class="timeline-item">

            <i class="bi bi-calendar-check"></i>

            <div>

              <span class="t-label">FECHA REQUERIDA</span>

              <span class="t-value">${formatearFecha(requerimiento.fecharequerida)}</span>

            </div>

          </div>

          

          <div class="timeline-item">

            <i class="bi bi-plus-square"></i>

            <div>

              <span class="t-label">FECHA DE SOLICITUD</span>

              <span class="t-value">${formatearFecha(requerimiento.fechacreacion)}</span>

            </div>

          </div>

          

          <div class="timeline-item">

            <i class="bi bi-arrow-repeat"></i>

            <div>

              <span class="t-label">MODIFICACIONES</span>

              <span class="t-value">${requerimiento.num_modificaciones || 0}</span>

            </div>

          </div>

        </div>

      </div>

    </div>

    

    <div class="row g-4 mt-1">

      <div class="col-md-6">

        <div class="card-dark-main p-3">

          <label class="label-tiny mb-3 d-block">CANALES DE DIFUSIÓN</label>

          <div class="d-flex flex-wrap gap-2">

            ${canales.length > 0 ?

      canales.map(c => `<span class="tag-outline">${escaparHtml(c)}</span>`).join('') :

      '<span class="text-muted">No especificados</span>'

    }

          </div>

        </div>

      </div>

      

      <div class="col-md-6">

        <div class="card-dark-main p-3">

          <label class="label-tiny mb-3 d-block">FORMATOS SOLICITADOS</label>

          <div class="d-flex flex-wrap gap-2">

            ${formatos.length > 0 ?

      formatos.map(f => `<span class="tag-outline">${escaparHtml(f)}</span>`).join('') : ''

    }

            ${formatosOtros.length > 0 ?

      formatosOtros.map(f => `<span class="tag-outline special">${escaparHtml(f)}</span>`).join('') : ''

    }

            ${formatos.length === 0 && formatosOtros.length === 0 ?

      '<span class="text-muted">No especificados</span>' : ''

    }

          </div>

        </div>

      </div>

      

      <div class="col-md-6">

        <div class="card-dark-main p-3">

          <label class="label-tiny mb-2 d-block">TIPO DE REQUERIMIENTO</label>

          <p class="content-text small m-0">${escaparHtml(requerimiento.tipo_requerimiento || 'No especificado')}</p>

        </div>

      </div>

      

      <!-- Archivos del cliente -->

      <div class="col-12">

        <div class="card-dark-main p-3">

          <label class="label-tiny mb-3 d-block">

            <i class="bi bi-person-badge me-1"></i> ARCHIVOS ENVIADOS POR EL CLIENTE

          </label>

          ${archivosCliente.length > 0 ? `

            <div class="d-flex flex-wrap gap-2">

              ${archivosCliente.map(archivo => {

      const icono = getFileIcon(archivo.tipo);

      const kb = numberFormat((archivo.tamano || 0) / 1024, 1);

      const nombreArchivo = basename(archivo.ruta || '');

      return `

                  <a href="${base_url}cliente/archivos/${nombreArchivo}" target="_blank"

                     class="archivo-adjunto-card cliente-file" title="${escaparHtml(archivo.nombre)}">

                    <i class="bi ${icono}"></i>

                    <div class="archivo-info">

                      <span class="archivo-nombre">${escaparHtml(archivo.nombre)}</span>

                      <span class="archivo-peso">${kb} KB</span>

                    </div>

                    <i class="bi bi-box-arrow-up-right archivo-open"></i>

                  </a>

                `;

    }).join('')}

            </div>

          ` : `

            <div class="text-muted text-center py-3">

              <i class="bi bi-inbox me-2"></i>

              <span class="small">No hay materiales de referencia</span>

            </div>

          `}

          

          ${requerimiento.url_subida ? `

            <div class="mt-3">

              <label class="label-tiny mb-2 d-block">

                <i class="bi bi-link-45deg me-1"></i> ENLACE DE REFERENCIA

              </label>

              <a href="${escaparHtml(requerimiento.url_subida)}" target="_blank" class="archivo-adjunto-card"

                 style="max-width: 100%;">

                <i class="bi bi-globe"></i>

                <div class="archivo-info" style="flex: 1;">

                  <span class="archivo-nombre" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">

                    ${escaparHtml(requerimiento.url_subida)}

                  </span>

                  <span class="archivo-peso">Haz clic para abrir</span>

                </div>

                <i class="bi bi-box-arrow-up-right archivo-open"></i>

              </a>

            </div>

          ` : ''}

        </div>

      </div>

    </div>

  `;
}

// Funciones auxiliares
function getFileIcon(mimeType) {
  if (!mimeType) return 'bi-file-earmark';
  if (mimeType.includes('image')) return 'bi-file-earmark-image';
  if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf';
  if (mimeType.includes('video')) return 'bi-file-earmark-play';
  if (mimeType.includes('word')) return 'bi-file-earmark-word';
  if (mimeType.includes('sheet') || mimeType.includes('excel')) return 'bi-file-earmark-excel';
  return 'bi-file-earmark';
}

function numberFormat(num, decimals) {
  return Number(num).toFixed(decimals);
}

function basename(path) {
  return path.split('/').pop() || path;
}

function nl2br(str) {
  return str.replace(/\n/g, '<br>');
}

function mb_strtoupper(str) {
  return str.toUpperCase();
}

// Exponer función al scope global
window.abrirModalAsignar = abrirModalAsignar;
window.seleccionarEmpleado = seleccionarEmpleado;
window.verDetalleRequerimiento = verDetalleRequerimiento;