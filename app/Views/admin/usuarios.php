<?= $this->extend('plantillas/admin') ?>
<!-- ESTILOS -->
<?= $this->section('styles') ?>

<link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
<link href="<?= base_url('recursos/styles/admin/paginas/usuarios.css') ?>" rel="stylesheet">

<?= $this->endSection() ?>
<!-- SCRIPTS -->
<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/usuarios.js') ?>"></script>
<?= $this->endSection() ?>
<?= $this->section('contenido') ?>
 <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="dropdown">
          <button class="btn-nuevo mb-3 dropdown-toggle" id="btn-nuevo" data-toggle="dropdown" aria-haspopup="true"
  aria-expanded="false">+ Nuevo</button>
          <div class="dropdown-menu" aria-labelledby="btn-nuevo">
              <a class="dropdown-item" href="#" id="opcion-empleado">Crear Empleado</a>
              <a class="dropdown-item" href="#" id="opcion-area">Crear Área con Responsable</a>
          </div>
      </div>
  </div>


<table class="tabla-usuarios">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Área/Empresa</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tabla-usuarios"></tbody>
</table>
<div class="modal fade" id="modal-usuario" data-backdrop="static" tabindex="-1">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modal-titulo">Nuevo Usuario</h5>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                  <form id="form-usuario" autocomplete="off">
                      <input type="hidden" id="tipo_registro" value="">

                      <!-- Selección de Empresa (solo para área con responsable) -->
                      <div class="form-group" id="grupo-empresa" style="display:none;">
                          <label>Empresa <span class="text-danger">*</span></label>
                          <select class="form-control" id="idempresa">
                              <option value="">— Selecciona una empresa —</option>
                              <?php foreach ($empresas as $e): ?>
                                  <option value="<?= $e['id'] ?>"><?= $e['nombreempresa'] ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>

                      <!-- Nombre del Área (solo para área con responsable) -->
                      <div class="form-group" id="grupo-nombre-area" style="display:none;">
                          <label>Nombre del Área <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="nombre_area" maxlength="100">
                      </div>

                      <!-- Descripción del Área (solo para área con responsable) -->
                      <div class="form-group" id="grupo-descripcion-area" style="display:none;">
                          <label>Descripción del Área</label>
                          <textarea class="form-control" id="descripcion_area" rows="2"></textarea>
                      </div>

                      <!-- Razón Social (solo cliente) -->
                      <div class="form-group" id="grupo-razonsocial" style="display:none;">
                          <label>Razón Social (Empresa)</label>
                          <input type="text" class="form-control" id="razonsocial" maxlength="255" required>
                      </div>

                      <!-- Campos comunes -->
                      <div id="campos-comunes">
                          <div class="row">
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label id="label-nombre">Nombre</label>
                                      <input type="text" class="form-control" id="nombre" maxlength="50" required>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Apellidos</label>
                                      <input type="text" class="form-control" id="apellidos" maxlength="50" required>
                                  </div>
                              </div>
                          </div>

                          <div class="row">
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Correo</label>
                                      <input type="email" class="form-control" id="correo" maxlength="100" required>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Teléfono</label>
                                      <input type="text" class="form-control" id="telefono" maxlength="9" minlength="9"
  placeholder="9 dígitos" required>
                                  </div>
                              </div>
                          </div>

                          <div class="row">
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Tipo Documento</label>
                                      <select class="form-control" id="tipodoc"></select>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Número Documento</label>
                                      <input type="text" class="form-control" id="numerodoc" required>
                                  </div>
                              </div>
                          </div>

                          <div class="row">
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Usuario</label>
                                      <input type="text" class="form-control" id="usuario" maxlength="50" required>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Contraseña</label>
                                      <input type="password" class="form-control" id="clave" minlength="6" required>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <!-- Solo empleado -->
                      <div id="campos-empleado" style="display:none;">
                          <div class="form-group">
                              <label>Área de la Agencia</label>
                              <select class="form-control" id="idarea_agencia">
                                  <option value="">— Seleccione Área —</option>
                                  <?php foreach ($areasAgencia as $a): ?>
                                      <option value="<?= $a['id'] ?>"><?= $a['nombre'] ?></option>
                                  <?php endforeach; ?>
                              </select>
                          </div>
                          <div class="form-check mb-3">
                              <input type="checkbox" class="form-check-input" id="esresponsable">
                              <label class="form-check-label">¿Es responsable de esta área?</label>
                          </div>
                      </div>

                  </form>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="submit" form="form-usuario" class="btn btn-primary" id="btn-guardar">Guardar</button>
              </div>
          </div>
      </div>
  </div>
</div>
<?= $this->endSection() ?>