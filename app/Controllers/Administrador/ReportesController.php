<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\UsuarioModel;
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
        $db = \Config\Database::connect();
        
        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        $idAreaInt = $this->request->getGet('idarea_int') ?: null;
        $idEmpresa = $this->request->getGet('idempresa') ?: null;
        $idEmpleado = $this->request->getGet('idempleado') ?: null;
        $soloCompletados = $this->request->getGet('solo_completados') == '1';

        // LOGICA IDENTICA AL RESPONSABLE PERO PARA ADMIN
        $params = [];
        $where = " WHERE 1=1 ";

        if ($idAreaInt) {
            $where .= " AND a.idarea_agencia = ? ";
            $params[] = $idAreaInt;
        }
        if ($idEmpresa) {
            $where .= " AND e.id = ? ";
            $params[] = $idEmpresa;
        }
        if ($idEmpleado) {
            $where .= " AND a.idempleado = ? ";
            $params[] = $idEmpleado;
        }
        if ($desde) {
            $where .= " AND a.fechacreacion >= ? ";
            $params[] = $desde . ' 00:00:00';
        }
        if ($hasta) {
            $where .= " AND a.fechacreacion <= ? ";
            $params[] = $hasta . ' 23:59:59';
        }
        if ($soloCompletados) {
            $where .= " AND a.estado = 'finalizado' ";
        }

        // 1. Detalle de Pedidos (Más ordenado por Área de Agencia y Empresa)
        $sqlDetalle = "SELECT a.id, a.titulo, a.estado, a.fechacreacion, e.nombreempresa as empresa_nombre,
                       aa.nombre as area_agencia_nombre,
                       u_emp.nombre as empleado_nombre, u_emp.apellidos as empleado_apellidos,
                       COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre,
                       CASE WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL 
                       THEN ROUND(EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600, 2) ELSE 0 END as horas_usadas
                       FROM atencion a
                       JOIN requerimiento r ON r.id = a.idrequerimiento
                       JOIN usuarios u_cli ON r.idusuarioempresa = u_cli.id
                       JOIN areas ar_cli ON u_cli.idarea = ar_cli.id
                       JOIN empresas e ON ar_cli.idempresa = e.id
                       JOIN areas_agencia aa ON a.idarea_agencia = aa.id
                       LEFT JOIN servicios s ON s.id = a.idservicio
                       LEFT JOIN usuarios u_emp ON u_emp.id = a.idempleado
                       $where ORDER BY aa.nombre ASC, e.nombreempresa ASC, a.fechacreacion DESC";
        $pedidos = $db->query($sqlDetalle, $params)->getResultArray();

        // Metricas de Técnicos (Igual al responsable)
        $whereMet = $idAreaInt ? " AND u.idarea_agencia = $idAreaInt " : "";
        $sqlMet = "SELECT u.nombre, u.apellidos, COUNT(a.id) as asignados,
                   COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) as completados,
                   CASE WHEN COUNT(a.id) > 0 THEN ROUND((COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END)::numeric / COUNT(a.id)) * 100, 1) ELSE 0 END as eficiencia,
                   COALESCE(SUM(CASE WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL THEN EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600 ELSE 0 END), 0) as horas_totales
                   FROM usuarios u LEFT JOIN atencion a ON a.idempleado = u.id
                   WHERE u.rol = 'empleado' $whereMet GROUP BY u.id, u.nombre, u.apellidos ORDER BY eficiencia DESC";
        $metricas = $db->query($sqlMet)->getResultArray();

        $resumen = [
            'total'       => count($pedidos),
            'completados' => count(array_filter($pedidos, fn($i) => $i['estado'] === 'finalizado')),
            'en_proceso'  => count(array_filter($pedidos, fn($i) => $i['estado'] === 'en_proceso')),
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

        $db = \Config\Database::connect();
        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;
        $idAreaInt = (int)($this->request->getGet('idarea_int') ?: 0);
        $idEmpresa = $this->request->getGet('idempresa') ?: null;

        $params = [];
        $where = " WHERE 1=1 ";

        if ($idAreaInt > 0) {
            $where .= " AND a.idarea_agencia = ? ";
            $params[] = $idAreaInt;
        }
        if ($idEmpresa) {
            $where .= " AND e.id = ? ";
            $params[] = $idEmpresa;
        }
        if ($desde) {
            $where .= " AND a.fechacreacion >= ? ";
            $params[] = $desde . ' 00:00:00';
        }
        if ($hasta) {
            $where .= " AND a.fechacreacion <= ? ";
            $params[] = $hasta . ' 23:59:59';
        }

        $sql = "SELECT a.id, a.estado, 
                CASE WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL 
                THEN ROUND(EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600, 2) ELSE 0 END as horas
                FROM atencion a
                JOIN requerimiento r ON r.id = a.idrequerimiento
                JOIN usuarios u_cli ON r.idusuarioempresa = u_cli.id
                JOIN areas ar_cli ON u_cli.idarea = ar_cli.id
                JOIN empresas e ON ar_cli.idempresa = e.id
                $where";

        $data = $db->query($sql, $params)->getResultArray();

        $resumen = [
            'total'         => count($data),
            'completados'   => count(array_filter($data, fn($i) => $i['estado'] === 'finalizado')),
            'en_proceso'    => count(array_filter($data, fn($i) => $i['estado'] === 'en_proceso')),
            'hrs_totales'   => array_sum(array_column($data, 'horas'))
        ];

        return $this->response->setJSON($resumen);
    }
}
