<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;

class ReportesController extends BaseController
{
    public function index()
    {
        // Para la barra lateral dinámica
        $empresaModel = new EmpresaModel();
        $empresasSidebar = $empresaModel->where('estado', true)->findAll();

        return view('admin/reportes', [
            'titulo' => 'Reportes de Gestión',
            'tituloPagina' => 'CENTRO DE REPORTES Y ESTADÍSTICAS',
            'paginaActual' => 'reportes',
            'empresas' => $empresasSidebar
        ]);
    }
}
