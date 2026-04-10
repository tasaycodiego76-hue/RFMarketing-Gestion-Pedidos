<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\AreasAgenciaModel;
use App\Models\AreasModel;
use App\Models\EmpresaModel;

class AreasController extends Controller
{
    // GET admin/areas  →  tab Agencia
    public function index(): string
{
    $areasAgenciaModel = new AreasAgenciaModel();
    $empresaModel      = new EmpresaModel();

    return view('admin/areas', [
        'titulo'       => 'Areas',
        'tituloPagina' => 'AREAS',
        'paginaActual' => 'areas',
        'tabActivo'    => 'agencia',
        'areas'        => $areasAgenciaModel->listarConResponsable(),
        'empresas'     => $empresaModel->findAll(),
    ]);
}


    // GET admin/areas/clientes  →  tab Clientes
    public function clientes(): string
    {
        $empresaModel = new EmpresaModel();

        return view('admin/areas', [
            'titulo'       => 'Areas',
            'tituloPagina' => 'AREAS',
            'paginaActual' => 'areas',
            'tabActivo'    => 'clientes',
            'empresas'     => $empresaModel->findAll(),    
            'areas'        => [],
        ]);
    }
    public function registrar(): \CodeIgniter\HTTP\ResponseInterface
{
    $json   = $this->request->getJSON(true);
    $model  = new AreasAgenciaModel();

    $model->insert([
        'nombre'      => $json['nombre'],
        'descripcion' => $json['descripcion'] ?? null,
        'activo'      => true,
    ]);

    return $this->response->setJSON(['success' => true]);
}

public function registrarCliente(): \CodeIgniter\HTTP\ResponseInterface
{
    $json  = $this->request->getJSON(true);
    $model = new AreasModel();

    $model->insert([
        'idempresa'   => $json['idempresa'],
        'nombre'      => $json['nombre'],
        'descripcion' => $json['descripcion'] ?? null,
        'activo'      => true,
    ]);

    return $this->response->setJSON(['success' => true]);
}
public function listarPorEmpresa(int $idEmpresa): \CodeIgniter\HTTP\ResponseInterface
{
    $model = new AreasModel();
    $areas = $model->where('idempresa', $idEmpresa)->findAll();
    return $this->response->setJSON($areas);
}
}