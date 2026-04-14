document.addEventListener('DOMContentLoaded', function () {
      const tabla = document.querySelector('#tabla-areas-body');
      const modalAgencia = document.querySelector('#modalAgencia');
      const btnNuevaArea = document.querySelector('#btnNuevaArea');
      const btnGuardarAgencia = document.querySelector('#btnGuardarAgencia');
      const modalTitulo = document.querySelector('#modal-titulo');
      const inputId = document.querySelector('#areaId');

      function notificar(mensaje) {
          alert(mensaje);
      }

      // ─── LISTAR ÁREAS ──────────────────────────────────────
      async function obtenerAreas() {
          const response = await fetch(BASE_URL + 'admin/areas/listar');
          const data = await response.json();

          tabla.innerHTML = '';

          if (!data || data.length === 0) {
              tabla.innerHTML = '<tr><td colspan="5" class="areas-empty">No hay áreas registradas.</td></tr>';
              return;
          }

          data.forEach(area => {
              const activo = area.activo === true || area.activo === 't' || area.activo == 1;
              const badge = activo
                  ? '<span class="badge badge-activo">Activo</span>'
                  : '<span class="badge badge-inactivo">Inactivo</span>';

              const btnToggle = activo
                  ? `<button class="btn btn-sm btn-warning" onclick="toggleEstado(${area.id},
  true)">Deshabilitar</button>`
                  : `<button class="btn btn-sm btn-success" onclick="toggleEstado(${area.id},
  false)">Habilitar</button>`;

              const rowClass = activo ? '' : 'row-inactivo';

              tabla.innerHTML += `
                  <tr data-id="${area.id}" class="${rowClass}">
                      <td class="area-nombre">${area.nombre}</td>
                      <td class="area-desc">${area.descripcion || ''}</td>
                      <td class="area-responsable">${area.responsable || '<span class="sin-responsable">Sinresponsable</span>'}</td>
                      <td class="area-estado">${badge}</td>
                      <td class="area-acciones">
                          <button class="btn btn-sm btn-primary" onclick="editarArea(${area.id})">Editar</button>
                          ${btnToggle}
                      </td>
                  </tr>
              `;
          });
      }

      // ─── NUEVA ÁREA ────────────────────────────────────────
      btnNuevaArea.addEventListener('click', () => {
          inputId.value = '';
          document.querySelector('#agenciaNombre').value = '';
          document.querySelector('#agenciaDescripcion').value = '';
          modalTitulo.textContent = 'NUEVA ÁREA';
          modalAgencia.style.display = 'flex';
      });

      // ─── CERRAR MODAL ─────────────────────────────────────
      document.querySelectorAll('.modal-cerrar').forEach(btn => {
          btn.addEventListener('click', () => {
              const modalId = btn.dataset.modal;
              document.querySelector('#' + modalId).style.display = 'none';
          });
      });

      // ─── GUARDAR ───────────────────────────────────────────
      btnGuardarAgencia.addEventListener('click', async () => {
          const id = inputId.value;
          const nombre = document.querySelector('#agenciaNombre').value.trim();
          const descripcion = document.querySelector('#agenciaDescripcion').value.trim();

          if (!nombre) {
              alert('El nombre del área es obligatorio');
              return;
          }

          const url = id
              ? BASE_URL + 'admin/areas/editar/' + id
              : BASE_URL + 'admin/areas/registrar';
          const method = id ? 'PUT' : 'POST';

          try {
              const response = await fetch(url, {
                  method: method,
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ nombre, descripcion })
              });

              const data = await response.json();

              if (data.success) {
                  notificar(data.message);
                  modalAgencia.style.display = 'none';
                  obtenerAreas(); // Recargar lista
              } else {
                  alert(data.message || 'Error al guardar');
              }
          } catch (error) {
              console.error('Error:', error);
              alert('Error al procesar');
          }
      });

      // ─── EDITAR ────────────────────────────────────────────
      window.editarArea = async function(id) {
          try {
              const response = await fetch(BASE_URL + 'admin/areas/obtener/' + id);
              const data = await response.json();

              if (!data || data.success === false) {
                  alert('Área no encontrada');
                  return;
              }

              inputId.value = data.id;
              document.querySelector('#agenciaNombre').value = data.nombre || '';
              document.querySelector('#agenciaDescripcion').value = data.descripcion || '';
              modalTitulo.textContent = 'EDITAR ÁREA';
              modalAgencia.style.display = 'flex';
          } catch (error) {
              console.error('Error:', error);
              alert('Error al cargar');
          }
      };

      // ─── TOGGLE ESTADO (igual que usuarios) ─────────────────
      window.toggleEstado = async function (id, estadoActual) {
          const mensaje = estadoActual
              ? '¿Seguro que deseas deshabilitar esta área?'
              : '¿Deseas volver a habilitar esta área?';

          if (!confirm(mensaje)) return;

          const response = await fetch(BASE_URL + 'admin/areas/toggleEstado', {
              method:  'POST',
              headers: { 'Content-Type': 'application/json' },
              body:    JSON.stringify({ id, estado: !estadoActual })
          });
          const data = await response.json();

          notificar(data.message);
          if (data.success) obtenerAreas(); // Recargar lista igual que usuarios
      };

      // Cerrar al hacer clic fuera
      modalAgencia.addEventListener('click', (e) => {
          if (e.target === modalAgencia) {
              modalAgencia.style.display = 'none';
          }
      });

      // ─── INICIO ────────────────────────────────────────────
      obtenerAreas();
  });