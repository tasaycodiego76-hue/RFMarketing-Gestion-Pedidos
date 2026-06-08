$(document).ready(function() {
    let timeoutBusqueda = null;
    let currentPage = 1;
    let currentSearch = "";
    const base_url = window.location.origin;

    // Cargar historial con paginación
    async function cargarHistorial() {
        const tbody = $("#tablaHistorialBody");
        const paginacion = $("#paginacion-historial");
        const counter = $("#historial-counter");

        tbody.html(`
            <tr>
                <td colspan="6" style="text-align: center; padding: 80px; color: #444;">
                    <span class="spinner-border spinner-border-sm text-warning"></span> Cargando...
                </td>
            </tr>
        `);
        paginacion.html("");
        counter.text("Cargando...");

        try {
            const url = `${base_url}/admin/historial-json?page=${currentPage}&search=${encodeURIComponent(currentSearch)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status !== 'success') throw new Error(data.detalle || "Respuesta inválida");

            renderizarTabla(data.data);
            renderPagination(data.currentPage, data.totalPages, data.totalItems);
            counter.text(`${data.totalItems} proyecto${data.totalItems !== 1 ? 's' : ''} en el historial`);
        } catch (err) {
            console.error("[Historial] Error:", err);
            tbody.html(`
                <tr>
                    <td colspan="6" style="text-align: center; padding: 80px; color: #444;">
                        <i class="bi bi-exclamation-triangle" style="font-size: 40px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                        <span style="font-weight: 800;">Error al cargar</span>
                    </td>
                </tr>
            `);
            counter.text("");
        }
    }

    // Renderizar tabla con datos
    function renderizarTabla(pedidos) {
        const tbody = $("#tablaHistorialBody");

        if (!pedidos.length) {
            tbody.html(`
                <tr>
                    <td colspan="6" style="text-align: center; padding: 80px; color: #444;">No hay registros</td>
                </tr>
            `);
            return;
        }

        tbody.html(pedidos.map(p => {
            const inicial = p.empresa_nombre ? p.empresa_nombre.charAt(0).toUpperCase() : '';
            return `
                <tr data-fecha="${p.fechacompletado ? p.fechacompletado.split(' ')[0] : ''}">
                    <td data-label="Área">
                        <div class="historial-area-badge">
                            <i class="bi bi-palette-fill"></i> ${p.area_nombre || 'General'}
                        </div>
                    </td>
                    <td data-label="Proyecto">
                        <div class="historial-title">${p.titulo || ''}</div>
                        <div class="historial-sub-info">${p.servicio_nombre || ''}</div>
                    </td>
                    <td data-label="Empresa">
                        <div class="historial-empresa-wrapper">
                            <div class="empresa-avatar-mini" style="background: ${p.empresa_color || '#666'};">
                                ${inicial}
                            </div>
                            <div class="empresa-nombre-text">${p.empresa_nombre || ''}</div>
                        </div>
                    </td>
                    <td style="text-align: center;" data-label="Finalización">
                        <div class="historial-fecha">${p.fechacompletado ? formatDate(p.fechacompletado) : ''}</div>
                        <div class="historial-hora">${p.fechacompletado ? formatTime(p.fechacompletado) : ''}</div>
                    </td>
                    <td data-label="Ejecutor">
                        <div class="historial-ejecutor-nombre">${p.empleado_nombre ? p.empleado_nombre.toUpperCase() : ''}</div>
                        <div class="historial-status">FINALIZADO</div>
                    </td>
                    <td style="text-align: center;" data-label="Acción">
                        <button class="btn-expediente" onclick="verDetalle(${p.id})">
                            VER PEDIDO
                        </button>
                    </td>
                </tr>
            `;
        }).join(''));
    }

    // Renderizar controles de paginación
    function renderPagination(page, totalPages, totalItems) {
        const paginacion = $("#paginacion-historial");

        if (totalPages <= 1) {
            paginacion.html("");
            return;
        }

        let html = `
            <div class="card-footer bg-transparent border-dark d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                <small class="text-muted" style="color: #888 !important;">
                    Mostrando página ${page} de ${totalPages} (Total: ${totalItems} registros)
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

        paginacion.html(html);
    }

    // Función global para cambiar página
    window.cambiarPagina = function(nuevaPagina) {
        currentPage = nuevaPagina;
        cargarHistorial();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Helper para formatear fecha
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-ES');
    }

    // Helper para formatear hora
    function formatTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }

    // Buscador con debounce
    $("#busquedaHistorial").on("input", function() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(function() {
            currentSearch = $("#busquedaHistorial").val().trim();
            currentPage = 1;
            cargarHistorial();
        }, 1000);
    });

    // Filtro de fecha (client-side filter on current page)
    $("#filtroFecha").on("change", function() {
        const date = $("#filtroFecha").val();
        $("#tablaHistorialBody tr").each(function() {
            const rowDate = $(this).data('fecha');
            const isVisible = (date === "" || rowDate === date);
            $(this).toggle(isVisible);
        });
    });

    // Carga inicial
    cargarHistorial();
});
