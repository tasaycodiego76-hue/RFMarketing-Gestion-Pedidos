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
            <form id="form-nuevo-pedido" class="needs-validation" novalidate autocomplete="off">
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
                                    class="ms-2 fw-bold text-white" style="font-size:11px;"></span>
                            </div>
                        </div>

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

                        <div class="field mb-3">
                            <label>TÍTULO DEL REQUERIMIENTO</label>
                            <input type="text" name="titulo" id="campo-titulo" class="field-input"
                                placeholder="Ej: Banner campaña de matrícula 2026" required>
                        </div>

                        <div class="field mb-3">
                            <label>OBJETIVO DE COMUNICACIÓN</label>
                            <textarea name="objetivo" class="field-input" style="height:60px;"
                                placeholder="¿Cuál es el objetivo? ¿A quién va dirigido?" required></textarea>
                        </div>

                        <!-- TIPO DE REQUERIMIENTO CON INFO -->
                        <div class="field mb-3">
                            <label>TIPO DE REQUERIMIENTO</label>
                            <p class="campo-sublabel">Selecciona según la complejidad de tu proyecto</p>

                            <select name="tipo_requerimiento" class="form-select select-estilizado" id="tipo_req"
                                required onchange="mostrarInfoTipo(this.value)">
                                <option value="" selected disabled>Seleccionar...</option>
                                <option value="adaptacion">Adaptación de Arte — 7 días hábiles</option>
                                <option value="creacion">Creación de Arte — 10 días hábiles</option>
                                <option value="editorial">Trabajo Editorial — 20 días hábiles</option>
                                <option value="audiovisual">Creación de Video — 20 días hábiles</option>
                            </select>

                            <!-- Info dinámica del tipo seleccionado -->
                            <div id="info-tipo-container" class="info-tipo-box" style="display:none;">
                                <div class="info-tipo-header">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span id="info-tipo-titulo">Título</span>
                                </div>
                                <p id="info-tipo-desc" class="info-tipo-desc">Descripción</p>
                                <div class="info-tipo-meta">
                                    <span><i class="bi bi-clock"></i> <span id="info-tipo-dias">0</span> días
                                        hábiles</span>
                                    <span><i class="bi bi-people"></i> <span
                                            id="info-tipo-equipo">Diseñador</span></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- FECHAR REQUERIDA - REQUERIMIENTO -->
                        <div class="field mb-3">
                            <label class="text-uppercase fw-bold" style="font-size: 12px; color: #bbb;">FECHA EN QUE SE NECESITA</label>
                            <div class="date-input-container">
                                <input type="date" id="fecha_entrega_input" name="fecha_entrega"
                                    class="custom-date-field" required>
                                <i class="bi bi-calendar2-week"></i>
                            </div>
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

                        <!-- SEPARADOR CANALES -->
                        <div class="field mb-3">
                            <label class="section-title">
                                <i class="bi bi-broadcast"></i> Canales de Difusión
                            </label>
                            <p class="campo-sublabel">Selecciona máximo 3 opciones</p>
                            <div class="checks-grid compact" id="canales-checks"></div>
                        </div>

                        <!-- Línea divisoria -->
                        <hr class="section-divider">

                        <!-- SEPARADOR FORMATOS -->
                        <div class="field mb-3">
                            <label class="section-title">
                                <i class="bi bi-file-earmark-image"></i> Formatos Solicitados
                            </label>
                            <div class="checks-grid compact" id="formatos-checks"></div>
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

                        <!-- Contenedor de archivos (se muestra al seleccionar "Sí") -->
                        <div id="contenedor-materiales" style="display:none;">

                            <!-- Nueva zona de carga simple -->
                            <div class="field mb-3">
                                <label>ADJUNTA TUS ARCHIVOS</label>
                                <p class="campo-sublabel">Máximo 100MB por archivo. PDF, imágenes, videos, documentos.
                                </p>

                                <div class="upload-area-simple"
                                    onclick="document.getElementById('input-archivos').click()">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Agregar archivos</span>
                                </div>

                                <input type="file" name="documentos[]" id="input-archivos" multiple
                                    style="display:none;"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.zip">

                                <div id="lista-archivos"></div>
                            </div>

                            <!-- Link de referencia -->
                            <div class="field mb-3">
                                <label>LINK DE REFERENCIA (Google Drive, WeTransfer, etc.)</label>
                                <input type="text" name="url_referencia" class="field-input"
                                    placeholder="https://drive.google.com/...">
                            </div>
                        </div>
                    </div>
                    <!-- SECTION 3: Confirmar y Enviar -->
                    <div class="wizard-section d-none" id="section-3">
                        <div class="resumen-header mb-4">
                            <div class="resumen-icon">
                                <i class="bi bi-clipboard-check-fill"></i>
                            </div>
                            <div>
                                <h5 class="resumen-titulo">Revisa tu requerimiento</h5>
                                <p class="resumen-sub">Verifica que la información clave esté correcta</p>
                            </div>
                        </div>

                        <!-- Bloque 1: Info Básica -->
                        <div class="resumen-card mb-3">
                            <div class="resumen-card-header">
                                <span class="resumen-numero">1</span>
                                <span class="resumen-card-titulo">Información Principal</span>
                            </div>
                            <div class="resumen-card-body">
                                <div class="resumen-fila">
                                    <span class="resumen-label">Servicio</span>
                                    <span class="resumen-valor" id="res-servicio">—</span>
                                </div>
                                <div class="resumen-fila">
                                    <span class="resumen-label">Título</span>
                                    <span class="resumen-valor" id="res-titulo">—</span>
                                </div>
                                <div class="resumen-fila">
                                    <span class="resumen-label">Fecha Requerida</span>
                                    <span class="resumen-valor" id="res-fecha">—</span>
                                </div>
                                <div class="resumen-fila">
                                    <span class="resumen-label">Prioridad</span>
                                    <span class="resumen-valor"><span class="resumen-badge"
                                            id="res-prioridad">—</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Bloque 2: Detalles -->
                        <div class="resumen-card mb-3">
                            <div class="resumen-card-header">
                                <span class="resumen-numero">2</span>
                                <span class="resumen-card-titulo">Detalles del Proyecto</span>
                            </div>
                            <div class="resumen-card-body">
                                <div class="resumen-fila vertical">
                                    <span class="resumen-label">Público Objetivo</span>
                                    <span class="resumen-valor descripcion" id="res-publico">—</span>
                                </div>
                                <div class="resumen-fila">
                                    <span class="resumen-label">Materiales de Referencia</span>
                                    <span class="resumen-valor" id="res-materiales">—</span>
                                </div>
                                <div class="resumen-fila d-none" id="res-link-wrap">
                                    <span class="resumen-label">Link de Referencia</span>
                                    <span class="resumen-valor link" id="res-link">—</span>
                                </div>
                                <div class="resumen-fila vertical d-none" id="res-archivos-wrap">
                                    <span class="resumen-label">Archivos Adjuntos</span>
                                    <div class="resumen-archivos" id="res-archivos"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Aviso final -->
                        <div class="resumen-aviso">
                            <i class="bi bi-info-circle-fill"></i>
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