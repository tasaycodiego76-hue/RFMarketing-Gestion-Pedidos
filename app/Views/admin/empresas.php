<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
<link href="<?= base_url('recursos/styles/admin/paginas/empresas.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/empresas.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <button class="btn-nuevo mb-0" id="btn-nueva-empresa">+ Nueva Empresa</button>
</div>

<div class="tabla-contenedor">
    <table class="tabla-usuarios">
        <thead>
            <tr>
                <th style="text-align: left;">Razón Social</th>
                <th style="text-align: left;">RUC</th>
                <th style="text-align: left;">Correo</th>
                <th style="text-align: left;">Teléfono</th>
                <th style="text-align: center;">Estado</th>
                <th style="text-align: left;">Acciones</th>
            </tr>
        </thead>
        <tbody id="tabla-empresas"></tbody>
    </table>
</div>

<!-- Modal Empresa (Editar / Nuevo) -->
<div class="modal fade" id="modal-empresa" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo">Nueva Empresa</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-empresa" autocomplete="off">
                    <div class="form-group">
                        <label>Razón Social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombreempresa" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" class="form-control" id="ruc" maxlength="11" placeholder="11 dígitos">
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" class="form-control" id="correo" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="telefono" maxlength="9" minlength="9" placeholder="9 dígitos">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-empresa" class="btn btn-primary" id="btn-guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Área (Mismo estilo que Editar) -->
<div class="modal fade" id="modal-area-empresa" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Área para <span id="nombre-empresa-modal" class="text-warning"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-nueva-area-empresa" autocomplete="off">
                    <input type="hidden" id="area-idempresa">
                    <div class="form-group">
                        <label>Nombre del Área <span class="text-danger">*</span></label>
                        <input type="text" id="nombre_area_emp" class="form-control" maxlength="100" placeholder="Ej: Marketing, Recursos Humanos..." required>
                    </div>
                    <div class="form-group">
                        <label>Descripción del Área (Opcional)</label>
                        <textarea id="descripcion_area_emp" class="form-control" rows="3" placeholder="Breve descripción de las funciones del área..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-nueva-area-empresa" class="btn btn-primary" id="btn-guardar-area-empresa">Registrar Área</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>