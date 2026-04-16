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

        <div class="d-flex justify-content-start">
            <button class="btn-nuevo" id="btnNuevaArea">+ Nueva Área</button>
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
                  <tbody id="tabla-areas-body">
      <?php if (!empty($areas)): ?>
          <?php foreach ($areas as $area): ?>
          <tr data-id="<?= $area['id'] ?>" class="<?= $area['activo'] ? '' : 'row-inactivo' ?>">
              <td class="area-nombre"><?= esc($area['nombre']) ?></td>
              <td class="area-desc"><?= esc($area['descripcion']) ?></td>
              <td class="area-responsable">
                  <?= $area['responsable'] ? esc($area['responsable']) : '<span class="sin-responsable">Sin
  responsable</span>' ?>
              </td>
              <td class="area-estado">
                  <?php if ($area['activo']): ?>
                      <span class="badge badge-activo">Activo</span>
                  <?php else: ?>
                      <span class="badge badge-inactivo">Inactivo</span>
                  <?php endif; ?>
              </td>
              <td class="area-acciones">
                  <button class="btn btn-sm btn-primary" onclick="editarArea(<?= $area['id'] ?>)">Editar</button>
                  <?php if ($area['activo']): ?>
                      <button class="btn btn-sm btn-warning" onclick="toggleEstado(<?= $area['id'] ?>,
  true)">Deshabilitar</button>
                  <?php else: ?>
                      <button class="btn btn-sm btn-success" onclick="toggleEstado(<?= $area['id'] ?>,
  false)">Habilitar</button>
                  <?php endif; ?>
              </td>
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
  </div>

  <!-- MODAL AGENCIA (Nueva/Editar) -->
  <div class="modal fade" id="modal-area" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
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