<?php

namespace App\Controllers\Responsable;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\RequerimientoModel;

class EquipoController extends BaseResponsableController
{
    /**
     * Renderiza la vista de Mi Equipo
     */
    public function index()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $metrics = $this->_getMetrics((int) $userS['user']['idarea_agencia']);

        $data = array_merge([
            'titulo' => 'Mi Equipo',
            'tituloPagina' => 'Mi Equipo',
            'user' => $userS['userData']
        ], $metrics);

        return view('Responsable/equipo', $data);
    }

    /**
     * Renderiza la vista de Tareas En Proceso del equipo
     */
    public function vistaTareasEnProceso()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);

        $usuarioModel = new UsuarioModel();
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia);
        $empleadosEstaticos = [];

        foreach ($empleados as $empleado) {
            $empleadosEstaticos[] = [
                'id' => (int) $empleado['id'],
                'nombre' => $empleado['nombre'] ?? '',
                'apellidos' => $empleado['apellidos'] ?? '',
                'nombre_completo' => trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellidos'] ?? '')),
                'esresponsable' => ($empleado['esresponsable'] === true || $empleado['esresponsable'] === 't' || $empleado['esresponsable'] == 1)
            ];
        }

        $data = array_merge([
            'titulo' => 'Tareas en Proceso',
            'tituloPagina' => 'Tareas en Proceso',
            'user' => $userS['userData'],
            'empleados' => $empleadosEstaticos
        ], $metrics);

        return view('Responsable/en_proceso', $data);
    }

    /**
     * Obtiene el detalle de un miembro del equipo y sus pedidos asignados
     */
    public function detalleMiembro($idEmpleado)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $usuarioModel = new UsuarioModel();
        
        // Validar que el empleado pertenece a la misma área
        $empleado = $usuarioModel->find($idEmpleado);
        if (!$empleado || $empleado['idarea_agencia'] != $idAreaAgencia) {
            return $this->response->setJSON(['success' => false, 'message' => 'Empleado no encontrado o no pertenece a tu área.']);
        }

        $atencionModel = new AtencionModel();
        $tareas = $atencionModel->obtenerDetalladoPorEmpleado($idEmpleado);

        return $this->response->setJSON([
            'success' => true,
            'empleado' => [
                'id' => $empleado['id'],
                'nombre_completo' => trim($empleado['nombre'] . ' ' . $empleado['apellidos']),
                'correo' => $empleado['correo'],
                'rol' => $empleado['rol'],
                'esresponsable' => $empleado['esresponsable']
            ],
            'tareas' => $tareas
        ]);
    }

    /**
     * Lista de los Empleados asignables del Area
     */
    public function empleadosMiAreaJson()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();
        $lista = $usuarioModel->obtenerAsignablesPorAreaAgencia((int) $userS['user']['idarea_agencia']);

        $data = array_map(function ($u) use ($atencionModel) {
            $esResponsable = ($u['esresponsable'] === true || $u['esresponsable'] === 't' || $u['esresponsable'] == 1);
            $tareas = $atencionModel->obtenerDetalladoPorEmpleado((int) $u['id']);

            $enProceso = 0;
            $completados = 0;
            $pendientes = 0;

            foreach ($tareas as $t) {
                if ($t['estado'] === 'en_proceso') $enProceso++;
                elseif (in_array($t['estado'], ['finalizado', 'completado'])) $completados++;
                else $pendientes++;
            }

            return [
                'id' => (int) $u['id'],
                'nombre_completo' => trim(($u['nombre'] ?? '') . ' ' . ($u['apellidos'] ?? '')),
                'esresponsable' => $esResponsable,
                'en_proceso' => $enProceso,
                'completados' => $completados,
                'pendientes' => $pendientes
            ];
        }, $lista);

        return $this->response->setJSON(['success' => true, 'total' => count($data), 'data' => $data]);
    }

    /**
     * Tareas en proceso de todos los empleados del área
     */
    public function tareasEnProceso()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();
        $requerimientoModel = new RequerimientoModel();

        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia((int) $userS['user']['idarea_agencia']);
        $tareasPorEmpleado = [];

        foreach ($empleados as $empleado) {
            $tareas = $atencionModel->obtenerTareasPorEmpleadoEstado($empleado['id'], 'en_proceso');
            $tareasDetalladas = [];
            foreach ($tareas as $tarea) {
                $detalle = $requerimientoModel->getDetalleCompleto($tarea['idrequerimiento']);
                if ($detalle) {
                    $tareaConDetalle = array_merge($tarea, $detalle);
                    $tareaConDetalle['identificador_unico'] = 'EMP_' . $empleado['id'] . '_TAREA_' . $tarea['id'] . '_' . date('His');
                    $tareasDetalladas[] = $tareaConDetalle;
                }
            }

            $prioridadOrden = ['alta' => 1, 'media' => 2, 'baja' => 3];
            usort($tareasDetalladas, function ($a, $b) use ($prioridadOrden) {
                $prioA = $prioridadOrden[strtolower($a['prioridad'] ?? 'media')] ?? 2;
                $prioB = $prioridadOrden[strtolower($b['prioridad'] ?? 'media')] ?? 2;
                if ($prioA != $prioB) return $prioA - $prioB;
                return strtotime($b['fechaasignacion'] ?? '0') - strtotime($a['fechaasignacion'] ?? '0');
            });

            $tareasPorEmpleado[] = [
                'id' => (int) $empleado['id'],
                'nombre_completo' => trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellidos'] ?? '')),
                'esresponsable' => ($empleado['esresponsable'] === true || $empleado['esresponsable'] === 't' || $empleado['esresponsable'] == 1),
                'tareas' => $tareasDetalladas,
                'total_tareas' => count($tareasDetalladas)
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $tareasPorEmpleado,
            'total_empleados' => count($tareasPorEmpleado),
            'total_tareas' => array_sum(array_column($tareasPorEmpleado, 'total_tareas'))
        ]);
    }

    /**
     * Tareas de un empleado específico
     */
    public function tareasPorEmpleado($idEmpleado = null)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        if (!$idEmpleado) return $this->response->setJSON(['success' => false, 'message' => 'ID de empleado no válido']);

        $atencionModel = new AtencionModel();
        $requerimientoModel = new RequerimientoModel();
        $tareas = $atencionModel->obtenerTareasPorEmpleadoEstado($idEmpleado, 'en_proceso');

        $tareasDetalladas = [];
        foreach ($tareas as $tarea) {
            $detalle = $requerimientoModel->getDetalleCompleto($tarea['idrequerimiento']);
            if ($detalle) {
                $tareaConDetalle = array_merge($tarea, $detalle);
                $tareaConDetalle['identificador_unico'] = 'EMP_' . $idEmpleado . '_T_' . $tarea['id'];
                $tareasDetalladas[] = $tareaConDetalle;
            }
        }

        return $this->response->setJSON([
            'success' => true, 
            'data' => $tareasDetalladas,
            'total_tareas' => count($tareasDetalladas)
        ]);
    }
}
