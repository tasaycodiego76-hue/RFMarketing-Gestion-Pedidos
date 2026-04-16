document.addEventListener('DOMContentLoaded', function () {
      const tabla = document.querySelector('#tabla-empresas');
      const formulario = document.querySelector('#form-empresa');

      function notificar(mensaje) {
          alert(mensaje);
      }

      async function obtenerEmpresas() {
          const response = await fetch(BASE_URL + 'admin/empresas/listar');
          const data = await response.json();

          tabla.innerHTML = '';

          if (data.length === 0) {
              tabla.innerHTML = '<tr><td colspan="6" class="text-center">No hay empresas</td></tr>';
              return;
          }

          data.forEach(e => {
              const badge = e.estado
                  ? '<span class="badge-activo">Activo</span>'
                  : '<span class="badge-inactivo">Inactivo</span>';

              const btnToggle = e.estado
                  ? `<button class="btn btn-sm btn-warning" onclick="toggleEstado(${e.id}, true)">Deshabilitar</button>`
                  : `<button class="btn btn-sm btn-success" onclick="toggleEstado(${e.id}, false)">Habilitar</button>`;

              tabla.innerHTML += `
                  <tr>
                      <td>${e.nombreempresa}</td>
                      <td>${e.ruc || '-'}</td>
                      <td>${e.correo || '-'}</td>
                      <td>${e.telefono || '-'}</td>
                      <td>${badge}</td>
                      <td>
                          <button class="btn btn-sm btn-primary" onclick="editarEmpresa(${e.id})">Editar</button>
                          ${btnToggle}
                      </td>
                  </tr>
              `;
          });
      }

      formulario.addEventListener('submit', async function (e) {
          e.preventDefault();
          const editId = formulario.dataset.editId;

          const datos = {
              nombreempresa: document.querySelector('#nombreempresa').value.trim(),
              ruc: document.querySelector('#ruc').value.trim(),
              correo: document.querySelector('#correo').value.trim(),
              telefono: document.querySelector('#telefono').value.trim(),
          };

          const url = editId
              ? BASE_URL + 'admin/empresas/editar/' + editId
              : BASE_URL + 'admin/empresas/registrar';
          const method = editId ? 'PUT' : 'POST';

          const response = await fetch(url, {
              method,
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(datos)
          });
          const data = await response.json();

          notificar(data.message);
          if (!data.success) return;

          $('#modal-empresa').modal('hide');
          formulario.reset();
          delete formulario.dataset.editId;
          obtenerEmpresas();
      });

      window.editarEmpresa = async function (id) {
          const response = await fetch(BASE_URL + 'admin/empresas/obtener/' + id);
          const e = await response.json();

          if (!e || e.success === false) {
              notificar('No encontrada');
              return;
          }

          formulario.reset();
          formulario.dataset.editId = id;

          document.querySelector('#nombreempresa').value = e.nombreempresa || '';
          document.querySelector('#ruc').value = e.ruc || '';
          document.querySelector('#correo').value = e.correo || '';
          document.querySelector('#telefono').value = e.telefono || '';

          document.querySelector('#modal-titulo').textContent = 'Editar Empresa';
          $('#modal-empresa').modal('show');
      };

    window.toggleEstado = async function (id, estadoActual) {
    // estadoActual viene como boolean de la base de datos (Postgres)
    const mensaje = estadoActual 
        ? '¿Seguro que deseas deshabilitar esta empresa?' 
        : '¿Deseas volver a habilitar esta empresa?';

    if (!confirm(mensaje)) return;

    // Enviamos el opuesto booleano
    const nuevoEstado = !estadoActual; 

    const response = await fetch(BASE_URL + 'admin/empresas/toggleEstado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, estado: nuevoEstado }) 
    });
    
    const data = await response.json();

    if (data.success) {
        notificar(data.message);
        
        // Actualizar Sidebar inmediatamente
        const itemSidebar = document.getElementById(`sidebar-item-${id}`);
        if (itemSidebar) {
            nuevoEstado ? itemSidebar.classList.remove('d-none') : itemSidebar.classList.add('d-none');
        }

        obtenerEmpresas(); // Refrescar tabla
    }
};

      document.querySelector('#btn-nueva-empresa').addEventListener('click', () => {
          formulario.reset();
          delete formulario.dataset.editId;
          document.querySelector('#modal-titulo').textContent = 'Nueva Empresa';
          $('#modal-empresa').modal('show');
      });

      obtenerEmpresas();
  });
