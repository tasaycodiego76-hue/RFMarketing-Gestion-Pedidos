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
    <button class="btn-nuevo mb-3" id="btn-nuevo">+ Nuevo Usuario</button>
</div>


<table class="tabla-usuarios">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Área</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tabla-usuarios"></tbody>
</table>
<div class="modal fade" id="modal-usuario" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-usuario" autocomplete="off">

                    <div class="form-group">
                        <label>Rol</label>
                        <select class="form-control" id="rol">
                            <option value="">— Selecciona un rol —</option>
                            <option value="empleado">Empleado</option>
                            <option value="cliente">Cliente</option>
                        </select>
                    </div>

                    <!-- Solo cliente -->
                    <div class="form-group campo-cliente" style="display:none;">
                        <label>Razón Social (Empresa)</label>
                        <input type="text" class="form-control" id="razonsocial" maxlength="255" required>
                    </div>

                    <!-- Todos -->
                    <div class="campo-todos" style="display:none;">
                        <div class="form-group">
                            <label id="label-nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" maxlength="50" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" maxlength="50" required>
                        </div>
                        <div class="form-group">
                            <label>Correo</label>
                            <input type="email" class="form-control" id="correo" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" id="telefono"
                                maxlength="9" minlength="9"
                                placeholder="9 dígitos" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo Documento</label>
                            <select class="form-control" id="tipodoc"></select>
                        </div>
                        <div class="form-group">
                            <label>Número Documento</label>
                            <input type="text" class="form-control" id="numerodoc" required>
                        </div>
                        <div class="form-group">
                            <label>Usuario</label>
                            <input type="text" class="form-control" id="usuario" maxlength="50" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" class="form-control" id="clave" minlength="6" required>
                        </div>
                    </div>

                    <!-- Solo empleado -->
                        <div class="campo-empleado" style="display:none;">
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
                <!-- Botón guardar con form= apuntando al formulario -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="form-usuario" class="btn btn-primary" id="btn-guardar">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>