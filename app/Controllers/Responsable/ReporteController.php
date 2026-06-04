<?php

namespace App\Controllers\Responsable;

use App\Controllers\Responsable\BaseResponsableController;
use App\Models\AtencionModel;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;

class ReporteController extends BaseResponsableController
{
    /**
     * Genera el Reporte de Gestión en PDF para el Responsable de Área.
     * Captura filtros opcionales de fecha, empresa y técnico.
     * @throws \RuntimeException
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function generarReporte()
    {
        // Validacion Credenciales
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to('auth/login');
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $atencionModel = new AtencionModel();

        // Capturar Filtros del Request (GET o POST)
        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        
        $filtros = [
            'idempresa'          => $this->request->getGet('idempresa') ?: null,
            'idempleado'         => $this->request->getGet('idempleado') ?: null,
            'idservicio'         => $this->request->getGet('idservicio') ?: null,
            'solo_completados'   => $this->request->getGet('solo_completados') ?: null,
            'solo_retrasos'      => $this->request->getGet('solo_retrasos') ?: null,
            'incluir_cancelados' => $this->request->getGet('incluir_cancelados') ?: null,
        ];

        // Obtener los Datos del Modelo
        $dataDetallada = $atencionModel->obtenerReporteDetallado($idAreaAgencia, $desde, $hasta, $filtros);
        $dataMetricas  = $atencionModel->obtenerMetricasTecnicosReporte($idAreaAgencia, $desde, $hasta);
        $dataAlertas   = $atencionModel->obtenerAlertasReporte($idAreaAgencia, $desde, $hasta);

        // Calcular Resumen
        $resumen = [
            'total'        => count($dataDetallada),
            'completados'  => count(array_filter($dataDetallada, fn($i) => $i['estado'] === 'finalizado')),
            'en_proceso'   => count(array_filter($dataDetallada, fn($i) => $i['estado'] === 'en_proceso')),
            'en_revision'  => count(array_filter($dataDetallada, fn($i) => $i['estado'] === 'en_revision')),
            'pendientes'   => count(array_filter($dataDetallada, fn($i) => in_array($i['estado'], ['pendiente_asignado', 'pendiente_sin_asignar']))),
            'hrs_promedio' => count($dataDetallada) > 0 ? array_sum(array_column($dataDetallada, 'horas_usadas')) / count($dataDetallada) : 0
        ];

        // Preparar la Vista HTML
        $html = view('responsable/reporte_area', [
            'titulo'    => 'REPORTE DE GESTIÓN OPERATIVA',
            'area'      => $userS['userData']['nombre_areaagencia'] ?? 'Área no asignada',
            'jefe'      => trim($userS['user']['nombre'] . ' ' . $userS['user']['apellidos']),
            'periodo'   => ($desde && $hasta) ? "$desde al $hasta" : "Historial Completo",
            'generado'  => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('d/m/Y H:i'),
            'resumen'   => $resumen,
            'pedidos'   => $dataDetallada,
            'metricas'  => $dataMetricas,
            'alertas'   => $dataAlertas
        ]);

        // Generar el PDF con Html2Pdf
        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', [0, 0, 0, 0]);
            $html2pdf->setDefaultFont('Arial');
            // Renderizar el HTML
            $html2pdf->writeHTML($html);
            // Output: I = Vista previa en navegador
            $nombreArchivo = 'Gestion_Reporte_' . date('Ymd_His') . '.pdf';
            ob_end_clean(); 
            $html2pdf->output($nombreArchivo, 'I');
            exit();
        } catch (Html2PdfException $e) {
            $html2pdf->clean();
            throw new \RuntimeException("Error al generar el PDF: " . $e->getMessage());
        }
    }

    public function generarCSV()
    {
        // Validacion Credenciales
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to('auth/login');
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $atencionModel = new AtencionModel();

        // Capturar Filtros del Request (GET o POST)
        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        
        $filtros = [
            'idempresa'          => $this->request->getGet('idempresa') ?: null,
            'idempleado'         => $this->request->getGet('idempleado') ?: null,
            'idservicio'         => $this->request->getGet('idservicio') ?: null,
            'solo_completados'   => $this->request->getGet('solo_completados') ?: null,
            'solo_retrasos'      => $this->request->getGet('solo_retrasos') ?: null,
            'incluir_cancelados' => $this->request->getGet('incluir_cancelados') ?: null,
        ];

        // Obtener los Datos del Modelo con función específica para CSV
        $dataDetallada = $atencionModel->obtenerReporteCSV($idAreaAgencia, $desde, $hasta, $filtros);

        // Definir encabezados del CSV (SIN ID)
        $headers = [
            'Título',
            'Servicio',
            'Empresa',
            'Área Cliente',
            'Estado',
            'Prioridad',
            'Fecha Creación',
            'Fecha Inicio',
            'Fecha Límite',
            'Fecha Completado',
            'Horas Usadas',
            'Empleado Asignado',
            'Área Agencia',
            'Objetivo de Comunicación',
            'Descripción Detallada',
            'Público Objetivo',
            'Canales de Difusión',
            'Formatos Solicitados',
            'Formato Otros',
            'URL Referencia',
            'Tipo Requerimiento',
            'Link Entrega',
            'Archivos Cliente',
            'Archivos Entrega'
        ];

        // Crear archivo CSV en memoria
        $nombreArea = $userS['userData']['nombre_areaagencia'] ?? 'Area';
        $filename = 'Reporte_' . $nombreArea . '_' . date('Ymd_His') . '.csv';
        
        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        // Abrir buffer de salida
        ob_start();
        
        $output = fopen('php://output', 'w');
        
        // Agregar BOM para que Excel reconozca caracteres especiales (UTF-8)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Escribir encabezados con tabuladores para mejor espaciado
        fputcsv($output, $headers, "\t");
        
        // Escribir datos
        foreach ($dataDetallada as $pedido) {
            $row = [
                $this->limpiarCSV($pedido['titulo'] ?? ''),
                $this->limpiarCSV($pedido['servicio_nombre'] ?? ''),
                $this->limpiarCSV($pedido['empresa_nombre'] ?? ''),
                $this->limpiarCSV($pedido['area_nombre'] ?? ''),
                $pedido['estado'] ?? '',
                $pedido['prioridad'] ?? '',
                $this->formatearFecha($pedido['fechacreacion'] ?? ''),
                $this->formatearFecha($pedido['fechainicio'] ?? ''),
                $this->formatearFecha($pedido['fecharequerida'] ?? ''),
                $this->formatearFecha($pedido['fechacompletado'] ?? ''),
                $this->formatearHoras($pedido['horas_usadas'] ?? 0),
                $this->limpiarCSV(($pedido['empleado_nombre'] ?? '') . ' ' . ($pedido['empleado_apellidos'] ?? '')),
                $this->limpiarCSV($pedido['area_agencia_nombre'] ?? ''),
                $this->limpiarCSV($pedido['objetivo_comunicacion'] ?? ''),
                $this->limpiarCSV($pedido['descripcion'] ?? ''),
                $this->limpiarCSV($pedido['publico_objetivo'] ?? ''),
                $this->limpiarCSV($pedido['canales_difusion'] ?? ''),
                $this->limpiarCSV($pedido['formatos_solicitados'] ?? ''),
                $this->limpiarCSV($pedido['formato_otros'] ?? ''),
                $this->limpiarCSV($pedido['url_subida'] ?? ''),
                $this->limpiarCSV($pedido['tipo_requerimiento'] ?? ''),
                $this->limpiarCSV($pedido['url_entrega'] ?? ''),
                $this->limpiarCSV($pedido['archivos_cliente'] ?? ''),
                $this->limpiarCSV($pedido['archivos_entrega'] ?? '')
            ];
            fputcsv($output, $row, "\t");
        }
        
        fclose($output);
        
        $csvContent = ob_get_clean();
        
        return $this->response->setBody($csvContent);
    }

    private function limpiarCSV($valor)
    {
        // Eliminar saltos de línea y caracteres problemáticos para CSV
        return preg_replace('/[\r\n\t]/', ' ', $valor);
    }

    private function formatearFecha($fecha)
    {
        if (empty($fecha) || $fecha === '0000-00-00 00:00:00') {
            return '';
        }
        try {
            $date = new \DateTime($fecha);
            return $date->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return $fecha;
        }
    }

    private function formatearHoras($horas)
    {
        if (empty($horas) || $horas == 0) {
            return '0.00';
        }
        return number_format((float)$horas, 2, '.', '');
    }
}
