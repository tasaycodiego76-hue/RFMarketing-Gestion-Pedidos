document.addEventListener('DOMContentLoaded', () => {

    // ── Abrir modal correcto según tab ──
  const btnNueva = document.getElementById('btnNuevaArea');
if (btnNueva) {
    btnNueva.addEventListener('click', () => {
        const esClientes = window.location.pathname.includes('/clientes');
        const modalId = esClientes ? 'modalCliente' : 'modalAgencia';
        document.getElementById(modalId).style.display = 'flex';
    });
}

    // ── Cerrar modales ──
    document.querySelectorAll('.modal-cerrar').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.modal;
            document.getElementById(id).style.display = 'none';
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.style.display = 'none';
        });
    });

    // ── Guardar área AGENCIA ──
    const btnGuardarAgencia = document.getElementById('btnGuardarAgencia');
    if (btnGuardarAgencia) {
        btnGuardarAgencia.addEventListener('click', () => {
            const nombre      = document.getElementById('agenciaNombre').value.trim();
            const descripcion = document.getElementById('agenciaDescripcion').value.trim();
            if (!nombre) { alert('El nombre es obligatorio.'); return; }

            fetch(`${BASE_URL}admin/areas/registrar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ nombre, descripcion })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) { document.getElementById('modalAgencia').style.display = 'none'; location.reload(); }
                else alert(res.mensaje || 'Error al guardar.');
            });
        });
    }

    // ── Guardar área CLIENTE ──
    const btnGuardarCliente = document.getElementById('btnGuardarCliente');
    if (btnGuardarCliente) {
        btnGuardarCliente.addEventListener('click', () => {
            const idempresa   = document.getElementById('clienteEmpresa').value;
            const nombre      = document.getElementById('clienteNombre').value.trim();
            const descripcion = document.getElementById('clienteDescripcion').value.trim();
            if (!idempresa) { alert('Seleccione una empresa.'); return; }
            if (!nombre)    { alert('El nombre es obligatorio.'); return; }

            fetch(`${BASE_URL}admin/areas/clientes/registrar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ idempresa, nombre, descripcion })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('modalCliente').style.display = 'none';
                    // recargar tabla de la empresa seleccionada
                    cargarAreasEmpresa(idempresa);
                } else alert(res.mensaje || 'Error al guardar.');
            });
        });
    }

    // ── Select empresa → cargar áreas ──
    const selectEmpresa = document.getElementById('selectEmpresa');
    if (selectEmpresa) {
        selectEmpresa.addEventListener('change', () => {
            const id = selectEmpresa.value;
            if (id) cargarAreasEmpresa(id);
            else vaciarTabla();
        });
    }

    function cargarAreasEmpresa(idEmpresa) {
        fetch(`${BASE_URL}admin/areas/clientes/listar/${idEmpresa}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(areas => renderTablaClientes(areas));
    }

    function renderTablaClientes(areas) {
        const tbody = document.getElementById('tbodyClientes');
        if (!areas.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="areas-empty">No hay áreas registradas para esta empresa.</td></tr>`;
            return;
        }
        tbody.innerHTML = areas.map(a => `
            <tr class="${a.activo ? '' : 'row-inactivo'}">
                <td class="area-nombre">${a.nombre}</td>
                <td class="area-desc">${a.descripcion ?? ''}</td>
                <td class="area-responsable"><!-- pendiente --></td>
                <td><span class="badge ${a.activo ? 'badge-activo' : 'badge-inactivo'}">${a.activo ? 'Activo' : 'Inactivo'}</span></td>
                <td class="area-acciones"><!-- pendiente --></td>
            </tr>
        `).join('');
    }

    function vaciarTabla() {
        document.getElementById('tbodyClientes').innerHTML =
            `<tr><td colspan="5" class="areas-empty">Seleccione una empresa para ver sus áreas</td></tr>`;
    }
});