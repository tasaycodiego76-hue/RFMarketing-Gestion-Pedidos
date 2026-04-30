<?= $this->extend('plantillas/empleado') ?>
<?= $this->section('styles') ?>
<style>
    .retro-card-minimal {
        background: #0d0d0d;
        border: 1px solid #1a1a1a;
        border-radius: 12px;
        display: flex;
        overflow: hidden;
        transition: transform 0.2s, border-color 0.2s;
    }
    .retro-card-minimal:hover {
        border-color: #333;
        transform: translateY(-2px);
    }
    .retro-side-accent {
        width: 4px;
        background: #ef4444;
        flex-shrink: 0;
    }
    .retro-content-wrap {
        padding: 16px 20px;
        flex-grow: 1;
    }
    .retro-header-mini {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #161616;
    }
    .retro-meta-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .retro-badge-urgent {
        font-size: 9px;
        font-weight: 900;
        color: #ef4444;
        letter-spacing: 1px;
        text-transform: uppercase;
        background: rgba(239, 68, 68, 0.1);
        padding: 2px 8px;
        border-radius: 4px;
    }
    .retro-date {
        font-size: 11px;
        color: #555;
        font-weight: 600;
    }
    .retro-evaluator {
        font-size: 11px;
        color: #888;
    }
    .retro-evaluator small {
        font-weight: 800;
        color: #444;
        margin-right: 4px;
    }
    .retro-evaluator span {
        font-weight: 700;
        color: #aaa;
    }
    .retro-main-body {
        display: grid;
        grid-template-columns: 200px 1fr 150px;
        gap: 20px;
        align-items: center;
    }
    .retro-project-info {
        border-right: 1px solid #161616;
        padding-right: 20px;
    }
    .retro-label-small {
        font-size: 8px;
        font-weight: 900;
        color: #444;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }
    .retro-project-name {
        font-size: 13px;
        font-weight: 800;
        color: var(--amarillo);
        line-height: 1.3;
    }
    .retro-message-box {
        font-size: 13px;
        color: #bbb;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .retro-actions-mini {
        text-align: right;
    }
    .btn-retro-action {
        background: transparent;
        border: 1px solid var(--amarillo);
        color: var(--amarillo);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.2s;
        width: 100%;
    }
    .btn-retro-action:hover {
        background: var(--amarillo);
        color: #000;
    }

    @media (max-width: 992px) {
        .retro-main-body {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .retro-project-info {
            border-right: none;
            border-bottom: 1px solid #161616;
            padding-bottom: 15px;
            padding-right: 0;
        }
        .retro-message-box {
            -webkit-line-clamp: 4;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('contenido') ?>

<p class="seccion-titulo">Retroalimentación del Administrador</p>

<div id="contenedor-retro" style="max-width: 1100px; margin: 0 auto; width: 100%;">
    <?php if(empty($retroalimentacion)): ?>
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 20px; margin-top: 20px;">
            <i class="bi bi-chat-dots-fill" style="font-size: 50px; color: var(--amarillo); opacity: 0.2;"></i>
            <p class="mt-3" style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 3px; font-weight: 700;">Sin observaciones por el momento</p>
        </div>
    <?php else: ?>
        <div class="row mt-3 justify-content-center">
            <?php foreach($retroalimentacion as $r): ?>
                <div class="col-12 mb-3">
                    <div class="retro-card-minimal">
                        <div class="retro-side-accent"></div>
                        <div class="retro-content-wrap">
                            <div class="retro-header-mini">
                                <div class="retro-meta-left">
                                    <span class="retro-badge-urgent">CORRECCIÓN REQUERIDA</span>
                                    <span class="retro-date"><i class="bi bi-calendar3"></i> <?= date('d M, Y', strtotime($r['fecha'])) ?></span>
                                </div>
                                <div class="retro-evaluator">
                                    <small>EVALUADOR:</small> <span>ADM. <?= esc(strtoupper($r['evaluador_nombre'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="retro-main-body">
                                <div class="retro-project-info">
                                    <div class="retro-label-small">PEDIDO VINCULADO</div>
                                    <div class="retro-project-name"><?= esc($r['pedido_titulo']) ?></div>
                                </div>
                                
                                <div class="retro-message-box" title="<?= esc($r['contenido']) ?>">
                                    <?= nl2br(esc($r['contenido'])) ?>
                                </div>
                                
                                <div class="retro-actions-mini">
                                    <button class="btn-retro-action" onclick="window.location.href='<?= base_url('empleado/mis_pedidos?highlight=' . $r['id_atencion']) ?>'">
                                        ATENDER AHORA <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<div style="height: 50px;"></div>

<?= $this->endSection() ?>
