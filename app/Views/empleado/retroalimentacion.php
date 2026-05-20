<?= $this->extend('plantillas/empleado') ?>
<?= $this->section('styles') ?>
<<style>
    /* Estilos Premium de Retroalimentación (Sincronizado con Responsable) */
    .retro-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    .retro-card {
        background: var(--panel);
        border: 1px solid var(--borde);
        border-radius: 16px;
        padding: 22px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .retro-card:hover {
        transform: translateY(-5px);
        border-color: var(--amarillo);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }
    .retro-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: #ef4444; /* Rojo para correcciones */
    }
    .retro-badge {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
    }
    .text-dim-small {
        font-size: 11px;
        color: var(--texto-3);
        font-weight: 600;
    }
    .title-bebas-retro {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 24px;
        letter-spacing: 1.5px;
        color: var(--texto);
        margin: 12px 0 8px 0;
        line-height: 1.1;
    }
    .badge-servicio-retro {
        font-size: 10px;
        color: var(--amarillo);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        background: rgba(245, 196, 0, 0.08);
        padding: 3px 10px;
        border-radius: 5px;
        display: inline-block;
    }
    .text-muted-extra-small {
        font-size: 11px;
        color: var(--texto-3);
        font-weight: 600;
    }
    .retro-msg-container {
        background: rgba(245, 158, 11, 0.03);
        border: 1px solid rgba(245, 158, 11, 0.2);
        padding: 18px 15px 15px 15px;
        border-radius: 12px;
        position: relative;
        margin-top: 20px;
        flex-grow: 1;
    }
    .retro-msg-label {
        position: absolute;
        top: -10px;
        left: 15px;
        background: var(--amarillo);
        color: #000;
        font-size: 9px;
        font-weight: 900;
        padding: 2px 10px;
        border-radius: 4px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .retro-msg-text {
        margin: 0;
        font-size: 13px;
        color: var(--texto-2);
        line-height: 1.6;
        font-weight: 500;
    }
    .retro-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid var(--borde);
    }
    .avatar-circle-retro {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--panel-2);
        border: 1px solid var(--borde);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        color: var(--amarillo);
    }
    .specialist-label {
        font-size: 9px;
        color: var(--texto-3);
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    .specialist-name {
        font-size: 12px;
        color: var(--texto);
        font-weight: 600;
        line-height: 1;
    }
    .btn-retro-action {
        background: var(--amarillo);
        color: #000;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 13px;
        letter-spacing: 1.2px;
        padding: 7px 18px;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }
    .btn-retro-action:hover {
        background: #fff;
        color: #000;
        transform: scale(1.05);
        text-decoration: none;
    }

    /* Adaptabilidad Móvil */
    @media (max-width: 576px) {
        .retro-grid {
            grid-template-columns: 1fr;
        }
        .retro-card {
            padding: 18px;
        }
        .title-bebas-retro {
            font-size: 20px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<p class="seccion-titulo">Bandeja de Correcciones</p>

<div id="contenedor-retro" style="width: 100%;">
    <?php if(empty($retroalimentacion)): ?>
        <div class="text-center py-5" style="background: var(--panel); border: 1px dashed var(--borde); border-radius: 24px; margin-top: 20px;">
            <div class="mb-4">
                <i class="bi bi-chat-heart" style="font-size: 60px; color: var(--amarillo); opacity: 0.2;"></i>
            </div>
            <h5 style="font-family: 'Bebas Neue'; letter-spacing: 2px; color: var(--texto);">¡Todo impecable!</h5>
            <p style="font-size: 12px; color: var(--texto-3); text-transform: uppercase; letter-spacing: 2px; font-weight: 600;">No tienes pedidos con correcciones pendientes.</p>
        </div>
    <?php else: ?>
        <div class="retro-grid">
            <?php foreach($retroalimentacion as $r): ?>
                <div class="retro-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="retro-badge"><i class="bi bi-exclamation-triangle-fill me-1"></i> Corrección</span>
                        <span class="text-dim-small">
                            <i class="bi bi-clock-history"></i> <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                        </span>
                    </div>
                    
                    <h4 class="title-bebas-retro">
                        <?= esc($r['pedido_titulo']) ?>
                    </h4>

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="badge-servicio-retro">
                            <?= esc($r['servicio_nombre']) ?>
                        </div>
                        <div class="text-muted-extra-small">
                            <i class="bi bi-building"></i> <?= esc($r['empresa_nombre']) ?>
                        </div>
                    </div>

                    <div class="retro-msg-container">
                        <div class="retro-msg-label">
                            Observación del Evaluador
                        </div>
                        <p class="retro-msg-text">
                            "<?= nl2br(esc($r['contenido'])) ?>"
                        </p>
                    </div>

                    <div class="retro-footer">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle-retro">
                                <?= strtoupper(substr($r['evaluador_nombre'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="specialist-label">Administrador</span>
                                <span class="specialist-name">
                                    <?= esc($r['evaluador_nombre'] . ' ' . $r['evaluador_apellidos']) ?>
                                </span>
                            </div>
                        </div>
                        <a href="<?= base_url('empleado/mis_pedidos?highlight=' . $r['id_atencion']) ?>" class="btn-retro-action">
                            CORREGIR <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<div style="height: 50px;"></div>


<?= $this->endSection() ?>
