document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.querySelector('#tabla-empresas');
    const formulario = document.querySelector('#form-empresa');
    const formArea = document.querySelector('#form-nueva-area-empresa');

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

    async function obtenerEmpresas() {
        try {
            const response = await fetch(BASE_URL + 'admin/empresas/listar');
            const data = await response.json();
            renderizarTabla(data);
        } catch (error) {
            console.error('Error al listar empresas:', error);
        }
    }

    function renderizarTabla(data) {
        tabla.innerHTML = '';

        if (data.length === 0) {
            tabla.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No hay empresas registradas.</td></tr>';
            return;
        }

        data.forEach(e => {
            const esActivo = (e.estado === true || e.estado === 't' || e.estado == 1);

            const badge = esActivo
                ? '<span class="badge-activo">Activo</span>'
                : '<span class="badge-inactivo">Inactivo</span>';

            const btnEditar = `<button class="btn-icon btn-icon-editar" onclick="editarEmpresa(${e.id})" title="Editar" style="cursor:pointer;">✎</button>`;
            const btnToggle = esActivo
                ? `<button class="btn-icon btn-icon-toggle activo" onclick="toggleEstado(${e.id}, true)" title="Desactivar" style="cursor:pointer;">✕</button>`
                : `<button class="btn-icon btn-icon-toggle inactivo" onclick="toggleEstado(${e.id}, false)" title="Activar" style="cursor:pointer;">✓</button>`;

            const btnAddArea = `<button class="btn-icon" onclick="abrirModalNuevaArea(${e.id}, '${e.nombreempresa.replace(/'/g, "\\'")}')" title="Nueva Área" style="cursor:pointer; background: rgba(167, 139, 250, 0.1); color: #a78bfa; border: 1px solid rgba(167, 139, 250, 0.3);"><i class="bi bi-diagram-3-fill"></i></button>`;

            tabla.innerHTML += `
                <tr>
                    <td class="td-nombre">${e.nombreempresa}</td>
                    <td class="td-usuario">${e.ruc || '-'}</td>
                    <td class="td-correo">${e.correo || '-'}</td>
                    <td class="td-area-empresa">${e.telefono || '-'}</td>
                    <td style="text-align: center;">${badge}</td>
                    <td>
                        <div class="acciones-contenedor">
                            ${btnAddArea}
                            ${btnEditar}
                            ${btnToggle}
                        </div>
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

        const url = editId ? BASE_URL + 'admin/empresas/editar/' + editId : BASE_URL + 'admin/empresas/registrar';
        const method = editId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        const data = await response.json();

        if (data.success) {
            notificar(data.message, 'success');
            $('#modal-empresa').modal('hide');
            formulario.reset();
            delete formulario.dataset.editId;
            obtenerEmpresas();
        } else {
            notificar(data.message || 'Error al guardar', 'error');
        }
    });

    // --- GESTIÓN DE ÁREAS ---
    window.abrirModalNuevaArea = function (idEmpresa, nombreEmpresa) {
        formArea.reset();
        document.querySelector('#area-idempresa').value = idEmpresa;
        document.querySelector('#nombre-empresa-modal').textContent = nombreEmpresa;
        $('#modal-area-empresa').modal('show');
    };

    formArea.addEventListener('submit', async function (e) {
        e.preventDefault();

        const idEmpresa = document.querySelector('#area-idempresa').value;
        const nombre = document.querySelector('#nombre_area_emp').value.trim();
        const descripcion = document.querySelector('#descripcion_area_emp').value.trim();

        if (!idEmpresa || !nombre) {
            notificar('Faltan campos obligatorios', 'warning');
            return;
        }

        const datos = { idempresa: idEmpresa, nombre: nombre, descripcion: descripcion };

        try {
            const response = await fetch(BASE_URL + 'admin/empresas/registrarArea', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const data = await response.json();

            if (data.success) {
                notificar(data.message, 'success');
                $('#modal-area-empresa').modal('hide');
                formArea.reset();
            } else {
                notificar(data.message, 'error');
            }
        } catch (error) {
            console.error('Error al registrar área:', error);
            notificar('Error técnico al registrar área', 'error');
        }
    });

    window.editarEmpresa = async function (id) {
        const response = await fetch(BASE_URL + 'admin/empresas/obtener/' + id);
        const e = await response.json();
        if (!e || e.success === false) return;

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
        const titulo = estadoActual ? '¿Deshabilitar Empresa?' : '¿Habilitar Empresa?';
        const texto = estadoActual
            ? 'Esta acción desactivará también a todos los responsables vinculados a esta empresa.'
            : 'Esta acción volverá a habilitar la empresa.';

        const isLight = document.documentElement.getAttribute('data-theme') === 'light';

        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F5C400',
            cancelButtonColor: isLight ? '#cbd5e1' : '#71717a',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            background: isLight ? '#ffffff' : '#161616',
            color: isLight ? '#1e293b' : '#ffffff'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const response = await fetch(BASE_URL + 'admin/empresas/toggleEstado', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, estado: !estadoActual })
                });
                const data = await response.json();
                if (data.success) {
                    notificar(data.message, 'success');
                    obtenerEmpresas();
                } else {
                    notificar(data.message || 'Error al cambiar estado', 'error');
                }
            }
        });
    };

    document.querySelector('#btn-nueva-empresa').addEventListener('click', () => {
        formulario.reset();
        delete formulario.dataset.editId;
        document.querySelector('#modal-titulo').textContent = 'Nueva Empresa';
        $('#modal-empresa').modal('show');
    });

    // Restringir entrada a solo números para RUC y teléfono
    const inputsNumericos = ['#ruc', '#telefono'];
    inputsNumericos.forEach(selector => {
        const input = document.querySelector(selector);
        if (input) {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '');
            });
        }
    });

    obtenerEmpresas();
});