<?php

namespace App\Controllers\Responsable;

use App\Models\AtencionModel;

class GestionController extends BaseResponsableController
{
    /**
     * Vista de Historial de tareas (Propias y del Área)
     */
    public function historial()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $user = $userS['user'];
        $idAreaAgencia = (int)$user['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);

        $atencionModel = new AtencionModel();
        $misCompletados = $atencionModel->obtenerDetalladoPorEmpleado((int)$user['id'], ['finalizado']);
        $areaCompletados = $atencionModel->obtenerDetalladoPorArea($idAreaAgencia, ['finalizado']);

        return view('responsable/historial', array_merge([
            'titulo' => 'Historial',
            'tituloPagina' => 'HISTORIAL DE TRABAJOS',
            'user' => $userS['userData'],
            'mis_completados' => $misCompletados,
            'area_completados' => $areaCompletados
        ], $metrics));
    }

    /**
     * Vista de Retroalimentación (Pedidos devueltos)
     */
    public function retroalimentacion()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);
        
        $atencionModel = new AtencionModel();
        $items = $atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia);

        return view('responsable/retroalimentacion', array_merge([
            'titulo' => 'Retroalimentación',
            'tituloPagina' => 'Retroalimentación',
            'user' => $userS['userData'],
            'data' => $items
        ], $metrics));
    }
}
