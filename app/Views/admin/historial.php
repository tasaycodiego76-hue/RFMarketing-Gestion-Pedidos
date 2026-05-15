<?= $this->extend('plantillas/admin') ?>

<?= $this->section('styles') ?>
<link href="<?= base_url('recursos/styles/admin/paginas/usuarios.css') ?>" rel="stylesheet">
<link href="<?= base_url('recursos/styles/admin/paginas/kanban.css') ?>" rel="stylesheet">
<link href="<?= base_url('recursos/styles/admin/paginas/historial.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const AREA_ACTUAL = 0;
    const AREA_NOMBRE = "HISTORIAL";
</script>
<script src="<?= base_url('recursos/scripts/admin/kanban.js') ?>"></script>
<script src="<?= base_url('recursos/scripts/admin/historial.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <p style="font-size: 12px; color: #888; margin: 0;">Registro visual de todos los proyectos finalizados.</p>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <label style="font-size: 10px; color: #555; font-weight: 800; text-transform: uppercase; margin: 0;">Filtrar
                día:</label>
            <input type="date" id="filtroFecha" class="form-control input-calendario" style="width: 170px;">
        </div>
        <input type="text" id="busquedaHistorial" class="input-busqueda" placeholder="Buscar proyecto..."
            style="max-width: 200px;">
    </div>
</div>

<div class="tabla-contenedor">
    <table class="tabla-usuarios" id="tablaHistorial">
        <thead>
            <tr>
                <th style="width: 170px;">Área</th>
                <th>Proyecto</th>
                <th>Cliente / Empresa</th>
                <th style="text-align: center;">Finalización</th>
                <th>Ejecutor</th>
                <th style="text-align: center; width: 180px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 80px; color: #444;">No hay registros</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p): ?>
                    <?php $inicial = mb_strtoupper(mb_substr($p['empresa_nombre'], 0, 1)); ?>
                    <tr data-fecha="<?= date('Y-m-d', strtotime($p['fechacompletado'])) ?>">
                        <td data-label="Área">
                            <div class="historial-area-badge">
                                <i class="bi bi-palette-fill"></i> <?= esc($p['area_nombre'] ?? 'General') ?>
                            </div>
                        </td>
                        <td data-label="Proyecto">
                            <div class="historial-title"><?= esc($p['titulo']) ?></div>
                            <div class="historial-sub-info"><?= esc($p['servicio_nombre']) ?></div>
                        </td>
                        <td data-label="Empresa">
                            <div class="historial-empresa-wrapper">
                                <div class="empresa-avatar-mini" style="background: <?= $p['empresa_color'] ?>;">
                                    <?= $inicial ?>
                                </div>
                                <div class="empresa-nombre-text"><?= esc($p['empresa_nombre']) ?></div>
                            </div>
                        </td>
                        <td style="text-align: center;" data-label="Finalización">
                            <div class="historial-fecha"><?= date('d/m/Y', strtotime($p['fechacompletado'])) ?></div>
                            <div class="historial-hora"><?= date('H:i A', strtotime($p['fechacompletado'])) ?></div>
                        </td>
                        <td data-label="Ejecutor">
                            <div class="historial-ejecutor-nombre"><?= esc(strtoupper($p['empleado_nombre'])) ?></div>
                            <div class="historial-status">FINALIZADO</div>
                        </td>
                        <td style="text-align: center;" data-label="Acción">
                            <button class="btn-expediente" onclick="verDetalle(<?= $p['id'] ?>)">
                                VER PEDIDO
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="height: 50px;"></div>

<!-- ═══ TEMPLATE: DETALLE DE KANBAN ═══ -->
<template id="template-detalle-kanban">
    <div class="exp-container">
        <!-- HEADER SECCIÓN -->
        <div class="exp-header-layout"
            style="padding: 40px 30px 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
            <div style="flex: 1; min-width: 0;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <span class="tpl-status-pill"></span>
                    <span style="color:#444; font-size:11px; font-weight:800;">ID: <span class="tpl-id"></span></span>
                </div>
                <h2 class="tpl-titulo"
                    style="font-family:'Bebas Neue'; font-size:48px; color:#fff; letter-spacing:1px; margin:0; line-height:1.1; word-wrap:break-word; overflow-wrap:break-word;">
                </h2>
                <div style="margin-top:15px; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                    <span style="color:#F5C400; font-weight:800; font-size:14px;"><i class="bi bi-building"></i> <span
                            class="tpl-empresa"></span></span>
                    <span style="color:#222;">|</span>
                    <span style="color:#888; font-size:13px; font-weight:600;">ÁREA: <span
                            class="tpl-area"></span></span>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div class="tpl-servicio" style="font-family:'Bebas Neue'; font-size:24px; color:#F5C400;"></div>
                <div style="color:#444; font-size:10px; font-weight:800; letter-spacing:1px; margin-top:5px;">ATENCIÓN
                    #<span class="tpl-idatencion"></span></div>
            </div>
        </div>

        <!-- STEPPER -->
        <div class="tpl-stepper-container"></div>

        <div class="exp-grid">
            <!-- COLUMNA PRINCIPAL -->
            <div class="exp-main-col">

                <!-- Descripción -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-file-text"></i> <span>DESCRIPCIÓN DEL
                            REQUERIMIENTO</span></div>
                    <div class="exp-card-body">
                        <div class="data-value tpl-descripcion" style="font-size:13px; color:#ccc;"></div>
                    </div>
                </div>

                <!-- Estrategia -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-compass"></i> <span>ESTRATEGIA DE COMUNICACIÓN</span>
                    </div>
                    <div class="exp-card-body">
                        <div class="data-row">
                            <div class="data-box">
                                <span class="data-label-large">Objetivo Principal</span>
                                <div class="data-value tpl-objetivo"></div>
                            </div>
                            <div class="data-box">
                                <span class="data-label-large">Público Objetivo</span>
                                <div class="data-value tpl-publico"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canales y Formatos -->
                <div class="data-row">
                    <div class="exp-card" style="margin-bottom:0;">
                        <div class="exp-card-header"><i class="bi bi-broadcast"></i> <span>CANALES</span></div>
                        <div class="exp-card-body tpl-canales"></div>
                    </div>
                    <div class="exp-card" style="margin-bottom:0;">
                        <div class="exp-card-header"><i class="bi bi-layers"></i> <span>FORMATOS</span></div>
                        <div class="exp-card-body tpl-formatos"></div>
                    </div>
                </div>

                <!-- Recursos Cliente -->
                <div class="exp-card" style="margin-top:25px;">
                    <div class="exp-card-header"><i class="bi bi-folder-symlink"></i> <span>RECURSOS DEL CLIENTE</span>
                    </div>
                    <div class="exp-card-body tpl-archivos-cliente"></div>
                </div>

                <!-- Recursos Empleado (Entrega) -->
                <div class="tpl-entrega-container"></div>
            </div>

            <!-- SIDEBAR -->
            <div class="exp-sidebar">

                <!-- Responsable -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-person-badge"></i> <span>RESPONSABLE</span></div>
                    <div class="exp-card-body tpl-empleado" style="padding:15px;"></div>
                </div>
                <!-- Contacto Cliente -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-person-lines-fill"></i> <span>CONTACTO CLIENTE</span>
                    </div>
                    <div class="exp-card-body" style="display:flex; flex-direction:column; gap:12px; padding:15px;">
                        <div class="data-box">
                            <span class="data-label">Nombre</span>
                            <div class="data-value tpl-cliente-nombre"></div>
                        </div>
                        <div class="data-box">
                            <span class="data-label">Teléfono</span>
                            <div class="data-value tpl-cliente-telefono"></div>
                        </div>
                        <div class="data-box">
                            <span class="data-label">Correo</span>
                            <div class="data-value tpl-cliente-correo"></div>
                        </div>
                    </div>
                </div>

                <!-- Cronología -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-calendar3"></i> <span>CRONOLOGÍA</span></div>
                    <div class="exp-card-body">
                        <div style="display:flex; flex-direction:column; gap:15px;">
                            <div class="data-box">
                                <span class="data-label-large">Fecha de Solicitud</span>
                                <div class="data-value tpl-f-solicitud"></div>
                            </div>
                            <div class="data-box" style="border-color:#F5C40033; background:rgba(245,196,0,0.02);">
                                <span class="data-label" style="color:#F5C400;">Fecha Límite</span>
                                <div class="data-value tpl-f-limite" style="font-weight:900;"></div>
                            </div>
                            <div class="tpl-f-inicio-container"></div>
                            <div class="tpl-f-fin-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Auditoría y Gestión -->
                <div class="exp-card">
                    <div class="exp-card-header"><i class="bi bi-shield-check"></i> <span>CONTROL Y GESTIÓN</span></div>
                    <div class="exp-card-body">

                        <!-- Prioridad  -->
                        <div class="priority-manager">
                            <span class="data-label">PRIORIDAD</span>
                            <div class="priority-pills">
                                <button type="button" class="prio-pill btn-prio" data-prio="Alta">
                                    <span class="prio-dot dot-alta"></span> ALTA
                                </button>
                                <button type="button" class="prio-pill btn-prio" data-prio="Media">
                                    <span class="prio-dot dot-media"></span> MEDIA
                                </button>
                                <button type="button" class="prio-pill btn-prio" data-prio="Baja">
                                    <span class="prio-dot dot-baja"></span> BAJA
                                </button>
                            </div>
                        </div>

                        <div
                            style="display:flex; justify-content:space-between; align-items:center; background:#000; padding:15px; border-radius:12px; border:1px solid #111;">
                            <span class="data-label" style="margin:0;">MODIFICACIONES</span>
                            <span class="tpl-modificaciones"
                                style="background:#F5C400; color:#000; padding:4px 12px; border-radius:8px; font-weight:900; font-size:14px;"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- FOOTER ACCIONES -->
        <div style="margin-top:30px; padding:30px; border-top:1px solid #151515; display:flex; justify-content:center;">
            <button class="btn" data-dismiss="modal"
                style="background:#111; border:1px solid #222; font-family:'Bebas Neue'; font-size:20px; letter-spacing:2px; padding:12px 60px; border-radius:12px; color:#F5C400; transition:all 0.3s;">
                CERRAR EXPEDIENTE DIGITAL
            </button>
        </div>
    </div>
</template>

<?= $this->endSection() ?>