<?php

namespace App\Controllers\Responsable;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\RequerimientoModel;

class EquipoController extends BaseResponsableController
{
    /**
     * Renderiza la página principal de "Mi Equipo", donde se listan los técnicos 
     * a cargo del responsable.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $metrics = $this->_getMetrics((int) $userS['user']['idarea_agencia']);

        $data = array_merge([
            'titulo' => 'Gestión de Equipo',
            'tituloPagina' => 'Mi Equipo de Trabajo',
            'user' => $userS['userData']
        ], $metrics);

        return view('Responsable/equipo', $data);
    }

    /**
     * Renderiza la vista de monitoreo en tiempo real. Permite ver en qué está 
     * trabajando cada miembro del equipo en el momento actual.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaTareasEnProceso()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);

        $usuarioModel = new UsuarioModel();
        
        // Obtenemos solo los empleados que pertenecen al área de la agencia del responsable
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia);
        $empleadosEstaticos = [];

        // Limpiamos y formateamos los datos de los empleados para la vista
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
            'titulo' => 'Monitoreo de Tareas',
            'tituloPagina' => 'Tareas en Proceso del Equipo',
            'user' => $userS['userData'],
            'empleados' => $empleadosEstaticos
        ], $metrics);

        return view('Responsable/en_proceso', $data);
    }

    /**
     * Obtiene el perfil completo de un técnico y su historial de pedidos actuales.
     * @param mixed $idEmpleado
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detalleMiembro($idEmpleado)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $usuarioModel = new UsuarioModel();
        
        // Validar que el empleado consultado realmente pertenece al área del responsable
        $empleado = $usuarioModel->find($idEmpleado);
        if (!$empleado || $empleado['idarea_agencia'] != $idAreaAgencia) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado: El empleado no pertenece a tu área o no existe.']);
        }

        $atencionModel = new AtencionModel();
        // Obtenemos todos los pedidos donde este técnico es el responsable (atencion.idempleado)
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
     * Endpoint API JSON: Construye una muestra para las listas de monitoreo
     * @return \CodeIgniter\HTTP\ResponseInterface
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

        // Obtener miembros del equipo
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia((int) $userS['user']['idarea_agencia']);
        $tareasPorEmpleado = [];

        // Por cada miembro, buscar sus tareas activas
        foreach ($empleados as $empleado) {
            $tareas = $atencionModel->where('idempleado', $empleado['id'])
                                    ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
                                    ->findAll();
            $tareasDetalladas = [];
            foreach ($tareas as $tarea) {
                // Unimos con el detalle del requerimiento (cliente, empresa, etc)
                $detalle = $requerimientoModel->getDetalleCompleto($tarea['idrequerimiento']);
                if ($detalle) {
                    $tareaConDetalle = array_merge($tarea, $detalle);
                    // Identificador único para el frontend (evitar colisiones de IDs en listas)
                    $tareaConDetalle['identificador_unico'] = 'EMP_' . $empleado['id'] . '_TAREA_' . $tarea['id'] . '_' . date('His');
                    $tareasDetalladas[] = $tareaConDetalle;
                }
            }

            // Ordenamos las tareas por prioridad (Alta primero)
            $prioridadOrden = ['alta' => 1, 'media' => 2, 'baja' => 3];
            usort($tareasDetalladas, function ($a, $b) use ($prioridadOrden) {
                $prioA = $prioridadOrden[strtolower($a['prioridad'] ?? 'media')] ?? 2;
                $prioB = $prioridadOrden[strtolower($b['prioridad'] ?? 'media')] ?? 2;
                if ($prioA != $prioB) return $prioA - $prioB;
                // Si tienen misma prioridad, el más viejo (asignado antes) va primero
                return strtotime($a['fechacreacion'] ?? '0') - strtotime($b['fechacreacion'] ?? '0');
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
}
