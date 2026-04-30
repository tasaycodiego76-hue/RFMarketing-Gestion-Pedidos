<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\AtencionModel;
use App\Models\EmpresaModel;

class HistorialController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Obtenemos los pedidos finalizados con toda la info necesaria
        $pedidosRaw = $db->query("
            SELECT 
                a.id, a.titulo, a.fechacompletado,
                e.id as empresa_id,
                e.nombreempresa as empresa_nombre,
                ar_ag.nombre as area_nombre,
                u.nombre as empleado_nombre,
                u.apellidos as empleado_apellidos,
                COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u_sol.idarea
            INNER JOIN empresas e ON e.id = ar.idempresa
            LEFT JOIN areas_agencia ar_ag ON ar_ag.id = a.idarea_agencia
            LEFT JOIN usuarios u ON u.id = a.idempleado
            LEFT JOIN servicios s ON s.id = a.idservicio
            WHERE a.estado = 'finalizado'
            ORDER BY a.fechacompletado DESC
        ")->getResultArray();

        // Aplicamos la misma lógica de colores que EmpresaModel
        $colores = ['#FF6B6B', '#FFD93D', '#6BCB77', '#4D96FF', '#C77DFF', '#FF9F43'];
        $pedidos = [];
        foreach ($pedidosRaw as $p) {
            $p['empresa_color'] = $colores[$p['empresa_id'] % count($colores)];
            $pedidos[] = $p;
        }

        // Para la barra lateral dinámica
        $empresaModel = new EmpresaModel();
        $empresasSidebar = $empresaModel->where('estado', true)->findAll();

        return view('admin/historial', [
            'titulo' => 'Historial de Trabajos',
            'tituloPagina' => 'HISTORIAL DE TRABAJOS FINALIZADOS',
            'paginaActual' => 'historial',
            'pedidos' => $pedidos,
            'empresas' => $empresasSidebar
        ]);
    }
}
