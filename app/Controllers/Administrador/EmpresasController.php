<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\EmpresaModel;

class EmpresasController extends Controller
{

    public function index(): string
    {
        $empresaModel = new EmpresaModel();

        return view('admin/empresas', [
            'titulo'       => 'Empresas',
            'tituloPagina' => 'EMPRESAS',
            'paginaActual' => 'todas_empresas',
            'empresas'     => $empresaModel->findAll(),
        ]);
    }

}
