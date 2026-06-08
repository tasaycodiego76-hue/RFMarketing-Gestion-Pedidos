<page backtop="10mm" backbottom="15mm" backleft="10mm" backright="10mm">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        .doc-header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2pt solid #1a1a1a;
            padding-bottom: 8px;
        }

        .doc-header td {
            border: none;
            vertical-align: bottom;
        }

        .report-name {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        .info-table {
            width: 100%;
            margin-bottom: 18px;
        }

        .info-table td {
            border: none;
            padding: 3px 0;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            table-layout: fixed;
        }

        th {
            background-color: #2c3e50;
            color: #fff;
            padding: 7px;
            text-align: center;
            font-size: 9px;
            border: 1pt solid #2c3e50;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            border: 1pt solid #c0c0c0;
            padding: 6px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background-color: #2c3e50;
            padding: 5px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .area-header {
            font-weight: bold;
            font-size: 10px;
            margin-top: 10px;
            margin-bottom: 4px;
            color: #fff;
            background-color: #2980b9;
            padding: 5px 10px;
            text-transform: uppercase;
        }

        .empresa-label {
            font-weight: bold;
            font-size: 10px;
            margin-top: 10px;
            margin-bottom: 4px;
            color: #2980b9;
            padding-left: 5px;
            border-left: 3pt solid #2980b9;
        }

        .estado-fin {
            font-size: 9px;
            font-weight: bold;
            color: #1a7a3c;
        }

        .estado-proc {
            font-size: 9px;
            font-weight: bold;
            color: #856404;
        }

        .estado-pend {
            font-size: 9px;
            font-weight: bold;
            color: #8B0000;
        }

        .estado-rev {
            font-size: 9px;
            font-weight: bold;
            color: #d97706;
        }

        .page-footer {
            text-align: center;
            font-size: 11px;
            color: #777;
            border-top: 0.5pt solid #bbb;
            padding-top: 5px;
            padding-bottom: 25px;
        }

        .col-id {
            width: 6%;
            text-align: center;
        }

        .col-fecha {
            width: 12%;
            text-align: center;
        }

        .col-req {
            width: 37%;
        }

        .col-emp {
            width: 22%;
        }

        .col-est {
            width: 13%;
            text-align: center;
        }

        .col-hrs {
            width: 10%;
            text-align: center;
        }
    </style>

    <page_footer>
        <div class="page-footer">
            RF Marketing | Reporte de Gestión Administrativa | Hoja [[page_cu]] - [[page_nb]]
        </div>
    </page_footer>

    <?php
    $formatHrs = function ($decimal) {
        if ($decimal <= 0) {
            return '-';
        }
        $totalMinutos = (int) round($decimal * 60);
        $horas   = (int) floor($totalMinutos / 60);
        $minutos = $totalMinutos % 60;
        if ($horas > 0 && $minutos > 0) return $horas . 'h ' . $minutos . 'm';
        if ($horas > 0) return $horas . 'h';
        return $minutos . 'm';
    };
    ?>

    <table class="doc-header">
        <tr>
            <td style="width: 55%; border:none;">
                <img src="<?= FCPATH . 'img/logo.png' ?>" alt="Logo" style="height: 55px; margin-bottom: 8px;">
            </td>
            <td style="width: 45%; border:none; text-align: right;" class="report-name"><?= $titulo ?></td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%;"><strong>Filtro Área:</strong> <?= mb_strtoupper($area) ?></td>
            <td style="width: 50%; text-align: right;"><strong>Período:</strong> <?= mb_strtoupper($periodo) ?></td>
        </tr>
        <tr>
            <td><strong>Responsable:</strong> <?= mb_strtoupper($jefe) ?></td>
            <td style="text-align: right;"><strong>Emitido:</strong> <?= $generado ?></td>
        </tr>
    </table>

    <div class="section-title">I. Resumen Operativo</div>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 16%;">TOTAL PEDIDOS</th>
                <th style="width: 16%;">PENDIENTES</th>
                <th style="width: 17%;">EN PROCESO</th>
                <th style="width: 17%;">EN REVISIÓN</th>
                <th style="width: 16%;">COMPLETADOS</th>
                <th style="width: 18%;">TIEMPO PROM.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-size: 14px; font-weight: bold;"><?= $resumen['total'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #8B0000;">
                    <?= $resumen['pendientes'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #856404;">
                    <?= $resumen['en_proceso'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #d97706;">
                    <?= $resumen['en_revision'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #1a7a3c;">
                    <?= $resumen['completados'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold;">
                    <?= $formatHrs(floatval($resumen['hrs_promedio'])) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">II. Relación Detallada de Atenciones (Por Área y Cliente)</div>
    <?php
    $areaActual = '';
    $empresaActual = '';
    foreach ($pedidos as $p):
        if ($areaActual != $p['area_agencia_nombre']):
            if ($areaActual != '')
                echo '</tbody></table>';
            $areaActual = $p['area_agencia_nombre'];
            $empresaActual = ''; // Reiniciar empresa al cambiar área
            ?>
            <div class="area-header">ÁREA: <?= mb_strtoupper($areaActual) ?></div>
        <?php endif; ?>

        <?php
        if ($empresaActual != $p['empresa_nombre']):
            if ($empresaActual != '')
                echo '</tbody></table>';
            $empresaActual = $p['empresa_nombre'];
            ?>
            <div class="empresa-label">CLIENTE: <?= mb_strtoupper($empresaActual) ?></div>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-fecha">FECHA</th>
                        <th class="col-req">REQUERIMIENTO</th>
                        <th class="col-emp">EMPLEADO</th>
                        <th class="col-est">ESTADO</th>
                        <th class="col-hrs">TIEMPO</th>
                    </tr>
                </thead>
                <tbody>
                <?php endif; ?>
                <tr>
                    <td class="col-id" style="color: #555;">#<?= $p['id'] ?></td>
                    <td class="col-fecha">
                        <?= date('d/m/y', strtotime($p['fechacreacion'])) ?><br>
                        <span style="font-size: 9px; color: #888"><?= date('H:i', strtotime($p['fechacreacion'])) ?></span>
                    </td>
                    <td class="col-req"><strong><?= mb_strtoupper($p['titulo']) ?></strong></td>
                    <td class="col-emp">
                        <?= $p['empleado_nombre'] ? mb_strtoupper($p['empleado_nombre'] . ' ' . $p['empleado_apellidos']) : '<em>Sin asignar</em>' ?>
                    </td>
                    <td class="col-est">
                        <?php
                        $est = $p['estado'];
                        if (str_starts_with($est, 'finalizado')) {
                            $cls = 'estado-fin';
                        } elseif (str_starts_with($est, 'en_proceso')) {
                            $cls = 'estado-proc';
                        } elseif ($est === 'en_revision') {
                            $cls = 'estado-rev';
                        } else {
                            $cls = 'estado-pend';
                        }
                        ?>
                        <span class="<?= $cls ?>"><?= mb_strtoupper(str_replace('_', ' ', $est)) ?></span>
                    </td>
                    <td class="col-hrs"><?= $formatHrs(floatval($p['horas_usadas'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($areaActual != '')
                echo '</tbody></table>'; ?>
</page>

<page backtop="20mm" backbottom="20mm" backleft="15mm" backright="15mm">
    <page_footer>
        <div class="page-footer">RF Marketing | Reporte Administrativo | Hoja [[page_cu]]/[[page_nb]]</div>
    </page_footer>

    <div class="section-title">III. Desempeño Individual del Equipo Técnico</div>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 40%; text-align: left;">EMPLEADO</th>
                <th style="width: 15%;">ASIG.</th>
                <th style="width: 15%;">COMP.</th>
                <th style="width: 15%;">EFICIENCIA</th>
                <th style="width: 15%;">TIEMPO TOT.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($metricas as $m): ?>
                <tr>
                    <td><strong><?= mb_strtoupper($m['nombre'] . ' ' . $m['apellidos']) ?></strong></td>
                    <td style="text-align: center;"><?= $m['asignados'] ?></td>
                    <td style="text-align: center;"><?= $m['completados'] ?></td>
                    <td style="text-align: center; font-weight: bold;"><?= $m['eficiencia'] ?>%</td>
                    <td style="text-align: center;"><?= $formatHrs(floatval($m['horas_totales'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</page>
<?php
$formatDuracion = function ($segundos) {
    $segundos = max(0, (int) $segundos);
    $h = (int) floor($segundos / 3600);
    $m = (int) floor(($segundos % 3600) / 60);
    $s = $segundos % 60;
    if ($h > 0) return $h . 'h ' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';
    if ($m > 0) return $m . 'm ' . str_pad($s, 2, '0', STR_PAD_LEFT) . 's';
    return $s . 's';
};

$hayPausas = !empty($pausasPorPedido) && count($pausasPorPedido) > 0 && ($incluirPausasReasignaciones ?? true);
$hayReasignaciones = !empty($reasignacionesPorPedido) && count($reasignacionesPorPedido) > 0 && ($incluirPausasReasignaciones ?? true);
?>

<?php if ($hayPausas): ?>
<page backtop="20mm" backbottom="20mm" backleft="15mm" backright="15mm">
    <page_footer>
        <div class="page-footer">RF Marketing | Reporte Administrativo | Hoja [[page_cu]]/[[page_nb]]</div>
    </page_footer>

    <div class="section-title">IV. Registro de Pausas por Pedido</div>

    <?php
    $totalPausasGlobal   = 0;
    $totalSegundosGlobal = 0;
    ?>

    <?php foreach ($pausasPorPedido as $idPedido => $pausas):
        $tituloPedido = '';
        $areaAgencia  = '';
        foreach ($pedidos as $px) {
            if ((int)$px['id'] === (int)$idPedido) {
                $tituloPedido = $px['titulo'];
                $areaAgencia  = $px['area_agencia_nombre'] ?? '';
                break;
            }
        }
        $totalPausasGlobal += count($pausas);
    ?>

    <div class="empresa-label">PEDIDO #<?= $idPedido ?> &#8212; <?= mb_strtoupper($tituloPedido) ?></div>
    <div class="area-header">AREA: <?= mb_strtoupper($areaAgencia) ?></div>
    <table style="width: 100%; table-layout: fixed;">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">INICIO PAUSA</th>
                <th style="width: 22%;">MOTIVO</th>
                <th style="width: 18%;">FIN PAUSA</th>
                <th style="width: 13%;">DURACION</th>
                <th style="width: 24%;">REALIZADO POR</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalSegPedido = 0;
            foreach ($pausas as $idx => $pausa):
                $motivo      = !empty($pausa['motivo_pausa']) ? $pausa['motivo_pausa'] : 'Sin motivo registrado';
                $inicioPausa = !empty($pausa['hora_fin'])      ? date('d/m/Y H:i', strtotime($pausa['hora_fin']))      : '---';
                $finPausa    = !empty($pausa['hora_reinicio']) ? date('d/m/Y H:i', strtotime($pausa['hora_reinicio'])) : '---';
                $durSeg      = (int)($pausa['duracion_segundos'] ?? 0);
                $totalSegPedido      += $durSeg;
                $totalSegundosGlobal += $durSeg;
                // Campos correctos según getAllPausas(): usuario_nombre + usuario_apellidos
                $realizadoPor = mb_strtoupper(trim(
                    ($pausa['usuario_nombre'] ?? '') . ' ' . ($pausa['usuario_apellidos'] ?? '')
                )) ?: 'Sin registrar';
            ?>
                <tr>
                    <td style="text-align: center; color: #555; font-size: 9px;"><?= $idx + 1 ?></td>
                    <td style="text-align: center; font-size: 9px;"><?= $inicioPausa ?></td>
                    <td style="font-size: 9px; word-wrap: break-word; word-break: break-word; white-space: normal;"><?= htmlspecialchars($motivo) ?></td>
                    <td style="text-align: center; font-size: 9px;"><?= $finPausa ?></td>
                    <td style="text-align: center; font-size: 9px; font-weight: bold;"><?= $formatDuracion($durSeg) ?></td>
                    <td style="font-size: 9px; word-wrap: break-word; word-break: break-word; white-space: normal;"><?= htmlspecialchars($realizadoPor) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold; font-size: 10px; border: none;">
                    Subtotal (<?= count($pausas) ?> pausa<?= count($pausas) !== 1 ? 's' : '' ?>):
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 11px; background: #ecf0f1; border: 1pt solid #bdc3c7;">
                    <?= $formatDuracion($totalSegPedido) ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php endforeach; ?>

    <div style="margin-top: 15px; padding: 10px; background-color: #2c3e50; color: #fff; font-size: 11px;">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="border: none; color: #fff; font-weight: bold; width: 50%;">
                    TOTAL GENERAL DE PAUSAS: <?= $totalPausasGlobal ?>
                </td>
                <td style="border: none; color: #fff; font-weight: bold; width: 50%; text-align: right;">
                    TIEMPO TOTAL EN PAUSA: <?= $formatDuracion($totalSegundosGlobal) ?>
                </td>
            </tr>
        </table>
    </div>
</page>
<?php endif; ?>

<?php if ($hayReasignaciones): ?>
<page backtop="20mm" backbottom="20mm" backleft="15mm" backright="15mm">
    <page_footer>
        <div class="page-footer">RF Marketing | Reporte Administrativo | Hoja [[page_cu]]/[[page_nb]]</div>
    </page_footer>

    <div class="section-title"><?= $hayPausas ? 'V' : 'IV' ?>. Historial de Reasignaciones</div>

    <?php foreach ($reasignacionesPorPedido as $idPedido => $reasigs):
        $tituloPedido = '';
        foreach ($pedidos as $px) {
            if ((int)$px['id'] === (int)$idPedido) {
                $tituloPedido = $px['titulo'];
                break;
            }
        }
    ?>

    <div class="empresa-label">PEDIDO #<?= $idPedido ?> &#8212; <?= mb_strtoupper($tituloPedido) ?></div>
    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 26%;">DE</th>
                <th style="width: 26%;">A</th>
                <th style="width: 16%;">FECHA</th>
                <th style="width: 28%;">MOTIVO / POR</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reasigs as $idx => $r):
                $anterior    = mb_strtoupper(trim(($r['nombre_anterior']    ?? '') . ' ' . ($r['apellidos_anterior']    ?? ''))) ?: '---';
                $nuevo       = mb_strtoupper(trim(($r['nombre_nuevo']       ?? '') . ' ' . ($r['apellidos_nuevo']       ?? ''))) ?: '---';
                $responsable = mb_strtoupper(trim(($r['nombre_responsable'] ?? '') . ' ' . ($r['apellidos_responsable'] ?? ''))) ?: '---';
                $fecha       = !empty($r['fecha_asignacion']) ? date('d/m/Y H:i', strtotime($r['fecha_asignacion'])) : '---';
                $motivo      = !empty($r['motivo_cambio']) ? $r['motivo_cambio'] : '---';
            ?>
                <tr>
                    <td style="text-align: center; color: #555; font-size: 8px;"><?= $idx + 1 ?></td>
                    <td style="font-size: 8px; color: #666; word-wrap: break-word; word-break: break-word; white-space: normal;">
                        <?= htmlspecialchars($anterior) ?>
                    </td>
                    <td style="font-size: 8px; font-weight: bold; color: #1a1a1a; word-wrap: break-word; word-break: break-word; white-space: normal;">
                        <?= htmlspecialchars($nuevo) ?>
                    </td>
                    <td style="text-align: center; font-size: 8px;"><?= $fecha ?></td>
                    <td style="font-size: 8px; color: #444; word-wrap: break-word; word-break: break-word; white-space: normal;">
                        <?= htmlspecialchars($motivo) ?><br>
                        <span style="color: #2c3e50; font-weight: 600;"><?= htmlspecialchars($responsable) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>
</page>
<?php endif; ?>