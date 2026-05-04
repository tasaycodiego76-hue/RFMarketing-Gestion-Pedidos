document.addEventListener('DOMContentLoaded', function () {

    const tabla = document.querySelector('#tabla-usuarios');
    const formulario = document.querySelector('#form-usuario');
    const btnGuardar = document.querySelector('#btn-guardar');
    const inputTipoRegistro = document.querySelector('#tipo_registro');

    // ─── NOTIFICACIÓN (SweetAlert2) ────────────────────────
    function notificar(mensaje, tipo = 'info') {
        Swal.fire({
            text: mensaje,
            icon: tipo,
            confirmButtonColor: '#F5C400',
            background: '#161616',
            color: '#ffffff',
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

                //  ROLES (Responsable > Empleado > Cliente)
                let rolClase = 'rol-empleado';
                let rolTexto = 'Empleado';

                // PRIORIDAD 1: CLIENTE (NO se debe sobreescribir)
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

                // Botón Reasignar (Solo para Clientes o Responsables de Área ACTIVO)
                let btnReasignar = '';
                if ((esCliente || esResponsable) && u.estado) {
                    btnReasignar = `<button class="btn-icon btn-icon-reasignar" onclick="abrirModalReasignar(${u.id})" title="Reasignar responsable" style="cursor:pointer;"><i class="bi bi-person-gear"></i></button>`;
                }

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
                            ${btnReasignar}
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
        const areaAgencia = document.querySelector('#idarea_agencia');
        areaAgencia.required = false;
        areaAgencia.disabled = false;

        const isEdit = !!formulario.dataset.editId;

        if (tipo === 'empleado') {
            modalTitulo.textContent = isEdit ? 'Editar Colaborador' : 'Nuevo Colaborador';
            camposEmpleado.style.display = 'block';
            labelNombre.textContent = 'Nombre';
            tipodoc.innerHTML = '<option value="DNI">DNI</option><option value="CE">CE</option>';
            areaAgencia.required = true;
            
            // Restricción: No se puede cambiar el área de la agencia al editar
            areaAgencia.disabled = isEdit;

        } else if (tipo === 'responsable_area') {
            modalTitulo.textContent = 'Crear Área de Empresa + Responsable';
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

    document.querySelector('#tipodoc').addEventListener('change', () =>
        actualizarDoc(document.querySelector('#tipodoc').value));

    // Función para mostrar un mensaje informativo sobre el estado del área
    async function verificarEstadoArea(idArea, excludeId = null) {
        const msgDiv = document.querySelector('#area-status-msg');
        const isEdit = !!excludeId;

        // En modo EDICIÓN no mostramos ningún mensaje informativo sobre el área
        if (isEdit || !idArea) {
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
            // Modo edición - mantener rol original
            datos.rol = rolOriginal;

            // Si es empleado, enviar campos de área
            if (rolOriginal === 'empleado') {
                datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
            }
        } else {
            // Modo creación según tipo
            if (tipo === 'empleado') {
                datos.rol = 'empleado';
                datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
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

        // Configurar según el rol existente
        if (u.rol === 'empleado') {
            configurarFormulario('empleado');
            setTimeout(() => {
                document.querySelector('#idarea_agencia').value = u.idarea_agencia ?? '';
                verificarEstadoArea(u.idarea_agencia, id);
            }, 50);
        } else if (u.rol === 'cliente') {
            if (u.idarea) {
                // Es un responsable de área de empresa
                configurarFormulario('responsable_area');
                setTimeout(() => {
                    document.querySelector('#idempresa').value = u.idempresa ?? '';
                    document.querySelector('#nombre_area').value = u.area_empresa_nombre ?? '';
                    document.querySelector('#descripcion_area').value = u.area_empresa_desc ?? '';
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
            actualizarDoc(u.tipodoc);
        }, 50);

        $('#modal-usuario').modal('show');
    };

    // ─── TOGGLE ESTADO ─────────────────────────────────────
    window.toggleEstado = async function (id, estadoActual) {
        const mensaje = estadoActual
            ? '¿Seguro que deseas deshabilitar este usuario?'
            : '¿Deseas volver a habilitar este usuario?';

        Swal.fire({
            title: '¿Confirmar Cambio?',
            text: mensaje,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F5C400',
            cancelButtonColor: '#71717a',
            confirmButtonText: 'Sí, Continuar',
            cancelButtonText: 'Cancelar',
            background: '#161616',
            color: '#ffffff'
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

            // Reset forms
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
                                 <h6 class="text-white mb-0 font-weight-bold" style="font-size: 16px;">${data.actual.nombreempresa} <span class="text-warning ml-2" style="font-size: 11px; background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px;">RUC: ${data.actual.ruc}</span></h6>
                                 <p class="mb-0 text-white-50 mt-1" style="font-size: 12px;"><i class="bi bi-diagram-3 mr-1"></i> Área: <span class="text-white">${data.actual.nombre_area || 'General'}</span></p>
                             </div>
                        </div>
                        <hr style="border-top: 1px solid rgba(255,255,255,0.1); margin: 12px 0;">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0 border-right border-secondary">
                                <p class="mb-0 text-white-50 small">Responsable en funciones</p>
                                <p class="mb-0 text-white font-weight-bold" style="font-size: 14px;">${data.usuario.nombre} ${data.usuario.apellidos}</p>
                            </div>
                            <div class="col-md-6 pl-md-4">
                                <p class="mb-0 text-white-50 small">Fecha de toma de cargo</p>
                                <p class="mb-0 text-warning font-weight-bold" style="font-size: 13px;"><i class="bi bi-calendar-check mr-1"></i> ${formatFecha(data.actual.fecha_inicio)}</p>
                            </div>
                        </div>
                    </div>
                `;

                // Historial (Solo mostrar registros pasados/inactivos para que sea una verdadera "línea de tiempo de reasignaciones")
                const listaHistorial = document.querySelector('#lista-historial-reasignar');
                listaHistorial.innerHTML = '';

                // Filtramos para no mostrar al que ya está activo arriba
                const reasignacionesPasadas = data.historial ? data.historial.filter(h => h.estado !== 'activo') : [];

                if (reasignacionesPasadas.length > 0) {
                    reasignacionesPasadas.forEach(h => {
                        listaHistorial.innerHTML += `
                            <tr style="border-bottom: 1px solid #222;">
                                <td class="py-2">
                                    <div class="font-weight-bold text-white" style="font-size: 12px;">${h.nombre || 'N/A'} ${h.apellidos || ''}</div>
                                    <div class="text-white-50 small" style="font-size: 10px;">${h.correo || '-'}</div>
                                </td>
                                <td class="py-2 text-white" style="opacity: 0.9; font-size: 11px;">
                                    <i class="bi bi-arrow-right-short text-success"></i> ${formatFecha(h.fecha_inicio)}
                                </td>
                                <td class="py-2 text-white" style="opacity: 0.9; font-size: 11px;">
                                    <i class="bi bi-arrow-left-short text-danger"></i> ${formatFecha(h.fecha_fin)}
                                </td>
                                <td class="py-2 text-center">
                                    <span class="badge bg-secondary text-white" style="font-size: 8px; padding: 3px 6px; opacity: 0.6; letter-spacing: 0.5px;">ANTERIOR</span>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    listaHistorial.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-white-50 mb-1" style="font-size: 12px;"><i class="bi bi-info-circle mr-1"></i> Sin reasignaciones previas</div>
                                <div class="small text-muted" style="font-size: 10px;">Este es el primer responsable asignado a la empresa.</div>
                            </td>
                        </tr>
                    `;
                }

            } else if (data.tipo === 'empleado') {
                formEmpleado.style.display = 'block';
                document.querySelector('#rea-emp-id-actual').value = data.actual.id;

                infoDiv.innerHTML = `
                    <div class="p-3 rounded mb-2" style="background: rgba(167, 139, 250, 0.1); border: 1px solid rgba(167, 139, 250, 0.3);">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: #a78bfa;">
                                    <i class="bi bi-person-badge text-dark fs-4"></i>
                                </div>
                            </div>
                            <div class="col">
                                <p class="mb-0 text-white-50 small font-weight-bold uppercase" style="letter-spacing: 1px;">Área de Agencia</p>
                                <h6 class="text-white mb-0 font-weight-bold" style="font-size: 16px;">${data.area ? data.area.nombre : 'Área no especificada'}</h6>
                            </div>
                        </div>
                        <hr style="border-top: 1px solid rgba(255,255,255,0.1); margin: 12px 0;">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-0 text-white-50 small">Jefe de Área Actual</p>
                                <p class="mb-0 text-white font-weight-bold" style="font-size: 14px;">${data.actual.nombre} ${data.actual.apellidos}</p>
                            </div>
                            <div class="col-md-6 text-md-right d-flex align-items-center justify-content-md-end">
                                <span class="badge" style="background: rgba(167, 139, 250, 0.2); color: #c4b5fd; border: 1px solid #a78bfa; font-size: 10px; font-weight: 700;">RESPONSABLE ACTIVO</span>
                            </div>
                        </div>
                    </div>
                `;

                const select = document.querySelector('#rea-emp-nuevo');
                select.innerHTML = '<option value="" style="background: #111;">— Seleccionar Sucesor del Equipo —</option>';
                if (data.asignables && data.asignables.length > 0) {
                    data.asignables.forEach(e => {
                        if (e.id != data.actual.id) {
                            const nombreFull = `${e.nombre || 'Sin nombre'} ${e.apellidos || ''}`.trim();
                            const correoInfo = e.correo ? ` (${e.correo})` : '';
                            select.innerHTML += `<option value="${e.id}" style="background: #111;">${nombreFull}${correoInfo}</option>`;
                        }
                    });
                }
            }

            $('#modal-reasignar').modal('show');
        } catch (e) {
            console.error(e);
            notificar('Error técnico al cargar el panel de reasignación');
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
                notificar('Por favor, selecciona al nuevo colaborador que asumirá la responsabilidad.');
                return;
            }

            url = BASE_URL + 'admin/usuarios/reasignarEmpleadoArea';
            datos = {
                id_actual: document.querySelector('#rea-emp-id-actual').value,
                id_nuevo: idNuevo
            };
        }

        Swal.fire({
            title: '¿Confirmar Reasignación?',
            text: 'Esta acción es irreversible y actualizará todos los permisos del sistema.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F5C400',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Confirmar Cambio',
            cancelButtonText: 'Cancelar',
            background: '#161616',
            color: '#ffffff'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                const resultData = await response.json();

                notificar(resultData.message || 'Operación completada', resultData.success ? 'success' : 'error');
                if (resultData.success) {
                    $('#modal-reasignar').modal('hide');
                    obtenerUsuarios();
                }
            } catch (e) {
                notificar('Ocurrió un error al procesar el cambio de responsable', 'error');
            }
        });
    });

    // ─── INICIO ────────────────────────────────────────────
    obtenerUsuarios();
});