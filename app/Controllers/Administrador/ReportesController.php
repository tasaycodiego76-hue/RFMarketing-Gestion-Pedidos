<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\SesionesTrabajosModel;
use App\Models\HistorialAsignacionesModel;
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
            'titulo' => 'Reportes de Gestión',
            'tituloPagina' => 'CENTRO DE REPORTES Y ESTADÍSTICAS',
            'paginaActual' => 'reportes',
            'empresas' => $empresaModel->where('estado', true)->orderBy('nombreempresa', 'ASC')->findAll(),
            'areasAgencia' => $areaAgenciaModel->findAll(),
            'empleados' => $usuarioModel->where('rol', 'empleado')->where('estado', true)->findAll()
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
            'idarea_int' => $idAreaInt,
            'idempresa' => $idEmpresa,
            'idempleado' => $idEmpleado,
            'solo_completados' => $soloCompletados,
        ];

        // 1. Detalle de Pedidos
        $pedidos = $atencionModel->obtenerReporteDetalladoAdmin($desde, $hasta, $filtros);

        // Metricas de Técnicos
        $metricas = $atencionModel->obtenerMetricasTecnicosAdmin($idAreaInt ? (int) $idAreaInt : null);

        // 2. Obtener pausas y reasignaciones por pedido
        $sesionesModel = new SesionesTrabajosModel();
        $historialModel = new HistorialAsignacionesModel();

        $pausasPorPedido = [];
        $reasignacionesPorPedido = [];
        foreach ($pedidos as $p) {
            $idAt = (int) $p['id'];
            $pausas = $sesionesModel->getAllPausas($idAt);
            if (!empty($pausas)) {
                $pausasPorPedido[$idAt] = $pausas;
            }
            $reasig = $historialModel->obtenerHistorialPorAtencion($idAt);
            if (!empty($reasig)) {
                $reasignacionesPorPedido[$idAt] = $reasig;
            }
        }

        $resumen = [
            'total' => count($pedidos),
            'completados' => count(array_filter($pedidos, fn($i) => in_array($i['estado'], ['en_revision', 'finalizado']))),
            'en_proceso' => count(array_filter($pedidos, fn($i) => $i['estado'] === 'en_proceso')),
            'en_revision' => count(array_filter($pedidos, fn($i) => $i['estado'] === 'en_revision')),
            'pendientes' => count(array_filter($pedidos, fn($i) => in_array($i['estado'], ['pendiente_asignado', 'pendiente_sin_asignar']))),
            'hrs_promedio' => (function () use ($pedidos) {
                $pedidosConHoras = array_filter($pedidos, fn($p) => floatval($p['horas_usadas'] ?? 0) > 0);
                return count($pedidosConHoras) > 0
                    ? array_sum(array_column($pedidosConHoras, 'horas_usadas')) / count($pedidosConHoras)
                    : 0;
            })()
        ];

        $html = view('admin/reporte_empresas_pdf', [
            'titulo' => 'REPORTE DE GESTIÓN ADMINISTRATIVA',
            'area' => ($idAreaInt) ? "ÁREA SELECCIONADA" : "TODAS LAS ÁREAS",
            'jefe' => 'ADMINISTRADOR',
            'periodo' => ($desde && $hasta) ? "$desde al $hasta" : "Historial Completo",
            'generado' => date('d/m/Y H:i'),
            'resumen' => $resumen,
            'pedidos' => $pedidos,
            'metricas' => $metricas,
            'pausasPorPedido' => $pausasPorPedido,
            'reasignacionesPorPedido' => $reasignacionesPorPedido,
        ]);

        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', [0, 0, 0, 0]);
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($html);
            if (ob_get_length())
                ob_end_clean();
            $html2pdf->output('Reporte_Admin.pdf', 'I');
            exit();
        } catch (Html2PdfException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function obtenerVistaPrevia()
    {
        if (!$this->request->isAJAX())
            return $this->response->setStatusCode(403);

        $atencionModel = new AtencionModel();

        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        $idAreaInt = (int) ($this->request->getGet('idarea_int') ?: 0);
        $idEmpresa = $this->request->getGet('idempresa') ?: null;

        $filtros = [
            'idarea_int' => $idAreaInt,
            'idempresa' => $idEmpresa,
        ];

        $data = $atencionModel->obtenerVistaPreviaAdmin($desde, $hasta, $filtros);

        $resumen = [
            'total' => count($data),
            'completados' => count(array_filter($data, fn($i) => in_array($i['estado'], ['en_revision', 'finalizado']))),
            'en_proceso' => count(array_filter($data, fn($i) => $i['estado'] === 'en_proceso')),
            'en_revision' => count(array_filter($data, fn($i) => $i['estado'] === 'en_revision')),
            'pendientes' => count(array_filter($data, fn($i) => in_array($i['estado'], ['pendiente_asignado', 'pendiente_sin_asignar']))),
            'hrs_totales' => array_sum(array_column($data, 'horas'))
        ];

        return $this->response->setJSON($resumen);
    }

    public function generarCSV()
    {
        $atencionModel = new AtencionModel();

        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        $idAreaInt = $this->request->getGet('idarea_int') ?: null;
        $idEmpresa = $this->request->getGet('idempresa') ?: null;
        $idEmpleado = $this->request->getGet('idempleado') ?: null;
        $soloCompletados = $this->request->getGet('solo_completados') == '1';

        $filtros = [
            'idarea_int' => $idAreaInt,
            'idempresa' => $idEmpresa,
            'idempleado' => $idEmpleado,
            'solo_completados' => $soloCompletados,
        ];

        // Obtener los Datos del Modelo con función específica para CSV
        $pedidos = $atencionModel->obtenerReporteCSVAdmin($desde, $hasta, $filtros);

        // Definir encabezados del CSV en el orden exacto solicitado.
        $headers = [
            'Empresa',
            'Área Cliente',
            'Usuario Solicitante',
            'Título',
            'Objetivo de Comunicación',
            'Público Objetivo',
            'Descripción Detallada',
            'Materiales Cliente',
            'URL Referencia',
            'Servicio',
            'Tipo Requerimiento',
            'Empleado Asignado',
            'Horas Usadas',
            'Fecha Inicio',
            'Fecha Límite',
            'Fecha Completado',
            'Estado',
            'Prioridad',
            'Archivos Entrega'
        ];

        // Crear archivo CSV en memoria
        $filename = 'Reporte_Admin_' . date('Ymd_His') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        // Abrir buffer de salida
        ob_start();

        $output = fopen('php://output', 'w');

        // Agregar BOM para que Excel reconozca caracteres especiales (UTF-8)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Escribir encabezados con tabuladores para mejor espaciado
        fputcsv($output, $headers, "\t");

        // Escribir datos en el orden solicitado (sin exponer IDs ni campos internos)
        foreach ($pedidos as $pedido) {
            $row = [
                // Empresa, Área Cliente, Usuario Solicitante
                $this->limpiarCSV($pedido['empresa_nombre'] ?? ''),
                $this->limpiarCSV($pedido['area_nombre'] ?? ''),
                $this->limpiarCSV(($pedido['usuario_cliente_nombre'] ?? '') . ' ' . ($pedido['usuario_cliente_apellidos'] ?? '')),

                // Requerimiento: título, objetivo, público, descripción, materiales, URL
                $this->limpiarCSV($pedido['titulo'] ?? ''),
                $this->limpiarCSV($pedido['objetivo_comunicacion'] ?? ''),
                $this->limpiarCSV($pedido['publico_objetivo'] ?? ''),
                $this->limpiarCSV($pedido['descripcion'] ?? ''),
                $this->limpiarCSV($pedido['archivos_cliente'] ?? ''),
                $this->limpiarCSV($pedido['url_subida'] ?? ''),

                // Servicio y tipo
                $this->limpiarCSV($pedido['servicio_nombre'] ?? ''),
                $this->limpiarCSV($pedido['tipo_requerimiento'] ?? ''),

                // Cómo trabajó el empleado asignado
                $this->limpiarCSV(($pedido['empleado_nombre'] ?? '') . ' ' . ($pedido['empleado_apellidos'] ?? '')),
                $this->formatearHoras($pedido['horas_usadas'] ?? 0),
                $this->formatearFecha($pedido['fechainicio'] ?? ''),
                $this->formatearFecha($pedido['fecharequerida'] ?? ''),
                $this->formatearFecha($pedido['fechacompletado'] ?? ''),
                $pedido['estado'] ?? '',
                $pedido['prioridad'] ?? '',
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
        return number_format((float) $horas, 2, '.', '');
    }
}
