window.verDetalle = (idReq) => {
    window.location.href = `${base_url}cliente/detalle_requerimiento/${idReq}`;
};
window.verSeguimiento = (idReq) => {
    window.location.href = `${base_url}cliente/seguimiento/${idReq}`;
};

document.addEventListener("DOMContentLoaded", function () {
    const contenedor = document.getElementById("contenedor-historial");
    const paginacionCont = document.getElementById("paginacion-historial");
    const inputBuscar = document.getElementById("buscador-historial");
    const counter = document.getElementById("historial-counter");
    let timerBuscar;

    // Variables de paginación
    let currentPage = 1;
    let currentSearch = "";

    // Mapas de prioridad
    const PRIO = {
        Alta: { chip: "alta", icon: "bi-arrow-up-circle-fill" },
        Media: { chip: "media", icon: "bi-dash-circle-fill" },
        Baja: { chip: "baja", icon: "bi-arrow-down-circle-fill" },
    };

    // Genera el HTML de una card
    const buildCard = (p, num) => {
        const prio = PRIO[p.prioridad] || { chip: "media", icon: "bi-dash-circle-fill" };
        const servicio = p.servicio || "—";

        return `
        <div class="pedido-card-historial">
            <div class="historial-header">
                <div>
                    <div class="historial-req-label">#REQ-${p.idrequerimiento} &nbsp;·&nbsp; Pedido #${num}</div>
                    <h3 class="historial-titulo">${p.titulo || "Sin título"}</h3>
                </div>
                <span class="historial-status">
                    <i class="bi bi-check-circle-fill"></i> FINALIZADO
                </span>
            </div>

            <div class="historial-body">
                <div class="historial-info-item">
                    <span class="historial-info-label">Servicio</span>
                    <span class="historial-info-value">
                        <i class="bi bi-gear-fill"></i> ${servicio}
                    </span>
                </div>
                <div class="historial-info-item">
                    <span class="historial-info-label">Completado</span>
                    <span class="historial-info-value">
                        <i class="bi bi-calendar-check-fill"></i> ${(p.fechacompletado)}
                    </span>
                </div>
                <div class="historial-info-item">
                    <span class="historial-info-label">Prioridad</span>
                    <span class="historial-info-value">
                        <span class="prio-chip ${prio.chip}">
                            <i class="bi ${prio.icon}"></i> ${p.prioridad || "Media"}
                        </span>
                    </span>
                </div>
                <div class="historial-info-item">
                    <span class="historial-info-label">Creado</span>
                    <span class="historial-info-value">
                        <i class="bi bi-calendar-plus"></i> ${(p.fechacreacion)}
                    </span>
                </div>
            </div>

            <div class="historial-footer">
                <button class="btn-hist seguimiento"
                        onclick="verSeguimiento(${p.idrequerimiento})"
                        title="Ver línea de tiempo del pedido">
                    <i class="bi bi-clock-history"></i> Seguimiento
                </button>
                <button class="btn-hist"
                        onclick="verDetalle(${p.idrequerimiento})"
                        title="Ver detalle completo">
                    <i class="bi bi-eye"></i> Ver Detalle
                </button>
            </div>
        </div>`;
    };

    // Renderiza controles de paginación
    const renderPagination = (page, totalPages, totalItems) => {
        if (totalPages <= 1) {
            paginacionCont.innerHTML = "";
            return;
        }

        let html = `
            <div class="card-footer bg-transparent border-dark d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                <small class="text-muted" style="color: var(--text-muted, #888) !important;">
                    Mostrando página ${page} de ${totalPages} (Total: ${totalItems} completados)
                </small>
                <nav aria-label="Paginación de historial">
                    <ul class="pagination pagination-rf mb-0">
                        <li class="page-item ${page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="cambiarPagina(${page - 1}); return false;" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                        </li>`;

        const range = 2;
        const startPage = Math.max(1, page - range);
        const endPage = Math.min(totalPages, page + range);

        if (startPage > 1) {
            html += `
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="cambiarPagina(1); return false;">1</a>
                        </li>`;
            if (startPage > 2) {
                html += `
                        <li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                        <li class="page-item ${page === i ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
                        </li>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `
                        <li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="cambiarPagina(${totalPages}); return false;">${totalPages}</a>
                        </li>`;
        }

        html += `
                        <li class="page-item ${page === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="cambiarPagina(${page + 1}); return false;" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>`;

        paginacionCont.innerHTML = html;
    };

    // Funciones globales para paginación
    window.cambiarPagina = (nuevaPagina) => {
        currentPage = nuevaPagina;
        cargarHistorial();
        // Desplazamiento suave al principio del historial
        document.querySelector('.seccion-titulo').scrollIntoView({ behavior: 'smooth' });
    };

    // Renderiza un array de pedidos
    const renderizar = (lista, totalItems) => {
        if (!lista.length) {
            contenedor.innerHTML = `
                <div class="historial-empty">
                    <i class="bi bi-archive"></i>
                    <p>No se encontraron requerimientos finalizados con ese criterio.</p>
                </div>`;
            paginacionCont.innerHTML = "";
            counter.textContent = "0 proyectos completados";
            return;
        }

        contenedor.innerHTML = lista
            .map((p, i) => buildCard(p, totalItems - (currentPage - 1) * 10 - i))
            .join("");

        counter.textContent = `${totalItems} proyecto${totalItems !== 1 ? "s" : ""} completado${totalItems !== 1 ? "s" : ""}`;
    };

    // Carga desde la API
    async function cargarHistorial() {
        contenedor.innerHTML = `
            <div class="spinner-hist">
                <span class="spinner-border spinner-border-sm text-warning"></span>
                Cargando historial...
            </div>`;
        paginacionCont.innerHTML = "";
        counter.textContent = "Cargando...";

        try {
            const url = `${base_url}cliente/pedidos/historial-json?page=${currentPage}&search=${encodeURIComponent(currentSearch)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status !== 'success') throw new Error(data.detalle || "Respuesta inválida");

            renderizar(data.data, data.totalItems);
            renderPagination(data.currentPage, data.totalPages, data.totalItems);
        } catch (err) {
            console.error("[Historial] Error:", err);
            contenedor.innerHTML = `
                <div class="historial-empty">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    <p>No se pudo cargar el historial. Recarga la página.</p>
                </div>`;
            counter.textContent = "";
        }
    }

    // Buscador en tiempo real (debounced y llamando al backend)
    inputBuscar?.addEventListener("input", () => {
        clearTimeout(timerBuscar);
        timerBuscar = setTimeout(() => {
            currentSearch = inputBuscar.value.trim();
            currentPage = 1; // Volver a la página 1 en cada nueva búsqueda
            cargarHistorial();
        }, 1000);
    });

    cargarHistorial();
});