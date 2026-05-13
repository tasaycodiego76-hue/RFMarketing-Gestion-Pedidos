<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/detalle_requerimiento.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- HEADER -->
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
    <!-- COLUMNA IZQUIERDA: Información principal del brief -->
    <div class="col-lg-8">
        <div class="card-dark-main p-4 h-100">

            <!-- Encabezado -->
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
                            <?= esc($user['nombre_area_agencia']) ?> - Agencia
                        <?php elseif (!empty($user['nombre_empresa'])): ?>
                            <?= esc($user['nombre_empresa']) ?>
                        <?php elseif (!empty($user['nombre_area'])): ?>
                            <?= esc($user['nombre_area']) ?>
                        <?php else: ?>
                            Cliente
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="divider-dark my-4"></div>

            <!-- Objetivo y Público -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="info-section">
                        <label class="label-tiny mb-2">OBJETIVO DE COMUNICACIÓN</label>
                        <div class="content-box">
                            <p class="content-text mb-0"><?= esc($requerimiento['objetivo_comunicacion']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-section">
                        <label class="label-tiny mb-2">PÚBLICO OBJETIVO</label>
                        <div class="content-box">
                            <p class="content-text mb-0"><?= esc($requerimiento['publico_objetivo']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            <div class="mb-4">
                <label class="label-tiny mb-2">DESCRIPCIÓN DETALLADA</label>
                <div class="brief-container">
                    <div id="descripcion-container" class="descripcion-box">
                        <?= nl2br(esc($requerimiento['descripcion'])) ?>
                    </div>
                </div>
            </div>

            <!-- Entrega del Diseñador -->
            <?php if (!empty($requerimiento['respuestatexto'])): ?>
                <div class="entrega-disenador p-3 mt-4">
                    <label class="label-tiny text-warning">ENTREGA DEL DISEÑADOR / ENLACES</label>
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

    <!-- COLUMNA DERECHA -->
    <div class="col-lg-4">
        <div class="card-dark-main p-4 mb-4">
            <label class="label-tiny mb-4 d-block">INFORMACIÓN DEL REQUERIMIENTO</label>

            <div class="timeline-item">
                <i class="bi bi-person-badge"></i>
                <div>
                    <span class="t-label">RESPONSABLE ASIGNADO</span>
                    <span class="t-value <?= !empty($requerimiento['idempleado']) ? 't-value-accent' : 'text-secondary' ?>">
                        <?php if (!empty($requerimiento['idempleado'])): ?>
                            <?= esc($requerimiento['empleado_nombre']) ?>
                        <?php else: ?>
                            Pendiente de asignar
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-calendar-event"></i>
                <div>
                    <span class="t-label">FECHA DE ENTREGA REQUERIDA</span>
                    <span class="t-value">
                        <?= date('d/m/Y', strtotime($requerimiento['fecharequerida'])) ?> 
                        <span style="color: var(--accent-yellow); margin-left: 5px;"><?= date('H:i', strtotime($requerimiento['fecharequerida'])) ?></span>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-file-earmark-plus"></i>
                <div>
                    <span class="t-label">REGISTRO DE SOLICITUD</span>
                    <span class="t-value">
                        <?= date('d/m/Y', strtotime($requerimiento['fechacreacion'])) ?>
                        <span style="color: var(--accent-yellow); margin-left: 5px;"><?= date('H:i', strtotime($requerimiento['fechacreacion'])) ?></span>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-play-circle"></i>
                <div>
                    <span class="t-label">INICIO DE TRABAJO</span>
                    <span class="t-value">
                        <?php if (!empty($requerimiento['fechainicio'])): ?>
                            <?= date('d/m/Y', strtotime($requerimiento['fechainicio'])) ?>
                            <span style="color: var(--accent-yellow); margin-left: 5px;"><?= date('H:i', strtotime($requerimiento['fechainicio'])) ?></span>
                        <?php else: ?>
                            -- / -- / --
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <span class="t-label">FECHA DE FINALIZACIÓN</span>
                    <span class="t-value">
                        <?php if (!empty($requerimiento['fechacompletado'])): ?>
                            <?= date('d/m/Y', strtotime($requerimiento['fechacompletado'])) ?>
                            <span style="color: var(--accent-yellow); margin-left: 5px;"><?= date('H:i', strtotime($requerimiento['fechacompletado'])) ?></span>
                        <?php else: ?>
                            -- / -- / --
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-info-square"></i>
                <div>
                    <span class="t-label">ESTADO DEL PROYECTO</span>
                    <span class="t-value t-value-accent"><?= strtoupper(str_replace('_', ' ', $requerimiento['estado'])) ?></span>
                </div>
            </div>

            <div class="timeline-item">
                <i class="bi bi-pencil-square"></i>
                <div>
                    <span class="t-label">MODIFICACIONES SOLICITADAS</span>
                    <span class="t-value"><?= $requerimiento['num_modificaciones'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN INFERIOR -->
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

    <div class="col-12 mt-4">
        <div class="card-dark-main p-4">
            <label class="label-tiny mb-4 d-block">ARCHIVOS ENVIADOS POR EL CLIENTE</label>
            
            <div class="files-container-clean">
                <?php
                $archivosCliente = array_filter($archivos ?? [], fn($archivo) => !empty($archivo['idrequerimiento']) && empty($archivo['idatencion']));
                if (!empty($archivosCliente) || !empty($requerimiento['url_subida'])):
                    ?>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach ($archivosCliente as $archivo): ?>
                            <?php
                            $icono = 'bi-file-earmark';
                            $mime = $archivo['tipo'] ?? '';
                            if (str_contains($mime, 'image')) $icono = 'bi-file-earmark-image';
                            elseif (str_contains($mime, 'pdf')) $icono = 'bi-file-earmark-pdf';
                            elseif (str_contains($mime, 'video')) $icono = 'bi-file-earmark-play';
                            elseif (str_contains($mime, 'word')) $icono = 'bi-file-earmark-word';
                            elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) $icono = 'bi-file-earmark-excel';

                            $kb = number_format(($archivo['tamano'] ?? 0) / 1024, 1);
                            ?>
                            <a href="<?= base_url('cliente/archivos/' . ($archivo['id'] ?? '')) ?>" target="_blank"
                                class="archivo-adjunto-card" title="<?= esc($archivo['nombre']) ?>">
                                <i class="bi <?= $icono ?>"></i>
                                <div class="archivo-info">
                                    <span class="archivo-nombre"><?= esc($archivo['nombre']) ?></span>
                                    <span class="archivo-peso"><?= $kb ?> KB</span>
                                </div>
                                <i class="bi bi-box-arrow-up-right archivo-open"></i>
                            </a>
                        <?php endforeach; ?>

                        <?php if (!empty($requerimiento['url_subida'])): ?>
                            <a href="<?= esc($requerimiento['url_subida']) ?>" target="_blank"
                                class="archivo-adjunto-card">
                                <i class="bi bi-globe"></i>
                                <div class="archivo-info">
                                    <span class="archivo-nombre">Enlace de Referencia</span>
                                    <span class="archivo-peso">Abrir URL</span>
                                </div>
                                <i class="bi bi-box-arrow-up-right archivo-open"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-2" style="font-size: 14px; opacity: 0.6;">
                        <i class="bi bi-folder2-open me-2"></i> No hay materiales adjuntos
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Entrega Final del Especialista -->
    <?php if (in_array(($requerimiento['estado'] ?? ''), ['finalizado', 'completado'])): ?>
        <div class="col-12 mt-4">
            <div class="card-dark-main p-4 border-success-simple">
                <label class="label-tiny mb-4 d-block text-success">ENTREGA FINAL DEL ESPECIALISTA</label>

                <div class="d-flex flex-wrap gap-3">
                    <!-- Archivos Entregados -->
                    <?php
                    $archivosEmpleado = array_filter($archivos ?? [], fn($archivo) => !empty($archivo['idatencion']));
                    foreach ($archivosEmpleado as $archivo):
                        $icono = 'bi-file-earmark';
                        $mime = $archivo['tipo'] ?? '';
                        if (str_contains($mime, 'image')) $icono = 'bi-file-earmark-image';
                        elseif (str_contains($mime, 'pdf')) $icono = 'bi-file-earmark-pdf';
                        elseif (str_contains($mime, 'video')) $icono = 'bi-file-earmark-play';
                        elseif (str_contains($mime, 'word')) $icono = 'bi-file-earmark-word';
                        elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) $icono = 'bi-file-earmark-excel';
                        $kb = number_format(($archivo['tamano'] ?? 0) / 1024, 1);
                        ?>
                        <a href="<?= base_url('cliente/archivos/' . ($archivo['id'] ?? '')) ?>" target="_blank"
                            class="archivo-adjunto-card success" title="<?= esc($archivo['nombre']) ?>">
                            <i class="bi <?= $icono ?>"></i>
                            <div class="archivo-info">
                                <span class="archivo-nombre"><?= esc($archivo['nombre']) ?></span>
                                <span class="archivo-peso"><?= $kb ?> KB</span>
                            </div>
                            <i class="bi bi-box-arrow-up-right archivo-open"></i>
                        </a>
                    <?php endforeach; ?>

                    <!-- Enlace de Entrega -->
                    <?php if (!empty($requerimiento['url_entrega'])): ?>
                        <a href="<?= esc($requerimiento['url_entrega']) ?>" target="_blank"
                            class="archivo-adjunto-card success">
                            <i class="bi bi-link-45deg"></i>
                            <div class="archivo-info">
                                <span class="archivo-nombre">Recurso / Enlace Final</span>
                                <span class="archivo-peso">Ver entrega en línea</span>
                            </div>
                            <i class="bi bi-box-arrow-up-right archivo-open"></i>
                        </a>
                    <?php endif; ?>

                    <?php if (empty($archivosEmpleado) && empty($requerimiento['url_entrega'])): ?>
                        <div class="text-muted text-center w-100 py-2" style="font-size: 14px; opacity: 0.6;">
                            <i class="bi bi-check2-circle me-2"></i> El requerimiento ha sido finalizado correctamente
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>