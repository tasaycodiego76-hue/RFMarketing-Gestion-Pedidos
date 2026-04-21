<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/detalle_requerimiento.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="header-detalle mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="breadcrumb-text">Mis Pedidos / Detalle del requerimiento</span>
            <h2 class="nombre-cliente-titulo bebas"><?= esc($user['nombre'] ?? 'ANA FLORES QUISPE') ?></h2>
            <p class="cliente-rol-sub">Cliente — Detalle del requerimiento</p>
        </div>
        <a href="<?= base_url('cliente/mis_solicitudes') ?>" class="btn-volver-custom">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-dark-main p-4 h-100">
            <div class="badges-row mb-4">
                <span class="badge-type">
                    <?= mb_strtoupper(esc($requerimiento['nombre_servicio'] ?? $requerimiento['servicio_personalizado']), 'UTF-8') ?>
                </span>
                <span class="badge-priority prio-<?= strtolower($requerimiento['prioridad']) ?>">
                    <?= esc($requerimiento['prioridad']) ?>
                </span>
            </div>

            <h1 class="main-project-title"><?= esc($requerimiento['titulo']) ?></h1>
            <p class="university-subtext">
                <?php if (!empty($user['nombre_area_agencia'])): ?>
                    <?= esc($user['nombre_area_agencia']) ?> — Agencia
                <?php elseif (!empty($user['nombre_empresa'])): ?>
                    <?= esc($user['nombre_empresa']) ?>
                <?php elseif (!empty($user['nombre_area'])): ?>
                    <?= esc($user['nombre_area']) ?>
                <?php else: ?>
                    Cliente
                <?php endif; ?>
            </p>

            <div class="divider-dark my-4"></div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="label-tiny">OBJETIVO DE COMUNICACIÓN</label>
                    <p class="content-text"><?= esc($requerimiento['objetivo_comunicacion']) ?></p>
                </div>
                <div class="col-md-6">
                    <label class="label-tiny">PÚBLICO OBJETIVO</label>
                    <p class="content-text"><?= esc($requerimiento['publico_objetivo']) ?></p>
                </div>
            </div>

            <div class="mb-4">
                <label class="label-tiny">DESCRIPCIÓN</label>
                <div class="brief-container mt-2">
                    <?= nl2br(esc($requerimiento['descripcion'])) ?>
                </div>
            </div>

            <?php if (!empty($requerimiento['respuestatexto'])): ?>
                <div class="entrega-disenador p-3 mt-4">
                    <label class="label-tiny text-warning"><i class="bi bi-stars"></i> ENTREGA DEL DISEÑADOR /
                        ENLACES</label>
                    <div class="mt-2 text-white">
                        <?php
                        $text = esc($requerimiento['respuestatexto']);
                        $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                        if (preg_match($reg_exUrl, $text, $url)) {
                            echo preg_replace($reg_exUrl, '<a href="' . $url[0] . '" target="_blank" class="btn-link-entrega"><i class="bi bi-link-45deg"></i> Ver Recurso (Canva/TikTok)</a>', $text);
                        } else {
                            echo $text;
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-dark-main p-4 mb-4">
            <label class="label-tiny mb-4 d-block">INFORMACIÓN DEL PEDIDO</label>

            <div class="timeline-item">
                <i class="bi bi-person-workspace"></i>
                <div>
                    <span class="t-label">EMPLEADO ASIGNADO</span>
                    <span class="t-value"><?= esc($requerimiento['empleado_nombre'] ?? 'Pendiente de asignar') ?></span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-calendar-check"></i>
                <div>
                    <span class="t-label">FECHA REQUERIDA</span>
                    <span class="t-value"><?= date('d/m/Y', strtotime($requerimiento['fecharequerida'])) ?></span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-plus-square"></i>
                <div>
                    <span class="t-label">FECHA DE SOLICITUD</span>
                    <span class="t-value"><?= date('d/m/Y', strtotime($requerimiento['fechacreacion'])) ?></span>
                </div>
            </div>

            <?php if (!empty($requerimiento['fechainicio'])): ?>
                <div class="timeline-item">
                    <i class="bi bi-play-circle"></i>
                    <div>
                        <span class="t-label">TRABAJO INICIADO</span>
                        <span class="t-value"><?= date('d/m/Y', strtotime($requerimiento['fechainicio'])) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div
                class="timeline-item <?= ($requerimiento['estado'] == 'finalizado') ? 'active-step-success' : 'pending-step' ?>">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <span class="t-label">REQUERIMIENTO FINALIZADO</span>
                    <span class="t-value">
                        <?= !empty($requerimiento['fechacompletado']) ? date('d/m/Y', strtotime($requerimiento['fechacompletado'])) : '<span class="text-muted">-- / -- / --</span>' ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item <?= ($requerimiento['estado'] == 'completado') ? 'active-step' : '' ?>">
                <i class="bi bi-check-circle"></i>
                <div>
                    <span class="t-label">ESTADO ACTUAL</span>
                    <span
                        class="t-value text-warning"><?= strtoupper(str_replace('_', ' ', $requerimiento['estado'])) ?></span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-arrow-repeat"></i>
                <div>
                    <span class="t-label">MODIFICACIONES</span>
                    <span class="t-value"><?= $requerimiento['num_modificaciones'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-6">
        <div class="card-dark-main p-3">
            <label class="label-tiny mb-3 d-block">CANALES DE DIFUSIÓN</label>
            <div class="d-flex flex-wrap gap-2">
                <?php
                $canales = json_decode($requerimiento['canales_difusion'] ?? '[]', true) ?: [];
                foreach ($canales as $canal):
                    ?>
                    <span class="tag-outline"><?= esc($canal) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dark-main p-3">
            <label class="label-tiny mb-3 d-block">FORMATOS SOLICITADOS</label>
            <div class="d-flex flex-wrap gap-2">
                <?php
                $formatos = json_decode($requerimiento['formatos_solicitados'] ?? '[]', true) ?: [];
                foreach ($formatos as $formato):
                    ?>
                    <span class="tag-outline"><?= esc($formato) ?></span>
                <?php endforeach; ?>
                <?php if (!empty($requerimiento['formato_otros'])): ?>
                    <span class="tag-outline special"><?= esc($requerimiento['formato_otros']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card-dark-main p-3">
            <label class="label-tiny mb-2 d-block">TIPO DE REQUERIMIENTO</label>
            <p class="content-text small m-0"><?= esc($requerimiento['tipo_requerimiento']) ?></p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card-dark-main p-3">
            <label class="label-tiny mb-2 d-block">ÁREA SOLICITANTE</label>
            <p class="content-text small m-0">
                <?php if (!empty($user['nombre_area_agencia'])): ?>
                    <?= esc($user['nombre_area_agencia']) ?>
                <?php elseif (!empty($user['nombre_area'])): ?>
                    <?= esc($user['nombre_area']) ?>
                <?php else: ?>
                    No especificado
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php if (!empty($archivos)): ?>
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="card-dark-main p-3">
                    <label class="label-tiny mb-3 d-block">
                        <i class="bi bi-paperclip me-1"></i> ARCHIVOS ADJUNTOS
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($archivos as $archivo): ?>
                            <?php
                            $icono = 'bi-file-earmark';
                            $mime = $archivo['tipo'] ?? '';
                            if (str_contains($mime, 'image'))
                                $icono = 'bi-file-earmark-image';
                            elseif (str_contains($mime, 'pdf'))
                                $icono = 'bi-file-earmark-pdf';
                            elseif (str_contains($mime, 'video'))
                                $icono = 'bi-file-earmark-play';
                            elseif (str_contains($mime, 'word'))
                                $icono = 'bi-file-earmark-word';
                            elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel'))
                                $icono = 'bi-file-earmark-excel';

                            $kb = number_format(($archivo['tamano'] ?? 0) / 1024, 1);
                            $nombreArchivo = basename($archivo['ruta'] ?? '');
                            ?>
                            <a href="<?= base_url('cliente/archivos/' . $nombreArchivo) ?>" target="_blank"
                                class="archivo-adjunto-card" title="<?= esc($archivo['nombre']) ?>">
                                <i class="bi <?= $icono ?>"></i>
                                <div class="archivo-info">
                                    <span class="archivo-nombre"><?= esc($archivo['nombre']) ?></span>
                                    <span class="archivo-peso"><?= $kb ?> KB</span>
                                </div>
                                <i class="bi bi-box-arrow-up-right archivo-open"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($requerimiento['url_subida'])): ?>
        <div class="col-12 mt-3">
            <div class="card-dark-main p-3">
                <label class="label-tiny mb-3 d-block">
                    <i class="bi bi-link-45deg me-1"></i> ENLACE DE REFERENCIA
                </label>
                <a href="<?= esc($requerimiento['url_subida']) ?>" target="_blank" class="archivo-adjunto-card"
                    style="max-width: 100%;">
                    <i class="bi bi-globe"></i>
                    <div class="archivo-info" style="flex: 1;">
                        <span class="archivo-nombre"
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= esc($requerimiento['url_subida']) ?>
                        </span>
                        <span class="archivo-peso">Haz clic para abrir</span>
                    </div>
                    <i class="bi bi-box-arrow-up-right archivo-open"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>