<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('contenido') ?>
<!-- Estrucutra de la PLantilla -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        //JSON del endpoint en la consola
        const datosBackend = <?= json_encode($user) ?>;
        console.log("DATOS RECIBIDOS DEL ENDPOINT:", datosBackend);
    </script>
<?= $this->endSection() ?>