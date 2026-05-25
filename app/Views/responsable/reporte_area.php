<page backtop="10mm" backbottom="15mm" backleft="10mm" backright="10mm">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; }

        /* ENCABEZADO */
        .doc-header { width: 100%; margin-bottom: 20px; border-bottom: 2pt solid #1a1a1a; padding-bottom: 8px; }
        .doc-header td { border: none; vertical-align: bottom; }
        .logo-text { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .report-name { text-align: right; font-size: 14px; font-weight: bold; text-transform: uppercase; color: #333; }

        /* INFO */
        .info-table { width: 100%; margin-bottom: 18px; }
        .info-table td { border: none; padding: 3px 0; font-size: 14px; }

        /* TABLAS */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; table-layout: fixed; }
        th { background-color: #2c3e50; color: #fff; padding: 7px; text-align: center; font-size: 10px; border: 1pt solid #2c3e50; font-weight: bold; text-transform: uppercase; }
        td { border: 1pt solid #c0c0c0; padding: 7px; vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all; white-space: normal; }
        tr:nth-child(even) td { background-color: #f9f9f9; }

        .tabla-detalle th { font-size: 10.5px; padding: 6px 5px; }
        .tabla-detalle td { font-size: 13.5px; padding: 6px 5px; }

        /* SECCIÓN */
        .section-title { font-size: 12.5px; font-weight: bold; color: #2c3e50; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; border-bottom: 1pt solid #2c3e50; padding-bottom: 3px; }
        .empresa-label { font-weight: bold; font-size: 11.5px; margin-top: 12px; margin-bottom: 4px; color: #2c3e50; }

        .estado-fin   { font-size: 9.5px; font-weight: bold; color: #1a7a3c; }
        .estado-proc  { font-size: 9.5px; font-weight: bold; color: #856404; }
        .estado-pend  { font-size: 9.5px; font-weight: bold; color: #8B0000; }
        .estado-rev   { font-size: 9.5px; font-weight: bold; color: #d97706; }

        /* PIE_PAGINA */
        .page-footer { text-align: center; font-size: 13px; color: #777; border-top: 0.5pt solid #bbb; padding-top: 5px; padding-bottom: 25px; }

        /* ANCHOS COLUMNAS */
        .col-id    { width: 6%;  text-align: center; }
        .col-fecha { width: 11%; text-align: center; }
        .col-req   { width: 38%; }
        .col-emp   { width: 25%; }
        .col-est   { width: 13%; text-align: center; }
        .col-hrs   { width: 7%;  text-align: center; }
    </style>

    <page_footer>
        <div class="page-footer">
            RF Marketing | Reporte de Gestión Operativa | Hoja [[page_cu]] - [[page_nb]]
        </div>
    </page_footer>

    <?php
    // Formatea horas decimales a formato HH:MM
    $formatHrs = function ($decimal) {
        if ($decimal <= 0){ return '-'; }
        // Obtenemos la parte entera (las horas completas)
        $horas = (int) floor($decimal);
        // Multiplicamos la parte decimal por 60 para sacar los minutos
        $minutos = (int) round(($decimal - $horas) * 60);
        // Retornamos el formato concatenado asegurando 2 dígitos para los minutos (ej. :05)
        return $horas . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);
    };
    ?>

    <!-- ENCABEZADO -->
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
            <td style="width: 50%;"><strong>Área:</strong> <?= mb_strtoupper($area) ?></td>
            <td style="width: 50%; text-align: right;"><strong>Período:</strong> <?= mb_strtoupper($periodo) ?></td>
        </tr>
        <tr>
            <td><strong>Responsable:</strong> <?= mb_strtoupper($jefe) ?></td>
            <td style="text-align: right;"><strong>Emitido:</strong> <?= $generado ?></td>
        </tr>
    </table>

    <!-- RESUMEN -->
    <div class="section-title">I. Resumen Operativo del Área</div>
    <table>
        <colgroup>
            <col style="width: 16%;">
            <col style="width: 16%;">
            <col style="width: 17%;">
            <col style="width: 17%;">
            <col style="width: 16%;">
            <col style="width: 18%;">
        </colgroup>
        <thead>
            <tr>
                <th style="width: 16%;">TOTAL</th>
                <th style="width: 16%;">PENDIENTES</th>
                <th style="width: 17%;">EN PROCESO</th>
                <th style="width: 17%;">EN REVISIÓN</th>
                <th style="width: 16%;">COMPLETADOS</th>
                <th style="width: 18%;">HRS. PROM.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-size: 14px; font-weight: bold;"><?= $resumen['total'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #8B0000;">
                    <?= $resumen['pendientes'] ?>
                </td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #856404;">
                    <?= $resumen['en_proceso'] ?>
                </td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #d97706;">
                    <?= $resumen['en_revision'] ?>
                </td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #1a7a3c;">
                    <?= $resumen['completados'] ?>
                </td>
                <td style="text-align: center; font-size: 14px; font-weight: bold;">
                    <?= $formatHrs(floatval($resumen['hrs_promedio'])) ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- DETALLE -->
    <div class="section-title">II. Relación Detallada de Atenciones</div>
    <?php
    $empresaActual = '';
    foreach ($pedidos as $p):
        if ($empresaActual != $p['empresa_nombre']):
            if ($empresaActual != '')
                echo '</tbody></table>';
            $empresaActual = $p['empresa_nombre'];
            ?>
            <div class="empresa-label">CLIENTE: <?= mb_strtoupper($empresaActual) ?></div>
            <table class="tabla-detalle">
                <colgroup>
                    <col style="width: 6%;">
                    <col style="width: 11%;">
                    <col style="width: 38%;">
                    <col style="width: 25%;">
                    <col style="width: 13%;">
                    <col style="width: 7%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-fecha">FECHA</th>
                        <th class="col-req">REQUERIMIENTO</th>
                        <th class="col-emp">EMPLEADO</th>
                        <th class="col-est">ESTADO</th>
                        <th class="col-hrs">HRS.</th>
                    </tr>
                </thead>
                <tbody>
                <?php endif; ?>
                <tr>
                    <td class="col-id" style="color: #555;">#<?= $p['id'] ?></td>
                    <td class="col-fecha">
                        <?= date('d/m/y', strtotime($p['fechacreacion'])) ?><br>
                        <span
                            style="font-size: 14px; color: #b6b6b6ff"><?= date('H:i', strtotime($p['fechacreacion'])) ?></span>
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
                        <span class="<?= $cls ?>"
                            style="font-size: 13px"><?= mb_strtoupper(str_replace('_', ' ', $est)) ?></span>
                    </td>
                    <td class="col-hrs"><?= $formatHrs(floatval($p['horas_usadas'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($empresaActual != '')
                echo '</tbody></table>'; ?>
</page>

<page backtop="20mm" backbottom="20mm" backleft="15mm" backright="15mm">
    <page_footer>
        <div class="page-footer">
            RF Marketing &nbsp;|&nbsp; Reporte de Gestión Operativa &nbsp;|&nbsp; Confidencial &mdash; Hoja
            [[page_cu]]/[[page_nb]]
        </div>
    </page_footer>

    <!-- DESEMPEÑO -->
    <div class="section-title">III. Desempeño Individual del Equipo</div>
    <table>
        <colgroup>
            <col style="width: 40%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
        </colgroup>
        <thead>
            <tr>
                <th style="width: 40%; text-align: left;">EMPLEADO</th>
                <th style="width: 15%;">ASIG.</th>
                <th style="width: 15%;">COMP.</th>
                <th style="width: 15%;">EFICIENCIA</th>
                <th style="width: 15%;">HRS. TOT.</th>
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

    <!-- ALERTAS -->
    <?php if (!empty($alertas)): ?>
        <div class="section-title">IV. Alertas y Casos Críticos</div>
        <table class="tabla-detalle">
            <colgroup>
                <col style="width: 7%;">
                <col style="width: 25%;">
                <col style="width: 38%;">
                <col style="width: 15%;">
                <col style="width: 15%;">
            </colgroup>
            <thead>
                <tr>
                    <th style="text-align: center;">ID</th>
                    <th style="text-align: left;">CLIENTE</th>
                    <th style="text-align: left;">MOTIVO</th>
                    <th style="text-align: center;">F. EMISIÓN</th>
                    <th style="text-align: center;">F. LÍMITE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alertas as $a): ?>
                    <tr>
                        <td style="text-align: center; color: #555;">#<?= $a['id'] ?></td>
                        <td><?= mb_strtoupper($a['empresa']) ?></td>
                        <td style="color: #8B0000; font-weight: bold;">** <?= mb_strtoupper($a['motivo_alerta']) ?></td>
                        <td style="text-align: center;">
                            <?= date('d/m/y', strtotime($a['fechacreacion'])) ?>
                        </td>
                        <td style="text-align: center;">
                            <?= $a['fecharequerida'] ? date('d/m/y', strtotime($a['fecharequerida'])) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>  
</page>