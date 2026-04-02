<?= $this->extend('plantillas/admin') ?>
<!-- ESTILOS -->
<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/paginas/usuarios.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>
<!-- SCRIPTS -->
<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/usuarios.js') ?>"></script>
<?= $this->endSection() ?>
<?= $this->section('contenido') ?>

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


<?= $this->endSection() ?>