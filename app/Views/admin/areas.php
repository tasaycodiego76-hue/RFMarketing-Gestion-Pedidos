<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
<link href="<?= base_url('recursos/styles/admin/paginas/areas.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/areas.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <button class="btn-nuevo mb-0" id="btnNuevaArea">+ Nueva Área</button>
</div>

<div class="tabla-contenedor">
    <table class="tabla-usuarios">
        <thead>
            <tr>
                <th style="text-align: left;">Área Agencial</th>
                <th style="text-align: left;">Descripción</th>
                <th style="text-align: left;">Responsable Asignado</th>
                <th style="text-align: center;">Estado</th>
                <th style="text-align: left;">Acciones</th>
            </tr>
        </thead>
        <tbody id="tabla-areas-body"></tbody>
    </table>
</div>

<div class="p-3 mt-4" style="background: rgba(245, 196, 0, 0.05); border-left: 4px solid #F5C400; border-radius: 4px;">
    <p class="mb-0 text-muted" style="font-size: 11.5px; line-height: 1.4;">
      <i class="bi bi-info-circle-fill text-warning mr-1"></i>
      <strong>NOTA:</strong> El responsable de cada área se asigna directamente al crear o editar un colaborador en la sección de Usuarios.
    </p>
</div>

<!-- MODAL AGENCIA -->
<div class="modal fade" id="modal-area" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo">Nueva Área</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-area" autocomplete="off">
                    <input type="hidden" id="areaId">
                    <div class="form-group">
                        <label>Nombre del Área <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agenciaNombre" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" id="agenciaDescripcion" rows="3"></textarea>
                    </div>
                    <div class="form-group form-check" style="display:none;">
                        <input type="checkbox" class="form-check-input" id="crearEnServicios">
                        <label class="form-check-label" for="crearEnServicios">Crear en Servicios</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-area" class="btn btn-primary" id="btnGuardarAgencia">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>