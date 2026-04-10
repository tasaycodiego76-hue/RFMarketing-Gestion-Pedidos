<?= $this->extend('plantillas/admin') ?>
<!-- ESTILOS -->
<?= $this->section('styles') ?>
    <link href="<?= base_url('recursos/styles/admin/paginas/admin.css') ?>" rel="stylesheet">
    <link href="<?= base_url('recursos/styles/admin/paginas/empresas.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>
<!-- SCRIPTS -->
<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/admin/empresas.js') ?>"></script>
<?= $this->endSection() ?>
<?= $this->section('contenido') ?>

<div>hola</div>
<?= $this->endSection() ?>