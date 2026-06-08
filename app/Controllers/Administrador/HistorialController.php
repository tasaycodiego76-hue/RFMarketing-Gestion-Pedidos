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

    public function historialJson()
    {
        $atencionModel = new AtencionModel();
        $page = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search') ?? '';
        $perPage = 15;

        // Obtener todos los pedidos finalizados
        $pedidosRaw = $atencionModel->obtenerHistorialFinalizado();

        // Aplicar filtro de búsqueda si existe
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $pedidosRaw = array_filter($pedidosRaw, function($p) use ($searchLower) {
                return (
                    stripos($p['titulo'] ?? '', $searchLower) !== false ||
                    stripos($p['empresa_nombre'] ?? '', $searchLower) !== false ||
                    stripos($p['area_nombre'] ?? '', $searchLower) !== false ||
                    stripos($p['empleado_nombre'] ?? '', $searchLower) !== false
                );
            });
            $pedidosRaw = array_values($pedidosRaw); // Reindexar array
        }

        // Aplicar colores
        $colores = ['#FF6B6B', '#FFD93D', '#6BCB77', '#4D96FF', '#C77DFF', '#FF9F43'];
        foreach ($pedidosRaw as &$p) {
            $p['empresa_color'] = $colores[$p['empresa_id'] % count($colores)];
        }

        // Paginar
        $totalItems = count($pedidosRaw);
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($pedidosRaw, $offset, $perPage);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $paginated,
            'currentPage' => (int)$page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ]);
    }
}
