// Función global: Redirige a la página que muestra toda la información de un pedido.
window.verDetalle = (id) => {
  window.location.href = `${base_url}cliente/detalle_requerimiento/${id}`;
};
// Función global: Redirige a la línea de tiempo o historial de estados del pedido.
window.verSeguimiento = (id) => {
  window.location.href = `${base_url}cliente/seguimiento/${id}`;
};

document.addEventListener("DOMContentLoaded", function () {
  const tablaPedidosBody = document.getElementById("content-pedidos"); // Cuerpo de la tabla para inserccion de Requerimientos
  const inputBuscador = document.getElementById("buscador"); //Inpur de Busqueda de Requerimientos
  const listaServicios = document.getElementById("lista-servicios"); // Lista donde se Renderizaran los Servicios de la Agencia
  const modalNuevoPedidoEl = document.getElementById("modal-nuevo-pedido"); // Referencia al 1er Modal (Eleccion Servicios)
  const modalFormularioDetalleEl = document.getElementById("modal-formulario-detalle"); // Referencia 2do Modal (Formulario Wizard)
  const selectMateriales = document.getElementById("select-materiales"); // Selector de Materiales (SI/NO)
  const inputArchivos = document.getElementById("input-archivos"); // Input para la Carga de Archivos
  const areaSubidaArchivos = document.getElementById("area-subida-archivos"); // Area Visual de la Carga de Archivos
  const listaArchivos = document.getElementById("lista-archivos"); // Contenedor donde se Renderizan los Archivos Subidos
  const selectTipoReq = document.getElementById("tipo_req"); // Select para mostrar los Tipos de Requerimiento
  const btnAtras = document.getElementById("btn-atras"); // Boton Atras para el Formulario
  const btnSiguiente = document.getElementById("btn-siguiente"); // Boton Siguiente para el Formulario
  const formNuevoPedido = document.getElementById("form-nuevo-pedido"); // Form que envuelve todo los datos de los pedidos

  // Listado de canales donde se puede difundir el diseño solicitado.
  const CANALES = [
    "Por correo", "Página web", "Redes sociales", "SIGU o Aula Virtual Estudiantes",
    "SIGU o Aula Virtual Docentes", "Impresión física de folletos", "Banner físico",
    "Letreros", "Merch para eventos específicos",
  ];
  // Opciones de formato que aparecen según el tipo de servicio seleccionado.
  const FORMATOS = {
    // Para Diseño Gráfico (ID 1)
    1: ["Emailing", "Post Facebook/IG", "Historia FB/IG", "Historia WhatsApp", "Post LinkedIn", "SIGU", "Aula Virtual", "Wallpaper", "Banner Web", "Volante A5", "Afiche A4/A3", "Credenciales", "Banner 2x1", "Tarjeta Personal", "Tríptico", "Díptico", "Folder", "Brochure", "Cartilla", "Banderola", "Módulos", "SMS", "IVR", "Marcos Selfie", "Boletín", "Guías", "Imagen JPG/PNG", "Otros"],
    // Para Audiovisual/Media (ID 2)
    2: ["Reels FB/IG", "Historia FB/IG", "Reel/TikTok", "Reels LinkedIn", "Historia WhatsApp", "Video YouTube", "SIGU", "Aula Virtual", "Pantallas LED", "Spot TV", "Videos eventos", "Reels Pauta", "Otros"],
    // Para otros servicios o personalizado (ID 0)
    0: ["Post FB/IG", "Historia FB/IG", "Historia WhatsApp", "Reels FB/IG", "Reel/TikTok", "Video YouTube", "Afiche A4/A3", "Banner Web", "Spot TV", "Banner físico", "Emailing", "Imagen JPG/PNG", "Otros"],
  };
  // Tiempos de entrega mínimos para servicios de Diseño.
  const TIPOS_DISENO = {
    adaptacion: { label: "Adaptación de Arte", dias: 2 },
    creacion: { label: "Creación de Arte", dias: 4 },
    creacion_editorial: { label: "Creación de editorial", dias: 7 },
    adaptacion_editorial: { label: "Adaptación de editorial", dias: 7 },
  };
  // Tiempos de entrega mínimos para servicios Audiovisuales.
  const TIPOS_AUDIOVISUAL = {
    adaptacion: { label: "Adaptación de Arte", dias: 2 },
    creacion: { label: "Creación de Arte", dias: 4 },
    creacion_video: { label: "Creación de Vídeos", dias: 7 },
    creacion_editorial: { label: "Creación de editorial", dias: 7 },
    adaptacion_editorial: { label: "Adaptación de editorial", dias: 7 },
  };
  // Traducción de las llaves internas a nombres amigables para el servidor.
  const MAPA_TIPOS = {
    adaptacion: "Adaptación de Arte",
    creacion: "Creación de Arte",
    creacion_editorial: "Creación de editorial",
    adaptacion_editorial: "Adaptación de editorial",
    creacion_video: "Creación de Videos",
  };
  // Definición rápida de días para validaciones matemáticas.
  const DIAS_HABILES_POR_TIPO = {
    adaptacion: 2, creacion: 4, creacion_editorial: 7, adaptacion_editorial: 7, creacion_video: 7,
  };
  // Información detallada que se muestra al usuario al elegir la complejidad.
  const INFO_TIPOS = {
    adaptacion: { titulo: "Adaptación de Arte", desc: "Ajustes menores o cambios de formato a un diseño existente.", dias: "2", equipo: "Diseñador Gráfico" },
    creacion: { titulo: "Creación de Arte", desc: "Diseño original desde cero para piezas visuales publicitarias.", dias: "4", equipo: "Diseñador + Director de Arte" },
    creacion_editorial: { titulo: "Creación de Editorial", desc: "Maquetación de revistas, boletines o guías de múltiples páginas.", dias: "7", equipo: "Equipo Editorial" },
    adaptacion_editorial: { titulo: "Adaptación de Editorial", desc: "Ajustes a publicaciones editoriales ya existentes.", dias: "7", equipo: "Equipo Editorial" },
    creacion_video: { titulo: "Creación de Video", desc: "Producción audiovisual completa (guion, edición, post-producción).", dias: "7", equipo: "Productor + Editor de Video" },
  };
  // Configuración de nombres y estilos CSS según el estado del pedido.
  const MAPA_ESTADOS = {
    pendiente_sin_asignar: { texto: "Por Aprobar", clase: "estado-por_aprobar" },
    pendiente_asignado: { texto: "Asignado", clase: "estado-pendiente_asignado" },
    en_proceso: { texto: "En Proceso", clase: "estado-en_proceso" },
    en_revision: { texto: "En Revisión", clase: "estado-en_revision" },
    finalizado: { texto: "Finalizado", clase: "estado-completado" },
    cancelado: { texto: "Cancelado", clase: "estado-cancelado" },
  };
  // Configuración de nombres y estilos CSS según la prioridad.
  const MAPA_PRIORIDADES = {
    Baja: { clase: "prio-baja", etiqueta: "Baja" },
    Media: { clase: "prio-media", etiqueta: "Media" },
    Alta: { clase: "prio-alta", etiqueta: "Alta" },
  };

  /* VARIABLES DE ESTADO DE CONTROL */
  let pasoActual = 1;                   // Variable numérica para saber si estamos en el paso 1, 2 o 3.
  let archivosSeleccionados = [];       // Array que guarda los objetos 'File' seleccionados por el usuario.
  let temporizadorBusqueda;             // Variable para manejar el retraso de la búsqueda
  let nombreServicioSeleccionado = "";  // Guarda el nombre del servicio que el usuario clicó primero.

  /* Funciones de Ayuda */
  const qs = (selector) => document.querySelector(selector);        // Función corta para buscar un solo elemento en el HTML.
  const qsAll = (selector) => document.querySelectorAll(selector);  // Función corta para buscar múltiples elementos (ej. todos los checkboxes).
  const getVal = (selector) => qs(selector)?.value?.trim() || "";   // Obtiene el valor de un campo de texto y le quita los espacios sobrantes.
  const getIdVal = (id) => document.getElementById(id)?.value?.trim() || "";  // Obtiene el valor de un elemento buscando directamente por su ID.
  // Crea una lista con los valores de todos los checkboxes marcados.
  const checkedVals = (name) => Array.from(qsAll(`input[name="${name}"]:checked`)).map((c) => c.value);
  // Limpia un texto (quita tildes, mayúsculas y espacios) para comparar nombres de servicios.
  const normalizarTexto = (texto = "") => texto.toString().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase();
  // Verifica si el servicio es "Creación de Contenido" para aplicar reglas más flexibles.
  const esServicioConsultivo = (nombre) => normalizarTexto(nombre) === "creacion de contenido";
  // Retorna el nombre de la clase del icono de Bootstrap según la extensión del archivo.
  const getIconoArchivo = (mimeType, fileName) => {
    if (mimeType?.startsWith("image/")) return "bi-file-earmark-image";
    if (mimeType?.startsWith("video/")) return "bi-file-earmark-play";
    if (mimeType?.includes("pdf")) return "bi-file-earmark-pdf";
    if (mimeType?.includes("word") || fileName?.endsWith(".doc") || fileName?.endsWith(".docx")) return "bi-file-earmark-word";
    if (mimeType?.includes("excel") || fileName?.endsWith(".xls") || fileName?.endsWith(".xlsx")) return "bi-file-earmark-excel";
    return "bi-file-earmark";
  };
  // Configuración de colores y fondo para las alertas de SweetAlert2 (modo oscuro).
  const swalBase = { background: "#1a1a1a", color: "#f0f0f0", confirmButtonColor: "#f5c400" };


  // Función asíncrona que descarga los servicios desde la base de datos y los pinta.
  async function cargarServicios() {
    if (!listaServicios) return;
    // Circulo de Carga al Esperar los Datos (Servicios)
    listaServicios.innerHTML = '<div class="text-center p-4"><span class="spinner-border text-warning"></span></div>';
    try {
      // Petición al controlador
      const response = await fetch(`${base_url}cliente/nuevo-pedido/servicios`);
      const datos = await response.json();

      // Construimos el HTML de las cards recorriendo los datos recibidos.
      let html = datos.map(s => `
        <div class="servicio-card" data-id="${s.id}" data-nombre="${s.nombre}">
          <div class="servicio-card-info">
            <p class="servicio-card-nombre">${s.nombre}</p>
            <p class="servicio-card-desc">${s.descripcion || ""}</p>
          </div>
          <i class="bi bi-arrow-right servicio-card-arrow"></i>
        </div>
      `).join("");

      // Agregamos manualmente la opción "Personalizado" al final de la lista.
      html += `
        <div class="servicio-card servicio-personalizado" data-id="0" data-nombre="Personalizado">
          <div class="servicio-card-info">
            <p class="servicio-card-nombre">Servicio Personalizado</p>
            <p class="servicio-card-desc">¿No encuentras lo que buscas? Cuéntanos tu idea aquí.</p>
          </div>
          <i class="bi bi-arrow-right servicio-card-arrow"></i>
        </div>`;

      listaServicios.innerHTML = html;
    } catch (error) {
      console.error("Error cargando servicios:", error);
    }
  }

  // Función que obtiene la lista de pedidos del usuario y actualiza toda la interfaz.
  async function obtenerPedidos() {
    if (!tablaPedidosBody) { return };
    try {
      const response = await fetch(`${base_url}cliente/pedidos/listar`);
      const datos = await response.json();

      // Actualizamos los números grandes de resumen (Métricas).
      document.getElementById("cnt-total").textContent = datos.length;
      document.getElementById("cnt-por-aprobar").textContent = datos.filter(p => p.estado === "pendiente_sin_asignar").length;
      document.getElementById("cnt-en-proceso").textContent = datos.filter(p => ["pendiente_asignado", "en_proceso", "en_revision"].includes(p.estado)).length;
      document.getElementById("cnt-completado").textContent = datos.filter(p => p.estado === "finalizado").length;

      // Si la base de datos devuelve cero resultados.
      if (datos.length === 0) {
        tablaPedidosBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted p-4">Sin pedidos registrados</td></tr>`;
        return;
      }

      const total = datos.length;
      // Generamos las filas de la tabla
      tablaPedidosBody.innerHTML = datos.map((pedido, index) => {
        const num = total - index; // Genera el #3, #2, #1 en orden.
        const cfgEstado = MAPA_ESTADOS[pedido.estado] || { texto: pedido.estado, clase: "" };
        const cfgPrioridad = MAPA_PRIORIDADES[pedido.prioridad] || { clase: "", etiqueta: pedido.prioridad };
        const servicio = pedido.servicio_personalizado || pedido.servicio;

        return `
          <tr>
            <td class="fw-bold text-light">#${num}</td>
            <td><span class="fw-semibold fs-6">${pedido.titulo || "Sin título"}</span></td>
            <td>${servicio}</td>
            <td><span class="badge-estado ${cfgEstado.clase}">${cfgEstado.texto.toUpperCase()}</span></td>
            <td><span class="badge-prio ${cfgPrioridad.clase}">${cfgPrioridad.etiqueta}</span></td>
            <td class="text-light small">${pedido.fechacreacion.substring(0, 10)}</td>
            <td>
              <div class="d-flex gap-2">
                <button onclick="verDetalle(${pedido.idrequerimiento})" class="btn-ver" title="Ver Detalle"><i class="bi bi-eye"></i></button>
                <button onclick="verSeguimiento(${pedido.idrequerimiento})" class="btn-ver" title="Seguimiento"><i class="bi bi-clock-history text-success"></i></button>
              </div>
            </td>
          </tr>`;
      }).join("");
    } catch (error) {
      console.error("Error obteniendo pedidos:", error);
    }
  }

  /* FORMULARIO WIZARD */
  // Función que calcula qué fecha es permitida según los días de trabajo (ignora Sábados y Domingos).
  const calcularFechaMinima = (dias) => {
    const fecha = new Date();
    let cont = 0;
    while (cont < dias) {
      fecha.setDate(fecha.getDate() + 1);
      // Solo contamos el día si es de Lunes (1) a Viernes (5).
      if (fecha.getDay() !== 0 && fecha.getDay() !== 6) cont++;
    }
    // Añadimos un día de gracia para que no sea tan ajustado.
    fecha.setDate(fecha.getDate() + 1);
    return fecha;
  };

  // Activa o desactiva la obligatoriedad de campos si el servicio es "Creación de Contenido".
  const aplicarModoConsultivo = (activo) => {
    const camposOpcionales = [qs('[name="objetivo"]'), selectTipoReq, qs('[name="publico"]')];
    camposOpcionales.forEach(c => {
      if (!c) { return };
      // Si es activo (Modo Flexible), quitamos el 'required'; si no, lo ponemos.
      activo ? c.removeAttribute("required") : c.setAttribute("required", "required");
    });

    // Muestra u oculta los cuadros azules de aviso de "Modo Flexible".
    qsAll(".modo-flexible-aviso").forEach(aviso => {
      aviso.classList.toggle("d-block", activo);
      aviso.classList.toggle("d-none", !activo);
    });
  };

  // Se activa al hacer clic en un servicio (ej: Diseño). Prepara todo para el Paso 1.
  const elegirServicio = (id, nombre) => {
    nombreServicioSeleccionado = nombre || "Personalizado";

    document.getElementById("form-idservicio").value = id; // Guardamos el ID del servicio para Envio Final
    document.getElementById("wbadge-container").textContent = nombreServicioSeleccionado; // Colocamos el Servicio en una Etiqueta para el Modal
    // Si eligió 'Personalizado', mostramos el campo para que escriba qué servicio es.
    document.getElementById("contenedor-nombre-personalizado").classList.toggle("d-none", id != 0);

    // Verificamos si este servicio específico activa el modo de campos opcionales.
    aplicarModoConsultivo(esServicioConsultivo(nombreServicioSeleccionado));

    // Llenamos la lista de canales de difusión (checks) con la constante de arriba.
    const cc = document.getElementById("canales-checks");
    if (cc) cc.innerHTML = CANALES.map(op => `
      <label class="check-item">
        <input type="checkbox" name="canales[]" value="${op}">
        <span>${op}</span>
      </label>`).join("");

    // Llenamos la lista de formatos (checks) según el servicio que eligió.
    const cf = document.getElementById("formatos-checks");
    const listaFormatos = FORMATOS[id] || FORMATOS[0];
    if (cf) cf.innerHTML = listaFormatos.map(op => `
      <label class="check-item">
        <input type="checkbox" name="formatos[]" value="${op}">
        <span>${op}</span>
      </label>`).join("");

    // Cargamos las opciones de complejidad (Adaptación, Creación) en el selector.
    selectTipoReq.innerHTML = '<option value="" selected disabled>Seleccionar...</option>' +
      Object.entries(id == "2" ? TIPOS_AUDIOVISUAL : TIPOS_DISENO)
        .map(([k, v]) => `<option value="${k}">${v.label} — ${v.dias} días hábiles</option>`).join("");

    // Ocultamos el cuadro de info técnica por si estaba abierto de antes.
    document.getElementById("info-tipo-container").classList.add("d-none");

    // Cerramos el primer modal y abrimos el formulario en el Paso 1.
    bootstrap.Modal.getInstance(modalNuevoPedidoEl)?.hide();
    irAlPaso(1);
    new bootstrap.Modal(modalFormularioDetalleEl).show();
  };

  // Cambia la visibilidad de las secciones del formulario para simular pasos.
  function irAlPaso(paso) {
    pasoActual = paso;
    // Escondemos todas las secciones primero.
    qsAll(".wizard-section").forEach(s => s.classList.add("d-none"));
    // Quitamos el color amarillo de todos los números de pasos.
    qsAll(".step").forEach(i => i.classList.remove("active"));

    // Mostramos solo la sección que corresponde al paso actual.
    document.getElementById(`section-${paso}`)?.classList.remove("d-none");
    // Ponemos en amarillo el número del paso actual en la parte superior.
    document.getElementById(`step-${paso}-indicador`)?.classList.add("active");

    // Cambiamos el título del modal según el paso.
    const titulos = ["Info básica", "Detalles y formatos", "Confirmar y enviar"];
    document.getElementById("form-titulo-servicio").textContent = `Paso ${paso}: ${titulos[paso - 1]}`;

    // Si estamos en el paso 1, el botón 'Atrás' no debe existir.
    btnAtras.classList.toggle("d-none", paso === 1);
    // En el paso 3, el botón 'Siguiente' cambia por el de 'Enviar'.
    document.getElementById("btn-siguiente").classList.toggle("d-none", paso === 3);
    document.getElementById("btn-enviar").classList.toggle("d-none", paso !== 3);
  }

  // Revisa que no falte nada antes de pasar de página.
  function validarYPasar() {
    const seccion = document.getElementById(`section-${pasoActual}`);
    const errores = []; // Aquí acumularemos los mensajes de error.

    // Buscamos todos los campos marcados como obligatorios en la sección actual.
    seccion.querySelectorAll("[required]").forEach(input => {
      // Ignoramos campos que están ocultos (como los de modo flexible).
      if (input.offsetParent === null) return;
      const esValido = input.value.trim() !== "";
      // Si está vacío, le ponemos un borde rojo de Bootstrap.
      input.classList.toggle("is-invalid", !esValido);
    });

    /* VALIDACIONES PARA LOS PASOS */
    // Validaciones específicas del PASO 1 (Identificación).
    if (pasoActual === 1) {
      const titulo = getIdVal("campo-titulo");
      if (!titulo) errores.push("El título del requerimiento es obligatorio.");

      const idServ = getIdVal("form-idservicio");
      if (idServ === "0" && !getIdVal("titulo_personalizado")) errores.push("El nombre del servicio personalizado es obligatorio.");

      const fechaEnt = getVal('[name="fecha_entrega"]');
      const tipo = getVal('[name="tipo_requerimiento"]');
      const modoConsultivo = esServicioConsultivo(nombreServicioSeleccionado);

      // Si NO estamos en modo flexible, el objetivo y tipo son obligatorios.
      if (!modoConsultivo && !getVal('[name="objetivo"]')) errores.push("El objetivo de comunicación es obligatorio.");
      if (!modoConsultivo && !tipo) errores.push("El tipo de requerimiento es obligatorio.");

      // Validación de fecha contra días hábiles.
      if (fechaEnt) {
        const diasMin = DIAS_HABILES_POR_TIPO[tipo] || 2;
        const fechaMin = calcularFechaMinima(diasMin);
        const fechaIng = new Date(fechaEnt + "T00:00:00");
        fechaMin.setHours(0, 0, 0, 0);
        fechaIng.setHours(0, 0, 0, 0);

        // Si la fecha elegida es menor a la permitida por contrato.
        if (fechaIng < fechaMin) errores.push(`La fecha mínima para este servicio es de ${diasMin} días hábiles.`);
        // Si eligió Sábado o Domingo.
        if (fechaIng.getDay() === 0 || fechaIng.getDay() === 6) errores.push("No se permiten entregas en fines de semana.");
      } else {
        errores.push("La fecha de entrega es obligatoria.");
      }
    }

    // Validaciones específicas del PASO 2 (Detalles).
    if (pasoActual === 2) {
      if (!getVal('[name="descripcion"]')) errores.push("La descripción detallada es obligatoria.");

      if (!esServicioConsultivo(nombreServicioSeleccionado)) {
        if (!getVal('[name="publico"]')) errores.push("El público objetivo es obligatorio.");
        if (checkedVals("canales[]").length === 0) errores.push("Selecciona al menos un canal de difusión.");
        if (checkedVals("formatos[]").length === 0) errores.push("Selecciona al menos un formato.");
      }

      // Validación de materiales de referencia.
      if (!selectMateriales.value) {
        errores.push("Debe seleccionar si cuenta con materiales de referencia.");
      } else if (selectMateriales.value === "1") {
        // Si dijo que sí tiene, debe haber subido un archivo O puesto un link de Drive/Wetransfer.
        if (!inputArchivos.files.length && !getVal('[name="url_referencia"]')) {
          errores.push("Debe subir un archivo o proporcionar una URL de referencia.");
        }
      }

      // Validación de seguridad de la URL.
      const urlVal = getVal('[name="url_referencia"]');
      if (urlVal) {
        const urlRegex = /^(https?:\/\/)/i; // Debe empezar con http:// o https://
        if (!urlRegex.test(urlVal)) {
          errores.push("Debe ingresar una URL válida para la referencia (ej: https://drive.google.com/...)");
          qs('[name="url_referencia"]')?.classList.add("is-invalid");
        } else {
          qs('[name="url_referencia"]')?.classList.remove("is-invalid");
        }
      }
    }

    // Si acumulamos errores, mostramos la alerta y detenemos el Wizard.
    if (errores.length > 0) {
      Swal.fire({ ...swalBase, title: "Por favor corrija los siguientes errores:", icon: "warning", html: `<ul style="text-align:left;">${errores.map(e => `<li>${e}</li>`).join("")}</ul>` });
      return;
    }

    // Si todo está bien y vamos al paso final, preparamos el resumen visual.
    if (pasoActual === 2) generarResumen();
    // Avanzamos a la siguiente sección.
    irAlPaso(pasoActual + 1);
  }

  // Toma los datos de los inputs y los escribe en los campos de texto del Paso 3.
  function generarResumen() {
    // Función interna rápida para poner texto en un elemento ID.
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || "—"; };

    set("res-titulo", getIdVal("campo-titulo"));
    set("res-servicio", nombreServicioSeleccionado);
    set("res-descripcion", getVal('[name="descripcion"]'));
    set("res-publico", getVal('[name="publico"]'));

    // Formateamos la fecha a algo legible: ej "04 de mayo de 2026".
    const fv = getVal('[name="fecha_entrega"]');
    if (fv) set("res-fecha", new Date(fv + "T00:00:00").toLocaleDateString("es-PE", { day: '2-digit', month: 'long', year: 'numeric' }));

    // Colocamos el color de la prioridad en el resumen.
    const prio = qs('input[name="prioridad"]:checked')?.value || "Media";
    const resPrio = document.getElementById("res-prioridad");
    if (resPrio) {
      resPrio.textContent = prio;
      resPrio.className = `resumen-badge prio-${prio.toLowerCase()}`;
    }

    // Resumen visual de materiales adjuntos.
    const tieneMat = selectMateriales.value === "1";
    document.getElementById("res-materiales").innerHTML = tieneMat ? '<span class="text-success">✓ Sí, incluye materiales</span>' : '<span class="text-muted">No incluye materiales</span>';
    // Mostramos el link solo si el usuario escribió uno.
    document.getElementById("res-link-wrap").classList.toggle("d-none", !getVal('[name="url_referencia"]'));
    set("res-link", getVal('[name="url_referencia"]'));
  }

  /* EVENTOS */
  // Detecta el clic en cualquier card de servicio para iniciar el Wizard.
  listaServicios?.addEventListener("click", (e) => {
    const card = e.target.closest(".servicio-card");
    if (card) elegirServicio(card.dataset.id, card.dataset.nombre);
  });

  // Al cambiar EL Tipo Req, actualizamos la descripción técnica abajo del select.
  selectTipoReq?.addEventListener("change", (e) => {
    const tipo = e.target.value;
    const info = INFO_TIPOS[tipo];
    if (!info) return;

    // Actualizamos el cuadro informativo de días y equipo.
    document.getElementById("info-tipo-titulo").textContent = info.titulo;
    document.getElementById("info-tipo-desc").textContent = info.desc;
    document.getElementById("info-tipo-dias").textContent = info.dias;
    document.getElementById("info-tipo-equipo").textContent = info.equipo;
    document.getElementById("info-tipo-container").classList.remove("d-none");

    // Ponemos automáticamente la fecha sugerida según los días hábiles del tipo elegido.
    const fechaMin = calcularFechaMinima(parseInt(info.dias));
    qs('[name="fecha_entrega"]').value = fechaMin.toISOString().split('T')[0];
  });

  // El buscador filtra las filas de la tabla ocultando las que no coinciden con lo escrito.
  inputBuscador?.addEventListener("input", () => {
    clearTimeout(temporizadorBusqueda); // Limpia el tiempo anterior.
    // Esperamos tras dejar de escribir para no saturar el navegador.
    temporizadorBusqueda = setTimeout(() => {
      const query = normalizarTexto(inputBuscador.value);
      qsAll("#tablaPedidos tbody tr").forEach(fila => {
        const match = fila.textContent.toLowerCase().includes(query);
        fila.style.display = match ? "" : "none";
      });
    }, 1000);
  });

  // Al hacer clic en el área de archivos, simulamos un clic en el input invisible.
  areaSubidaArchivos?.addEventListener("click", () => inputArchivos.click());

  // Al elegir archivos, actualizamos la lista visual debajo del área de subida.
  inputArchivos?.addEventListener("change", (e) => {
    const files = Array.from(e.target.files);
    archivosSeleccionados = files; // Guardamos en memoria.
    listaArchivos.innerHTML = files.map(f => `
      <div class="d-flex align-items-center gap-2 p-2 border border-secondary rounded mb-2 bg-dark">
        <i class="bi ${getIconoArchivo(f.type, f.name)} text-warning fs-4"></i>
        <span class="small text-truncate">${f.name}</span>
      </div>`).join("");
  });

  // Oculta o muestra el panel de subida según el selector de materiales (SÍ/NO).
  selectMateriales?.addEventListener("change", (e) => {
    document.getElementById("contenedor-materiales").classList.toggle("d-none", e.target.value !== "1");
  });

  // Acciones de los botones de navegación.
  btnSiguiente?.addEventListener("click", validarYPasar);
  btnAtras?.addEventListener("click", () => irAlPaso(pasoActual - 1));

  // ENVÍO FINAL
  formNuevoPedido?.addEventListener("submit", async (e) => {
    e.preventDefault(); // Evita que la página se recargue.

    const btnEnv = document.getElementById("btn-enviar");
    btnEnv.disabled = true; // Bloqueamos para evitar doble clic.
    const textoOriginal = btnEnv.innerHTML;
    btnEnv.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

    const modoConsultivo = esServicioConsultivo(nombreServicioSeleccionado);
    const idServ = getIdVal("form-idservicio");
    const tipoReq = getVal('[name="tipo_requerimiento"]');

    fd.append("servicio_ui_nombre", nombreServicioSeleccionado || "");
    fd.append("objetivo_comunicacion", objetivo);
    fd.append("descripcion", descripcion);
    fd.append("tipo_requerimiento", modoConsultivo ? (MAPA_TIPOS[tipoReq] || tipoReq || "") : (MAPA_TIPOS[tipoReq] || tipoReq));
    fd.append("publico_objetivo", publico);
    fd.append("canales_difusion", JSON.stringify(canales));
    fd.append("formatos_solicitados", JSON.stringify(formatos));

    fd.append("fecharequerida", getVal('[name="fecha_entrega"]'));
    fd.append("prioridad", qs('input[name="prioridad"]:checked')?.value || "Media");
    fd.append("tiene_materiales", selectMateriales.value || "0");

    const fo = getVal('[name="formato_otros"]');
    if (!modoConsultivo && fo) fd.append("formato_otros", fo);

    const url = getVal('[name="url_referencia"]');
    if (url) fd.append("url_subida", url);

    // Si hay archivos en memoria, los adjuntamos uno por uno.
    if (archivosSeleccionados.length) {
      archivosSeleccionados.forEach(f => fd.append("documentos[]", f));
    }

    try {
      // Envío por POST al controlador de requerimientos.
      const response = await fetch(`${base_url}cliente/requerimiento/guardar`, { method: "POST", body: fd });
      const data = await response.json();

      if (data.status === "success") {
        // Cerramos el modal y limpiamos todo.
        bootstrap.Modal.getInstance(modalFormularioDetalleEl)?.hide();
        Swal.fire({ ...swalBase, icon: "success", title: "¡Enviado!", text: "Tu pedido ha sido registrado correctamente." });
        obtenerPedidos(); // Refrescamos la tabla de fondo sin recargar.
        formNuevoPedido.reset();
        archivosSeleccionados = [];
        listaArchivos.innerHTML = "";
      } else {
        // Si hay errores de validación en el servidor
        let msg = data.msg || "Error al enviar";
        if (data.errores?.length) msg += ":<br>• " + data.errores.join("<br>• ");
        Swal.fire({ ...swalBase, icon: "error", title: "Error", html: msg });
      }
    } catch (err) {
      console.error(err);
      Swal.fire({ ...swalBase, icon: "error", title: "Error de red", text: "No se pudo conectar con el servidor." });
    } finally {
      btnEnv.disabled = false;
      btnEnv.innerHTML = textoOriginal;
    }
  });
  obtenerPedidos(); // Carga la tabla inicial de pedidos.
  modalNuevoPedidoEl?.addEventListener("shown.bs.modal", cargarServicios); // Solo cargamos los servicios si el usuario abre el modal de 'Nuevo Pedido'.
});