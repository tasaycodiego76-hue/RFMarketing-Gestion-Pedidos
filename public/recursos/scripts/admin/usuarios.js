document.addEventListener('DOMContentLoaded', function () {

    const tabla      = document.querySelector('#tabla-usuarios');
    const formulario = document.querySelector('#form-usuario');
    const selectRol  = document.querySelector('#rol');
    const btnGuardar = document.querySelector('#btn-guardar');

    // ─── NOTIFICACIÓN ──────────────────────────────────────
    function notificar(mensaje) {
        alert(mensaje);
    }

    // ─── LISTAR USUARIOS ───────────────────────────────────
    async function obtenerUsuarios() {
        const response = await fetch(BASE_URL + 'admin/usuarios/listar');
        const data     = await response.json();

        tabla.innerHTML = '';
        data.forEach(u => {
            const badge     = u.estado ? `<span class="badge-activo">Activo</span>` : `<span class="badge-inactivo">Inactivo</span>`;
            const btnToggle = u.estado
                ? `<button class="btn btn-sm btn-warning"  onclick="toggleEstado(${u.id}, true)">Deshabilitar</button>`
                : `<button class="btn btn-sm btn-success"  onclick="toggleEstado(${u.id}, false)">Habilitar</button>`;

            tabla.innerHTML += `
                <tr>
                    <td>${u.nombre} ${u.apellidos}</td>
                    <td>${u.usuario ?? '-'}</td>
                    <td>${u.correo}</td>
                    <td>${u.rol}</td>
                    <td>${u.area_nombre ?? '-'}</td>
                    <td>${badge}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editarUsuario(${u.id})">Editar</button>
                        ${btnToggle}
                    </td>
                </tr>`;
        });
    }

    // ─── CAMBIO DE ROL ─────────────────────────────────────
    selectRol.addEventListener('change', function () {
        const rol     = this.value; 
        const tipodoc = document.querySelector('#tipodoc');
        const rs      = document.querySelector('#razonsocial');
        const aa      = document.querySelector('#idarea_agencia');

        document.querySelectorAll('.campo-todos, .campo-cliente, .campo-empleado')
            .forEach(el => el.style.display = 'none');
        [rs, aa].forEach(el => { if (el) el.disabled = true; });

        btnGuardar.disabled = !rol;
        if (!rol) return;

        const config = {
            empleado: { docs: 'DNI/CE', label: 'Nombre',                 show: '.campo-empleado', field: aa },
            cliente:  { docs: 'RUC',    label: 'Nombre del Responsable',  show: '.campo-cliente',  field: rs }
        };

        const c = config[rol];
        document.querySelector('.campo-todos').style.display    = 'block';
        document.querySelector('#label-nombre').textContent     = c.label;
        tipodoc.innerHTML = c.docs === 'RUC'
            ? '<option value="RUC">RUC</option>'
            : '<option value="DNI">DNI</option><option value="CE">CE</option>';

        document.querySelectorAll(c.show).forEach(el => el.style.display = 'block');
        if (c.field) c.field.disabled = false;

        actualizarDoc(tipodoc.value);
        tipodoc.onchange = () => actualizarDoc(tipodoc.value);
    });

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

    // ─── GUARDAR (REGISTRAR O EDITAR) ──────────────────────
    formulario.addEventListener('submit', async function (e) {
        e.preventDefault();

        const editId = formulario.dataset.editId;
        const rol    = selectRol.value;

        const datos = {
            rol,
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

        if (rol === 'cliente')  { datos.razonsocial = document.querySelector('#razonsocial').value; }
        if (rol === 'empleado') {
            datos.idarea_agencia = document.querySelector('#idarea_agencia').value || null;
            datos.esresponsable  = document.querySelector('#esresponsable').checked;
        }

        const url    = editId ? BASE_URL + 'admin/usuarios/editar/' + editId : BASE_URL + 'admin/usuarios/registrar';
        const method = editId ? 'PUT' : 'POST';

        const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) });
        const data     = await response.json();

        notificar(data.message);
        if (!data.success) return;

        $('#modal-usuario').modal('hide');
        formulario.reset();
        delete formulario.dataset.editId;
        selectRol.value = '';
        selectRol.dispatchEvent(new Event('change'));
        obtenerUsuarios();
    });

    // ─── EDITAR USUARIO ────────────────────────────────────
    window.editarUsuario = async function (id) {
        const response = await fetch(BASE_URL + 'admin/usuarios/obtener/' + id);
        const u        = await response.json();

        formulario.reset();
        formulario.dataset.editId = id;

        const inputClave = document.querySelector('#clave');
        inputClave.removeAttribute('required');
        inputClave.placeholder = 'Dejar en blanco para no cambiar';

        selectRol.value = u.rol;
        selectRol.dispatchEvent(new Event('change'));

        setTimeout(() => {
            document.querySelector('#nombre').value    = u.nombre    ?? '';
            document.querySelector('#apellidos').value = u.apellidos ?? '';
            document.querySelector('#correo').value    = u.correo    ?? '';
            document.querySelector('#telefono').value  = u.telefono  ?? '';
            document.querySelector('#tipodoc').value   = u.tipodoc   ?? '';
            document.querySelector('#numerodoc').value = u.numerodoc ?? '';
            document.querySelector('#usuario').value   = u.usuario   ?? '';

            if (u.rol === 'cliente')  document.querySelector('#razonsocial').value   = u.razonsocial    ?? '';
            if (u.rol === 'empleado') {
                document.querySelector('#idarea_agencia').value = u.idarea_agencia ?? '';
                document.querySelector('#esresponsable').checked = !!u.esresponsable;
            }

            actualizarDoc(document.querySelector('#tipodoc').value);
        }, 50);

        document.querySelector('#modal-usuario .modal-title').textContent = 'Editar Usuario';
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

    // ─── NUEVO USUARIO ─────────────────────────────────────
document.querySelector('#btn-nuevo').addEventListener('click', () => {
    formulario.reset();
    delete formulario.dataset.editId;

    document.querySelector('#modal-usuario .modal-title').textContent = 'Nuevo Usuario';
    selectRol.value = '';
    selectRol.dispatchEvent(new Event('change'));
    $('#modal-usuario').modal('show');
});

    // ─── INICIO ────────────────────────────────────────────
    obtenerUsuarios();
});