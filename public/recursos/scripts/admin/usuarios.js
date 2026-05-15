document.addEventListener('DOMContentLoaded', function () {

    const tabla = document.querySelector('#tabla-usuarios');
    const formulario = document.querySelector('#form-usuario');
    const btnGuardar = document.querySelector('#btn-guardar');
    const inputTipoRegistro = document.querySelector('#tipo_registro');

    // ─── NOTIFICACIÓN (SweetAlert2) ────────────────────────
    function notificar(mensaje, tipo = 'info') {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        Swal.fire({
            text: mensaje,
            icon: tipo,
            confirmButtonColor: '#F5C400',
            background: isLight ? '#ffffff' : '#161616',
            color: isLight ? '#1e293b' : '#ffffff',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }


    // ─── LISTAR USUARIOS ───────────────────────────────────
    async function obtenerUsuarios(search = '') {
        try {
            const baseUrl = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
            const query = search ? '?search=' + encodeURIComponent(search) : '';
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
            <td class="td-nombre" data-label="Nombre">${u.nombre} ${u.apellidos}</td>
            <td class="td-usuario" data-label="Usuario">${u.usuario ?? '-'}</td>
            <td class="td-correo" data-label="Correo">${u.correo}</td>
            <td data-label="Rol">-</td>
            <td data-label="Área / Empresa">-</td>
            <td data-label="Estado">-</td>
            <td data-label="Acciones">-</td>
        </tr>`;
                    return;
                }

                //  ROLES (Responsable > Empleado > Cliente)
                let rolClase = 'rol-empleado';
                let rolTexto = 'Empleado';

                const esCliente = u.rol === 'cliente';

                const esResponsable = u.rol === 'responsable_area'
                    || u.esresponsable === true
                    || u.esresponsable === 't'
                    || u.esresponsable == 1;

                if (esCliente) {
                    rolClase = 'rol-cliente';
                    rolTexto = 'Cliente';
                }
                else if (esResponsable) {
                    rolClase = 'rol-responsable';
                    rolTexto = 'Responsable';
                }

                const rolBadge = `<span class="rol-badge ${rolClase}">${rolTexto}</span>`;

                // 🔹 ÁREA
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

                // Botón Reasignar (Solo para Clientes o Responsables de Área ACTIVO)
                let btnReasignar = '';
                if ((esCliente || esResponsable) && u.estado) {
                    btnReasignar = `<button class="btn-icon btn-icon-reasignar" onclick="abrirModalReasignar(${u.id})" title="Reasignar responsable" style="cursor:pointer;"><i class="bi bi-person-gear"></i></button>`;
                }

                tabla.innerHTML += `
                <tr>
                    <td class="td-nombre" data-label="Nombre">${u.nombre} ${u.apellidos}</td>
                    <td class="td-usuario" data-label="Usuario">${u.usuario ?? '-'}</td>
                    <td class="td-correo" data-label="Correo/Telf.">
                        <div class="user-email">${u.correo}</div>
                        <div class="user-phone"><i class="bi bi-telephone"></i> ${u.telefono ?? '-'}</div>
                    </td>
                    <td data-label="Rol">${rolBadge}</td>
                    <td class="td-area-empresa" data-label="Área / Empresa">${areaEmpresa}</td>
                    <td data-label="Estado">${badge}</td>
                    <td data-label="Acciones">
                        <div class="acciones-contenedor">
                            ${btnEditar}
                            ${btnToggle}
                            ${btnReasignar}
                        </div>
                    </td>
                </tr>`;
            });

        } catch (error) {
            console.error('Error al procesar usuarios:', error);
        }
    }

    // ─── CARGAR ÁREAS DE EMPRESA DINÁMICAMENTE ─────────────
    async function cargarAreasEmpresa(idEmpresa, idAreaSeleccionada = null) {
        const selectArea = document.querySelector('#idarea');
        if (!idEmpresa) {
            selectArea.innerHTML = '<option value="">— Primero selecciona una empresa —</option>';
            return;
        }

        selectArea.innerHTML = '<option value="">— Cargando áreas... —</option>';

        try {
            const response = await fetch(BASE_URL + 'admin/usuarios/listarAreasPorEmpresa/' + idEmpresa);
            const areas = await response.json();

            selectArea.innerHTML = '<option value="">— Selecciona el área —</option>';
            areas.forEach(a => {
                const selected = idAreaSeleccionada == a.id ? 'selected' : '';
                selectArea.innerHTML += `<option value="${a.id}" ${selected}>${a.nombre}</option>`;
            });

            if (areas.length === 0) {
                selectArea.innerHTML = '<option value="">No hay áreas creadas para esta empresa</option>';
            }
        } catch (error) {
            console.error('Error al cargar áreas:', error);
            selectArea.innerHTML = '<option value="">Error al cargar áreas</option>';
        }
    }

    document.querySelector('#idempresa').addEventListener('change', function() {
        cargarAreasEmpresa(this.value);
    });

    // ─── CONFIGURAR FORMULARIO SEGÚN TIPO ─────────────────
    function configurarFormulario(tipo) {
        inputTipoRegistro.value = tipo;

        const grupoEmpresa = document.querySelector('#grupo-empresa');
        const grupoAreaEmpresa = document.querySelector('#grupo-area-empresa');
        const grupoRazonSocial = document.querySelector('#grupo-razonsocial');
        const camposEmpleado = document.querySelector('#campos-empleado');
        const tipodoc = document.querySelector('#tipodoc');
        const labelNombre = document.querySelector('#label-nombre');
        const modalTitulo = document.querySelector('#modal-titulo');

        // Ocultar todo primero
        [grupoEmpresa, grupoAreaEmpresa, grupoRazonSocial, camposEmpleado].forEach(el => {
            if (el) el.style.display = 'none';
        });

        // Reset inputs
        document.querySelector('#idempresa').required = false;
        document.querySelector('#idarea').required = false;
        document.querySelector('#razonsocial').required = false;
        const areaAgencia = document.querySelector('#idarea_agencia');
        areaAgencia.required = false;
        areaAgencia.disabled = false;

        const isEdit = !!formulario.dataset.editId;

        if (tipo === 'empleado') {
            modalTitulo.textContent = isEdit ? 'Editar Colaborador (Agencia)' : 'Nuevo Colaborador (Agencia)';
            camposEmpleado.style.display = 'block';
            labelNombre.textContent = 'Nombre';
            tipodoc.innerHTML = '<option value="DNI">DNI</option><option value="CE">CE</option>';
            areaAgencia.required = true;
            areaAgencia.disabled = isEdit;

        } else if (tipo === 'responsable_area') {
            modalTitulo.textContent = 'Registrar Responsable de Área (Empresa)';
            grupoEmpresa.style.display = 'block';
            grupoAreaEmpresa.style.display = 'block';

            document.querySelector('#idempresa').required = true;
            document.querySelector('#idarea').required = true;

            labelNombre.textContent = 'Nombre del Responsable';
            tipodoc.innerHTML = '<option value="DNI">DNI</option><option value="CE">CE</option>';

        } else if (tipo === 'cliente') {
            modalTitulo.textContent = 'Crear Cliente';
            grupoRazonSocial.style.display = 'block';
            document.querySelector('#razonsocial').required = true;
            labelNombre.textContent = 'Nombre del Responsable';
            tipodoc.innerHTML = '<option value="RUC">RUC</option>';
        }

        actualizarValidacionDoc(tipodoc.value, '#numerodoc');
    }

    function actualizarValidacionDoc(tipo, inputSelector) {
        const nd = document.querySelector(inputSelector);
        if (!nd) return;
        
        const limites = {
            DNI: { max: '8', min: '8', ph: '8 dígitos' },
            RUC: { max: '11', min: '11', ph: '11 dígitos' },
            CE: { max: '12', min: '9', ph: '9-12 caracteres' }
        };
        
        const l = limites[tipo];
        if (!l) return;
        
        nd.setAttribute('maxlength', l.max);
        nd.setAttribute('minlength', l.min);
        nd.placeholder = l.ph;
    }

    document.querySelector('#tipodoc').addEventListener('change', function() {
        actualizarValidacionDoc(this.value, '#numerodoc');
    });

    async function verificarEstadoArea(idArea, excludeId = null) {
        const msgDiv = document.querySelector('#area-status-msg');
        if (excludeId || !idArea) {
            msgDiv.style.display = 'none';
            return;
        }

        try {
            const url = BASE_URL + 'admin/usuarios/verificarAreaResponsable/' + idArea;
            const response = await fetch(url);
            const data = await response.json();
            
            msgDiv.style.display = 'block';
            if (data.ocupado) {
                msgDiv.innerHTML = `<i class="bi bi-info-circle-fill text-info"></i> El área ya cuenta con un responsable: <strong>${data.nombre}</strong>. El nuevo usuario será un colaborador.`;
                msgDiv.className = 'mt-1 small text-muted';
            } else {
                msgDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-warning"></i> Esta área no tiene responsable. El usuario será asignado como <strong>RESPONSABLE</strong> automáticamente.`;
                msgDiv.className = 'mt-1 small text-warning';
            }
        } catch (error) {
            console.error('Error al verificar estado del área:', error);
        }
    }

    document.querySelector('#idarea_agencia').addEventListener('change', function () {
        const editId = formulario.dataset.editId;
        verificarEstadoArea(this.value, editId);
    });

    // ─── EVENTOS DE OPCIONES ────────────────────────────────
    document.querySelector('#opcion-empleado').addEventListener('click', (e) => {
        e.preventDefault();
        formulario.reset();
        delete formulario.dataset.editId;
        configurarFormulario('empleado');
        document.querySelector('#area-status-msg').style.display = 'none';
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
            nombre: document.querySelector('#nombre').value,
            apellidos: document.querySelector('#apellidos').value,
            correo: document.querySelector('#correo').value,
            telefono: document.querySelector('#telefono').value,
            tipodoc: document.querySelector('#tipodoc').value,
            numerodoc: document.querySelector('#numerodoc').value,
            usuario: document.querySelector('#usuario').value,
        };

        const clave = document.querySelector('#clave').value;
        if (clave) datos.clave = clave;

        if (editId) {
            datos.rol = rolOriginal;
            if (rolOriginal === 'empleado') {
                datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
            } else if (rolOriginal === 'cliente') {
                // Si es un responsable de área editando
                const idArea = document.querySelector('#idarea').value;
                if (idArea) datos.idarea = idArea;
            }
        } else {
            if (tipo === 'empleado') {
                datos.rol = 'empleado';
                datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
            } else if (tipo === 'responsable_area') {
                datos.rol = 'responsable_area';
                datos.idempresa = document.querySelector('#idempresa').value;
                datos.idarea = document.querySelector('#idarea').value;
            } else if (tipo === 'cliente') {
                datos.rol = 'cliente';
                datos.razonsocial = document.querySelector('#razonsocial').value;
            }
        }

        const url = editId ? BASE_URL + 'admin/usuarios/editar/' + editId : BASE_URL + 'admin/usuarios/registrar';
        const method = editId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json' }, body:
                JSON.stringify(datos)
        });
        const data = await response.json();

        if (!data.success) {
            notificar(data.message, 'error');
            return;
        }

        notificar(data.message, 'success');

        $('#modal-usuario').modal('hide');
        formulario.reset();
        delete formulario.dataset.editId;
        delete formulario.dataset.rolOriginal;
        obtenerUsuarios();
    });

    // ─── EDITAR USUARIO ────────────────────────────────────
    window.editarUsuario = async function (id) {
        const response = await fetch(BASE_URL + 'admin/usuarios/obtener/' + id);
        const u = await response.json();

        formulario.reset();
        formulario.dataset.editId = id;
        formulario.dataset.rolOriginal = u.rol;

        const inputClave = document.querySelector('#clave');
        inputClave.removeAttribute('required');
        inputClave.placeholder = 'Dejar en blanco para no cambiar';

        if (u.rol === 'empleado') {
            configurarFormulario('empleado');
            setTimeout(() => {
                document.querySelector('#idarea_agencia').value = u.idarea_agencia ?? '';
                verificarEstadoArea(u.idarea_agencia, id);
            }, 50);
        } else if (u.rol === 'cliente') {
            if (u.idarea) {
                configurarFormulario('responsable_area');
                setTimeout(() => {
                    document.querySelector('#idempresa').value = u.idempresa ?? '';
                    cargarAreasEmpresa(u.idempresa, u.idarea);
                }, 50);
            } else {
                configurarFormulario('cliente');
                setTimeout(() => {
                    document.querySelector('#razonsocial').value = u.razonsocial ?? '';
                }, 50);
            }
        }

        setTimeout(() => {
            document.querySelector('#nombre').value = u.nombre ?? '';
            document.querySelector('#apellidos').value = u.apellidos ?? '';
            document.querySelector('#correo').value = u.correo ?? '';
            document.querySelector('#telefono').value = u.telefono ?? '';
            document.querySelector('#tipodoc').value = u.tipodoc ?? '';
            document.querySelector('#numerodoc').value = u.numerodoc ?? '';
            document.querySelector('#usuario').value = u.usuario ?? '';
            actualizarValidacionDoc(u.tipodoc, '#numerodoc');
        }, 50);

        $('#modal-usuario').modal('show');
    };

    // ─── TOGGLE ESTADO ─────────────────────────────────────
    window.toggleEstado = async function (id, estadoActual) {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        const mensaje = estadoActual
            ? '¿Seguro que deseas deshabilitar este usuario?'
            : '¿Deseas volver a habilitar este usuario?';

        Swal.fire({
            title: '¿Confirmar Cambio?',
            text: mensaje,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F5C400',
            cancelButtonColor: isLight ? '#cbd5e1' : '#71717a',
            confirmButtonText: 'Sí, Continuar',
            cancelButtonText: 'Cancelar',
            background: isLight ? '#ffffff' : '#161616',
            color: isLight ? '#1e293b' : '#ffffff'
        }).then(async (result) => {

            if (!result.isConfirmed) return;

            const response = await fetch(BASE_URL + 'admin/usuarios/toggleEstado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, estado: !estadoActual })
            });
            const data = await response.json();

            notificar(data.message, data.success ? 'success' : 'error');
            if (data.success) obtenerUsuarios();
        });
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

    // ─── REASIGNACIÓN ──────────────────────────────────────
    window.abrirModalReasignar = async function (id) {
        try {
            const response = await fetch(BASE_URL + 'admin/usuarios/infoReasignar/' + id);
            const data = await response.json();

            if (!data.success) {
                notificar(data.message);
                return;
            }

            const infoDiv = document.querySelector('#info-reasignar-actual');
            const formCliente = document.querySelector('#form-reasignar-cliente');
            const formEmpleado = document.querySelector('#form-reasignar-empleado');
            const historialDiv = document.querySelector('#historial-reasignaciones');
            const btnProcesar = document.querySelector('#btn-procesar-reasignar');

            formCliente.reset();
            formEmpleado.reset();
            formCliente.style.display = 'none';
            formEmpleado.style.display = 'none';
            historialDiv.style.display = 'none';
            btnProcesar.dataset.tipo = data.tipo;

            const formatFecha = (f) => f ? new Date(f).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

            if (data.tipo === 'cliente') {
                formCliente.style.display = 'block';
                historialDiv.style.display = 'block';

                document.querySelector('#rea-id-registro-actual').value = data.actual.id || '';
                document.querySelector('#rea-id-empresa').value = data.actual.idempresa;
                document.querySelector('#rea-id-usuario-anterior').value = data.usuario.id;
                document.querySelector('#rea-id-area').value = data.usuario.idarea || '';

                infoDiv.innerHTML = `
                    <div class="p-3 rounded mb-2" style="background: rgba(245, 196, 0, 0.1); border: 1px solid rgba(245, 196, 0, 0.3);">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="bi bi-building text-dark fs-4"></i>
                                </div>
                            </div>
                            <div class="col">
                                 <h6 class="rea-text-main mb-0 font-weight-bold" style="font-size: 16px;">${data.actual.nombreempresa}</h6>
                                 <p class="mb-0 rea-text-muted mt-1" style="font-size: 12px;"><i class="bi bi-diagram-3 mr-1"></i> Área: <span class="rea-text-main">${data.actual.nombre_area || 'General'}</span></p>
                             </div>
                        </div>
                    </div>
                `;

                const listaHistorial = document.querySelector('#lista-historial-reasignar');
                listaHistorial.innerHTML = '';
                const reasignacionesPasadas = data.historial ? data.historial.filter(h => h.estado !== 'activo') : [];

                if (reasignacionesPasadas.length > 0) {
                    reasignacionesPasadas.forEach(h => {
                        listaHistorial.innerHTML += `
                            <tr style="border-bottom: 1px solid #222;">
                                <td class="py-2">
                                    <div class="font-weight-bold rea-text-main" style="font-size: 12px;">${h.nombre || 'N/A'} ${h.apellidos || ''}</div>
                                </td>
                                <td class="py-2 rea-text-main" style="font-size: 11px;">${formatFecha(h.fecha_inicio)}</td>
                                <td class="py-2 rea-text-main" style="font-size: 11px;">${formatFecha(h.fecha_fin)}</td>
                                <td class="py-2 text-center"><span class="badge bg-secondary text-white">ANTERIOR</span></td>
                            </tr>
                        `;
                    });
                } else {
                    listaHistorial.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin historial de reasignaciones.</td></tr>';
                }

            } else if (data.tipo === 'empleado') {
                formEmpleado.style.display = 'block';
                document.querySelector('#rea-emp-id-actual').value = data.actual.id;

                infoDiv.innerHTML = `
                    <div class="p-3 rounded mb-2" style="background: rgba(167, 139, 250, 0.1); border: 1px solid rgba(167, 139, 250, 0.3);">
                        <h6 class="rea-text-main mb-0 font-weight-bold">${data.area ? data.area.nombre : 'Área no especificada'}</h6>
                        <p class="mb-0 rea-text-muted small">Jefe Actual: ${data.actual.nombre} ${data.actual.apellidos}</p>
                    </div>
                `;

                const select = document.querySelector('#rea-emp-nuevo');
                select.innerHTML = '<option value="">— Seleccionar Sucesor —</option>';
                if (data.asignables) {
                    data.asignables.forEach(e => {
                        if (e.id != data.actual.id) {
                            select.innerHTML += `<option value="${e.id}">${e.nombre} ${e.apellidos}</option>`;
                        }
                    });
                }
            }

            $('#modal-reasignar').modal('show');
        } catch (e) {
            console.error(e);
            notificar('Error técnico al cargar el panel');
        }
    };

    document.querySelector('#btn-procesar-reasignar').addEventListener('click', async function () {
        const tipo = this.dataset.tipo;
        let url = '';
        let datos = {};

        if (tipo === 'cliente') {
            const form = document.querySelector('#form-reasignar-cliente');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            url = BASE_URL + 'admin/usuarios/reasignarCliente';
            datos = {
                id_registro_actual: document.querySelector('#rea-id-registro-actual').value,
                id_usuario_anterior: document.querySelector('#rea-id-usuario-anterior').value,
                id_empresa: document.querySelector('#rea-id-empresa').value,
                id_area: document.querySelector('#rea-id-area').value,
                tipodoc: document.querySelector('#rea-tipodoc').value,
                numerodoc: document.querySelector('#rea-numerodoc').value,
                nombre: document.querySelector('#rea-nombre').value,
                apellidos: document.querySelector('#rea-apellidos').value,
                correo: document.querySelector('#rea-correo').value,
                telefono: document.querySelector('#rea-telefono').value,
                usuario: document.querySelector('#rea-usuario').value,
                clave: document.querySelector('#rea-clave').value
            };

        } else if (tipo === 'empleado') {
            const idNuevo = document.querySelector('#rea-emp-nuevo').value;
            if (!idNuevo) {
                notificar('Selecciona al nuevo colaborador.');
                return;
            }

            url = BASE_URL + 'admin/usuarios/reasignarEmpleadoArea';
            datos = {
                id_actual: document.querySelector('#rea-emp-id-actual').value,
                id_nuevo: idNuevo
            };
        }

        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        Swal.fire({
            title: '¿Confirmar Cambio?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F5C400',
            confirmButtonText: 'Sí, Confirmar',
            cancelButtonText: 'Cancelar',
            background: isLight ? '#ffffff' : '#161616',
            color: isLight ? '#1e293b' : '#ffffff'
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                const res = await response.json();
                notificar(res.message, res.success ? 'success' : 'error');
                if (res.success) {
                    $('#modal-reasignar').modal('hide');
                    obtenerUsuarios();
                }
            } catch (e) {
                notificar('Error al procesar el cambio', 'error');
            }
        });
    });

    obtenerUsuarios();
});