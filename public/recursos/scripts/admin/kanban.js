
  // ═══ ASIGNAR ÁREA ═══

  /**
   * Abre el modal de asignación y carga las áreas disponibles
   * @param {number} idAtencion - ID de la atención a asignar
   */
  async function abrirModalAsignar(idAtencion) {
      const inputId = document.getElementById('asignar-idatencion');
      const select = document.getElementById('asignar-empleado');

      inputId.value = idAtencion;
      select.innerHTML = '<option value="">Cargando...</option>';

      try {
          const response = await fetch(BASE_URL + 'admin/kanban/areas');

          if (!response.ok) {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const data = await response.json();

          if (data.length === 0) {
              select.innerHTML = '<option value="">No hay áreas disponibles</option>';
          } else {
              select.innerHTML = '<option value="">-- Seleccionar área --</option>';
              data.forEach(area => {
                  select.innerHTML += `<option value="${area.id}">${area.nombre}</option>`;
              });
          }
      } catch (error) {
          console.error('Error al cargar áreas:', error);
          select.innerHTML = '<option value="">Error al cargar áreas</option>';
      }

      $('#modalAsignar').modal('show');
  }

  /**
   * Confirma la asignación de área a una atención
   */
  async function confirmarAsignacion() {
      const idAtencion = document.getElementById('asignar-idatencion').value;
      const idArea = document.getElementById('asignar-empleado').value;

      if (!idArea) {
          alert('Selecciona un área');
          return;
      }

      try {
          const response = await fetch(BASE_URL + 'admin/kanban/asignarArea', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  idatencion: idAtencion,
                  idareaagencia: idArea
              })
          });

          if (!response.ok) {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const res = await response.json();

          if (res.status === 'success') {
              $('#modalAsignar').modal('hide');
              location.reload();
          } else {
              alert(res.msg || 'Error al asignar el área');
          }
      } catch (error) {
          console.error('Error en asignación:', error);
          alert('Error al asignar el área. Intenta nuevamente.');
      }
  }

  // ═══ CAMBIAR ESTADO ═══

  /**
   * Cambia el estado de una atención
   * @param {number} idAtencion - ID de la atención
   * @param {string} nuevoEstado - Nuevo estado a asignar
   * @param {string} accion - Nombre de la acción para confirmación
   */
  async function cambiarEstado(idAtencion, nuevoEstado, accion) {
      if (!confirm(`¿Confirmar: ${accion}?`)) {
          return;
      }

      try {
          const response = await fetch(BASE_URL + 'admin/kanban/cambiarEstado', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  idatencion: idAtencion,
                  estado: nuevoEstado,
                  accion: accion
              })
          });

          if (!response.ok) {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const res = await response.json();

          if (res.status === 'success') {
              location.reload();
          } else {
              alert(res.msg || 'Error al cambiar el estado');
          }
      } catch (error) {
          console.error('Error al cambiar estado:', error);
          alert('Error al cambiar el estado. Intenta nuevamente.');
      }
  }

  // ═══ CANCELAR ═══

  /**
   * Cancela una atención solicitando motivo
   * @param {number} idAtencion - ID de la atención a cancelar
   */
  async function cancelarAtencion(idAtencion) {
      const motivo = prompt('Motivo de cancelación:');

      if (motivo === null || motivo.trim() === '') {
          return;
      }

      try {
          const response = await fetch(BASE_URL + 'admin/kanban/cancelar', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  idatencion: idAtencion,
                  motivo: motivo.trim()
              })
          });

          if (!response.ok) {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const res = await response.json();

          if (res.status === 'success') {
              location.reload();
          } else {
              alert(res.msg || 'Error al cancelar la atención');
          }
      } catch (error) {
          console.error('Error al cancelar:', error);
          alert('Error al cancelar la atención. Intenta nuevamente.');
      }
  }

  // ═══ VER DETALLE ═══

  /**
   * Muestra el detalle completo de una atención
   * @param {number} idAtencion - ID de la atención a consultar
   */
  async function verDetalle(idAtencion) {
      const cuerpo = document.getElementById('detalle-cuerpo');
      const titulo = document.getElementById('detalle-titulo');

      cuerpo.innerHTML = 'Cargando...';
      $('#modalDetalle').modal('show');

      try {
          const response = await fetch(BASE_URL + 'admin/kanban/detalle/' + idAtencion);

          if (!response.ok) {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const res = await response.json();

          if (res.status !== 'success') {
              alert(res.msg || 'Error al cargar el detalle');
              return;
          }

          const d = res.data;
          const html = `
              <div class="kb-detalle-grid">
                  <div><strong>Título</strong><br>${escapeHtml(d.titulo)}</div>
                  <div><strong>Servicio</strong><br>${escapeHtml(d.servicio)}</div>
                  <div><strong>Estado</strong><br>${escapeHtml(d.estado)}</div>
                  <div><strong>Prioridad del cliente</strong><br>
    <span>${d.prioridad_cliente}</span>
</div>
<div><strong>Prioridad asignada</strong><br>
    <select id="detalle-prioridad" class="form-control form-control-sm" style="width:auto">
        <option value="Baja"  ${d.prioridad_admin === 'Baja'  ? 'selected' : ''}>▼ Baja</option>
        <option value="Media" ${d.prioridad_admin === 'Media' ? 'selected' : ''}>● Media</option>
        <option value="Alta"  ${d.prioridad_admin === 'Alta'  ? 'selected' : ''}>▲ Alta</option>
    </select>
    <button class="btn btn-sm btn-primary mt-1" onclick="cambiarPrioridad(${d.id})">Guardar</button>
</div>
                  <div><strong>Empresa</strong><br>${escapeHtml(d.nombreempresa)}</div>
                  <div><strong>Área Asignada</strong><br>${escapeHtml(d.area_nombre) || 'Sin asignar'}</div>
                  <div><strong>Fecha requerida</strong><br>${d.fecharequerida || '—'}</div>
                  <div><strong>Fecha fin</strong><br>${d.fechafin || '—'}</div>
              </div>
              <hr class="kb-detalle-hr">
              <div><strong>Descripción</strong><br>${escapeHtml(d.descripcion) || '—'}</div>
          `;

          titulo.textContent = d.titulo;
          cuerpo.innerHTML = html;

      } catch (error) {
          console.error('Error al cargar detalle:', error);
          cuerpo.innerHTML = '<div class="alert alert-danger">Error al cargar el detalle. Intenta nuevamente.</div>';
      }

    }
    async function cambiarPrioridad(idAtencion) {
    const prioridad = document.getElementById('detalle-prioridad').value;
    const res = await fetch(BASE_URL + 'admin/kanban/cambiarPrioridad', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idatencion: idAtencion, prioridad: prioridad })
    });
    const data = await res.json();
    if (data.status === 'success') {
        $('#modalDetalle').modal('hide');
        location.reload();
    } else {
        alert(data.msg || 'Error al cambiar prioridad');
    }

}
/**
 * Escapa caracteres HTML para prevenir XSS
 * @param string text  Texto a escapar
 * @returns string  Texto escapado
 */
function escapeHtml(text) {
    if (!text) return '';

    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ═══ DRAG & DROP INTERACTIVO ═══

document.addEventListener('DOMContentLoaded', function() {
    let draggedCardId = null;

    // Hacer arrastrables las cards de "Por Aprobar"
    document.querySelectorAll('.kb-card').forEach(card => {
        const colEstado = card.closest('.kb-col-body')?.dataset?.estado;

        if (colEstado === 'pendiente_sin_asignar') {
            card.draggable = true;

            card.ondragstart = function(e) {
                // No arrastrar si se hizo clic en un botón
                if (e.target.closest('.kb-btn')) {
                    e.preventDefault();
                    return;
                }

                draggedCardId = this.dataset.id;
                e.dataTransfer.setData('text/plain', draggedCardId);
                e.dataTransfer.effectAllowed = 'move';

                // Efecto visual sutil
                this.classList.add('dragging');
            };

            card.ondragend = function() {
                this.classList.remove('dragging');
                draggedCardId = null;

                // Limpiar efectos de columna
                document.querySelectorAll('.kb-col-body').forEach(col => {
                    col.classList.remove('drop-ready');
                });
            };
        }
    });

    // Columnas como áreas de drop
    document.querySelectorAll('.kb-col-body').forEach(col => {
        if (col.dataset.estado !== 'en_proceso') return;

        col.ondragenter = function(e) {
            e.preventDefault();
            this.classList.add('drop-ready');
        };

        col.ondragover = function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        };

        col.ondragleave = function(e) {
            // Solo quitar si salimos de la columna completa
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drop-ready');
            }
        };

        col.ondrop = function(e) {
            e.preventDefault();
            this.classList.remove('drop-ready');

            if (!draggedCardId) return;

            cambiarEstadoConArea(draggedCardId, 'en_proceso', 'Mover a En Proceso');
        };
    });
});

/**
 * Cambia el estado de una atención asignando el área actual
 * @param {number} idAtencion - ID de la atención
 * @param {string} nuevoEstado - Nuevo estado a asignar
 * @param {string} accion - Nombre de la acción
 */
async function cambiarEstadoConArea(idAtencion, nuevoEstado, accion) {
    try {
        const response = await fetch(BASE_URL + 'admin/kanban/cambiarEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                idatencion: idAtencion,
                estado: nuevoEstado,
                accion: accion,
                idareaagencia: AREA_ACTUAL
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const res = await response.json();

        if (res.status === 'success') {
            location.reload();
        } else {
            alert(res.msg || 'Error al cambiar el estado');
        }
    } catch (error) {
        console.error('Error al cambiar estado:', error);
        alert('Error al cambiar el estado. Intenta nuevamente.');
    }
}

