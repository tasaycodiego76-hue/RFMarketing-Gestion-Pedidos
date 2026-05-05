<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\AtencionModel;
use App\Models\EmpresaModel;

class HistorialController extends BaseController
{
    public function index()
    {
        $atencionModel = new AtencionModel();
        
        // Obtenemos los pedidos finalizados desde el modelo
        $pedidosRaw = $atencionModel->obtenerHistorialFinalizado();

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
