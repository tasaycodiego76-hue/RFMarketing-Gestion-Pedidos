$(document).ready(function() {
    $("#busquedaHistorial").on("keyup", function() { filtrarTabla(); });
    $("#filtroFecha").on("change", function() { filtrarTabla(); });

    function filtrarTabla() {
        var search = $("#busquedaHistorial").val().toLowerCase();
        var date = $("#filtroFecha").val();
        var visibleCount = 0;

        $("#tablaHistorial tbody tr:not(#noResultsRow)").each(function() {
            var text = $(this).text().toLowerCase();
            var rowDate = $(this).data('fecha');
            var matchesSearch = text.indexOf(search) > -1;
            var matchesDate = (date === "" || rowDate === date);
            
            var isVisible = matchesSearch && matchesDate;
            $(this).toggle(isVisible);
            if (isVisible) visibleCount++;
        });

        // Mostrar u ocultar el mensaje de "sin resultados"
        if (visibleCount === 0) {
            if ($("#noResultsRow").length === 0) {
                $("#tablaHistorial tbody").append(`
                    <tr id="noResultsRow">
                        <td colspan="6" style="text-align: center; padding: 100px 20px; color: #444;">
                            <i class="bi bi-search" style="font-size: 40px; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                            <span style="font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">No se encontraron resultados</span>
                            <p style="font-size: 12px; margin-top: 5px; opacity: 0.5;">Intenta con otros términos o cambia la fecha</p>
                        </td>
                    </tr>
                `);
            } else {
                $("#noResultsRow").show();
            }
        } else {
            $("#noResultsRow").hide();
        }
    }
});
