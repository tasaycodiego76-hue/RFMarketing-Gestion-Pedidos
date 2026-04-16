document.addEventListener("DOMContentLoaded", function () {
  const tablaPedidos = document.getElementById("content-pedidos");        // Cuerpo de la tabla
  const inputBuscador = document.getElementById("buscador");              // Campo de búsqueda
  const listaServicios = document.getElementById("lista-servicios");      // Lista de servicios modal
  const modalNuevoPedido = document.getElementById("modal-nuevo-pedido"); // Modal principal
  const selectMateriales = document.getElementById("select-materiales");  // Select Sí/No materiales
  const inputArchivos = document.getElementById("input-archivos");        // Input file oculto
  const listaArchivos = document.getElementById("lista-archivos");        // Donde se muestran archivos

  // Canales disponibles para difusión
  const CANALES = [
    "Por correo", "Página web", "Redes sociales", "SIGU o Aula Virtual Estudiantes", "SIGU o Aula Virtual Docentes", 
    "Impresión física de folletos","Banner físico", "Letreros","Merch para eventos específicos",
  ];
  // Formatos según tipo de servicio (1=Diseño, 2=Audiovisual, 0=Personalizado)
  const FORMATOS = {
    1: [
      "Emailing","Post Facebook/IG","Historia FB/IG","Historia WhatsApp","Post LinkedIn","SIGU","Aula Virtual","Wallpaper","Banner Web","Volante A5",
      "Afiche A4/A3","Credenciales","Banner 2x1","Tarjeta Personal","Tríptico","Díptico","Folder","Brochure","Cartilla","Banderola",
      "Módulos","SMS","IVR","Marcos Selfie","Boletín","Guías","Imagen JPG/PNG","Otros",
    ],
    2: [
      "Reels FB/IG","Historia FB/IG","Reel/TikTok","Reels LinkedIn","Historia WhatsApp","Video YouTube",
      "SIGU","Aula Virtual","Pantallas LED","Spot TV","Videos eventos","Reels Pauta","Otros",
    ],
    0: [
      "Post FB/IG","Historia FB/IG","Historia WhatsApp","Reels FB/IG","Reel/TikTok","Video YouTube","Afiche A4/A3","Banner Web","Spot TV",
      "Banner físico","Emailing","Imagen JPG/PNG","Otros",
    ],
  };
  // Estilos visuales para cada tipo de servicio
  const ESTILOS_SERVICIO = {
    1: { label: "Diseño Gráfico", cls: "sd" },
    2: { label: "AudioVisual", cls: "sav" },
    0: { label: "Personalizado", cls: "sot" },
  };
  // Mapeo de tipos de requerimiento para la BD
  const MAPA_TIPOS = {
    adaptacion: "Adaptación de Arte", creacion: "Creación de Arte",
    editorial: "Trabajo editorial", audiovisual: "Creación de Videos",
  };
  // Días hábiles mínimos según tipo de requerimiento
  const DIAS_HABILES_POR_TIPO = {
    adaptacion: 7, creacion: 10,
    editorial: 20, audiovisual: 20,
  };
  // Info de tipos de requerimiento
  const INFO_TIPOS = {
    adaptacion: {
      titulo: "Adaptación de Arte",
      desc: "Tienes un diseño existente y necesitas adaptarlo a otros formatos o hacer ajustes menores. Ideal para redimensionar banners o ajustar textos.",
      dias: "7",
      equipo: "Diseñador Gráfico",
    },
    creacion: {
      titulo: "Creación de Arte",
      desc: "Creación de piezas visuales desde cero siguiendo tu marca y objetivos. Incluye diseño original de banners, posts, afiches y materiales promocionales.",
      dias: "10",
      equipo: "Diseñador + Director de Arte",
    },
    editorial: {
      titulo: "Trabajo Editorial",
      desc: "Publicaciones extensas como brochures, trípticos, cartillas o documentos de múltiples páginas que requieren maquetación profesional.",
      dias: "20",
      equipo: "Equipo Editorial",
    },
    audiovisual: {
      titulo: "Creación de Video",
      desc: "Producción audiovisual completa: guion, filmación, edición y post-producción. Para spots, reels, videos institucionales o tutoriales.",
      dias: "20",
      equipo: "Productor + Editor de Video",
    },
  };
  // Configuración visual de estados
  const MAPA_ESTADOS = {
    pendiente_sin_asignar: { texto: "Por Aprobar", clase: "estado-por_aprobar" },
    pendiente_asignado: { texto: "Asignado", clase: "estado-pendiente_asignado" },
    en_proceso: { texto: "En Proceso", clase: "estado-en_proceso" },
    en_revision: { texto: "En Revisión", clase: "estado-en_revision" },
    finalizado: { texto: "Finalizado", clase: "estado-completado" },
    cancelado: { texto: "Cancelado", clase: "estado-cancelado" },
  };
  // Configuración visual de prioridades
  const MAPA_PRIORIDADES = {
    Baja: { clase: "prio-baja", etiqueta: "Baja" },
    Media: { clase: "prio-media", etiqueta: "Media" },
    Alta: { clase: "prio-alta", etiqueta: "Alta" },
  };

  // Variables Globales
  let pasoActual = 1;                 // Controla en qué paso del wizard está
  let archivosSeleccionados = [];     // Array para guardar archivos antes de enviar
  let temporizadorBusqueda;           // Para el debounce del buscador (Temporizador)

  // HELPERS (funciones de ayuda/reutilizables)
  const qs = (selector) => document.querySelector(selector);          // Función: buscar el primer elemento que coincida con el selector
  const qsAll = (selector) => document.querySelectorAll(selector);    // Similar al anterior pero devuelve todos los elementos que coincidan
  const getVal = (selector) => qs(selector)?.value?.trim() || "";     // Obtiene valor limpio de un input, o vacío si no existe
  const getIdVal = (id) => document.getElementById(id)?.value?.trim() || "";    // Similar a línea 81 pero busca por ID
  const checkedVals = (name) => Array.from(qsAll(`input[name="${name}"]:checked`)).map((c) => c.value);   // Devuelve array con los valores de todos los checkboxes

  // Convierte bytes a formato legible (KB, MB, GB)
  const formatearTamano = (bytes) => {
    if (!bytes) return "0 Bytes";
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (
      parseFloat((bytes / Math.pow(1024, i)).toFixed(1)) + " " + ["Bytes", "KB", "MB", "GB"][i]
    );
  };

  // Retorna el icono Bootstrap según el tipo de archivo
  const getIconoArchivo = (mimeType, fileName) => {
    if (mimeType?.startsWith("image/")) return "bi-file-earmark-image";
    if (mimeType?.startsWith("video/")) return "bi-file-earmark-play";
    if (mimeType?.startsWith("audio/")) return "bi-file-earmark-music";
    if (mimeType?.includes("pdf")) return "bi-file-earmark-pdf";
    if ( mimeType?.includes("word") || fileName?.endsWith(".doc") || fileName?.endsWith(".docx") )
      return "bi-file-earmark-word";
    if ( mimeType?.includes("excel") || fileName?.endsWith(".xls") || fileName?.endsWith(".xlsx") )
      return "bi-file-earmark-excel";
    if (mimeType?.includes("powerpoint") || fileName?.endsWith(".ppt") || fileName?.endsWith(".pptx") )
      return "bi-file-earmark-ppt";
    if (mimeType?.includes("zip") || fileName?.endsWith(".zip") || fileName?.endsWith(".rar"))
      return "bi-file-earmark-zip";
    return "bi-file-earmark";
  };

  // SweetAlert2 - Configuración base
  const swalBase = {
    background: "#1a1a1a",
    color: "#f0f0f0",
    confirmButtonColor: "#f5c400",
  };

  // Muestra alerta tipo toast (esquina superior derecha)
  const mostrarAlerta = (tipo, mensaje) => {
    Swal.fire({
      ...swalBase,
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      icon: tipo,
      title: tipo === "success" ? "¡Éxito!" : "Error",
      text: mensaje,
      iconColor: tipo === "success" ? "#22c55e" : "#ef4444",
    });
  };

  // Muestra modal con lista de errores de validación
  const mostrarErrores = (errores) => {
    Swal.fire({
      ...swalBase,
      title: "Por favor corrija los siguientes errores:",
      html: `<ul style="list-style:none;padding:0;margin:10px 0;">${errores.map((e) => `<li style="margin-bottom:5px;text-align:left;">• ${e}</li>`).join("")}</ul>`,
      icon: "warning",
      confirmButtonText: "Entendido",
    });
  };

  // Vista Previa (Archivos)
  // Renderiza la lista de archivos seleccionados con preview
  function mostrarPreviewArchivos(files) {
    if (!listaArchivos){ return; }
    archivosSeleccionados = files;

    listaArchivos.innerHTML = `
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:15px;max-height:300px;overflow-y:auto;">
        ${files.map((file, index) => {
            // Si es imagen, muestra thumbnail; si no, muestra icono
            const iconoPreview = file.type.startsWith("image/")
              ? `<img src="${URL.createObjectURL(file)}" style="width:50px;height:50px;object-fit:cover;border-radius:6px;"/>`
              : `<div style="width:50px;height:50px;background:#1e1e1e;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:24px;color:#f5c400;">
                <i class="bi ${getIconoArchivo(file.type, file.name)}"></i>
               </div>`;
            return `
            <div style="display:flex;align-items:center;gap:12px;background:#111;border:1px solid #333;border-radius:8px;padding:10px;">
              ${iconoPreview}
              <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:#f0f0f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${file.name}">
                  ${file.name}
                </div>
                <div style="font-size:11px;color:#888;margin-top:4px;">
                  ${formatearTamano(file.size)} • ${file.type || "Desconocido"}
                </div>
              </div>
            </div>
          `;
          }).join("")}
      </div>
    `;
  }

  // Evento: cuando el usuario selecciona archivos
  if (inputArchivos) {
    inputArchivos.addEventListener("change", (e) => {
      const files = Array.from(e.target.files);
      if (files.length) mostrarPreviewArchivos(files);
      else if (listaArchivos) listaArchivos.innerHTML = "";
    });
  }

  // Cargar Servicios y Pedidos

  // Obtiene y muestra los servicios disponibles en el modal
  async function cargarServicios() {
    listaServicios.innerHTML = "";
    try {
      const response = await fetch(`${base_url}cliente/nuevo-pedido/servicios`);
      const datos = await response.json();

      // Genera cards de servicios
      listaServicios.innerHTML =
        datos
          .map(
            (s) => `
        <div class="servicio-card" onclick="elegirServicio(${s.id}, '${s.nombre}')">
          <div class="servicio-card-info">
            <p class="servicio-card-nombre">${s.nombre}</p>
            <p class="servicio-card-desc">${s.descripcion || ""}</p>
          </div>
          <i class="bi bi-arrow-right servicio-card-arrow"></i>
        </div>
      `,
          )
          .join("") +
        `
        <div class="servicio-card servicio-personalizado" onclick="elegirServicio(0)">
          <div class="servicio-card-info">
            <p class="servicio-card-nombre">Servicio Personalizado</p>
            <p class="servicio-card-desc">¿No encuentras lo que buscas? Cuéntanos tu idea aquí.</p>
          </div>
          <i class="bi bi-arrow-right servicio-card-arrow"></i>
        </div>
      `;
      listaServicios.style.display = "block";
    } catch (error) {
      console.error(error);
      listaServicios.innerHTML = `<p style="color:#555;text-align:center;">Error al cargar servicios</p>`;
      listaServicios.style.display = "block";
    }
  }

  // Obtiene y muestra los pedidos del cliente en la tabla
  async function obtenerPedidos() {
    try {
      const response = await fetch(`${base_url}cliente/pedidos/listar`);
      const datos = await response.json();

      // Actualiza contadores del dashboard
      document.getElementById("cnt-total").textContent = datos.length;
      document.getElementById("cnt-por-aprobar").textContent = datos.filter(
        (p) => p.estado === "pendiente_sin_asignar",
      ).length;
      document.getElementById("cnt-en-proceso").textContent = datos.filter(
        (p) =>
          ["pendiente_asignado", "en_proceso", "en_revision"].includes(
            p.estado,
          ),
      ).length;
      document.getElementById("cnt-completado").textContent = datos.filter(
        (p) => p.estado === "finalizado",
      ).length;

      // Sin pedidos
      if (datos.length === 0) {
        tablaPedidos.innerHTML = `<tr><td colspan="7" style="text-align:center;">Sin pedidos registrados</td></tr>`;
        return;
      }

      // Renderiza filas de la tabla
      const total = datos.length;
      tablaPedidos.innerHTML = datos
        .map((pedido, index) => {
          const num = total - index;
          const cfgEstado = MAPA_ESTADOS[pedido.estado] || {
            texto: pedido.estado,
            clase: "estado-default",
          };
          const cfgPrioridad = MAPA_PRIORIDADES[pedido.prioridad] || {
            clase: "prio-default",
            etiqueta: pedido.prioridad,
          };
          const servicio = pedido.servicio || pedido.servicio_personalizado;

          return `
          <tr data-numero="${pedido.idrequerimiento}">
            <td style="color:#555;font-size:11px;font-weight:bold;">#${num}</td>
            <td>${pedido.titulo ? `<span style="font-weight:600;font-size:13px;">${pedido.titulo}</span>` : `<span style="color:#777;font-style:italic;">Sin título</span>`}</td>
            <td>${servicio}</td>
            <td><span class="badge-estado ${cfgEstado.clase}">${cfgEstado.texto.toUpperCase()}</span></td>
            <td>${pedido.prioridad ? `<span class="badge-prio ${cfgPrioridad.clase}">${cfgPrioridad.etiqueta}</span>` : "—"}</td>
            <td style="color:#777;font-size:11px;">${pedido.fechacreacion?.substring(0, 10)}</td>
            <td>
              <button onclick="verDetalle(${pedido.idrequerimiento})" class="btn-ver" style="border:none;background:none;cursor:pointer;">
                <i class="bi bi-eye" style="color:#007bff;"></i>
              </button>
              <button onclick="verSeguimiento(${pedido.idrequerimiento})" class="btn-ver" style="border:none;background:none;cursor:pointer;margin-left:8px;">
                <i class="bi bi-clock-history" style="color:#28a745;"></i>
              </button>
            </td>
          </tr>
        `;
        })
        .join("");
    } catch (error) {
      console.error("Error al obtener pedidos:", error);
    }
  }

  // Inicializa
  modalNuevoPedido?.addEventListener("shown.bs.modal", cargarServicios);
  obtenerPedidos();

  // Buscador
  inputBuscador?.addEventListener("keyup", () => {
    clearTimeout(temporizadorBusqueda);
    temporizadorBusqueda = setTimeout(() => {
      const termino = inputBuscador.value.trim().toLowerCase();
      qsAll("#tablaPedidos tbody tr").forEach((fila) => {
        if (!termino) {
          fila.style.display = "";
          return;
        }
        const celdas = Array.from(fila.querySelectorAll("td")).map((td) =>
          td.textContent.toLowerCase(),
        );
        fila.style.display = celdas.some((c) => c.includes(termino))
          ? ""
          : "none";
      });
    }, 1000);
  });

  // Formulario en 3 Pasos (Wizard)

  // Genera HTML de checkboxes
  const generarChecks = (opciones, name, onchange) =>
    opciones
      .map(
        (op) =>
          `<label class="check-item"><input type="checkbox" name="${name}" value="${op}" onchange="${onchange}"><span>${op}</span></label>`,
      )
      .join("");

  // Calcula fecha mínima según días hábiles (sin fines de semana)
  const calcularFechaMinima = (dias) => {
    const fecha = new Date();
    let cont = 0;
    while (cont < dias) {
      fecha.setDate(fecha.getDate() + 1);
      if (fecha.getDay() !== 0 && fecha.getDay() !== 6) cont++;
    }
    return fecha;
  };

  // Abre el wizard cuando elige un servicio
  window.elegirServicio = (idServicio, nombreServicio) => {
    const modalForm = document.getElementById("modal-formulario-detalle");
    if (!modalForm) return;

    const estilo = ESTILOS_SERVICIO[idServicio] || ESTILOS_SERVICIO[0];
    const el = (id) => document.getElementById(id);
    const show = (id, visible) => {
      const e = el(id);
      if (e) e.style.display = visible ? "block" : "none";
    };

    // Actualiza UI con el servicio seleccionado
    el("wbadge-container") &&
      (el("wbadge-container").innerHTML =
        `<span class="sbadge ${estilo.cls}">${estilo.label}</span>`);
    el("form-idservicio") && (el("form-idservicio").value = idServicio);
    el("txt-servicio-seleccionado") &&
      (el("txt-servicio-seleccionado").innerText =
        nombreServicio || "Servicio Especial");

    // Muestra/oculta campos según si es personalizado
    show("contenedor-nombre-personalizado", idServicio === 0);
    show("requerimiento-libre", idServicio === 0);
    show("lista-requerimientos-estandar", true);

    // Genera checkboxes de canales y formatos
    const cc = el("canales-checks");
    if (cc)
      cc.innerHTML = generarChecks(
        CANALES,
        "canales[]",
        "limitarSeleccion(this, 'canales[]', 3)",
      );
    const cf = el("formatos-checks");
    if (cf)
      cf.innerHTML = generarChecks(
        FORMATOS[idServicio] ?? FORMATOS[0],
        "formatos[]",
        "toggleFormatoOtros(this)",
      );

    // Cierra modal de selección y abre wizard
    const modalSel = document.getElementById("modal-nuevo-pedido");
    if (modalSel) bootstrap.Modal.getInstance(modalSel)?.hide();
    irAlPaso(1);
    new bootstrap.Modal(modalForm).show();
  };

  // Limita selección de checkboxes (máximo N)
  window.limitarSeleccion = (checkbox, nombre, maximo) => {
    if (qsAll(`input[name="${nombre}"]:checked`).length > maximo) {
      checkbox.checked = false;
      checkbox.closest(".check-item").style.borderColor = "#ef4444";
      setTimeout(
        () => (checkbox.closest(".check-item").style.borderColor = ""),
        800,
      );
    }
  };

  // Muestra/oculta campo "Otros" en formatos
  window.toggleFormatoOtros = () => {
    const cont = document.getElementById("contenedor-formato-otros");
    if (cont)
      cont.style.display = qs('input[name="formatos[]"][value="Otros"]:checked')
        ? "block"
        : "none";
  };

  // Mostrar info del tipo seleccionado
  window.mostrarInfoTipo = (tipo) => {
    const container = document.getElementById("info-tipo-container");
    if (!container || !tipo) {
      if (container) container.style.display = "none";
      return;
    }

    const info = INFO_TIPOS[tipo];
    if (!info) return;

    document.getElementById("info-tipo-titulo").textContent = info.titulo;
    document.getElementById("info-tipo-desc").textContent = info.desc;
    document.getElementById("info-tipo-dias").textContent = info.dias;
    document.getElementById("info-tipo-equipo").textContent = info.equipo;

    container.style.display = "block";
  };

  // Fecha input - actualizar badge cuando se selecciona
  const fechaInput = document.getElementById("fecha_entrega_input");
  if (fechaInput) {
    fechaInput.addEventListener("change", (e) => {
      const badge = document.getElementById("fecha-badge");
      if (e.target.value) {
        badge.style.display = "flex";
      } else {
        badge.style.display = "none";
      }
    });
  }

  // Navega a un paso específico del wizard
  function irAlPaso(numeroPaso) {
    pasoActual = numeroPaso;
    const secciones = qsAll(".wizard-section");
    const indicadores = qsAll(".step");
    const btnAtras = document.getElementById("btn-atras");
    const btnSig = document.getElementById("btn-siguiente");
    const btnEnv = document.getElementById("btn-enviar");

    // Actualiza visibilidad de secciones
    secciones.forEach((s) => s.classList.add("d-none"));
    indicadores.forEach((i) => i.classList.remove("active"));
    document
      .getElementById(`section-${numeroPaso}`)
      ?.classList.remove("d-none");
    document
      .getElementById(`step-${numeroPaso}-indicador`)
      ?.classList.add("active");

    // Actualiza título
    const titulo = document.getElementById("form-titulo-servicio");
    if (titulo)
      titulo.innerText = `Paso ${numeroPaso}: ${["Info básica", "Detalles y formatos", "Confirmar y enviar"][numeroPaso - 1]}`;

    // Actualiza botones
    btnAtras?.classList.toggle("d-none", numeroPaso === 1);
    if (numeroPaso === 3) {
      btnSig?.classList.add("d-none");
      btnEnv?.classList.remove("d-none");
    } else {
      btnSig?.classList.remove("d-none");
      btnEnv?.classList.add("d-none");
    }
  }

  // Retrocede un paso
  window.retrocederPaso = () => {
    if (pasoActual > 1) irAlPaso(pasoActual - 1);
  };

  document
    .getElementById("btn-siguiente")
    ?.addEventListener("click", validarYPasar);

  // Validaciones en el Wizard (Reigstro de Requerimientos)
  function validarYPasar() {
    const seccion = document.getElementById(`section-${pasoActual}`);
    if (!seccion) return;
    const errores = [];

    // Valida campos required visibles
    let valido = true;
    seccion
      .querySelectorAll("input[required], select[required], textarea[required]")
      .forEach((input) => {
        if (input.offsetParent === null) return;
        if (!input.value.trim()) {
          input.classList.add("is-invalid");
          valido = false;
        } else input.classList.remove("is-invalid");
      });
    if (!valido) errores.push("Por favor completa todos los campos requeridos");

    // Validaciones específicas Paso 1
    if (pasoActual === 1) {
      if (
        getIdVal("form-idservicio") === "0" &&
        !getIdVal("titulo_personalizado")
      )
        errores.push("El nombre del servicio personalizado es obligatorio");

      // Valida fecha según tipo de requerimiento
      const tipoReq = getVal('[name="tipo_requerimiento"]');
      const fechaEnt = getVal('[name="fecha_entrega"]');
      if (tipoReq && fechaEnt) {
        const dias = DIAS_HABILES_POR_TIPO[tipoReq] || 2;
        const fechaMin = calcularFechaMinima(dias);
        const fechaIng = new Date(fechaEnt + "T00:00:00");
        fechaMin.setHours(0, 0, 0, 0);
        fechaIng.setHours(0, 0, 0, 0);
        if (fechaIng < fechaMin)
          errores.push(
            `La fecha debe ser al menos dentro de ${dias} días hábiles (sin contar sábados ni domingos) para "${MAPA_TIPOS[tipoReq]}"`,
          );
        if (fechaIng < new Date().setHours(0, 0, 0, 0)) {
          errores.push("La fecha no puede ser anterior a hoy");
          qs('[name="fecha_entrega"]')?.classList.add("is-invalid");
        }
      }
    }

    // Validaciones específicas Paso 2
    if (pasoActual === 2) {
      if (!getVal('[name="descripcion"]'))
        errores.push("La descripción es obligatoria");
      if (!getVal('[name="publico"]'))
        errores.push("El público objetivo es obligatorio");
      if (!qsAll('input[name="canales[]"]:checked').length)
        errores.push("Debe seleccionar al menos un canal de difusión");
      if (!qsAll('input[name="formatos[]"]:checked').length)
        errores.push("Debe seleccionar al menos un formato");
      if (
        qs('input[name="formatos[]"][value="Otros"]:checked') &&
        !getVal('[name="formato_otros"]')
      )
        errores.push("Debe especificar el formato deseado");
      if (
        document.getElementById("select-materiales")?.value === "1" &&
        !inputArchivos?.files?.length &&
        !getVal('[name="url_referencia"]')
      )
        errores.push("Debe subir un archivo o proporcionar URL de referencia");
      if (!errores.length) generarResumen();
    }

    if (errores.length) {
      mostrarErrores(errores);
      return;
    }
    irAlPaso(pasoActual + 1);
  }

  // Toggle de materiales (muestra/oculta zona de archivos)
  selectMateriales?.addEventListener("change", (e) => {
    const valor = e.target.value;
    const contM = document.getElementById("contenedor-materiales");
    const inputL = qs('[name="url_referencia"]');
    if (contM) contM.style.display = valor === "1" ? "block" : "none";
    if (valor === "0") {
      if (inputArchivos) inputArchivos.value = "";
      if (inputL) inputL.value = "";
      if (listaArchivos) listaArchivos.innerHTML = "";
      archivosSeleccionados = [];
    }
  });

  // Preview de archivos
  function mostrarPreviewArchivos(files) {
      if (!listaArchivos) return;
      archivosSeleccionados = files;

      if (files.length === 0) {
          listaArchivos.innerHTML = "";
          return;
      }

      listaArchivos.innerHTML = files.map((file, index) => `
          <div class="archivo-item-simple">
              <i class="bi ${getIconoArchivo(file.type, file.name)}"></i>
              <div class="archivo-info">
                  <div class="archivo-nombre" title="${file.name}">${file.name}</div>
                  <div class="archivo-size">${formatearTamano(file.size)}</div>
              </div>
              <button type="button" class="btn-eliminar" onclick="eliminarArchivo(${index})">
                  <i class="bi bi-trash"></i>
              </button>
          </div>
      `).join("");
  }

  // Envio del Formulario
  function armarFormData() {
    const fd = new FormData();
    const idServ = getIdVal("form-idservicio") || "0";

    fd.append("idservicio", idServ);
    if (idServ === "0") {
      const sp = getIdVal("titulo_personalizado");
      if (sp) fd.append("servicio_personalizado", sp);
    }
    fd.append("titulo", getIdVal("campo-titulo"));
    fd.append("objetivo_comunicacion", getVal('[name="objetivo"]'));
    fd.append("descripcion", getVal('[name="descripcion"]'));
    fd.append(
      "tipo_requerimiento",
      MAPA_TIPOS[getVal('[name="tipo_requerimiento"]')] ||
        getVal('[name="tipo_requerimiento"]'),
    );
    fd.append("fecharequerida", getVal('[name="fecha_entrega"]'));
    fd.append(
      "prioridad",
      qs('input[name="prioridad"]:checked')?.value || "Media",
    );
    fd.append("publico_objetivo", getVal('[name="publico"]'));
    fd.append("canales_difusion", JSON.stringify(checkedVals("canales[]")));
    fd.append(
      "formatos_solicitados",
      JSON.stringify(checkedVals("formatos[]")),
    );
    fd.append(
      "tiene_materiales",
      document.getElementById("select-materiales")?.value || "0",
    );
    const fo = getVal('[name="formato_otros"]');
    if (fo) fd.append("formato_otros", fo);
    const url = getVal('[name="url_referencia"]');
    if (url) fd.append("url_subida", url);

    // Agrega archivos (del array actualizado o del input)
    if (archivosSeleccionados.length)
      archivosSeleccionados.forEach((f) => fd.append("documentos[]", f));
    else
      Array.from(inputArchivos?.files || []).forEach((f) =>
        fd.append("documentos[]", f),
      );
    return fd;
  }

  document
    .getElementById("form-nuevo-pedido")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const btnEnviar = document.getElementById("btn-enviar");
      btnEnviar.disabled = true;
      btnEnviar.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

      try {
        const response = await fetch(
          `${base_url}cliente/requerimiento/guardar`,
          { method: "POST", body: armarFormData() },
        );
        const data = await response.json();

        if (data.status === "success") {
          bootstrap.Modal.getInstance(
            document.getElementById("modal-formulario-detalle"),
          )?.hide();
          archivosSeleccionados = [];
          listaArchivos && (listaArchivos.innerHTML = "");
          e.target.reset();
          Swal.fire({
            ...swalBase,
            icon: "success",
            title: "¡Requerimiento enviado!",
            text: "Tu solicitud ha sido registrada correctamente.",
            confirmButtonText: "Perfecto",
            timer: 5000,
            timerProgressBar: true,
          });
          obtenerPedidos();
        } else {
          let msg = data.msg || "Error al enviar";
          if (data.errores?.length)
            msg += ":\n\n• " + data.errores.join("\n• ");
          mostrarAlerta("error", msg);
          console.error("Debug:", data);
        }
      } catch (err) {
        console.error(err);
        mostrarAlerta("error", "Error de conexión.");
      } finally {
        btnEnviar.disabled = false;
        btnEnviar.innerHTML =
          'Enviar Requerimiento <i class="bi bi-check-lg"></i>';
      }
    });

  // Paso 3 - Resumen
  function generarResumen() {
    const setText = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val || "—";
    };

    // Servicio con badge
    const badge = document.getElementById("wbadge-container"),
      idServ = getIdVal("form-idservicio"),
      titPers = getIdVal("titulo_personalizado");

    const resServicio = document.getElementById("res-servicio");
    if (resServicio && badge) {
      const estilo = ESTILOS_SERVICIO[idServ] || ESTILOS_SERVICIO[0];
      const nombreServicio =
        idServ === "0" && titPers
          ? titPers
          : idServ === "0"
            ? "Servicio Especial"
            : estilo.label;
      resServicio.innerHTML = `<span class="sbadge-resumen ${estilo.cls}">${nombreServicio}</span>`;
    }

    // Info básica
    setText("res-titulo", getIdVal("campo-titulo"));

    const fv = getVal('[name="fecha_entrega"]');
    if (fv) {
      setText(
        "res-fecha",
        new Date(fv + "T00:00:00").toLocaleDateString("es-PE", {
          day: "2-digit",
          month: "long",
          year: "numeric",
        }),
      );
    }

    // Prioridad con badge de color
    const prioVal = qs('input[name="prioridad"]:checked')?.value || "Media";
    const prioEl = document.getElementById("res-prioridad");
    if (prioEl) {
      const prioClass =
        prioVal === "Baja"
          ? "prio-baja"
          : prioVal === "Alta"
            ? "prio-alta"
            : "prio-media";
      prioEl.className = `resumen-badge ${prioClass}`;
      prioEl.textContent = prioVal;
    }

    // Detalles - Público objetivo
    setText("res-publico", getVal('[name="publico"]'));

    // Materiales
    const tieneMat =
      document.getElementById("select-materiales")?.value === "1";
    const matEl = document.getElementById("res-materiales");
    if (matEl) {
      matEl.innerHTML = tieneMat
        ? '<span style="color:#22c55e;"><i class="bi bi-check-circle-fill"></i> Sí, tiene materiales</span>'
        : '<span style="color:#888;">No tiene materiales</span>';
    }

    // Link de referencia (solo si existe)
    const linkVal = getVal('[name="url_referencia"]');
    const linkWrap = document.getElementById("res-link-wrap");
    const linkEl = document.getElementById("res-link");
    if (linkWrap && linkEl) {
      if (tieneMat && linkVal) {
        linkWrap.classList.remove("d-none");
        linkEl.textContent = linkVal;
      } else {
        linkWrap.classList.add("d-none");
      }
    }

    // Archivos adjuntos (solo si existen)
    const resArch = document.getElementById("res-archivos");
    const resWrap = document.getElementById("res-archivos-wrap");
    const archivosPrev = archivosSeleccionados.length
      ? archivosSeleccionados
      : Array.from(inputArchivos?.files || []);

    if (resArch && resWrap) {
      if (tieneMat && archivosPrev.length) {
        resArch.innerHTML = archivosPrev
          .map(
            (f) => `
                      <div class="resumen-archivo-item">
                          <i class="bi ${getIconoArchivo(f.type, f.name)}"></i>
                          <span>${f.name}</span>
                      </div>
                  `,
          )
          .join("");
        resWrap.classList.remove("d-none");
      } else {
        resWrap.classList.add("d-none");
      }
    }
  }

  // Otras Vistas dentro de la Tabla (Detalle y Seguimiento)
  window.verDetalle = (id) => {
    if (id)
      window.location.href = `${base_url}cliente/detalle_requerimiento/${id}`;
  };
  window.verSeguimiento = (id) => {
    if (id) window.location.href = `${base_url}cliente/seguimiento/${id}`;
  };
});

// Funciones Globales (badges Tablas)
function crearBadgeEstado(estado) {
  const cfg = {
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
  }[estado] || { texto: estado, clase: "estado-default" };
  return `<span class="badge-estado ${cfg.clase}">${cfg.texto.toUpperCase()}</span>`;
}

function crearBadgePrioridad(prioridad) {
  const cfg = {
    Baja: { clase: "prio-baja", etiqueta: "Baja" },
    Media: { clase: "prio-media", etiqueta: "Media" },
    Alta: { clase: "prio-alta", etiqueta: "Alta" },
  }[prioridad] || { clase: "prio-default", etiqueta: prioridad };
  return `<span class="badge-prio ${cfg.clase}">${cfg.etiqueta}</span>`;
}
