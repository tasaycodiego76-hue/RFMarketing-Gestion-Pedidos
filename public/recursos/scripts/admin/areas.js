document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.querySelector('#tabla-areas-body');
    const formulario = document.querySelector('#form-area');
    const inputId = document.querySelector('#areaId');
    const modalTitulo = document.querySelector('#modal-titulo');

    function notificar(mensaje) {
        alert(mensaje);
    }

    // 1. LISTAR ÁREAS
    async function obtenerAreas() {
        const response = await fetch(BASE_URL + 'admin/areas/listar');
        const data = await response.json();

        tabla.innerHTML = '';

        if (!data || data.length === 0) {
            tabla.innerHTML = '<tr><td colspan="5" class="text-center">No hay áreas registradas.</td></tr>';
            return;
        }

        data.forEach(area => {
            // Manejo de booleano para Postgres
            const activo = area.activo === true || area.activo === 't' || area.activo == 1;
            
            const badge = activo
                ? '<span class="badge-activo">Activo</span>'
                : '<span class="badge-inactivo">Inactivo</span>';

            const btnToggle = activo
                ? `<button class="btn btn-sm btn-warning" onclick="toggleEstado(${area.id}, true)">Deshabilitar</button>`
                : `<button class="btn btn-sm btn-success" onclick="toggleEstado(${area.id}, false)">Habilitar</button>`;

            tabla.innerHTML += `
                <tr>
                    <td>${area.nombre}</td>
                    <td>${area.descripcion || '-'}</td>
                    <td>${area.responsable || '<span class="text-muted">Sin responsable</span>'}</td>
                    <td>${badge}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editarArea(${area.id})">Editar</button>
                        ${btnToggle}
                    </td>
                </tr>
            `;
        });
    }

    // 2. BOTÓN NUEVA ÁREA (Abre modal)
    document.querySelector('#btnNuevaArea').addEventListener('click', () => {
        formulario.reset();
        inputId.value = '';
        modalTitulo.textContent = 'NUEVA ÁREA';
        // Lógica desacoplada: servicios ya no se crean desde Áreas
        document.querySelector('#crearEnServicios').closest('.form-check').style.display = 'none';
        document.querySelector('#crearEnServicios').checked = false;
        $('#modal-area').modal('show'); // Función de Bootstrap
    });

    // 3. GUARDAR (Create / Update)
    formulario.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id = inputId.value;
        const datos = {
            nombre: document.querySelector('#agenciaNombre').value.trim(),
            descripcion: document.querySelector('#agenciaDescripcion').value.trim()
        };

        const url = id
            ? BASE_URL + 'admin/areas/editar/' + id
            : BASE_URL + 'admin/areas/registrar';
        const method = id ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const data = await response.json();

            if (data.success) {
                notificar(data.message);
                $('#modal-area').modal('hide'); // Cierra modal
                obtenerAreas();
            } else {
                alert(data.message || 'Error al guardar');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // 4. EDITAR (Carga datos en modal)
    window.editarArea = async function (id) {
        const response = await fetch(BASE_URL + 'admin/areas/obtener/' + id);
        const data = await response.json();

        if (!data || data.success === false) {
            notificar('Área no encontrada');
            return;
        }

        inputId.value = data.id;
        document.querySelector('#agenciaNombre').value = data.nombre || '';
        document.querySelector('#agenciaDescripcion').value = data.descripcion || '';

        // Ocultar opción de crear en servicios al editar (no aplica)
        document.querySelector('#crearEnServicios').closest('.form-check').style.display = 'none';

        modalTitulo.textContent = 'EDITAR ÁREA';
        $('#modal-area').modal('show');
    };

    // 5. TOGGLE ESTADO
    window.toggleEstado = async function (id, estadoActual) {
        const mensaje = estadoActual 
            ? '¿Seguro que deseas deshabilitar esta área?' 
            : '¿Deseas volver a habilitar esta área?';

        if (!confirm(mensaje)) return;

        const response = await fetch(BASE_URL + 'admin/areas/toggleEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, estado: !estadoActual })
        });
        const data = await response.json();

        if (data.success) {
            notificar(data.message);
            obtenerAreas();
        }
    };

    // Inicio
    obtenerAreas();
});