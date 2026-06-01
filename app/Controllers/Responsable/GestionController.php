<?php

namespace App\Controllers\Responsable;

use App\Models\AtencionModel;

class GestionController extends BaseResponsableController
{
    /**
     * Muestra una vista con dos tablas:
     * - Trabajos finalizados por el propio responsable (si es que ejecutó alguno).
     * - Trabajos finalizados por todo su equipo del área.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function historial()
    {
        // Validar identidad
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to(base_url('/'))->with('error', $userS['message']);
        }

        $user = $userS['user'];
        $idAreaAgencia = (int) $user['idarea_agencia'];

        // Cargar métricas para el Sidebar
        $metrics = $this->_getMetrics($idAreaAgencia);

        $atencionModel = new AtencionModel();

        // Obtener datos históricos (Estado: finalizado)
        $misCompletados = $atencionModel->obtenerDetalladoPorEmpleado((int) $user['id'], ['finalizado']);
        $areaCompletados = $atencionModel->obtenerDetalladoPorArea($idAreaAgencia, ['finalizado']);

        // Ordenar por fecha de completado (Reciente -> Antiguo: DESC)
        usort($misCompletados, function ($a, $b) {
            $fechaA = $a['fechacompletado'] ?? '';
            $fechaB = $b['fechacompletado'] ?? '';
            return strcmp($fechaB, $fechaA);
        });

        usort($areaCompletados, function ($a, $b) {
            $fechaA = $a['fechacompletado'] ?? '';
            $fechaB = $b['fechacompletado'] ?? '';
            return strcmp($fechaB, $fechaA);
        });

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
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to(base_url('/'))->with('error', $userS['message']);
        }

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

    /**
     * Endpoint API JSON: Devuelve la retroalimentación del responsable para carga dinámica (Pusher).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function retroalimentacionJson()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])->setStatusCode(401);
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $atencionModel = new AtencionModel();
        $items = $atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia);

        return $this->response->setJSON([
            'success' => true,
            'data' => $items,
            'count' => count($items)
        ]);
    }
}

