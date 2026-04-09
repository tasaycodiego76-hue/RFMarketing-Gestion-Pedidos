<?= $this->extend('plantillas/admin') ?>
<!-- ESTILOS -->
<?= $this->section('styles') ?>
    <link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
    <link href="<?= base_url('recursos/styles/admin/paginas/areas.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>
<!-- SCRIPTS -->
<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/areas.js') ?>"></script>
<?= $this->endSection() ?>
<?= $this->section('contenido') ?>

<div class="areas-wrapper">
 
    <!-- TABS (navegación por rutas) -->
    <div class="areas-tabs">
        <a href="<?= base_url('admin/areas') ?>"
           class="areas-tab <?= $tabActivo === 'agencia' ? 'active' : '' ?>">
            Agencia
        </a>
        <a href="<?= base_url('admin/areas/clientes') ?>"
           class="areas-tab <?= $tabActivo === 'clientes' ? 'active' : '' ?>">
            Clientes
        </a>
    </div>
 
    <!-- ============================
         PANEL: AGENCIA
         ============================ -->
    <?php if ($tabActivo === 'agencia'): ?>
    <div class="areas-card">
        <div class="areas-card-header">
            <h2 class="areas-card-title">Áreas de la Agencia</h2>
            <button class="btn-nueva-area" id="btnNuevaArea">+ Nueva Área</button>
        </div>
 
        <div class="areas-table-wrap">
            <table class="areas-table">
                <thead>
                    <tr>
                        <th>ÁREA</th>
                        <th>DESCRIPCIÓN</th>
                        <th>RESPONSABLE</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($areas)): ?>
                        <?php foreach ($areas as $area): ?>
                        <tr class="<?= $area['activo'] ? '' : 'row-inactivo' ?>">
    <td class="area-nombre"><?= esc($area['nombre']) ?></td>
    <td class="area-desc"><?= esc($area['descripcion']) ?></td>
   <td class="area-responsable">
    <?= $area['responsable'] ? esc($area['responsable']) : '<span class="sin-responsable">Sin responsable</span>' ?>
</td>
    <td>
        <?php if ($area['activo']): ?>
            <span class="badge badge-activo">Activo</span>
        <?php else: ?>
            <span class="badge badge-inactivo">Inactivo</span>
        <?php endif; ?>
    </td>
    <td class="area-acciones"><!-- pendiente --></td>
</tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="areas-empty">No hay áreas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
 
        <div class="areas-nota">
            El responsable de cada área se asigna al crear o editar un empleado en la sección de Usuarios.
        </div>
    </div>
 
    <!-- ============================
         PANEL: CLIENTES
         ============================ -->
    <?php elseif ($tabActivo === 'clientes'): ?>
    <div class="areas-card">
        <div class="areas-card-header">
            <h2 class="areas-card-title">Áreas de Empresas</h2>
            <button class="btn-nueva-area" id="btnNuevaArea">+ Nueva Área</button>
        </div>
 
        <div class="areas-select-wrap">
            <select class="areas-select" id="selectEmpresa">
                <option value="">— Seleccione una empresa —</option>
                <?php if (!empty($empresas)): ?>
                    <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= $empresa['id'] ?>">
                          <?= esc($empresa['nombreempresa']) ?>
                    </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
 
        <div class="areas-table-wrap">
            <table class="areas-table">
                <thead>
                    <tr>
                        <th>ÁREA</th>
                        <th>DESCRIPCIÓN</th>
                        <th>RESPONSABLE</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="tbodyClientes">
                    <tr>
                        <td colspan="5" class="areas-empty">
                            Seleccione una empresa para ver sus áreas
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
 
        <div class="areas-nota">
            El responsable de cada área se asigna al crear o editar un usuario cliente en la sección de Usuarios.
        </div>
    </div>
    <?php endif; ?>
 
</div>
<<!-- MODAL AGENCIA -->
<div class="modal-overlay" id="modalAgencia" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <span>NUEVA ÁREA</span>
            <button class="modal-cerrar" data-modal="modalAgencia">&times;</button>
        </div>
        <div class="modal-body">
            <label class="modal-label">NOMBRE DEL ÁREA *</label>
            <input type="text" id="agenciaNombre" class="modal-input" placeholder="Ej: Marketing, Finanzas...">
            <label class="modal-label">DESCRIPCIÓN</label>
            <textarea id="agenciaDescripcion" class="modal-input modal-textarea" placeholder="Opcional"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-cancelar modal-cerrar" data-modal="modalAgencia">Cancelar</button>
            <button class="btn-guardar" id="btnGuardarAgencia">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL CLIENTES -->
<div class="modal-overlay" id="modalCliente" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <span>NUEVA ÁREA</span>
            <button class="modal-cerrar" data-modal="modalCliente">&times;</button>
        </div>
        <div class="modal-body">
            <label class="modal-label">EMPRESA</label>
            <select id="clienteEmpresa" class="modal-input areas-select">
                <option value="">— Seleccione —</option>
                <?php if (!empty($empresas)): ?>
                    <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= esc($e['nombreempresa']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <label class="modal-label">NOMBRE DEL ÁREA *</label>
            <input type="text" id="clienteNombre" class="modal-input" placeholder="Ej: Marketing, Finanzas...">
            <label class="modal-label">DESCRIPCIÓN</label>
            <textarea id="clienteDescripcion" class="modal-input modal-textarea" placeholder="Opcional"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-cancelar modal-cerrar" data-modal="modalCliente">Cancelar</button>
            <button class="btn-guardar" id="btnGuardarCliente">Guardar</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>