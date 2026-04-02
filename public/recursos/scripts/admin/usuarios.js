document.addEventListener('DOMContentLoaded', function () {

    const tabla = document.querySelector('#tabla-usuarios');

    // trae usuarios del backend y los pinta en la tabla
    async function obtenerUsuarios() {
        try {
            const response = await fetch(BASE_URL + 'admin/usuarios/listar');
            const data     = await response.json();

            if (response.status != 200) { return; }
            if (!data) { return; }

            tabla.innerHTML = '';

            // recorre cada usuario y arma la fila
            data.forEach(u => {
                tabla.innerHTML += `
                    <tr>
                        <td class="td-nombre">${u.nombre} ${u.apellidos}</td>
                        <td class="td-usuario">${u.usuario ?? '-'}</td>
                        <td>${u.correo}</td>
                        <td class="td-rol">${u.rol}</td>
                        <td>${u.area_nombre ?? '-'}</td>
                        <td><span class="${u.estado ? 'badge-activo' : 'badge-inactivo'}">${u.estado ? 'Activo' : 'Inactivo'}</span></td>
                        <td></td>
                    </tr>
                `;
            });
        } catch (e) {
            console.error('Error al obtener los usuarios', e);
        }
    }

    // carga al iniciar la página
    obtenerUsuarios();

});