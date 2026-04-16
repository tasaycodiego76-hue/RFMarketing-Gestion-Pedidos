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
      <button class="btn-nuevo mb-3" id="btn-nueva-empresa">+ Nueva Empresa</button>
  </div>

  <table class="tabla-usuarios">
      <thead>
          <tr>
              <th>Razón Social</th>
              <th>RUC</th>
              <th>Correo</th>
              <th>Teléfono</th>
              <th>Estado</th>
              <th>Acciones</th>
          </tr>
      </thead>
      <tbody id="tabla-empresas"></tbody>
  </table>

  <div class="modal fade" id="modal-empresa" data-backdrop="static" tabindex="-1">
      <div class="modal-dialog">
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
                          <input type="text" class="form-control" id="telefono" maxlength="20">
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

  <?= $this->endSection() ?>