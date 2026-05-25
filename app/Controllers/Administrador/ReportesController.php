<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;

class ReportesController extends BaseController
{
    public function index()
    {
        $empresaModel = new EmpresaModel();
        $areaAgenciaModel = new AreasAgenciaModel();
        $usuarioModel = new UsuarioModel();

        return view('admin/reportes', [
            'titulo'        => 'Reportes de Gestión',
            'tituloPagina'  => 'CENTRO DE REPORTES Y ESTADÍSTICAS',
            'paginaActual'  => 'reportes',
            'empresas'      => $empresaModel->where('estado', true)->orderBy('nombreempresa', 'ASC')->findAll(),
            'areasAgencia'  => $areaAgenciaModel->findAll(),
            'empleados'     => $usuarioModel->where('rol', 'empleado')->where('estado', true)->findAll()
        ]);
    }

    public function generarReporte()
    {
        $atencionModel = new AtencionModel();

        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        $idAreaInt = $this->request->getGet('idarea_int') ?: null;
        $idEmpresa = $this->request->getGet('idempresa') ?: null;
        $idEmpleado = $this->request->getGet('idempleado') ?: null;
        $soloCompletados = $this->request->getGet('solo_completados') == '1';

        $filtros = [
            'idarea_int'       => $idAreaInt,
            'idempresa'        => $idEmpresa,
            'idempleado'       => $idEmpleado,
            'solo_completados' => $soloCompletados,
        ];

        // 1. Detalle de Pedidos
        $pedidos = $atencionModel->obtenerReporteDetalladoAdmin($desde, $hasta, $filtros);

        // Metricas de Técnicos
        $metricas = $atencionModel->obtenerMetricasTecnicosAdmin($idAreaInt ? (int)$idAreaInt : null);

        $resumen = [
            'total'        => count($pedidos),
            'completados'  => count(array_filter($pedidos, fn($i) => $i['estado'] === 'finalizado')),
            'en_proceso'   => count(array_filter($pedidos, fn($i) => $i['estado'] === 'en_proceso')),
            'en_revision'  => count(array_filter($pedidos, fn($i) => $i['estado'] === 'en_revision')),
            'pendientes'   => count(array_filter($pedidos, fn($i) => in_array($i['estado'], ['pendiente_asignado', 'pendiente_sin_asignar']))),
            'hrs_promedio' => count($pedidos) > 0 ? array_sum(array_column($pedidos, 'horas_usadas')) / count($pedidos) : 0
        ];

        $html = view('admin/reporte_empresas_pdf', [
            'titulo'    => 'REPORTE DE GESTIÓN ADMINISTRATIVA',
            'area'      => ($idAreaInt) ? "ÁREA SELECCIONADA" : "TODAS LAS ÁREAS",
            'jefe'      => 'ADMINISTRADOR',
            'periodo'   => ($desde && $hasta) ? "$desde al $hasta" : "Historial Completo",
            'generado'  => date('d/m/Y H:i'),
            'resumen'   => $resumen,
            'pedidos'   => $pedidos,
            'metricas'  => $metricas
        ]);

        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', [0, 0, 0, 0]);
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($html);
            if (ob_get_length()) ob_end_clean();
            $html2pdf->output('Reporte_Admin.pdf', 'I');
            exit();
        } catch (Html2PdfException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function obtenerVistaPrevia()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $atencionModel = new AtencionModel();

        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        $idAreaInt = (int)($this->request->getGet('idarea_int') ?: 0);
        $idEmpresa = $this->request->getGet('idempresa') ?: null;

        $filtros = [
            'idarea_int' => $idAreaInt,
            'idempresa'  => $idEmpresa,
        ];

        $data = $atencionModel->obtenerVistaPreviaAdmin($desde, $hasta, $filtros);

        $resumen = [
            'total'         => count($data),
            'completados'   => count(array_filter($data, fn($i) => $i['estado'] === 'finalizado')),
            'en_proceso'    => count(array_filter($data, fn($i) => $i['estado'] === 'en_proceso')),
            'en_revision'   => count(array_filter($data, fn($i) => $i['estado'] === 'en_revision')),
            'pendientes'    => count(array_filter($data, fn($i) => in_array($i['estado'], ['pendiente_asignado', 'pendiente_sin_asignar']))),
            'hrs_totales'   => array_sum(array_column($data, 'horas'))
        ];

        return $this->response->setJSON($resumen);
    }
}
