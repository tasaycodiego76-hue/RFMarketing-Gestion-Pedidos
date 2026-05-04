<?php

namespace App\Controllers\Responsable;

use App\Models\AtencionModel;

class GestionController extends BaseResponsableController
{
    /**
     * Muestra una vista con dos tablas:
     * 1. Trabajos finalizados por el propio responsable (si es que ejecutó alguno).
     * 2. Trabajos finalizados por todo su equipo del área.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function historial()
    {
        // Validar identidad
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $user = $userS['user'];
        $idAreaAgencia = (int)$user['idarea_agencia'];
        
        // Cargar métricas para el Sidebar
        $metrics = $this->_getMetrics($idAreaAgencia);

        $atencionModel = new AtencionModel();
        
        // Obtener datos históricos (Estado: finalizado)
        $misCompletados = $atencionModel->obtenerDetalladoPorEmpleado((int)$user['id'], ['finalizado']);
        $areaCompletados = $atencionModel->obtenerDetalladoPorArea($idAreaAgencia, ['finalizado']);

        // Inyectar en la vista
        return view('responsable/historial', array_merge([
            'titulo' => 'Historial de Pedidos',
            'tituloPagina' => 'HISTORIAL DE TRABAJOS FINALIZADOS',
            'user' => $userS['userData'],
            'mis_completados' => $misCompletados,
            'area_completados' => $areaCompletados
        ], $metrics));
    }

    /**
     * Muestra los pedidos que tienen observaciones o que fueron devueltos 
     * desde administración o por el propio cliente para ser corregidos.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function retroalimentacion()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);
        
        $atencionModel = new AtencionModel();
        
        // Obtener solo los pedidos del área que tengan el campo 'observacion_revision' lleno
        $items = $atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia);

        return view('responsable/retroalimentacion', array_merge([
            'titulo' => 'Buzón de Retroalimentación',
            'tituloPagina' => 'Requerimientos Observados / Devueltos',
            'user' => $userS['userData'],
            'data' => $items
        ], $metrics));
    }
}
