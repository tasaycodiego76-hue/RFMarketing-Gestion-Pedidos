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

  /**
   * elegirServicio()
   * Se ejecuta cuando el usuario selecciona un servicio
   * Mostrar/ocultar campos según el tipo de servicio
   * Cargar opciones dinámicas (canales y formatos)
   * Iniciar el wizard en el paso 1
   */
  window.elegirServicio = function (idServicio, nombreServicio) {
    const elModalForm = document.getElementById("modal-formulario-detalle");
    if (!elModalForm) return;

    // Configuración de Estilos según tu CSS original
    // ID=1: Diseño Gráfico, ID=2: AudioVisual, ID=0: Personalizado
    const configEstilos = {
      1: { label: "Diseño Gráfico", cls: "sd" }, // Amarillo
      2: { label: "AudioVisual", cls: "sav" }, // Azul
      0: { label: "Personalizado", cls: "sot" }, // Morado
    };
    const estilo = configEstilos[idServicio] || configEstilos[0];

    // Elementos de la UI que vamos a actualizar
    const inputId = document.getElementById("form-idservicio"); // Campo hidden para guardar ID
    const badgeContainer = document.getElementById("wbadge-container"); // Contenedor del badge
    const txtServicio = document.getElementById("txt-servicio-seleccionado"); // Nombre del servicio seleccionado
    const tituloForm = document.getElementById("form-titulo-servicio"); // Título dinámico del paso

    // Inyectar el Badge con colores según el servicio
    if (badgeContainer) {
      badgeContainer.innerHTML = `<span class="sbadge ${estilo.cls}">${estilo.label}</span>`;
    }

    if (inputId) inputId.value = idServicio;
    if (txtServicio)
      txtServicio.innerText = nombreServicio || "Servicio Especial";
    if (tituloForm) {
      tituloForm.innerText =
        idServicio === 0 ? "CUÉNTANOS TU IDEA" : "DETALLE DEL REQUERIMIENTO";
    }

    // LÓGICA DINÁMICAMENTE: Mostrar/Ocultar campos según tipo de servicio
    // Si es Personalizado (ID=0): mostrar campo para que defina el nombre del servicio
    // Si es Estándar (ID=1 o 2): ocultar ese campo y usar lista predefinida
    const contNombrePers = document.getElementById(
      "contenedor-nombre-personalizado",
    );
    const listaEstandar = document.getElementById(
      "lista-requerimientos-estandar",
    );
    const reqLibre = document.getElementById("requerimiento-libre");

    if (idServicio === 0) {
      // SERVICIO PERSONALIZADO: mostrar input para escribir el nombre del servicio
      if (contNombrePers) contNombrePers.style.display = "block";
      if (reqLibre) reqLibre.style.display = "block";
      if (listaEstandar) listaEstandar.style.display = "block";
    } else {
      // SERVICIOS ESTÁNDAR (Diseño o Audiovisual): usar campos predefinidos
      if (contNombrePers) contNombrePers.style.display = "none";
      if (reqLibre) reqLibre.style.display = "none";
      if (listaEstandar) listaEstandar.style.display = "block";
    }

    // CANALES DE DIFUSIÓN: Opciones iguales para todos los servicios
    const canalesOpciones = [
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

    // FORMATOS POR SERVICIO: Cada servicio tiene sus propios formatos disponibles
    const formatosPorServicio = {
      1: [
        // Diseño
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
        "Guías (trámites, pagos, etc.)",
        "Imagen JPG - PNG",
        "Otros",
      ],
      2: [
        // Audiovisual
        "Reels de Facebook/Instagram",
        "Historia Facebook/Instagram",
        "Reel/Historia TikTok",
        "Reels de LinkedIn",
        "Historia de Whatsapp",
        "Video para Youtube",
        "SIGU (comunicado)",
        "Aula Virtual (Pop up)",
        "Pantallas LED publicitarias",
        "Spot publicitario para TV",
        "Videos para proyección de eventos",
        "Reels para Pauta (publicidad)",
        "Otros",
      ],
      0: [
        // Personalizado — combina todos
        "Post de Facebook/Instagram",
        "Historia Facebook/Instagram",
        "Historia de Whatsapp",
        "Reels de Facebook/Instagram",
        "Reel/Historia TikTok",
        "Video para Youtube",
        "Afiche A4",
        "Afiche A3",
        "Banner Web Portada",
        "Spot publicitario para TV",
        "Banner físico",
        "Emailing (pieza para correo)",
        "Imagen JPG - PNG",
        "Otros",
      ],
    };

    // Generar dinámicamente los checkboxes con límite de 3 selecciones máximo
    const checkCanales = document.getElementById("canales-checks");
    if (checkCanales) {
      checkCanales.innerHTML = canalesOpciones
        .map(
          (c) => `
        <label class="check-item">
            <input type="checkbox" name="canales[]" value="${c}"
                onchange="limitarSeleccion(this, 'canales[]', 3)">
            <span>${c}</span>
        </label>`,
        )
        .join("");
    }

    // Busca en el array de formatosPorServicio usando el ID del servicio
    const checkFormatos = document.getElementById("formatos-checks");
    if (checkFormatos) {
      // Obtener los formatos para este servicio, o usar personalizado como fallback
      const formatos =
        formatosPorServicio[idServicio] ?? formatosPorServicio[0];
      checkFormatos.innerHTML = formatos
        .map(
          (f) => `
        <label class="check-item">
            <input type="checkbox" name="formatos[]" value="${f}"
                onchange="toggleFormatoOtros(this)">
            <span>${f}</span>
        </label>`,
        )
        .join("");
    }

    // Cierra el modal de "Selecciona el servicio" y abre el modal del formulario
    const elModalSeleccion = document.getElementById("modal-nuevo-pedido");
    if (elModalSeleccion) {
      const instance = bootstrap.Modal.getInstance(elModalSeleccion);
      if (instance) instance.hide();
    }

    // Iniciar wizard en paso 1 y mostrar modal del formulario
    irAlPaso(1);
    const modalForm = new bootstrap.Modal(elModalForm);
    modalForm.show();
  };

  /**
   * Valida que no se seleccionen más de N opciones en un grupo de checkboxes.
   * Se usa para limitar canales a máximo 3.
   */
  window.limitarSeleccion = function (checkbox, nombre, maximo) {
    const seleccionados = document.querySelectorAll(
      `input[name="${nombre}"]:checked`,
    );
    if (seleccionados.length > maximo) {
      // Si pasas el límite, desmarcar el último y mostrar aviso visual
      checkbox.checked = false;
      // Pequeño aviso visual sin alert: borde rojo que desaparece en 800ms
      checkbox.closest(".check-item").style.borderColor = "#ef4444";
      setTimeout(() => {
        checkbox.closest(".check-item").style.borderColor = "";
      }, 800);
    }
  };

  /**
   * Si el usuario marca la opción "Otros" en formatos,
   * muestra un input adicional para que especifique el formato personalizado.
   */
  window.toggleFormatoOtros = function (checkbox) {
    const contenedor = document.getElementById("contenedor-formato-otros");
    if (!contenedor) return;
    // Verificar si "Otros" está marcado
    const otrosChecked = document.querySelector(
      'input[name="formatos[]"][value="Otros"]:checked',
    );
    // Mostrar/ocultar campo de entrada para especificar el formato custom
    contenedor.style.display = otrosChecked ? "block" : "none";
  };

  /**
   * Navega entre los 3 pasos del wizard.
   * - Mostrar/ocultar secciones
   * - Actualizar indicadores de progreso
   * - Mostrar/ocultar botones (Atrás, Siguiente, Enviar)
   * - Actualizar título del paso
   */
  function irAlPaso(numeroPaso) {
    const secciones = document.querySelectorAll(".wizard-section"); // Todos los pasos
    const indicadores = document.querySelectorAll(".step"); // Los números 1, 2, 3
    const btnAtras = document.getElementById("btn-atras");
    const btnSiguiente = document.getElementById("btn-siguiente");
    const btnEnviar = document.getElementById("btn-enviar");

    // Si no existen los elementos básicos, abortamos
    if (secciones.length === 0 || !btnSiguiente) return;

    // Actualizar variable global para saber en qué paso estamos
    pasoActual = numeroPaso;

    // Ocultar todas las secciones y quitar estado activo de indicadores
    secciones.forEach((s) => s.classList.add("d-none")); // Ocultar todos los pasos
    indicadores.forEach((i) => i.classList.remove("active")); // Desmarcar todos los números

    // Mostrar SOLO la sección actual
    const seccionActiva = document.getElementById(`section-${numeroPaso}`);
    const indicadorActivo = document.getElementById(
      `step-${numeroPaso}-indicador`,
    );

    if (seccionActiva) seccionActiva.classList.remove("d-none"); // Mostrar paso actual
    if (indicadorActivo) indicadorActivo.classList.add("active"); // Marcar número del paso

    // Actualizar título del formulario con el número de paso actual
    const pasoLabels = {
      1: "Info básica",
      2: "Detalles y formatos",
      3: "Confirmar y enviar",
    };
    const tituloForm = document.getElementById("form-titulo-servicio");
    if (tituloForm) {
      tituloForm.innerText = `Paso ${numeroPaso}: ${pasoLabels[numeroPaso]}`;
    }

    // Controlar visibilidad del botón "Atrás"
    // Solo mostrar en pasos 2 y 3 (ocultar en paso 1)
    if (btnAtras) {
      btnAtras.classList.toggle("d-none", numeroPaso === 1);
    }

    // Controlar visibilidad de los botones de acción
    if (numeroPaso === 3) {
      // ÚLTIMO PASO: mostrar "Enviar Requerimiento", ocultar "Siguiente Paso"
      btnSiguiente.classList.add("d-none");
      if (btnEnviar) btnEnviar.classList.remove("d-none");
    } else {
      // PASOS 1 y 2: mostrar "Siguiente Paso", ocultar "Enviar Requerimiento"
      btnSiguiente.classList.remove("d-none");
      if (btnEnviar) btnEnviar.classList.add("d-none");
    }
  }

  /**
   * Permite ir al paso anterior.
   * No permite retroceder antes del paso 1.
   */
  window.retrocederPaso = function () {
    if (pasoActual <= 1) return; // No retroceder del paso 1
    irAlPaso(pasoActual - 1); // Ir al paso anterior
  };

  /**
   * Cuando el usuario selecciona "¿Tienes materiales de referencia?"
   * Se muestran los campos correspondientes (archivos, link, o ambos)
   */
  const selectMateriales = document.getElementById("select-materiales");

  document.addEventListener("change", function (e) {
    if (e.target.id !== "select-materiales") return;

    const valor = e.target.value;
    const contArchivos = document.getElementById("contenedor-archivos"); // Área de subida de archivos
    const contLink = document.getElementById("contenedor-link"); // Campo de URL

    // Primero: ocultarlo todo
    contArchivos.style.display = "none";
    contLink.style.display = "none";

    // Luego: mostrar según la opción seleccionada
    if (valor === "archivos") {
      // Usuario tiene archivos para adjuntar
      contArchivos.style.display = "block";
    } else if (valor === "link") {
      // Usuario tiene un link de referencia
      contLink.style.display = "block";
    } else if (valor === "ambos") {
      // Usuario tiene archivos y link
      contArchivos.style.display = "block";
      contLink.style.display = "block";
    }
    // Si valor === "no": dejar todo oculto
  });

  // VARIABLE GLOBAL: Seguimiento del paso actual del wizard
  let pasoActual = 1;

  // EVENT LISTENER: Botón "Siguiente Paso"
  document
    .getElementById("btn-siguiente")
    .addEventListener("click", function () {
      validarYPasar();
    });

  /**
   * Valida que todos los campos requeridos del paso actual estén completos.
   * Si pasa validación, avanza al siguiente paso.
   */
  function validarYPasar() {
    const seccionActual = document.getElementById(`section-${pasoActual}`);
    if (!seccionActual) return;

    // Obtener todos los inputs/selects/textareas con atributo 'required'
    const inputs = seccionActual.querySelectorAll(
      "input[required], select[required], textarea[required]",
    );

    let valido = true;
    inputs.forEach((input) => {
      // IMPORTANTE: Check de visibilidad (offsetParent === null = elemento oculto)
      // Esto evita validar campos que están hidden por lógica del wizard
      if (input.offsetParent === null) return;
      
      // Validar que el campo no esté vacío
      if (!input.value.trim()) {
        input.classList.add("is-invalid"); // Marcar como inválido con estilo CSS
        valido = false;
      } else {
        input.classList.remove("is-invalid");
      }
    });

    // Si hay campos sin completar, mostrar aviso
    if (!valido) {
      alert("Por favor completa todos los campos requeridos");
      return;
    }

    // VALIDACIONES ESPECIALES EN PASO 2: "Detalles y formatos"
    if (pasoActual === 2) {
      // Validación 1: Al menos 1 canal de difusión seleccionado
      const canales = document.querySelectorAll(
        'input[name="canales[]"]:checked',
      );
      if (canales.length === 0) {
        alert("Selecciona al menos un canal de difusión");
        return;
      }
      
      // Validación 2: Al menos 1 formato seleccionado
      const formatos = document.querySelectorAll(
        'input[name="formatos[]"]:checked',
      );
      const formatoLibre = document.querySelector('textarea[name="formatos"]');
      if (
        formatos.length === 0 &&
        (!formatoLibre || !formatoLibre.value.trim())
      ) {
        alert("Selecciona al menos un formato");
        return;
      }
      
      // Si todo pasó validación: generar resumen para paso 3
      generarResumen();
    }

    // TODO VALIDÓ CORRECTAMENTE: Avanzar al siguiente paso
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
