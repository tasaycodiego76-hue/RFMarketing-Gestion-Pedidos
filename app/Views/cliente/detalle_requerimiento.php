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

            <!-- Header del Requerimiento -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div class="flex-grow-1">
                    <div class="badges-row mb-3">
                        <span class="badge-type">
                            <?= mb_strtoupper(esc($requerimiento['servicio_personalizado'] ?? $requerimiento['nombre_servicio']), 'UTF-8') ?>
                        </span>
                        <span class="badge-priority prio-<?= strtolower($requerimiento['prioridad']) ?>">
                            <?= esc($requerimiento['prioridad']) ?>
                        </span>
                    </div>
                    <h1 class="main-project-title mb-2"><?= esc($requerimiento['titulo']) ?></h1>
                    <p class="university-subtext mb-0">
                        <?php if (!empty($user['nombre_area_agencia'])): ?>
                            <i class="bi bi-building me-1"></i><?= esc($user['nombre_area_agencia']) ?> - Agencia
                        <?php elseif (!empty($user['nombre_empresa'])): ?>
                            <i class="bi bi-briefcase me-1"></i><?= esc($user['nombre_empresa']) ?>
                        <?php elseif (!empty($user['nombre_area'])): ?>
                            <i class="bi bi-geo-alt me-1"></i><?= esc($user['nombre_area']) ?>
                        <?php else: ?>
                            <i class="bi bi-person me-1"></i>Cliente
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="divider-dark my-4"></div>

            <!-- Información Principal -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="info-section">
                        <label class="label-tiny mb-2">
                            <i class="bi bi-bullseye me-1"></i>OBJETIVO DE COMUNICACIÓN
                        </label>
                        <div class="content-box">
                            <p class="content-text mb-0"><?= esc($requerimiento['objetivo_comunicacion']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-section">
                        <label class="label-tiny mb-2">
                            <i class="bi bi-people me-1"></i>PÚBLICO OBJETIVO
                        </label>
                        <div class="content-box">
                            <p class="content-text mb-0"><?= esc($requerimiento['publico_objetivo']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            <div class="mb-4">
                <label class="label-tiny mb-2">
                    <i class="bi bi-file-text me-1"></i>DESCRIPCIÓN DETALLADA
                </label>
                <div class="brief-container">
                    <div id="descripcion-container" class="fade-bottom"
                        style="overflow: hidden; transition: max-height 0.3s ease;">
                            <?= nl2br(esc($requerimiento['descripcion'])) ?>
                    </div>
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
                $canalesRaw = $requerimiento['canales_difusion'] ?? '[]';
                $canales = json_decode($canalesRaw, true);
                if (!is_array($canales)) {
                    $canales = array_filter(array_map('trim', explode(',', $canalesRaw)));
                }
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
                $formatosRaw = $requerimiento['formatos_solicitados'] ?? '[]';
                $formatos = json_decode($formatosRaw, true);
                if (!is_array($formatos)) {
                    $formatos = array_filter(array_map('trim', explode(',', $formatosRaw)));
                }
                foreach ($formatos as $formato):
                    ?>
                    <span class="tag-outline"><?= esc($formato) ?></span>
                <?php endforeach; ?>
                <?php if (!empty($requerimiento['formato_otros'])): ?>
                    <?php 
                    // Separar por comas y limpiar espacios
                    $formatosOtros = array_map('trim', explode(',', $requerimiento['formato_otros']));
                    foreach ($formatosOtros as $formatoOtro):
                        if (!empty($formatoOtro)):
                    ?>
                            <span class="tag-outline special"><?= esc($formatoOtro) ?></span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
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
    <div class="row g-4 mt-1">
        <!-- Archivos del Cliente -->
        <div class="col-12">
            <div class="card-dark-main p-3">
                <label class="label-tiny mb-3 d-block">
                    <i class="bi bi-person-badge me-1"></i> ARCHIVOS ENVIADOS POR EL CLIENTE
                </label>
                <?php
                $archivosCliente = array_filter($archivos ?? [], fn($archivo) => !empty($archivo['idrequerimiento']) && empty($archivo['idatencion']));
                if (!empty($archivosCliente)):
                    ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($archivosCliente as $archivo): ?>
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
                            <a href="<?= base_url('cliente/archivos/' . ($archivo['id'] ?? '')) ?>" target="_blank"
                                class="archivo-adjunto-card cliente-file" title="<?= esc($archivo['nombre']) ?>">
                                <i class="bi <?= $icono ?>"></i>
                                <div class="archivo-info">
                                    <span class="archivo-nombre"><?= esc($archivo['nombre']) ?></span>
                                    <span class="archivo-peso"><?= $kb ?> KB</span>
                                </div>
                                <i class="bi bi-box-arrow-up-right archivo-open"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-3">
                        <i class="bi bi-inbox me-2"></i>
                        <span class="small">No hay materiales de referencia</span>
                    </div>
                <?php endif; ?>
                
                <!-- URL de Referencia del Cliente -->
                <?php if (!empty($requerimiento['url_subida'])): ?>
                    <div class="mt-3">
                        <label class="label-tiny mb-2 d-block">
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
                <?php endif; ?>
            </div>
        </div>

        <!-- Archivos de Entrega del Empleado - Solo visible si está finalizado o completado -->
        <?php if (in_array(($requerimiento['estado'] ?? ''), ['finalizado', 'completado'])): ?>
            <div class="col-12 mt-3">
                <div class="card-dark-main p-3">
                    <label class="label-tiny mb-3 d-block">
                        <i class="bi bi-briefcase me-1"></i> ARCHIVOS DE ENTREGA DEL EMPLEADO
                    </label>
                    <?php
                    $archivosEmpleado = array_filter($archivos ?? [], fn($archivo) => !empty($archivo['idatencion']));
                    if (!empty($archivosEmpleado)):
                        ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($archivosEmpleado as $archivo): ?>
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
                                <a href="<?= base_url($archivo['ruta']) ?>" target="_blank"
                                    class="archivo-adjunto-card empleado-file" title="<?= esc($archivo['nombre']) ?>">
                                    <i class="bi <?= $icono ?>"></i>
                                    <div class="archivo-info">
                                        <span class="archivo-nombre"><?= esc($archivo['nombre']) ?></span>
                                        <span class="archivo-peso"><?= $kb ?> KB</span>
                                    </div>
                                    <i class="bi bi-box-arrow-up-right archivo-open"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted text-center py-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <span class="small">No hay archivos de entrega para este requerimiento.</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- URL de Entrega del Empleado - Solo visible si está finalizado o completado -->
                    <?php if (!empty($requerimiento['url_entrega'])): ?>
                        <div class="mt-3">
                            <label class="label-tiny mb-2 d-block">
                                <i class="bi bi-link-45deg me-1"></i> ENLACE DE ENTREGA
                            </label>
                            <a href="<?= esc($requerimiento['url_entrega']) ?>" target="_blank" class="archivo-adjunto-card"
                                style="max-width: 100%;">
                                <i class="bi bi-globe"></i>
                                <div class="archivo-info" style="flex: 1;">
                                    <span class="archivo-nombre"
                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= esc($requerimiento['url_entrega']) ?>
                                    </span>
                                    <span class="archivo-peso">Haz clic para abrir</span>
                                </div>
                                <i class="bi bi-box-arrow-up-right archivo-open"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?= $this->endSection() ?>