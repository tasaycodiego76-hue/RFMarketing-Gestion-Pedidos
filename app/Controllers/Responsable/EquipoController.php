<?php

namespace App\Controllers\Responsable;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\RequerimientoModel;
use App\Models\HistorialAsignacionesModel;
use App\Models\TrackingModel;

class EquipoController extends BaseResponsableController
{
    /**
     * Renderiza la página principal de "Mi Equipo", donde se listan los Empleados a cargo del responsable.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaEquipo()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to(base_url('/'))->with('error', $userS['message']);
        }

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
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to(base_url('/'))->with('error', $userS['message']);
        }

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
        // Obtenemos todas las tareas del técnico para filtrarlas con lógica inteligente en memoria
        $todasLasTareas = $atencionModel->obtenerDetalladoPorEmpleado($idEmpleado);

        $tareasActivas = [];
        $tareasCompletadasHoy = [];
        $hoy = date('Y-m-d');

        foreach ($todasLasTareas as $t) {
            $est = $t['estado'] ?? '';
            if (in_array($est, ['finalizado', 'completado'])) {
                // Solo mostrar en el modal si se completó el día de hoy
                $fechaCompleto = $t['fechacompletado'] ? substr($t['fechacompletado'], 0, 10) : '';
                if ($fechaCompleto === $hoy) {
                    $tareasCompletadasHoy[] = $t;
                }
            } else {
                // Las activas se muestran siempre para estar sincronizadas con los contadores de la tarjeta
                $tareasActivas[] = $t;
            }
        }

        // Combinar tareas activas con las completadas hoy
        $tareas = array_merge($tareasActivas, $tareasCompletadasHoy);

        // Ordenar el resultado final por fecha de inicio / creación (Más reciente -> Más antiguo)
        usort($tareas, function ($a, $b) {
            $f1 = $a['fechainicio'] ? strtotime($a['fechainicio']) : 0;
            $f2 = $b['fechainicio'] ? strtotime($b['fechainicio']) : 0;
            if ($f1 === 0) $f1 = strtotime($a['fechacreacion'] ?? '0');
            if ($f2 === 0) $f2 = strtotime($b['fechacreacion'] ?? '0');
            
            return $f2 <=> $f1; // DESC
        });

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
     *  Lista de los Empleados asignables del Area
     * @return \CodeIgniter\HTTP\ResponseInterface
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
                if ($t['estado'] === 'en_proceso') {
                    $enProceso++;
                } elseif (in_array($t['estado'], ['finalizado', 'completado'])) {
                    $completados++;
                } elseif ($t['estado'] === 'pendiente_asignado') {
                    $pendientes++;
                }
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
                    // El array_merge de derecha a izquierda sobreescribe claves. 
                    // Ponemos $tarea al final para que su 'id' (de atencion) no sea pisado por el 'id' de requerimiento.
                    $tareaConDetalle = array_merge($detalle, $tarea);
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

    /**
     * Obtiene las tareas activas de un empleado específico.
     * Utilizado para refrescar la carga de trabajo de un miembro puntual.
     * @param int|null $idEmpleado
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function tareasPorEmpleado($idEmpleado = null)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        if (!$idEmpleado) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de empleado no válido']);
        }

        $atencionModel = new AtencionModel();
        $requerimientoModel = new RequerimientoModel();

        // Buscamos tareas que estén en manos del técnico
        $tareas = $atencionModel->where('idempleado', $idEmpleado)
                                ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
                                ->findAll();

        $tareasDetalladas = [];
        foreach ($tareas as $tarea) {
            $detalle = $requerimientoModel->getDetalleCompleto($tarea['idrequerimiento']);
            if ($detalle) {
                // Ponemos $tarea al final para preservar el 'id' correcto de la atención
                $tareaConDetalle = array_merge($detalle, $tarea);
                $tareaConDetalle['identificador_unico'] = 'EMP_' . $idEmpleado . '_T_' . $tarea['id'];
                $tareasDetalladas[] = $tareaConDetalle;
            }
        }

        return $this->response->setJSON([
            'success' => true, 
            'data'    => $tareasDetalladas,
            'total_tareas' => count($tareasDetalladas)
        ]);
    }

    /**
     * Reasigna una tarea de un especialista a otro.
     * Solo el Responsable de Área puede hacer esto.
     * Registra el cambio en historial_asignaciones y en tracking.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function reasignarTarea()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $idResponsable  = (int) $userS['user']['id'];
        $idAreaAgencia  = (int) $userS['user']['idarea_agencia'];

        $idAtencion      = (int) $this->request->getPost('idatencion');
        $idNuevoEmpleado = (int) $this->request->getPost('idempleado_nuevo');
        $motivo          = trim($this->request->getPost('motivo') ?? '');

        // Validaciones básicas
        if ($idAtencion <= 0 || $idNuevoEmpleado <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Faltan el ID de la tarea o el nuevo especialista.']);
        }

        if (empty($motivo)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El motivo de la reasignación es obligatorio.']);
        }

        $atencionModel  = new AtencionModel();
        $usuarioModel   = new UsuarioModel();
        $historialModel = new HistorialAsignacionesModel();
        $trackingModel  = new TrackingModel();

        // Cargar la tarea
        $tarea = $atencionModel->find($idAtencion);
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'message' => 'La tarea no existe.']);
        }

        // Verificar que la tarea pertenece al área del responsable
        if ((int) $tarea['idarea_agencia'] !== $idAreaAgencia) {
            return $this->response->setJSON(['success' => false, 'message' => 'Seguridad: esta tarea no pertenece a tu área.']);
        }

        // Solo se pueden reasignar tareas activas
        if (!in_array($tarea['estado'], ['en_proceso', 'pendiente_asignado'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se pueden reasignar tareas activas (En proceso o Pendiente de inicio).']);
        }

        // Verificar que el nuevo empleado exista y pertenezca al área
        $nuevoEmpleado = $usuarioModel->find($idNuevoEmpleado);
        if (!$nuevoEmpleado || (int) $nuevoEmpleado['idarea_agencia'] !== $idAreaAgencia) {
            return $this->response->setJSON(['success' => false, 'message' => 'El especialista seleccionado no pertenece a tu área.']);
        }

        // No tiene sentido reasignar al mismo empleado
        if ((int) $tarea['idempleado'] === $idNuevoEmpleado) {
            return $this->response->setJSON(['success' => false, 'message' => 'La tarea ya está asignada a ese especialista.']);
        }

        $idEmpleadoAnterior = (int) $tarea['idempleado'];
        $ahora = (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s');

        // Transacción
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Actualizar la atención con el nuevo empleado
            $atencionModel->update($idAtencion, [
                'idempleado' => $idNuevoEmpleado,
                // Reiniciamos la fecha de inicio para que el nuevo empleado registre su propio comienzo
                'fechainicio' => null,
                'estado'     => 'pendiente_asignado',
            ]);

            // 2. Registrar en historial_asignaciones
            $historialModel->insert([
                'idatencion'         => $idAtencion,
                'idempleado_anterior' => $idEmpleadoAnterior ?: null,
                'idempleado'         => $idNuevoEmpleado,
                'idadmin'            => $idResponsable,
                'fecha_asignacion'   => $ahora,
                'motivo_cambio'      => $motivo,
            ]);

            // 3. Registrar en tracking para que quede en el historial visible
            $nombreNuevo = trim($nuevoEmpleado['nombre'] . ' ' . $nuevoEmpleado['apellidos']);
            $trackingModel->insert([
                'idatencion'    => $idAtencion,
                'idusuario'     => $idResponsable,
                'accion'        => "Tarea reasignada al especialista: $nombreNuevo. Motivo: $motivo",
                'estado'        => 'pendiente_asignado',
                'fecha_registro' => $ahora,
            ]);

            $db->transCommit();

            // Emitir evento Pusher en tiempo real para actualizar las vistas de todos los involucrados (incluyendo al cliente)
            try {
                $pusher = new \App\Services\PusherService();
                $pusher->notificarCambioEstado($idAtencion, 'pendiente_asignado');
            } catch (\Exception $exPusher) {
                log_message('error', '[reasignarTarea Pusher] ' . $exPusher->getMessage());
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "¡Tarea reasignada correctamente a $nombreNuevo!",
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[reasignarTarea] ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error interno al reasignar: ' . $e->getMessage()]);
        }
    }

    /**
     * Devuelve la lista de empleados del área aptos para ser asignados
     * (excluyendo al actual asignado de la tarea si se pasa su ID).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function empleadosParaReasignar()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $excluir      = (int) $this->request->getGet('excluir');
        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

        $usuarioModel = new UsuarioModel();
        $lista = $usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia);

        $resultado = array_values(array_filter(array_map(function ($u) use ($excluir) {
            if ((int) $u['id'] === $excluir) return null; // Excluir al asignado actual
            return [
                'id'            => (int) $u['id'],
                'nombre_completo' => trim(($u['nombre'] ?? '') . ' ' . ($u['apellidos'] ?? '')),
            ];
        }, $lista)));

        return $this->response->setJSON(['success' => true, 'data' => $resultado]);
    }

    /**
     * Devuelve el historial de reasignaciones de una tarea específica.
     * Solo accesible si la tarea pertenece al área del responsable.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function historialAsignaciones()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $idAtencion   = (int) $this->request->getGet('idatencion');
        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

        if ($idAtencion <= 0) {
            return $this->response->setJSON(['success' => false, 'data' => []]);
        }

        // Verificar que la tarea pertenece al área del responsable
        $atencionModel = new AtencionModel();
        $tarea = $atencionModel->find($idAtencion);
        if (!$tarea || (int) $tarea['idarea_agencia'] !== $idAreaAgencia) {
            return $this->response->setJSON(['success' => false, 'data' => [], 'message' => 'Acceso no autorizado.']);
        }

        $historialModel = new HistorialAsignacionesModel();
        $historial = $historialModel->obtenerHistorialPorAtencion($idAtencion);

        return $this->response->setJSON(['success' => true, 'data' => $historial]);
    }
}