<?= $this->extend('plantillas/empleado') ?>

<?= $this->section('contenido') ?>

<p class="seccion-titulo">Retroalimentación del Administrador</p>

<div id="contenedor-retro" style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <?php if(empty($retroalimentacion)): ?>
        <div class="text-center py-5" style="background: rgba(0,0,0,.1); border: 1px dashed var(--borde); border-radius: 20px; margin-top: 20px;">
            <i class="bi bi-chat-dots-fill" style="font-size: 50px; color: var(--amarillo); opacity: 0.2;"></i>
            <p class="mt-3" style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 3px; font-weight: 700;">Sin observaciones por el momento</p>
        </div>
    <?php else: ?>
        <div class="row mt-3 justify-content-center">
            <?php foreach($retroalimentacion as $r): ?>
                <div class="col-12 mb-4">
                    <div class="emp-task-card" style="border: 1px solid #222; background: #111; padding: 0; overflow: hidden; border-radius: 14px;">
                        
                        <!-- Header de la tarjeta -->
                        <div style="background: #1a1a1a; padding: 15px 25px; border-bottom: 1px solid #222; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; background: var(--amarillo); color: #000; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px;">
                                    <?= mb_substr($r['evaluador_nombre'], 0, 1) ?>
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 700; color: #fff; letter-spacing: 0.5px;">ADM. <?= esc(strtoupper($r['evaluador_nombre'] . ' ' . $r['evaluador_apellidos'])) ?></div>
                                    <div style="font-size: 10px; color: #666; text-transform: uppercase; font-weight: 600;">Evaluador de Calidad</div>
                                </div>
                            </div>
                            <span style="background: #ef4444; color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 10px; font-weight: 900; letter-spacing: 1px;">CORRECCIÓN URGENTE</span>
                        </div>

                        <!-- Cuerpo de la tarjeta -->
                        <div style="padding: 25px;">
                            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px;">
                                <!-- Mensaje Principal -->
                                <div>
                                    <div style="font-size: 11px; color: #ef4444; font-weight: 900; letter-spacing: 1.5px; margin-bottom: 12px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
                                        <i class="bi bi-chat-square-text-fill"></i> Detalle de la Retroalimentación
                                    </div>
                                    <div style="background: #0a0a0a; border: 1px solid #1a1a1a; border-radius: 12px; padding: 20px; font-size: 15px; color: #eee; line-height: 1.8; font-weight: 400; min-height: 120px;">
                                        <?= nl2br(esc($r['contenido'])) ?>
                                    </div>
                                </div>

                                <!-- Metadata Lateral -->
                                <div style="background: #161616; border-radius: 12px; padding: 20px; border: 1px solid #222;">
                                    <div style="margin-bottom: 20px;">
                                        <div style="font-size: 9px; color: #555; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Vinculado al Pedido:</div>
                                        <div style="font-size: 13px; font-weight: 700; color: var(--amarillo); line-height: 1.4;"><?= esc($r['pedido_titulo']) ?></div>
                                    </div>
                                    <div style="margin-bottom: 25px;">
                                        <div style="font-size: 9px; color: #555; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Fecha de Recepción:</div>
                                        <div style="font-size: 13px; font-weight: 600; color: #888;"><i class="bi bi-calendar3 mr-2"></i><?= date('d M, Y', strtotime($r['fecha'])) ?></div>
                                    </div>

                                    <button class="btn-yellow" style="width: 100%; justify-content: center; height: 42px; font-size: 14px;" onclick="window.location.href='<?= base_url('empleado/mis_pedidos') ?>'">
                                        ATENDER AHORA <i class="bi bi-arrow-right-short ml-1" style="font-size: 18px;"></i>
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
<div style="height: 50px;"></div> <!-- Espacio extra al final para evitar cortes -->

<?= $this->endSection() ?>
