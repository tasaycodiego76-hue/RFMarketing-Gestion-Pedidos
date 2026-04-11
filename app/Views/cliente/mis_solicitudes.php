<?= $this->extend('plantillas/cliente') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= base_url('recursos/styles/cliente/paginas/mis-pedidos.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<!-- Encabezado -->
<div class="seccion-titulo">MIS PEDIDOS</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0" style="font-size:2rem; font-weight:800;">
            <?= esc($user['nombre'] . ' ' . $user['apellidos']) ?>
        </h2>
        <p class="small mb-0" style="color:#aaa;">Cliente — Historial de requerimientos</p>
    </div>
    <button class="btn-rf" data-bs-toggle="modal" data-bs-target="#modal-nuevo-pedido">
        <i class="bi bi-plus-lg"></i> Nuevo Pedido
    </button>
</div>

<!-- Métricas -->
<div class="seccion-titulo">RESUMEN</div>
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">Por Aprobar</div>
            <div class="met-num amarillo" id="cnt-por-aprobar">—</div>
            <div class="met-sub">Pendientes de revisión</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">En Proceso</div>
            <div class="met-num azul" id="cnt-en-proceso">—</div>
            <div class="met-sub">En curso</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">Completados</div>
            <div class="met-num verde" id="cnt-completado">—</div>
            <div class="met-sub">Total histórico</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3">
            <div class="met-label">Total</div>
            <div class="met-num" style="color:#f0f0f0" id="cnt-total">—</div>
            <div class="met-sub">Todos los pedidos</div>
        </div>
    </div>
</div>

<!-- Tabla de pedidos -->
<div class="seccion-titulo">TODOS LOS PEDIDOS</div>
<div class="card" style="overflow:hidden;">
    <div class="tabla-header">
        <div class="buscador-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="buscador" placeholder="Buscar pedido..." class="input-buscar">
        </div>
    </div>
    <div class="table-responsive">
        <table class="tabla-rf" id="tablaPedidos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="content-pedidos">
                <!-- Contenido de los Pedidos -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Selección de Servicios -->
<div class="modal fade" id="modal-nuevo-pedido" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-rf">
            <div class="modal-header modal-rf-header">
                <div>
                    <p class="campo-label mb-1">NUEVO PEDIDO</p>
                    <h5 class="modal-title mb-0">Selecciona el tipo de servicio</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-rf-body p-4">
                <!-- Cards de servicios -->
                <div id="lista-servicios"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Para la Estructura del Formulario (Wizard) -->
<div class="modal fade" id="modal-formulario-detalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-rf">
            <form id="form-nuevo-pedido" class="needs-validation" novalidate>
                <input type="hidden" name="idservicio" id="form-idservicio">

                <div class="modal-header modal-rf-header">
                    <div style="width:100%">
                        <h5 id="form-titulo-servicio" style="margin-bottom:20px; font-size:18px;">Paso 1: Info básica
                        </h5>
                        <div class="wizard-steps">
                            <div class="step-wrapper">
                                <div class="step active" id="step-1-indicador">1</div>
                                <div class="step-label" id="step-1-label">Info básica</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step-wrapper">
                                <div class="step" id="step-2-indicador">2</div>
                                <div class="step-label" id="step-2-label">Detalles y formatos</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step-wrapper">
                                <div class="step" id="step-3-indicador">3</div>
                                <div class="step-label" id="step-3-label">Confirmar y enviar</div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body modal-rf-body p-4">
                    <!-- SECTION 1: Datos Iniciales -->
                    <div class="wizard-section" id="section-1">
                        <div class="autofill mb-4">
                            <div class="autofill-title"><span>&#10003;</span> DATOS DE TU CUENTA</div>
                            <div class="autofill-row">
                                <span class="autofill-k">Solicitante</span>
                                <span class="autofill-v"><?= esc($user['nombre'] . ' ' . $user['apellidos']) ?></span>
                            </div>
                            <div class="autofill-row">
                                <span class="autofill-k">Empresa / Área</span>
                                <span class="autofill-v">
                                    <span class="dot"></span>
                                    <?php
                                    // Usamos la misma lógica que en tu sidebar para ser consistentes
                                    $empresa_modal = $user['nombre_empresa'] ?? $user['empresa'] ?? 'Empresa no asignada';
                                    $area_modal = $user['nombre_area'] ?? $user['area'] ?? 'Área General';
                                    echo esc($empresa_modal . ' / ' . $area_modal);
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="field mb-3">
                            <label>TÍTULO DEL REQUERIMIENTO</label>
                            <input type="text" name="titulo" id="campo-titulo" class="field-input"
                                placeholder="Ej: Banner campaña de matrícula 2026" required>
                        </div>

                        <!-- Prioridad -->
                        <div class="field mb-3">
                            <label>PRIORIDAD</label>
                            <div class="prioridad-opciones">
                                <label class="prio-opcion">
                                    <input type="radio" name="prioridad" value="Baja">
                                    <span class="prio-badge prio-baja">Baja</span>
                                </label>
                                <label class="prio-opcion">
                                    <input type="radio" name="prioridad" value="Media" checked>
                                    <span class="prio-badge prio-media">Media</span>
                                </label>
                                <label class="prio-opcion">
                                    <input type="radio" name="prioridad" value="Alta">
                                    <span class="prio-badge prio-alta">Alta</span>
                                </label>
                            </div>
                        </div>
                        <div id="contenedor-nombre-personalizado" class="field mb-3" style="display:none;">
                            <label>NOMBRE DEL SERVICIO REQUERIDO</label>
                            <input type="text" name="titulo" id="titulo_personalizado" class="field-input"
                                placeholder="Ej: Gestion de Redes Sociales (Social Media)">
                        </div>

                        <div class="field mb-3">
                            <label>SERVICIO SELECCIONADO</label>
                            <div class="d-flex align-items-center"
                                style="background:#111; padding:8px 12px; border-radius:6px; border:1px solid #1e1e1e;">
                                <span id="wbadge-container"></span> <span id="txt-servicio-seleccionado"
                                    class="ms-2 fw-bold text-white" style="font-size:11px;">---</span>
                            </div>
                        </div>

                        <div class="field mb-3" id="contenedor-titulo-standar" style="display:none;">
                            <label>TÍTULO DEL REQUERIMIENTO</label>
                            <input type="text" name="titulo" id="titulo_standar" class="field-input"
                                placeholder="Ej: Banner para campaña de primavera">
                        </div>

                        <div class="field mb-3">
                            <label>OBJETIVO DE COMUNICACIÓN</label>
                            <textarea name="objetivo" class="field-input" style="height:60px;"
                                placeholder="¿Cuál es el objetivo? ¿A quién va dirigido?" required></textarea>
                        </div>

                        <div class="field mb-3">
                            <label>¿QUÉ TIPO DE REQUERIMIENTO ES?</label>

                            <div id="lista-requerimientos-estandar">
                                <select name="tipo_requerimiento" class="form-select" id="tipo_req"
                                    style="background:#0a0a0a; border:1px solid #1e1e1e; color:#c0c0c0; font-size:11px;">
                                    <option value="" selected disabled>Seleccionar...</option>
                                    <option value="adaptacion">Adaptación de Arte (7 días hábiles)</option>
                                    <option value="creacion">Creación de Arte (10 días hábiles)</option>
                                    <option value="editorial">Trabajo Editorial (20 días hábiles)</option>
                                    <option value="audiovisual">Creación de Video (20 días hábiles)</option>
                                </select>
                            </div>
                        </div>

                        <div class="field mb-3">
                            <label>FECHA EN QUE SE NECESITA</label>
                            <input type="date" id="fecha_entrega_input" name="fecha_entrega" required>
                        </div>
                    </div>
                    <!-- SECTION 2: Detalles y Formatos  -->
                    <div class="wizard-section d-none" id="section-2">

                        <!-- Descripción -->
                        <div class="field mb-3">
                            <label>DESCRIPCIÓN DETALLADA</label>
                            <textarea name="descripcion" class="field-input" style="height:80px;"
                                placeholder="Describe con detalle lo que necesitas..." required></textarea>
                        </div>

                        <!-- Público objetivo -->
                        <div class="field mb-3">
                            <label>PÚBLICO OBJETIVO</label>
                            <textarea name="publico" class="field-input" style="height:50px;"
                                placeholder="¿A quién va dirigido? Tono del mensaje..." required></textarea>
                        </div>

                        <!-- Canales de difusión -->
                        <div class="field mb-3">
                            <label>¿EN DÓNDE SE VA A DIFUNDIR?</label>
                            <p class="campo-sublabel">Selecciona como máximo 3 opciones.</p>
                            <div class="checks-grid" id="canales-checks"></div>
                        </div>

                        <!-- Formatos solicitados -->
                        <div class="field mb-3">
                            <label>¿EN QUÉ FORMATO QUIERES TU REQUERIMIENTO?</label>
                            <div class="checks-grid" id="formatos-checks"></div>
                        </div>

                        <!-- Formato otros -->
                        <div class="field mb-2" id="contenedor-formato-otros" style="display:none;">
                            <label>SOLO SI MENCIONASTE OTROS — MENCIONA EL FORMATO Y MEDIDAS</label>
                            <input type="text" name="formato_otros" class="field-input"
                                placeholder="Ej: Banner 3x2 metros, formato PNG">
                        </div>

                        <!-- ¿Tiene materiales? -->
                        <div class="field mb-3">
                            <label>¿CUENTAS CON MATERIALES DE REFERENCIA?</label>
                            <select name="tiene_materiales" id="select-materiales" class="form-select field-select"
                                required>
                                <option value="" disabled selected>Seleccionar...</option>
                                <option value="1">Sí, tengo materiales</option>
                                <option value="0">No, no tengo materiales</option>
                            </select>
                        </div>

                        <!-- Subida de archivos + Link (aparecen juntos cuando selecciona Sí) -->
                        <div id="contenedor-materiales" style="display:none;">
                            <!-- Subida de archivos -->
                            <div class="field mb-3" id="contenedor-archivos">
                                <label>ADJUNTA TUS ARCHIVOS</label>
                                <p class="campo-sublabel">Máximo 100MB por archivo. PDF, imágenes, videos, documentos.
                                </p>
                                <div class="upload-area" id="upload-area"
                                    onclick="document.getElementById('input-archivos').click()">
                                    <i class="bi bi-cloud-arrow-up" style="font-size:28px; color:#555;"></i>
                                    <p style="color:#555; font-size:12px; margin:6px 0 0 0;">Haz clic o arrastra tus
                                        archivos aquí</p>
                                </div>
                                <input type="file" name="documentos[]" id="input-archivos" multiple
                                    style="display:none;"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.zip">
                                <div id="lista-archivos" style="margin-top:8px;"></div>
                            </div>

                            <!-- Link de referencia -->
                            <div class="field mb-3" id="contenedor-link">
                                <label>LINK DE REFERENCIA (Google Drive, WeTransfer, etc.)</label>
                                <input type="text" name="url_referencia" class="field-input"
                                    placeholder="https://drive.google.com/...">
                            </div>
                        </div>

                    </div>
                    <!-- SECTION 3: Confirmar y Enviar -->
                    <div class="wizard-section d-none" id="section-3">

                        <div class="resumen-header mb-3">
                            <i class="bi bi-clipboard-check" style="font-size:24px; color:#f5c400;"></i>
                            <div>
                                <p class="campo-label mb-0">REVISA TU REQUERIMIENTO</p>
                                <p style="color:#555; font-size:11px; margin:0;">Verifica que todo esté correcto antes
                                    de enviar</p>
                            </div>
                        </div>

                        <!-- Bloque: Info básica -->
                        <div class="resumen-bloque mb-3">
                            <div class="resumen-bloque-titulo">
                                <span class="step-mini">1</span> INFO BÁSICA
                            </div>
                            <div class="resumen-grid">
                                <div class="resumen-item">
                                    <span class="info-label">SERVICIO</span>
                                    <span class="info-valor" id="res-servicio">—</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="info-label">TÍTULO</span>
                                    <span class="info-valor" id="res-titulo">—</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="info-label">TIPO DE REQUERIMIENTO</span>
                                    <span class="info-valor" id="res-tipo">—</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="info-label">FECHA REQUERIDA</span>
                                    <span class="info-valor" id="res-fecha">—</span>
                                </div>
                                <div class="resumen-item resumen-full">
                                    <span class="info-label">OBJETIVO DE COMUNICACIÓN</span>
                                    <span class="info-valor" id="res-objetivo">—</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="info-label">PRIORIDAD</span>
                                    <span class="info-valor" id="res-prioridad">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bloque: Detalles -->
                        <div class="resumen-bloque mb-3">
                            <div class="resumen-bloque-titulo">
                                <span class="step-mini">2</span> DETALLES Y FORMATOS
                            </div>
                            <div class="resumen-grid">
                                <div class="resumen-item resumen-full">
                                    <span class="info-label">DESCRIPCIÓN</span>
                                    <span class="info-valor" id="res-descripcion">—</span>
                                </div>
                                <div class="resumen-item resumen-full">
                                    <span class="info-label">PÚBLICO OBJETIVO</span>
                                    <span class="info-valor" id="res-publico">—</span>
                                </div>
                                <div class="resumen-item resumen-full">
                                    <span class="info-label">CANALES DE DIFUSIÓN</span>
                                    <div class="tags-wrap" id="res-canales"></div>
                                </div>
                                <div class="resumen-item resumen-full">
                                    <span class="info-label">FORMATOS SOLICITADOS</span>
                                    <div class="tags-wrap" id="res-formatos"></div>
                                </div>
                                <div class="resumen-item resumen-full" id="res-formato-otros-wrap"
                                    style="display:none;">
                                    <span class="info-label">FORMATO PERSONALIZADO</span>
                                    <span class="info-valor" id="res-formato-otros">—</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="info-label">MATERIALES</span>
                                    <span class="info-valor" id="res-materiales">—</span>
                                </div>
                                <div class="resumen-item resumen-full" id="res-link-wrap" style="display:none;">
                                    <span class="info-label">LINK DE REFERENCIA</span>
                                    <span class="info-valor" id="res-link">—</span>
                                </div>
                                <div class="resumen-item resumen-full" id="res-archivos-wrap" style="display:none;">
                                    <span class="info-label">ARCHIVOS ADJUNTOS</span>
                                    <div id="res-archivos"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Aviso final -->
                        <div class="aviso-envio">
                            <i class="bi bi-info-circle"></i>
                            <span>Una vez enviado, el equipo de RF Marketing revisará tu requerimiento y te notificará
                                por correo.</span>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0" style="gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline-light d-none" id="btn-atras"
                        onclick="window.retrocederPaso()">
                        <i class="bi bi-arrow-left"></i> Atrás
                    </button>
                    <button type="button" class="btn-rf" id="btn-siguiente">Siguiente Paso <i
                            class="bi bi-arrow-right"></i></button>
                    <button type="submit" class="btn-rf d-none" id="btn-enviar">Enviar Requerimiento <i
                            class="bi bi-check-lg"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pasar base_url al JS -->
<script>
    const base_url = "<?= base_url() ?>";
    const userId = "<?= esc($user['id']) ?>";
    const userRol = "<?= esc($user['rol']) ?>";
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('recursos/scripts/cliente/mis-pedidos.js') ?>"></script>
<?= $this->endSection() ?>