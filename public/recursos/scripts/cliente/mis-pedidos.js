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

  // ── DATOS: Canales y formatos disponibles por servicio ──
  const CANALES = [
    "Por correo", "Página web", "Redes sociales",
    "SIGU o Aula Virtual Estudiantes", "SIGU o Aula Virtual Docentes",
    "Impresión física de folletos", "Banner físico", "Letreros",
    "Merch para eventos específicos",
  ];

  const FORMATOS = {
    1: [ // Diseño
      "Emailing (pieza para correo)", "Post de Facebook/Instagram", "Historia Facebook/Instagram",
      "Historia de Whatsapp", "Post de LinkedIn", "SIGU (comunicado)", "Aula Virtual (Pop up)",
      "Wallpaper – Computadoras", "Banner Web Portada", "Volante A5", "Afiche A4", "Afiche A3",
      "Credenciales", "Banner 2x1", "Tarjeta Personal", "Tríptico", "Díptico", "Folder A4",
      "Brochure", "Cartilla", "Banderola", "Módulos", "SMS", "IVR", "Marcos Selfie",
      "Boletín", "Guías (trámites, pagos, etc.)", "Imagen JPG - PNG", "Otros",
    ],
    2: [ // Audiovisual
      "Reels de Facebook/Instagram", "Historia Facebook/Instagram", "Reel/Historia TikTok",
      "Reels de LinkedIn", "Historia de Whatsapp", "Video para Youtube", "SIGU (comunicado)",
      "Aula Virtual (Pop up)", "Pantallas LED publicitarias", "Spot publicitario para TV",
      "Videos para proyección de eventos", "Reels para Pauta (publicidad)", "Otros",
    ],
    0: [ // Personalizado
      "Post de Facebook/Instagram", "Historia Facebook/Instagram", "Historia de Whatsapp",
      "Reels de Facebook/Instagram", "Reel/Historia TikTok", "Video para Youtube",
      "Afiche A4", "Afiche A3", "Banner Web Portada", "Spot publicitario para TV",
      "Banner físico", "Emailing (pieza para correo)", "Imagen JPG - PNG", "Otros",
    ],
  };

  const ESTILOS_SERVICIO = {
    1: { label: "Diseño Gráfico", cls: "sd" },
    2: { label: "AudioVisual", cls: "sav" },
    0: { label: "Personalizado", cls: "sot" },
  };

  // Helper: genera checkboxes HTML desde un array de opciones
  function generarChecks(opciones, name, onchangeFn) {
    return opciones.map(op => `
      <label class="check-item">
        <input type="checkbox" name="${name}" value="${op}" onchange="${onchangeFn}">
        <span>${op}</span>
      </label>`).join("");
  }

  // Se ejecuta cuando el usuario selecciona un servicio
  window.elegirServicio = function (idServicio, nombreServicio) {
    const elModalForm = document.getElementById("modal-formulario-detalle");
    if (!elModalForm) return;

    const estilo = ESTILOS_SERVICIO[idServicio] || ESTILOS_SERVICIO[0];

    // Actualizar badge y nombre del servicio
    const badge = document.getElementById("wbadge-container");
    if (badge) badge.innerHTML = `<span class="sbadge ${estilo.cls}">${estilo.label}</span>`;
    const inputId = document.getElementById("form-idservicio");
    if (inputId) inputId.value = idServicio;
    const txtServ = document.getElementById("txt-servicio-seleccionado");
    if (txtServ) txtServ.innerText = nombreServicio || "Servicio Especial";

    // Mostrar/ocultar campo de nombre personalizado
    const show = (id, visible) => { const el = document.getElementById(id); if (el) el.style.display = visible ? "block" : "none"; };
    show("contenedor-nombre-personalizado", idServicio === 0);
    show("requerimiento-libre", idServicio === 0);
    show("lista-requerimientos-estandar", true);

    // Generar checkboxes de canales y formatos
    const checkCanales = document.getElementById("canales-checks");
    if (checkCanales) checkCanales.innerHTML = generarChecks(CANALES, 'canales[]', "limitarSeleccion(this, 'canales[]', 3)");

    const checkFormatos = document.getElementById("formatos-checks");
    if (checkFormatos) checkFormatos.innerHTML = generarChecks(FORMATOS[idServicio] ?? FORMATOS[0], 'formatos[]', "toggleFormatoOtros(this)");

    // Cerrar modal de selección → abrir modal del formulario
    const elModalSeleccion = document.getElementById("modal-nuevo-pedido");
    if (elModalSeleccion) { const inst = bootstrap.Modal.getInstance(elModalSeleccion); if (inst) inst.hide(); }

    irAlPaso(1);
    new bootstrap.Modal(elModalForm).show();
  };

  //Limita la Seleccion de 3 opciones en un grupo de checkboxes
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

  //Si el usuario marca la opción "Otros" en formatos, muestra un input adicional para que especifique el formato
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

  //Navega entre los 3 pasos del wizard.
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

  //Permite ir al paso anterior.
  window.retrocederPaso = function () {
    if (pasoActual <= 1) return; // No retroceder del paso 1
    irAlPaso(pasoActual - 1); // Ir al paso anterior
  };

  //Cuando el usuario selecciona "¿Tienes materiales de referencia?"
  //Se muestran los campos correspondientes (archivos, link, o ambos)
  const selectMateriales = document.getElementById("select-materiales");

  document.addEventListener("change", function (e) {
    if (e.target.id !== "select-materiales") return;

    const valor = e.target.value;
    const contMateriales = document.getElementById("contenedor-materiales"); // Contenedor general de materiales
    const inputArchivos = document.getElementById("input-archivos");
    const inputLink = document.querySelector('[name="url_referencia"]');
    const listaArchivos = document.getElementById("lista-archivos");

    if (valor === "1") {
      // Usuario TIENE materiales: mostrar ambos campos
      contMateriales.style.display = "block";
    } else if (valor === "0") {
      // Usuario NO TIENE materiales: ocultar y limpiar
      contMateriales.style.display = "none";
      if (inputArchivos) inputArchivos.value = "";
      if (inputLink) inputLink.value = "";
      if (listaArchivos) listaArchivos.innerHTML = "";
    }
  });

  // VARIABLE GLOBAL: Seguimiento del paso actual del wizard
  let pasoActual = 1;

  // EVENT LISTENER: Botón "Siguiente Paso"
  document.getElementById("btn-siguiente").addEventListener("click", function () {
      validarYPasar();
  });

  //Valida que todos los campos requeridos del paso actual estén completos.
  //Si pasa validación, avanza al siguiente paso.
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

  // Mapa de tipos de requerimiento (value del select → texto para la BD)
  const MAPA_TIPOS = {
    adaptacion: "Adaptación de Arte",
    creacion: "Creación de Arte",
    editorial: "Trabajo editorial",
    audiovisual: "Creación de Videos",
  };

  // Helpers para leer valores del formulario
  const val = (selector) => document.querySelector(selector)?.value?.trim() || "";
  const valId = (id) => document.getElementById(id)?.value?.trim() || "";
  const checkedVals = (name) => Array.from(document.querySelectorAll(`input[name="${name}"]:checked`)).map(c => c.value);

  // Arma el FormData con todos los campos del wizard
  function armarFormData() {
    const fd = new FormData();
    const idServicio = valId("form-idservicio") || "0";

    fd.append("idservicio", idServicio);
    if (idServicio === "0") {
      const sp = valId("titulo_personalizado");
      if (sp) fd.append("servicio_personalizado", sp);
    }

    fd.append("titulo", valId("campo-titulo"));
    fd.append("objetivo_comunicacion", val('[name="objetivo"]'));
    fd.append("descripcion", val('[name="descripcion"]'));
    fd.append("tipo_requerimiento", MAPA_TIPOS[val('[name="tipo_requerimiento"]')] || val('[name="tipo_requerimiento"]'));
    fd.append("fecharequerida", val('[name="fecha_entrega"]'));
    fd.append("prioridad", document.querySelector('input[name="prioridad"]:checked')?.value || "Media");
    fd.append("publico_objetivo", val('[name="publico"]'));
    fd.append("canales_difusion", JSON.stringify(checkedVals('canales[]')));
    fd.append("formatos_solicitados", JSON.stringify(checkedVals('formatos[]')));
    fd.append("tiene_materiales", document.getElementById("select-materiales")?.value || "0");

    const fOtros = val('[name="formato_otros"]');
    if (fOtros) fd.append("formato_otros", fOtros);
    const urlRef = val('[name="url_referencia"]');
    if (urlRef) fd.append("url_subida", urlRef);

    // Archivos adjuntos
    const archivos = document.getElementById("input-archivos");
    if (archivos?.files.length > 0) {
      Array.from(archivos.files).forEach(f => fd.append("documentos[]", f));
    }
    return fd;
  }

  // Envío del formulario
  document.getElementById("form-nuevo-pedido").addEventListener("submit", async function (e) {
    e.preventDefault();
    const btnEnviar = document.getElementById("btn-enviar");
    btnEnviar.disabled = true;
    btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

    try {
      const res = await fetch(`${base_url}cliente/requerimiento/guardar`, { method: "POST", body: armarFormData() });
      const data = await res.json();

      if (data.status === "success") {
        const modal = bootstrap.Modal.getInstance(document.getElementById("modal-formulario-detalle"));
        if (modal) modal.hide();
        mostrarAlerta("success", "¡Requerimiento enviado con éxito!");
        obtenerPedidos();
      } else {
        mostrarAlerta("error", data.msg || "Error al enviar");
        console.error("Debug:", data);
      }
    } catch (err) {
      console.error(err);
      mostrarAlerta("error", "Error de conexión.");
    } finally {
      btnEnviar.disabled = false;
      btnEnviar.innerHTML = 'Enviar Requerimiento <i class="bi bi-check-lg"></i>';
    }
  });

  // Genera el resumen del Paso 3 leyendo los valores de los pasos 1 y 2
  function generarResumen() {
    // Helpers para evitar repetir código
    const setText = (id, valor) => { const el = document.getElementById(id); if (el) el.textContent = valor || "—"; };
    const setTags = (checkboxName, targetId) => {
      const el = document.getElementById(targetId);
      if (!el) return;
      const checked = document.querySelectorAll(`input[name="${checkboxName}"]:checked`);
      el.innerHTML = checked.length
        ? Array.from(checked).map(c => `<span class="tag-item">${c.value}</span>`).join(" ")
        : "<span style='color:#555'>Ninguno</span>";
    };
    const toggleWrap = (valor, wrapId, contentId) => {
      const wrap = document.getElementById(wrapId);
      const content = document.getElementById(contentId);
      if (!wrap) return;
      wrap.style.display = valor ? "block" : "none";
      if (content && valor) content.textContent = valor;
    };

    // ── SERVICIO (badge + nombre personalizado si aplica)
    const badge = document.getElementById("wbadge-container");
    const resServicio = document.getElementById("res-servicio");
    const idServ = document.getElementById("form-idservicio")?.value;
    const titPers = document.getElementById("titulo_personalizado")?.value?.trim();
    if (resServicio && badge) {
      resServicio.innerHTML = badge.innerHTML + (idServ === "0" && titPers ? ` — ${titPers}` : "");
    }

    // ── CAMPOS DE TEXTO (Paso 1)
    setText("res-titulo", document.getElementById("campo-titulo")?.value.trim() || "Sin título");
    setText("res-objetivo", document.querySelector('[name="objetivo"]')?.value);
    setText("res-prioridad", document.querySelector('input[name="prioridad"]:checked')?.value || "Media");

    // Tipo de requerimiento (texto del option seleccionado)
    const tipoReq = document.querySelector('[name="tipo_requerimiento"]');
    setText("res-tipo", tipoReq?.options[tipoReq.selectedIndex]?.text);

    // Fecha formateada
    const fechaVal = document.querySelector('[name="fecha_entrega"]')?.value;
    if (fechaVal) {
      const d = new Date(fechaVal + "T00:00:00");
      setText("res-fecha", d.toLocaleDateString("es-PE", { day: "2-digit", month: "long", year: "numeric" }));
    }

    // ── CAMPOS DE TEXTO (Paso 2)
    setText("res-descripcion", document.querySelector('[name="descripcion"]')?.value);
    setText("res-publico", document.querySelector('[name="publico"]')?.value);

    // ── CHECKBOXES → TAGS
    setTags('canales[]', "res-canales");
    setTags('formatos[]', "res-formatos");

    // ── CAMPOS CONDICIONALES (mostrar/ocultar según valor)
    toggleWrap(document.querySelector('[name="formato_otros"]')?.value.trim(), "res-formato-otros-wrap", "res-formato-otros");
    toggleWrap(document.querySelector('[name="url_referencia"]')?.value.trim(), "res-link-wrap", "res-link");

    // Materiales
    const matVal = document.getElementById("select-materiales")?.value;
    setText("res-materiales", matVal === "1" ? "Sí, tiene materiales" : "No tiene materiales");

    // Archivos adjuntos
    const archivos = document.getElementById("input-archivos");
    const resArchivos = document.getElementById("res-archivos");
    const resArchivosWrap = document.getElementById("res-archivos-wrap");
    if (archivos?.files.length > 0) {
      resArchivos.innerHTML = Array.from(archivos.files).map(f =>
        `<div class="archivo-item">
          <i class="bi bi-file-earmark"></i> <span>${f.name}</span>
          <span style="color:#555; font-size:10px;">${(f.size / 1024).toFixed(1)} KB</span>
        </div>`
      ).join("");
      resArchivosWrap.style.display = "block";
    } else if (resArchivosWrap) {
      resArchivosWrap.style.display = "none";
    }
  }

  // Alerta visual sin alert()
  function mostrarAlerta(tipo, mensaje) {
    const colores = {
      success: {
        bg: "rgba(34,197,94,0.1)",
        border: "#22c55e",
        color: "#22c55e",
        icono: "bi-check-circle",
      },
      error: {
        bg: "rgba(239,68,68,0.1)",
        border: "#ef4444",
        color: "#ef4444",
        icono: "bi-x-circle",
      },
    };
    const c = colores[tipo] || colores.error;

    const div = document.createElement("div");
    div.style.cssText = `
        position:fixed; top:20px; right:20px; z-index:9999;
        background:${c.bg}; border:1px solid ${c.border};
        color:${c.color}; border-radius:10px;
        padding:14px 20px; font-size:13px;
        display:flex; align-items:center; gap:10px;
        max-width:350px; animation: fadeIn .3s ease;
    `;
    div.innerHTML = `<i class="bi ${c.icono}" style="font-size:18px;"></i><span>${mensaje}</span>`;
    document.body.appendChild(div);

    setTimeout(() => div.remove(), 4000);
  }

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
