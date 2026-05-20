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
            'total'       => count($dataDetallada),
            'completados' => count(array_filter($dataDetallada, fn($i) => $i['estado'] === 'finalizado')),
            'en_proceso'  => count(array_filter($dataDetallada, fn($i) => $i['estado'] === 'en_proceso')),
            // Solo contar pendientes que YA FUERON DELEGADOS (idempleado > 0)
            'pendientes'  => count(array_filter($dataDetallada, fn($i) => in_array($i['estado'], ['pendiente_asignado', 'pendiente_sin_asignar']) && (isset($i['idempleado']) && $i['idempleado'] > 0))),
            'hrs_promedio' => count($dataDetallada) > 0 ? array_sum(array_column($dataDetallada, 'horas_usadas')) / count($dataDetallada) : 0
        ];

        // Preparar la Vista HTML
        $html = view('Responsable/reporte_area', [
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
}
