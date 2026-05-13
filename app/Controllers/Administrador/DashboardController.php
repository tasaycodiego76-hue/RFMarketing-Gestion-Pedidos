<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;
use App\Models\AtencionModel;
use App\Models\AreasAgenciaModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $atencionModel = new AtencionModel();
        $empresaModel = new EmpresaModel();
        $areaModel = new AreasAgenciaModel();
        $usuarioModel = new \App\Models\UsuarioModel();

        $porAprobar = $atencionModel->contarPorEstado('pendiente_sin_asignar');
        $activos = $atencionModel->contarActivos();
        $enRevision = $atencionModel->contarPorEstado('en_revision');
        $completados = $atencionModel->contarPorEstado('finalizado');
        $total = max(1, $porAprobar + $activos + $enRevision + $completados);

        return view('admin/dashboard', [
            'titulo' => 'Dashboard',
            'tituloPagina' => 'DASHBOARD',
            'paginaActual' => 'dashboard',
            'porAprobar' => $porAprobar,
            'activos' => $activos,
            'enRevision' => $atencionModel->contarPorEstado('en_revision'),
            'completados' => $completados,
            'empresas' => $empresaModel->obtenerConStatsActivas(),
            'areas' => $areaModel->listarActivas(),
            'totalPedidos' => $total,
            'pctActivos' => round($activos / $total * 100),
            'pctPorAprobar' => round($porAprobar / $total * 100),
            'pctCompletados' => round($completados / $total * 100),
            // Nuevas métricas
            'totalEmpresas' => $empresaModel->where('estado', true)->countAllResults(),
            'totalEmpleados' => $usuarioModel->where('rol', 'empleado')->where('estado', true)->countAllResults(),
            'totalResponsables' => $usuarioModel->where('rol', 'cliente')->where('estado', true)->countAllResults(),
        ]);
    }
}