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
        try {
            const response = await fetch(BASE_URL + 'admin/areas/listar');
            const data = await response.json();

            tabla.innerHTML = '';

            if (!data || data.length === 0) {
                tabla.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No hay áreas registradas.</td></tr>';
                return;
            }

            data.forEach(area => {
                const activo = area.activo === true || area.activo === 't' || area.activo == 1;
                
                const badge = activo
                    ? '<span class="badge-activo">Activo</span>'
                    : '<span class="badge-inactivo">Inactivo</span>';

                // BOTONES IGUALES A USUARIOS
                const btnEditar = `<button class="btn-icon btn-icon-editar" onclick="editarArea(${area.id})" title="Editar" style="cursor:pointer;">✎</button>`;
                const btnToggle = activo
                    ? `<button class="btn-icon btn-icon-toggle activo" onclick="toggleEstado(${area.id}, true)" title="Desactivar" style="cursor:pointer;">✕</button>`
                    : `<button class="btn-icon btn-icon-toggle inactivo" onclick="toggleEstado(${area.id}, false)" title="Activar" style="cursor:pointer;">✓</button>`;

                tabla.innerHTML += `
                    <tr>
                        <td class="td-nombre">${area.nombre}</td>
                        <td class="td-correo">${area.descripcion || '-'}</td>
                        <td class="td-area-empresa">${area.responsable || '<span class="text-muted">Sin responsable</span>'}</td>
                        <td style="text-align: center;">${badge}</td>
                        <td>
                            <div class="acciones-contenedor">
                                ${btnEditar}
                                ${btnToggle}
                            </div>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error('Error al listar áreas:', error);
        }
    }

    // 2. BOTÓN NUEVA ÁREA
    document.querySelector('#btnNuevaArea').addEventListener('click', () => {
        formulario.reset();
        inputId.value = '';
        modalTitulo.textContent = 'Nueva Área';
        document.querySelector('#crearEnServicios').closest('.form-check').style.display = 'none';
        $('#modal-area').modal('show');
    });

    // 3. GUARDAR
    formulario.addEventListener('submit', async function (e) {
        e.preventDefault();
        const id = inputId.value;
        const datos = {
            nombre: document.querySelector('#agenciaNombre').value.trim(),
            descripcion: document.querySelector('#agenciaDescripcion').value.trim()
        };

        const url = id ? BASE_URL + 'admin/areas/editar/' + id : BASE_URL + 'admin/areas/registrar';
        const method = id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        const result = await response.json();

        if (result.success) {
            notificar(result.message);
            $('#modal-area').modal('hide');
            obtenerAreas();
        } else {
            alert(result.message || 'Error al guardar');
        }
    });

    // 4. EDITAR
    window.editarArea = async function (id) {
        const response = await fetch(BASE_URL + 'admin/areas/obtener/' + id);
        const data = await response.json();
        if (!data || data.success === false) return;

        inputId.value = data.id;
        document.querySelector('#agenciaNombre').value = data.nombre || '';
        document.querySelector('#agenciaDescripcion').value = data.descripcion || '';
        modalTitulo.textContent = 'Editar Área';
        $('#modal-area').modal('show');
    };

    // 5. TOGGLE ESTADO
    window.toggleEstado = async function (id, estadoActual) {
        if (!confirm('¿Seguro que deseas cambiar el estado de esta área?')) return;
        const response = await fetch(BASE_URL + 'admin/areas/toggleEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, estado: !estadoActual })
        });
        const result = await response.json();
        if (result.success) {
            notificar(result.message);
            obtenerAreas();
        }
    };

    obtenerAreas();
});