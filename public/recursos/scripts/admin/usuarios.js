document.addEventListener('DOMContentLoaded', function () {

      const tabla      = document.querySelector('#tabla-usuarios');
      const formulario = document.querySelector('#form-usuario');
      const btnGuardar = document.querySelector('#btn-guardar');
      const inputTipoRegistro = document.querySelector('#tipo_registro');

      // ─── NOTIFICACIÓN ──────────────────────────────────────
      function notificar(mensaje) {
          alert(mensaje);
      }

      // ─── LISTAR USUARIOS ───────────────────────────────────
      async function obtenerUsuarios(search = '') {
    try {
        const baseUrl  = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
        const query    = search ? '?search=' + encodeURIComponent(search) : '';
        const response = await fetch(baseUrl + 'admin/usuarios/listar' + query);
        
        if (!response.ok) {
            console.error('Error al obtener usuarios:', response.statusText);
            return;
        }
        
        const data = await response.json();

        tabla.innerHTML = '';
        if (data.length === 0) {
            tabla.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron resultados para su búsqueda.</td></tr>';
            return;
        }

        data.forEach(u => {

            if (u.rol && u.rol.toLowerCase().includes('administrador')) {
    tabla.innerHTML += `
        <tr>
            <td class="td-nombre">${u.nombre} ${u.apellidos}</td>
            <td class="td-usuario">${u.usuario ?? '-'}</td>
            <td class="td-correo">${u.correo}</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
        </tr>`;
    return; 
}
            
            //  ROLES (Responsable limpio)
            let rolClase = 'rol-empleado';
            let rolTexto = 'Empleado';

            const esResponsable = u.rol === 'responsable_area' 
                || u.esresponsable === true 
                || u.esresponsable === 't' 
                || u.esresponsable == 1;

            if (esResponsable) {
                rolClase = 'rol-responsable';
                rolTexto = 'Responsable';
            } else if (u.rol === 'cliente') {
                rolClase = 'rol-cliente';
                rolTexto = 'Cliente';
            }

            const rolBadge = `<span class="rol-badge ${rolClase}">${rolTexto}</span>`;

            // 🔹 ÁREA (QUITAR EMPRESA ENTRE PARÉNTESIS)
            let areaMostrar = u.area_completa || u.area_nombre || '-';

            if (areaMostrar.includes('(')) {
                areaMostrar = areaMostrar.split(' (')[0];
            }

            //  EMPRESA
            const empresaMostrar = u.empresa_nombre || '';

            //  MOSTRAR EN DOS LÍNEAS
           const mostrarIcono = u.rol === 'cliente' || (u.empresa_nombre && u.empresa_nombre !== '');

const areaEmpresa = `
    <div class="area-empresa-contenedor">
        ${mostrarIcono ? '<i class="bi bi-building area-icon"></i>' : ''}
        <div class="area-info">
            <div class="area-nombre">${areaMostrar}</div>
            ${empresaMostrar ? `<div class="area-empresa">${empresaMostrar}</div>` : ''}
        </div>
    </div>`;

            //  ESTADO
            const badge = u.estado 
                ? `<span class="badge-activo">Activo</span>` 
                : `<span class="badge-inactivo">Inactivo</span>`;

            //  BOTONES
            const btnEditar = `<button class="btn-icon btn-icon-editar" onclick="editarUsuario(${u.id})" title="Editar usuario" style="cursor:pointer;">✎</button>`;
            const btnToggle = u.estado
                ? `<button class="btn-icon btn-icon-toggle activo" onclick="toggleEstado(${u.id}, true)" title="Desactivar usuario" style="cursor:pointer;">✕</button>`
                : `<button class="btn-icon btn-icon-toggle inactivo" onclick="toggleEstado(${u.id}, false)" title="Activar usuario" style="cursor:pointer;">✓</button>`;

            tabla.innerHTML += `
                <tr>
                    <td class="td-nombre">${u.nombre} ${u.apellidos}</td>
                    <td class="td-usuario">${u.usuario ?? '-'}</td>
                    <td class="td-correo">${u.correo}</td>
                    <td>${rolBadge}</td>
                    <td class="td-area-empresa">${areaEmpresa}</td>
                    <td>${badge}</td>
                    <td>
                        <div class="acciones-contenedor">
                            ${btnEditar}
                            ${btnToggle}
                        </div>
                    </td>
                </tr>`;
        });

    } catch (error) {
        console.error('Error al procesar usuarios:', error);
    }
}

      // ─── CONFIGURAR FORMULARIO SEGÚN TIPO ─────────────────
      function configurarFormulario(tipo) {
          inputTipoRegistro.value = tipo;

          const grupoEmpresa = document.querySelector('#grupo-empresa');
          const grupoNombreArea = document.querySelector('#grupo-nombre-area');
          const grupoDescArea = document.querySelector('#grupo-descripcion-area');
          const grupoRazonSocial = document.querySelector('#grupo-razonsocial');
          const camposEmpleado = document.querySelector('#campos-empleado');
          const tipodoc = document.querySelector('#tipodoc');
          const labelNombre = document.querySelector('#label-nombre');
          const modalTitulo = document.querySelector('#modal-titulo');

          // Ocultar todo primero
          [grupoEmpresa, grupoNombreArea, grupoDescArea, grupoRazonSocial, camposEmpleado].forEach(el => {
              if (el) el.style.display = 'none';
          });

          // Deshabilitar required de campos condicionales
          document.querySelector('#idempresa').required = false;
          document.querySelector('#nombre_area').required = false;
          document.querySelector('#razonsocial').required = false;
          document.querySelector('#idarea_agencia').required = false;

          if (tipo === 'empleado') {
              modalTitulo.textContent = 'Crear Empleado';
              camposEmpleado.style.display = 'block';
              labelNombre.textContent = 'Nombre';
              tipodoc.innerHTML = '<option value="DNI">DNI</option><option value="CE">CE</option>';
              document.querySelector('#idarea_agencia').required = true;

          } else if (tipo === 'responsable_area') {
              modalTitulo.textContent = 'Crear Área con Responsable';
              grupoEmpresa.style.display = 'block';
              grupoNombreArea.style.display = 'block';
              grupoDescArea.style.display = 'block';

              // Hacer requeridos
              document.querySelector('#idempresa').required = true;
              document.querySelector('#nombre_area').required = true;

              labelNombre.textContent = 'Nombre del Responsable';
              tipodoc.innerHTML = '<option value="DNI">DNI</option><option value="CE">CE</option>';

          } else if (tipo === 'cliente') {
              modalTitulo.textContent = 'Crear Cliente';
              grupoRazonSocial.style.display = 'block';
              document.querySelector('#razonsocial').required = true;
              labelNombre.textContent = 'Nombre del Responsable';
              tipodoc.innerHTML = '<option value="RUC">RUC</option>';
          }

          actualizarDoc(tipodoc.value);
      }

      function actualizarDoc(tipo) {
          const nd = document.querySelector('#numerodoc');
          const limites = {
              DNI: { max: '8',  min: '8',  ph: '8 dígitos' },
              RUC: { max: '11', min: '11', ph: '11 dígitos' },
              CE:  { max: '12', min: '9',  ph: '9-12 caracteres' }
          };
          const l = limites[tipo];
          if (!l) return;
          nd.setAttribute('maxlength', l.max);
          nd.setAttribute('minlength', l.min);
          nd.placeholder = l.ph;
      }

      document.querySelector('#tipodoc').addEventListener('change', () =>
  actualizarDoc(document.querySelector('#tipodoc').value));

      // ─── EVENTOS DE OPCIONES ────────────────────────────────
      document.querySelector('#opcion-empleado').addEventListener('click', (e) => {
          e.preventDefault();
          formulario.reset();
          delete formulario.dataset.editId;
          configurarFormulario('empleado');
          $('#modal-usuario').modal('show');
      });

      document.querySelector('#opcion-area').addEventListener('click', (e) => {
          e.preventDefault();
          formulario.reset();
          delete formulario.dataset.editId;
          configurarFormulario('responsable_area');
          $('#modal-usuario').modal('show');
      });

      // ─── GUARDAR (REGISTRAR O EDITAR) ──────────────────────
  formulario.addEventListener('submit', async function (e) {
      e.preventDefault();

      const editId = formulario.dataset.editId;
      const tipo = inputTipoRegistro.value;
      const rolOriginal = formulario.dataset.rolOriginal;

      const datos = {
          nombre:    document.querySelector('#nombre').value,
          apellidos: document.querySelector('#apellidos').value,
          correo:    document.querySelector('#correo').value,
          telefono:  document.querySelector('#telefono').value,
          tipodoc:   document.querySelector('#tipodoc').value,
          numerodoc: document.querySelector('#numerodoc').value,
          usuario:   document.querySelector('#usuario').value,
      };

      const clave = document.querySelector('#clave').value;
      if (clave) datos.clave = clave;

      if (editId) {
          // Modo edición - mantener rol original
          datos.rol = rolOriginal;

          // Si es empleado, enviar campos de área y responsable
          if (rolOriginal === 'empleado') {
              datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
              datos.esresponsable = document.querySelector('#esresponsable').checked;
          }
      } else {
          // Modo creación según tipo
          if (tipo === 'empleado') {
              datos.rol = 'empleado';
              datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
              datos.esresponsable = document.querySelector('#esresponsable').checked;
          } else if (tipo === 'responsable_area') {
              datos.rol = 'responsable_area';
              datos.idempresa = document.querySelector('#idempresa').value;
              datos.nombre_area = document.querySelector('#nombre_area').value;
              datos.descripcion_area = document.querySelector('#descripcion_area').value;
          } else if (tipo === 'cliente') {
              datos.rol = 'cliente';
              datos.razonsocial = document.querySelector('#razonsocial').value;
          }
      }

      const url    = editId ? BASE_URL + 'admin/usuarios/editar/' + editId : BASE_URL + 'admin/usuarios/registrar';
      const method = editId ? 'PUT' : 'POST';

      const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body:
  JSON.stringify(datos) });
      const data     = await response.json();

      notificar(data.message);
      if (!data.success) return;

      $('#modal-usuario').modal('hide');
      formulario.reset();
      delete formulario.dataset.editId;
      delete formulario.dataset.rolOriginal;
      obtenerUsuarios();
  });

  // ─── EDITAR USUARIO ────────────────────────────────────
  window.editarUsuario = async function (id) {
      const response = await fetch(BASE_URL + 'admin/usuarios/obtener/' + id);
      const u        = await response.json();

      formulario.reset();
      formulario.dataset.editId = id;
      formulario.dataset.rolOriginal = u.rol;

      const inputClave = document.querySelector('#clave');
      inputClave.removeAttribute('required');
      inputClave.placeholder = 'Dejar en blanco para no cambiar';

      // Configurar según el rol existente
      if (u.rol === 'empleado') {
          configurarFormulario('empleado');
          setTimeout(() => {
              document.querySelector('#idarea_agencia').value = u.idarea_agencia ?? '';
              // Fix: Postgres devuelve 't' o 'f' como string, o true/false boolean
              const esResponsable = u.esresponsable === true || u.esresponsable === 't' || u.esresponsable === 1;
              document.querySelector('#esresponsable').checked = esResponsable;
          }, 50);
      } else if (u.rol === 'cliente') {
          if (u.idarea) {
              // Es un responsable de área
              configurarFormulario('responsable_area');
          } else {
              configurarFormulario('cliente');
              setTimeout(() => {
                  document.querySelector('#razonsocial').value = u.razonsocial ?? '';
              }, 50);
          }
      }

      setTimeout(() => {
          document.querySelector('#nombre').value    = u.nombre    ?? '';
          document.querySelector('#apellidos').value = u.apellidos ?? '';
          document.querySelector('#correo').value    = u.correo    ?? '';
          document.querySelector('#telefono').value  = u.telefono  ?? '';
          document.querySelector('#tipodoc').value   = u.tipodoc   ?? '';
          document.querySelector('#numerodoc').value = u.numerodoc ?? '';
          document.querySelector('#usuario').value   = u.usuario   ?? '';
          actualizarDoc(u.tipodoc);
      }, 50);

      $('#modal-usuario').modal('show');
  };

      // ─── TOGGLE ESTADO ─────────────────────────────────────
      window.toggleEstado = async function (id, estadoActual) {
          const mensaje = estadoActual
              ? '¿Seguro que deseas deshabilitar este usuario?'
              : '¿Deseas volver a habilitar este usuario?';

          if (!confirm(mensaje)) return;

          const response = await fetch(BASE_URL + 'admin/usuarios/toggleEstado', {
              method:  'POST',
              headers: { 'Content-Type': 'application/json' },
              body:    JSON.stringify({ id, estado: !estadoActual })
          });
          const data = await response.json();

          notificar(data.message);
          if (data.success) obtenerUsuarios();
      };

      // ─── BUSCADOR ──────────────────────────────────────────
      const inputBusqueda = document.querySelector('#input-busqueda');
      let timeoutBusqueda = null;

      inputBusqueda.addEventListener('input', function (e) {
          const valor = e.target.value;
          clearTimeout(timeoutBusqueda);
          timeoutBusqueda = setTimeout(() => {
              obtenerUsuarios(valor);
          }, 1500);
      });

      // ─── INICIO ────────────────────────────────────────────
      obtenerUsuarios();
  });