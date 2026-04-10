document.addEventListener("DOMContentLoaded", function () {
  // Referencias a elementos del DOM
  const tablaPedidos = document.getElementById("content-pedidos");
  const inputBuscador = document.getElementById("buscador");
  const listaServicios = document.getElementById("lista-servicios");
  const modalNuevoPedido = document.getElementById("modal-nuevo-pedido");

  // Funcion Obtener Servicios y Cargar en el Modal
  async function cargarServicios() {
    // Limpiar lista de servicios
    listaServicios.innerHTML = "";

    try {
      // Hacer petición al servidor para obtener servicios
      const respuesta = await fetch(
        `${base_url}cliente/nuevo-pedido/servicios`,
      );
      if (!respuesta.ok) {
        throw new Error("Error al cargar servicios");
      }

      const datos = await respuesta.json();

      // Crear tarjetas para cada servicio
      datos.forEach((servicio) => {
        listaServicios.innerHTML += `
          <div class="servicio-card" onclick="elegirServicio(${servicio.id}, '${servicio.nombre}')">
            <div class="servicio-card-info">
              <p class="servicio-card-nombre">${servicio.nombre}</p>
              <p class="servicio-card-desc">${servicio.descripcion || ""}</p>
            </div>
            <i class="bi bi-arrow-right servicio-card-arrow"></i>
          </div>`;
      });

      //Servicio Personalizado / Id 0 Pára diferenciar de la BD
      listaServicios.innerHTML += `
        <div class="servicio-card servicio-personalizado" onclick="elegirServicio(0)">
          <div class="servicio-card-info">
            <p class="servicio-card-nombre">Servicio Personalizado</p>
            <p class="servicio-card-desc">¿No encuentras lo que buscas? Cuéntanos tu idea aquí.</p>
          </div>
          <i class="bi bi-arrow-right servicio-card-arrow"></i>
        </div>`;

      // Mostrar lista de servicios
      listaServicios.style.display = "block";
    } catch (error) {
      console.error(error);
      listaServicios.innerHTML = `<p style="color:#555; text-align:center;">Error al cargar servicios</p>`;
      listaServicios.style.display = "block";
    }
  }

  // Llamar cargar_servicios, al Abrir el Modal
  modalNuevoPedido.addEventListener("shown.bs.modal", cargarServicios);

  // Funcion para Otener Pedidos y Mostrarlos en la Tabla
  async function obtenerPedidos() {
    try {
      // Petición al servidor para obtener pedidos del usuario
      const respuesta = await fetch(`${base_url}cliente/pedidos/listar`);
      if (!respuesta.ok) {
        return;
      }
      const datos = await respuesta.json();
      // Limpiar tabla
      tablaPedidos.innerHTML = "";
      // Actualizar contadores en las métricas
      document.getElementById("cnt-total").textContent = datos.length;
      document.getElementById("cnt-por-aprobar").textContent = datos.filter(
        (pedido) => pedido.estado === "pendiente_sin_asignar",
      ).length;
      document.getElementById("cnt-en-proceso").textContent = datos.filter(
        (pedido) =>
          ["pendiente_asignado", "en_proceso", "en_revision"].includes(
            pedido.estado,
          ),
      ).length;
      document.getElementById("cnt-completado").textContent = datos.filter(
        (pedido) => pedido.estado === "finalizado",
      ).length;

      // Si no hay pedidos, mostrar mensaje
      if (datos.length === 0) {
        tablaPedidos.innerHTML = `<tr><td colspan="7" style="text-align:center;">Sin pedidos registrados</td></tr>`;
        return;
      }

      // Guardar total de registros para numeración inversa
      const totalRegistros = datos.length;

      // Crear filas de la tabla para cada pedido
      datos.forEach((pedido, indice) => {
        // Número correlativo inverso (último pedido es #1)
        const numeroVisual = totalRegistros - indice;

        // Nombre del servicio (o personalizado si no hay)
        const nombreServicio = pedido.servicio || pedido.servicio_personalizado;

        // Agregar fila a la tabla
        tablaPedidos.innerHTML += `
          <tr data-numero="${pedido.idrequerimiento}">
            <td style="color:#555; font-size:11px; font-weight:bold;">#${numeroVisual}</td>
            <td>
              ${
                pedido.titulo
                  ? `<span style="font-weight:600; font-size:13px;">${pedido.titulo}</span>`
                  : `<span style="color:#777; font-style:italic;">Sin título</span>`
              }
            </td>
            <td>${nombreServicio}</td>
            <td>${crearBadgeEstado(pedido.estado)}</td>
            <td>${pedido.prioridad ? crearBadgePrioridad(pedido.prioridad) : "—"}</td>
            <td style="color:#777; font-size:11px;">${pedido.fechacreacion?.substring(0, 10)}</td>
            <td>
              <button onclick="verDetalle(${pedido.idrequerimiento})" class="btn-ver" style="border:none; background:none; cursor:pointer;">
                  <i class="bi bi-eye" style="color: #007bff;"></i>
              </button>
            </td>
          </tr>`;
      });
    } catch (error) {
      console.error("Error al obtener pedidos:", error);
    }
  }

  // Variable para controlar el tiempo de búsqueda
  let temporizadorBusqueda;

  // Funcion de Busqueda
  inputBuscador.addEventListener("keyup", function () {
    // Limpiar temporizador anterior
    clearTimeout(temporizadorBusqueda);
    // Esperar 1 segundo antes de buscar
    temporizadorBusqueda = setTimeout(() => {
      const termino = this.value.trim().toLowerCase();
      const filas = document.querySelectorAll("#tablaPedidos tbody tr");
      filas.forEach((fila) => {
        if (termino === "") {
          fila.style.display = "";
          return;
        }

        let coincide = false;

        // Obtener datos de la fila para comparar
        const numeroFila = fila.getAttribute("data-numero");
        const numeroVisual = fila
          .querySelector("td:first-child")
          ?.textContent.toLowerCase();
        const titulo = fila
          .querySelector("td:nth-child(2)")
          ?.textContent.toLowerCase();
        const servicio = fila
          .querySelector("td:nth-child(3)")
          ?.textContent.toLowerCase();
        const estado = fila
          .querySelector("td:nth-child(4)")
          ?.textContent.toLowerCase();
        const prioridad = fila
          .querySelector("td:nth-child(5)")
          ?.textContent.toLowerCase();

        // Verificar si el término coincide con algún campo
        coincide =
          numeroFila.includes(termino) ||
          numeroVisual.includes(termino) ||
          titulo.includes(termino) ||
          servicio.includes(termino) ||
          estado.includes(termino) ||
          prioridad.includes(termino);
        // Mostrar u ocultar fila según coincidencia
        fila.style.display = coincide ? "" : "none";
      });
    }, 1000);
  });

  window.elegirServicio = function (idServicio, nombreServicio) {
    const elModalForm = document.getElementById("modal-formulario-detalle");
    if (!elModalForm) return;

    // 1. Configuración de Estilos según tu CSS original
    const configEstilos = {
        1: { label: 'Diseño Gráfico', cls: 'sd' },  // Amarillo
        2: { label: 'AudioVisual', cls: 'sav' },    // Azul
        0: { label: 'Personalizado', cls: 'sot' }   // Morado
    };
    const estilo = configEstilos[idServicio] || configEstilos[0];

    // 2. Elementos de la UI
    const inputId = document.getElementById("form-idservicio");
    const badgeContainer = document.getElementById("wbadge-container"); // Contenedor del badge
    const txtServicio = document.getElementById("txt-servicio-seleccionado");
    const tituloForm = document.getElementById("form-titulo-servicio");

    // 3. Inyectar el Badge con tu clase original
    if (badgeContainer) {
        badgeContainer.innerHTML = `<span class="sbadge ${estilo.cls}">${estilo.label}</span>`;
    }
    
    if (inputId) inputId.value = idServicio;
    if (txtServicio) txtServicio.innerText = nombreServicio || "Servicio Especial";
    if (tituloForm) {
        tituloForm.innerText = idServicio === 0 ? "CUÉNTANOS TU IDEA" : "DETALLE DEL REQUERIMIENTO";
    }

    // 4. Lógica de mostrar/ocultar (Campos de Personalizado)
    const contNombrePers = document.getElementById("contenedor-nombre-personalizado");
    const listaEstandar = document.getElementById("lista-requerimientos-estandar");
    const reqLibre = document.getElementById("requerimiento-libre");
    
    if (idServicio === 0) {
        if (contNombrePers) contNombrePers.style.display = "block";
        if (reqLibre) reqLibre.style.display = "block";
        if (listaEstandar) listaEstandar.style.display = "block";
    } else {
        if (contNombrePers) contNombrePers.style.display = "none";
        if (reqLibre) reqLibre.style.display = "none";
        if (listaEstandar) listaEstandar.style.display = "block";
    }

    // 5. Cerrar modal de selección y abrir Wizard
    const elModalSeleccion = document.getElementById("modal-nuevo-pedido");
    if (elModalSeleccion) {
        const instance = bootstrap.Modal.getInstance(elModalSeleccion);
        if (instance) instance.hide();
    }

    irAlPaso(1);
    const modalForm = new bootstrap.Modal(elModalForm);
    modalForm.show();
};

  function irAlPaso(numeroPaso) {
    const secciones = document.querySelectorAll(".wizard-section");
    const indicadores = document.querySelectorAll(".step");
    const btnAtras = document.getElementById("btn-atras");
    const btnSiguiente = document.getElementById("btn-siguiente");
    const btnEnviar = document.getElementById("btn-enviar");

    // Si no existen los elementos básicos, abortamos
    if (secciones.length === 0 || !btnSiguiente) return;

    // Actualizar variable global de seguimiento
    pasoActual = numeroPaso;

    // Ocultar todas las secciones y quitar estados activos
    secciones.forEach((s) => s.classList.add("d-none"));
    indicadores.forEach((i) => i.classList.remove("active"));

    // Mostrar sección actual
    const seccionActiva = document.getElementById(`section-${numeroPaso}`);
    const indicadorActivo = document.getElementById(`step-${numeroPaso}-indicador`);

    if (seccionActiva) seccionActiva.classList.remove("d-none");
    if (indicadorActivo) indicadorActivo.classList.add("active");

    // Actualizar título del paso
    const pasoLabels = {
      1: "Info básica",
      2: "Detalles y formatos",
      3: "Confirmar y enviar"
    };
    const tituloForm = document.getElementById("form-titulo-servicio");
    if (tituloForm) {
      tituloForm.innerText = `Paso ${numeroPaso}: ${pasoLabels[numeroPaso]}`;
    }

    // Control de botones Atrás
    if (btnAtras) {
      btnAtras.classList.toggle("d-none", numeroPaso === 1);
    }

    // Control de botones Siguiente/Enviar
    if (numeroPaso === 3) {
      // Último paso: mostrar Enviar, ocultar Siguiente
      btnSiguiente.classList.add("d-none");
      if (btnEnviar) btnEnviar.classList.remove("d-none");
    } else {
      // Pasos anteriores: mostrar Siguiente, ocultar Enviar
      btnSiguiente.classList.remove("d-none");
      if (btnEnviar) btnEnviar.classList.add("d-none");
    }
  }

  window.retrocederPaso = function () {
    if (pasoActual <= 1) return;
    irAlPaso(pasoActual - 1);
  };

  // Lógica para mostrar el input de URL si dice que sí tiene materiales (Solo Si Existe)
  const selectMateriales = document.getElementById("select-materiales");

  if (selectMateriales) {
    selectMateriales.addEventListener("change", function () {
      const inputUrl = document.getElementById("input-url-referencia");
      if (inputUrl) {
        inputUrl.style.display = this.value === "si" ? "block" : "none";
      }
    });
  }

  let pasoActual = 1;

  document.getElementById("btn-siguiente").addEventListener("click", function () {
      validarYPasar();
    });

  // Modificación necesaria en validarYPasar para que no bloquee campos ocultos
  function validarYPasar() {
    const seccionActual = document.getElementById(`section-${pasoActual}`);
    if (!seccionActual) return;

    // Solo validamos inputs que sean VISIBLES y requeridos
    const inputs = seccionActual.querySelectorAll(
      "input[required], select[required], textarea[required]",
    );

    let valido = true;
    inputs.forEach((input) => {
      // Si el padre está oculto, no validamos
      if (input.offsetParent === null) return;

      if (!input.value.trim()) {
        input.classList.add("is-invalid");
        valido = false;
      } else {
        input.classList.remove("is-invalid");
      }
    });

    if (!valido) {
      alert("Por favor completa todos los campos requeridos");
      return;
    }

    // POR AHORA: Solo mostramos un mensaje cuando completa Paso 1
    if (pasoActual === 1) {
      alert("✅ Paso 1 completado correctamente. Los próximos pasos se habilitarán pronto.");
      return;
    }

    // Lógica para pasos futuros (2 y 3)
    if (pasoActual === 2) {
      generarResumen();
    }

    irAlPaso(pasoActual + 1);
  }

  // Redirige a la vista de detalle del requerimiento (JSON)
  window.verDetalle = function (id) {
    if (!id) {
      return;
    }
    //Redirección dinámica: La Base_url configurada hacia el método 'detalle' del controlador de Requerimientos
    window.location.href = `${base_url}cliente/detalle_requerimiento/${id}`;
  };

  obtenerPedidos();
});
// Funciones para Crear Badge (Etiquetas visuales)

// Badge de estado
function crearBadgeEstado(estado) {
  // Mapa de estados a texto y clase CSS
  const mapaEstados = {
    pendiente_sin_asignar: {
      texto: "Por Aprobar",
      clase: "estado-por_aprobar",
    },
    pendiente_asignado: {
      texto: "Asignado",
      clase: "estado-pendiente_asignado",
    },
    en_proceso: { texto: "En Proceso", clase: "estado-en_proceso" },
    en_revision: { texto: "En Revisión", clase: "estado-en_revision" },
    finalizado: { texto: "Finalizado", clase: "estado-completado" },
    cancelado: { texto: "Cancelado", clase: "estado-cancelado" },
  };

  // Obtener configuración o usar por defecto
  const config = mapaEstados[estado] || {
    texto: estado,
    clase: "estado-default",
  };
  // Retornar HTML del badge
  return `<span class="badge-estado ${config.clase}">${config.texto.toUpperCase()}</span>`;
}

// Badge de prioridad
function crearBadgePrioridad(prioridad) {
  // Mapa de prioridades a clase CSS y etiqueta
  const mapaPrioridades = {
    Baja: { clase: "prio-baja", etiqueta: "Baja" },
    Media: { clase: "prio-media", etiqueta: "Media" },
    Alta: { clase: "prio-alta", etiqueta: "Alta" },
  };

  // Obtener configuración o usar por defecto
  const config = mapaPrioridades[prioridad] || {
    clase: "prio-default",
    etiqueta: prioridad,
  };
  // Retornar HTML del badge
  return `<span class="badge-prio ${config.clase}">${config.etiqueta}</span>`;
}
