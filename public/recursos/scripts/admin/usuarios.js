document.addEventListener('DOMContentLoaded', function () {

    const tabla = document.querySelector('#tabla-usuarios');
    const formulario = document.querySelector('#form-usuario');
    const selectRol  = document.querySelector('#rol');
    const btnGuardar = document.querySelector('#btn-guardar');

    // trae usuarios del backend y los pinta en la tabla
    // ─── CARGAR SERVICIOS ──────────────────────────────────
async function cargarServicios() {
    try {
        const response = await fetch(BASE_URL + 'admin/servicios/listar');
        const data     = await response.json();
        const select   = document.querySelector('#idservicio');

        select.innerHTML = '<option value="">— Sin servicio —</option>';
        data.forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
        });
    } catch (e) {
        console.error('Error al cargar servicios:', e);
    }
}
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
                        <td>${u.servicio_nombre ?? '-'}</td>
                        <td><span class="${u.estado ? 'badge-activo' : 'badge-inactivo'}">${u.estado ? 'Activo' : 'Inactivo'}</span></td>
                        <td></td>
                    </tr>
                `;
            });
        } catch (e) {
            console.error('Error al obtener los usuarios', e);
        }
    }

    // ─── LÍMITES POR TIPO DOC ──────────────────────────────
    function actualizarDoc(tipo) {
    const numerodoc = document.querySelector('#numerodoc');
    if (tipo === 'DNI') {
        numerodoc.setAttribute('maxlength', '8');
        numerodoc.setAttribute('minlength', '8');
        numerodoc.placeholder = '8 dígitos';
    } else if (tipo === 'RUC') {
        numerodoc.setAttribute('maxlength', '11');
        numerodoc.setAttribute('minlength', '11');
        numerodoc.placeholder = '11 dígitos';
    } else if (tipo === 'CE') {
        numerodoc.setAttribute('maxlength', '12');
        numerodoc.setAttribute('minlength', '9');
        numerodoc.placeholder = '9-12 caracteres';
    }
}
    // ─── LÓGICA POR ROL ────────────────────────────────────
    selectRol.addEventListener('change', function () {
        const rol    = this.value;
        const tipodoc = document.querySelector('#tipodoc');

        // ocultar todo
        document.querySelectorAll('.campo-todos, .campo-cliente, .campo-empleado')
            .forEach(el => el.style.display = 'none');
        btnGuardar.disabled = true;

        if (!rol) return;

        // campos comunes a todos
        document.querySelector('.campo-todos').style.display = 'block';

        if (rol === 'administrador') {
            // tipodoc libre: DNI, CE (no RUC porque no es empresa)
            tipodoc.innerHTML = `
                <option value="DNI">DNI</option>
                <option value="CE">CE</option>
            `;
            document.querySelector('#label-nombre').textContent = 'Nombre';
        }

        if (rol === 'empleado') {
            tipodoc.innerHTML = `
                <option value="DNI">DNI</option>
                <option value="CE">CE</option>
            `;
            actualizarDoc('DNI'); 
            document.querySelector('#label-nombre').textContent = 'Nombre';
            document.querySelectorAll('.campo-empleado')
                .forEach(el => el.style.display = 'block');
            tipodoc.onchange = () => actualizarDoc(tipodoc.value);
        }
        if (rol === 'cliente') {
            // cliente es empresa: solo RUC
            tipodoc.innerHTML = `<option value="RUC">RUC</option>`;
            actualizarDoc('RUC');
            document.querySelector('#label-nombre').textContent = 'Nombre del Responsable';
            document.querySelector('.campo-cliente').style.display = 'block';
        }

        btnGuardar.disabled = false;
    });

    // ─── REGISTRAR ─────────────────────────────────────────
    async function registrarUsuario() {
        try {
            const rol = selectRol.value;

            const datos = {
                rol,
                nombre:    document.querySelector('#nombre').value,
                apellidos: document.querySelector('#apellidos').value,
                correo:    document.querySelector('#correo').value,
                telefono:  document.querySelector('#telefono').value,
                tipodoc:   document.querySelector('#tipodoc').value,
                numerodoc: document.querySelector('#numerodoc').value,
                usuario:   document.querySelector('#usuario').value,
                clave:     document.querySelector('#clave').value,
            };

            if (rol === 'cliente') {
                datos.razonsocial = document.querySelector('#razonsocial').value;
            }

            if (rol === 'empleado') {
                datos.idservicio    = document.querySelector('#idservicio').value || null;
                datos.esresponsable = document.querySelector('#esresponsable').checked;
            }

            const response = await fetch(BASE_URL + 'admin/usuarios/registrar', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(datos)
            });

            const data = await response.json();
            alert(data.message);

            if (!data.success) { return; }

            $('#modal-usuario').modal('hide');
            formulario.reset();
            selectRol.value = '';
            selectRol.dispatchEvent(new Event('change'));
            obtenerUsuarios();

        } catch (e) {
            console.error('Error al registrar usuario:', e);
        }
    }

    // ─── ABRIR MODAL ───────────────────────────────────────
    document.querySelector('#btn-nuevo').addEventListener('click', () => {
        formulario.reset();
        selectRol.value = '';
        selectRol.dispatchEvent(new Event('change'));
        $('#modal-usuario').modal('show');
    });

    // ─── EVENTOS ───────────────────────────────────────────
    formulario.addEventListener('submit', function(event) {
    event.preventDefault();
    registrarUsuario();
});

    // ─── INIT ──────────────────────────────────────────────
    cargarServicios();
    obtenerUsuarios();

});