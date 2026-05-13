<page backtop="10mm" backbottom="15mm" backleft="10mm" backright="10mm">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .doc-header { width: 100%; margin-bottom: 20px; border-bottom: 2pt solid #1a1a1a; padding-bottom: 8px; }
        .doc-header td { border: none; vertical-align: bottom; }
        .report-name { text-align: right; font-size: 14px; font-weight: bold; text-transform: uppercase; color: #333; }
        .info-table { width: 100%; margin-bottom: 18px; }
        .info-table td { border: none; padding: 3px 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; table-layout: fixed; }
        th { background-color: #2c3e50; color: #fff; padding: 7px; text-align: center; font-size: 9px; border: 1pt solid #2c3e50; font-weight: bold; text-transform: uppercase; }
        td { border: 1pt solid #c0c0c0; padding: 6px; vertical-align: middle; word-wrap: break-word; }
        tr:nth-child(even) td { background-color: #f9f9f9; }
        .section-title { font-size: 12px; font-weight: bold; color: #fff; background-color: #2c3e50; padding: 5px 10px; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; }
        .area-header { background-color: #ecf0f1; padding: 8px; font-weight: bold; font-size: 12px; border: 1pt solid #bdc3c7; color: #2c3e50; margin-top: 15px; margin-bottom: 5px; }
        .empresa-label { font-weight: bold; font-size: 10px; margin-top: 10px; margin-bottom: 4px; color: #2980b9; padding-left: 5px; border-left: 3pt solid #2980b9; }
        .estado-fin   { font-size: 9px; font-weight: bold; color: #1a7a3c; }
        .estado-proc  { font-size: 9px; font-weight: bold; color: #856404; }
        .estado-pend  { font-size: 9px; font-weight: bold; color: #8B0000; }
        .page-footer { text-align: center; font-size: 11px; color: #777; border-top: 0.5pt solid #bbb; padding-top: 5px; padding-bottom: 25px; }
        .col-id    { width: 6%;  text-align: center; }
        .col-fecha { width: 12%; text-align: center; }
        .col-req   { width: 40%; }
        .col-emp   { width: 23%; }
        .col-est   { width: 12%; text-align: center; }
        .col-hrs   { width: 7%;  text-align: center; }
    </style>

    <page_footer>
        <div class="page-footer">
            RF Marketing | Reporte de Gestión Administrativa | Hoja [[page_cu]] - [[page_nb]]
        </div>
    </page_footer>

    <?php
    $formatHrs = function ($decimal) {
        if ($decimal <= 0){ return '-'; }
        $horas = (int) floor($decimal);
        $minutos = (int) round(($decimal - $horas) * 60);
        return $horas . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);
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
                <th style="width: 25%;">TOTAL PEDIDOS</th>
                <th style="width: 25%;">COMPLETADOS</th>
                <th style="width: 25%;">EN PROCESO</th>
                <th style="width: 25%;">HRS. PROM. POR TAREA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-size: 14px; font-weight: bold;"><?= $resumen['total'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #1a7a3c;"><?= $resumen['completados'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold; color: #856404;"><?= $resumen['en_proceso'] ?></td>
                <td style="text-align: center; font-size: 14px; font-weight: bold;"><?= $formatHrs(floatval($resumen['hrs_promedio'])) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">II. Relación Detallada de Atenciones (Por Área y Cliente)</div>
    <?php
    $areaActual = '';
    $empresaActual = '';
    foreach ($pedidos as $p):
        if ($areaActual != $p['area_agencia_nombre']):
            if ($areaActual != '') echo '</tbody></table>';
            $areaActual = $p['area_agencia_nombre'];
            $empresaActual = ''; // Reiniciar empresa al cambiar área
            ?>
            <div class="area-header">ÁREA: <?= mb_strtoupper($areaActual) ?></div>
        <?php endif; ?>

        <?php
        if ($empresaActual != $p['empresa_nombre']):
            if ($empresaActual != '') echo '</tbody></table>';
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
                        <th class="col-hrs">HRS.</th>
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
                    <td class="col-emp"><?= $p['empleado_nombre'] ? mb_strtoupper($p['empleado_nombre'] . ' ' . $p['empleado_apellidos']) : '<em>Sin asignar</em>' ?></td>
                    <td class="col-est">
                        <?php
                        $est = $p['estado'];
                        $cls = str_starts_with($est, 'finalizado') ? 'estado-fin' : (str_contains($est, 'proceso') ? 'estado-proc' : 'estado-pend');
                        ?>
                        <span class="<?= $cls ?>"><?= mb_strtoupper(str_replace('_', ' ', $est)) ?></span>
                    </td>
                    <td class="col-hrs"><?= $formatHrs(floatval($p['horas_usadas'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($areaActual != '') echo '</tbody></table>'; ?>
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
</page>
