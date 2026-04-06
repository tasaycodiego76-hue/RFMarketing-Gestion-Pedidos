document.addEventListener("DOMContentLoaded", function () {
  // Referencias DOM
  const tabla = document.getElementById("content-pedidos");
  const buscador = document.getElementById("buscador");
  const skeleton = document.getElementById("sk-servicios");
  const lista = document.getElementById("lista-servicios");
  const modal = document.getElementById("modal-nuevo-pedido");

  // Config de iconos y colores por servicio
  const servicioConfig = {
    Diseño: { icono: "bi-palette", color: "#f5c400" },
    Audiovisual: { icono: "bi-camera-video", color: "#60a5fa" },
  };

  // ── CARGAR SERVICIOS EN EL MODAL
  async function cargarServicios() {
    skeleton.style.display = "block";
    lista.style.display = "none";
    lista.innerHTML = "";

    try {
      const res = await fetch(`${base_url}cliente/nuevo-pedido/servicios`);
      if (!res.ok) throw new Error("Error al cargar servicios");
      const data = await res.json();

      data.forEach((s) => {
        const cfg = servicioConfig[s.nombre] ?? {
          icono: "bi-box",
          color: "#888",
        };
        lista.innerHTML += `
                    <div class="servicio-card" onclick="elegirServicio(${s.id})">
                        <div class="servicio-card-icon" style="color:${cfg.color};">
                            <i class="bi ${cfg.icono}"></i>
                        </div>
                        <div class="servicio-card-info">
                            <p class="servicio-card-nombre">${s.nombre}</p>
                            <p class="servicio-card-desc">${s.descripcion ?? ""}</p>
                        </div>
                        <i class="bi bi-arrow-right servicio-card-arrow"></i>
                    </div>`;
      });

      skeleton.style.display = "none";
      lista.style.display = "block";
    } catch (e) {
      console.error(e);
      skeleton.style.display = "none";
      lista.style.display = "block";
      lista.innerHTML = `<p style="color:#555; text-align:center;">Error al cargar servicios</p>`;
    }
  }

  // Cargar servicios cuando se abre el modal
  modal.addEventListener("shown.bs.modal", cargarServicios);

  // Redirige al formulario con el servicio elegido
  window.elegirServicio = function (idServicio) {
    window.location.href = `${base_url}/cliente/nuevo-pedido/${idServicio}`;
  };

  /**
   * Obtiene los pedidos del usuario desde Backend y renderiza tabla dinámica
   */
  async function obtenerPedidos() {
    try {
      const res = await fetch(`${base_url}/cliente/pedidos/listar`);
      if (!res.ok) return;
      const data = await res.json();

      const skTabla = document.getElementById("sk-tabla");
      if (skTabla) skTabla.remove();

      const tabla = document.getElementById("content-pedidos");
      tabla.innerHTML = "";

      // Contadores Actualizados
      document.getElementById("cnt-total").textContent = data.length;
      document.getElementById("cnt-por-aprobar").textContent = data.filter(
        (p) => p.estado === "pendiente_sin_asignar",
      ).length;
      document.getElementById("cnt-en-proceso").textContent = data.filter((p) =>
        ["pendiente_asignado", "en_proceso", "en_revision"].includes(p.estado),
      ).length;
      document.getElementById("cnt-completado").textContent = data.filter(
        (p) => p.estado === "finalizado",
      ).length;

      if (data.length === 0) {
        tabla.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px;">Sin pedidos registrados</td></tr>`;
        return;
      }

      // LA CLAVE: data[0] es el más nuevo, por eso su número debe ser data.length
      data.forEach((p, index) => {
        // Usar el ID real de la base de datos (Momentaneo)
        const nroFila = p.idformpedido;

        const nombreServicio =
          p.servicio ||
          p.servicio_personalizado ||
          '<span style="color:#ce8011; font-style:italic;">Personalizado</span>';

        tabla.innerHTML += `
        <tr data-numero="${nroFila}">
            <td style="color:#555; font-size:11px;">#${nroFila}</td>
            <td>
                ${
                  p.titulo
                    ? `<span style="font-weight:600; font-size:13px;">${p.titulo}</span>`
                    : `<span style="color:#777; font-style:italic;">Pendiente de revisión</span>`
                }
            </td>
            <td>${nombreServicio}</td>
            <td>${badgeEstado(p.estado)}</td>
            <td>${p.prioridad ? badgePrioridad(p.prioridad) : '<span style="color:#555">—</span>'}</td>
            <td style="color:#777; font-size:11px;">${(p.fechainicio || p.fechacreacion)?.substring(0, 10) ?? "—"}</td>
            <td>
                <a href="${base_url}index.php/cliente/pedidos/detalle/${p.id}" class="btn-ver" title="Ver detalle">
                    <i class="bi bi-eye"></i>
                </a>
            </td>
        </tr>`;
      });
    } catch (e) {
      console.error("Error al obtener pedidos:", e);
    }
  }

  /**
   * Implementa búsqueda en tiempo real sobre la tabla de pedidos
   */
  if (buscador) {
    buscador.addEventListener("keyup", function () {
      const termino = this.value.trim().toLowerCase();
      const filas = document.querySelectorAll("#tablaPedidos tbody tr");

      filas.forEach((fila) => {
        // Si el buscador está vacío, mostramos todo y salimos
        if (termino === "") {
          fila.style.display = "";
          return;
        }

        let coincide = false;

        // 1. LÓGICA POR NÚMERO
        if (/^\d+$/.test(termino)) {
          const nroFila = fila.getAttribute("data-numero");
          coincide = nroFila === termino;
        }
        // 2. LÓGICA POR TEXTO (Solo si no es un número)
        else {
          const titulo =
            fila.querySelector("td:nth-child(2)")?.textContent.toLowerCase() ||
            "";
          const servicio =
            fila.querySelector("td:nth-child(3)")?.textContent.toLowerCase() ||
            "";
          const estado =
            fila.querySelector("td:nth-child(4)")?.textContent.toLowerCase() ||
            "";

          coincide =
            titulo.includes(termino) ||
            servicio.includes(termino) ||
            estado.includes(termino);
        }

        // Aplicamos el resultado una sola vez por fila
        fila.style.display = coincide ? "" : "none";
      });
    });
  }

  // Auto-ejecutar al cargar
  obtenerPedidos();
});

/**
 * BADGES
 * Convierte el estado ENUM de BD a su representación visual (badge HTML)
 *
 * @param {string} estado - Valor ENUM de la BD
 * @returns {string} - HTML badge con clase de estilo correspondiente
 */
function badgeEstado(estado) {
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

  const config = mapaEstados[estado] || {
    texto: estado,
    clase: "estado-default",
  };

  return `<span class="badge-estado ${config.clase}">${config.texto.toUpperCase()}</span>`;
}

function badgePrioridad(prio) {
  // Mapeo basado en tu CREATE TYPE prioridad_enum
  const mapaPrioridades = {
    Baja: { clase: "prio-baja", label: "Baja" },
    Media: { clase: "prio-media", label: "Media" },
    Alta: { clase: "prio-alta", label: "Alta" },
    Urgente: { clase: "prio-urgente", label: "¡URGENTE!" }, // Nuevo nivel
  };

  const config = mapaPrioridades[prio] || {
    clase: "prio-default",
    label: prio,
  };

  return `<span class="badge-prio ${config.clase}">${config.label}</span>`;
}
