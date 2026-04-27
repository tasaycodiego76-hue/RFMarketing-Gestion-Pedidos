<?php

namespace App\Controllers\Responsable;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\TrackingModel;
use App\Models\RequerimientoModel;
use App\Models\ArchivoModel;

class PedidosAreaController extends BaseController
{

    /**
     * Renderiza el dashboard principal del Responsable x Area (Empleado)
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $user = $this->getActiveUser();

        // Validar sesión activa para evitar errores de índice
        if (!$user) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Sesión no válida o expirada.',
            ]);
        }

        // Comparamos directamente con el string que manda Postgres
        $es_responsable = ($user['esresponsable'] === 't' || $user['esresponsable'] === true);

        if (!$user || $user['rol'] !== 'empleado' || !$es_responsable) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Acceso Denegado. Solo para Jefes de Área.',
            ]);
        }

        //Usar el Modelo para traer la información completa
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);
        $atencionModel = new AtencionModel();

        $idAreaAgencia = (int) $user['idarea_agencia'];

        // Obtener métricas para el dashboard
        $bandeja = $atencionModel->obtenerBandejaResponsable($idAreaAgencia);
        $porAsignar = count($bandeja);

        // Obtener métricas usando el modelo
        $enProceso = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('estado', 'en_proceso')
            ->countAllResults();

        $enRevision = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('estado', 'en_revision')
            ->countAllResults();

        $completados = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('estado', 'finalizado')
            ->countAllResults();

        // Contar miembros del equipo
        $totalMiembros = count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia));

        $data = [
            'titulo' => 'Mis Pedidos - Area',
            'tituloPagina' => 'Dashboard',
            'user' => [
                'id' => $userData['id'],
                'nombre' => $userData['nombre'],
                'apellidos' => $userData['apellidos'],
                'correo' => $userData['correo'],
                'telefono' => $userData['telefono'],
                'documento' => $userData['numerodoc'],
                'rol' => $userData['rol'],
                'nombre_area' => $userData['nombre_areaagencia'] ?? 'Área no asignada',
                'es_responsable' => true
            ],
            'porAsignar' => $porAsignar,
            'enProceso' => $enProceso,
            'enRevision' => $enRevision,
            'completados' => $completados,
            'pendientes_asignar' => $porAsignar // Para el badge del sidebar
        ];

        return view('Responsable/dashboard', $data);
    }

    /**
     * Renderiza la vista de Bandeja de Entrada
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaBandeja()
    {
        $user = $this->getActiveUser();

        if (!$user) {
            return redirect()->to('login')->with('error', 'Sesión no válida');
        }

        $es_responsable = ($user['esresponsable'] === 't' || $user['esresponsable'] === true);

        if ($user['rol'] !== 'empleado' || !$es_responsable) {
            return redirect()->to('responsable/dashboard')->with('error', 'Acceso denegado');
        }

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        $idAreaAgencia = (int) $user['idarea_agencia'];

        // Métricas para el sidebar
        $porAsignar = count($atencionModel->obtenerBandejaResponsable($idAreaAgencia));
        $enProceso = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('estado', 'en_proceso')
            ->countAllResults();
        $totalMiembros = count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia));

        $data = [
            'titulo' => 'Bandeja de Entrada',
            'tituloPagina' => 'Bandeja de Entrada',
            'user' => [
                'id' => $userData['id'],
                'nombre' => $userData['nombre'],
                'apellidos' => $userData['apellidos'],
                'correo' => $userData['correo'],
                'telefono' => $userData['telefono'],
                'documento' => $userData['numerodoc'],
                'rol' => $userData['rol'],
                'nombre_area' => $userData['nombre_areaagencia'] ?? 'Área no asignada',
                'es_responsable' => true
            ],
            'pendientes_asignar' => $porAsignar,
            'en_proceso' => $enProceso,
            'totalMiembros' => $totalMiembros
        ];

        return view('Responsable/bandeja', $data);
    }

    /**
     * Renderiza la vista de Mi Equipo
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaEquipo()
    {
        $user = $this->getActiveUser();

        if (!$user) {
            return redirect()->to('login')->with('error', 'Sesión no válida');
        }

        $es_responsable = ($user['esresponsable'] === 't' || $user['esresponsable'] === true);

        if ($user['rol'] !== 'empleado' || !$es_responsable) {
            return redirect()->to('responsable/dashboard')->with('error', 'Acceso denegado');
        }

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        $idAreaAgencia = (int) $user['idarea_agencia'];

        // Métricas para el sidebar
        $porAsignar = count($atencionModel->obtenerBandejaResponsable($idAreaAgencia));
        $enProceso = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('estado', 'en_proceso')
            ->countAllResults();
        $totalMiembros = count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia));

        $data = [
            'titulo' => 'Mi Equipo',
            'tituloPagina' => 'Mi Equipo',
            'user' => [
                'id' => $userData['id'],
                'nombre' => $userData['nombre'],
                'apellidos' => $userData['apellidos'],
                'correo' => $userData['correo'],
                'telefono' => $userData['telefono'],
                'documento' => $userData['numerodoc'],
                'rol' => $userData['rol'],
                'nombre_area' => $userData['nombre_areaagencia'] ?? 'Área no asignada',
                'es_responsable' => true
            ],
            'pendientes_asignar' => $porAsignar,
            'en_proceso' => $enProceso,
            'totalMiembros' => $totalMiembros
        ];

        return view('Responsable/equipo', $data);
    }

    /**
     * Renderiza la vista de Tareas En Proceso
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaTareasEnProceso()
    {
        $user = $this->ValidarSesion_DatosUser();
        if (!$user['ok']) {
            return redirect()->to('login')->with('error', $user['message']);
        }

        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();

        $idAreaAgencia = (int) $user['user']['idarea_agencia'];

        // Métricas para el sidebar/dashboard
        $porAsignar = count($atencionModel->obtenerBandejaResponsable($idAreaAgencia));
        $enProceso = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('estado', 'en_proceso')
            ->countAllResults();
        $totalMiembros = count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia));

        $data = [
            'titulo' => 'Tareas en Proceso',
            'tituloPagina' => 'Tareas en Proceso',
            'user' => $user['userData'],
            'pendientes_asignar' => $porAsignar,
            'en_proceso' => $enProceso,
            'totalMiembros' => $totalMiembros
        ];

        // Asegúrate de que esta vista exista en /app/Views/Responsable/tareas_proceso.php
        return view('Responsable/en_proceso', $data);
    }

    /**
     * Datos de la Bandeja de Entrada para el Responsable de Area
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function bandeja()
    {
        $user = $this->ValidarSesion_DatosUser();
        if (!$user['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $user['message']]);
        }
        $atencionModel = new AtencionModel();
        $items = $atencionModel->obtenerBandejaResponsable((int) $user['user']['idarea_agencia']);
        return $this->response->setJSON([
            'success' => true,
            'area' => [
                'id' => (int) $user['user']['idarea_agencia'],
                'nombre' => $user['userData']['nombre_areaagencia'] ?? 'Área no asignada',
            ],
            'total' => count($items),
            'data' => $items
        ]);
    }

    /**
     * Lista de los Empleados, que se pueden asignar solicitudes del Area
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function empleadosMiAreaJson()
    {
        $user = $this->ValidarSesion_DatosUser();
        if (!$user['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $user['message']]);
        }

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();

        $lista = $usuarioModel->obtenerAsignablesPorAreaAgencia((int) $user['user']['idarea_agencia']);

        $data = array_map(function ($u) use ($atencionModel) {
            $esResponsable = ($u['esresponsable'] === true || $u['esresponsable'] === 't' || $u['esresponsable'] == 1);

            // Obtener todas las tareas del empleado
            $tareas = $atencionModel->obtenerDetalladoPorEmpleado((int) $u['id']);

            $enProceso = 0;
            $completados = 0;
            $pendientes = 0;

            foreach ($tareas as $t) {
                if ($t['estado'] === 'en_proceso') {
                    $enProceso++;
                } elseif ($t['estado'] === 'finalizado' || $t['estado'] === 'completado') {
                    $completados++;
                } else {
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

        return $this->response->setJSON([
            'success' => true,
            'total' => count($data),
            'data' => $data
        ]);
    }

    public function tareasEnProceso()
    {
        $user = $this->getActiveUser();
        $esResponsable = isset($user['esresponsable']) && ($user['esresponsable'] === 't' || $user['esresponsable'] === true || $user['esresponsable'] === 1);

        if (!$user || $user['rol'] !== 'empleado' || !$esResponsable) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        if (empty($user['idarea_agencia'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes un área de agencia asignada.']);
        }

        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();
        $requerimientoModel = new \App\Models\RequerimientoModel();

        // Obtener todos los empleados del área
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia((int) $user['idarea_agencia']);

        $tareasPorEmpleado = [];

        foreach ($empleados as $empleado) {
            // Obtener tareas en proceso de este empleado
            $tareas = $atencionModel->obtenerTareasPorEmpleadoEstado($empleado['id'], 'en_proceso');

            $tareasDetalladas = [];
            foreach ($tareas as $tarea) {
                $detalle = $requerimientoModel->getDetalleCompleto($tarea['idrequerimiento']);
                if ($detalle) {
                    $tareaConDetalle = array_merge($tarea, $detalle);
                    // Agregar identificador único para mejor manejo en frontend
                    $tareaConDetalle['identificador_unico'] = 'EMP_' . $empleado['id'] . '_TAREA_' . $tarea['id'] . '_' . date('His');
                    $tareasDetalladas[] = $tareaConDetalle;
                }
            }

            // Ordenar por prioridad (alta > media > baja) y luego por fecha de asignación
            $prioridadOrden = ['alta' => 1, 'media' => 2, 'baja' => 3];
            usort($tareasDetalladas, function ($a, $b) use ($prioridadOrden) {
                $prioA = $prioridadOrden[strtolower($a['prioridad'] ?? 'media')] ?? 2;
                $prioB = $prioridadOrden[strtolower($b['prioridad'] ?? 'media')] ?? 2;

                if ($prioA != $prioB) {
                    return $prioA - $prioB;
                }

                // Si misma prioridad, ordenar por fecha de asignación (más reciente primero)
                $fechaA = strtotime($a['fechaasignacion'] ?? '1970-01-01');
                $fechaB = strtotime($b['fechaasignacion'] ?? '1970-01-01');
                return $fechaB - $fechaA;
            });

            $tareasPorEmpleado[] = [
                'id' => (int) $empleado['id'],
                'nombre' => $empleado['nombre'] ?? '',
                'apellidos' => $empleado['apellidos'] ?? '',
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
     * Asignar la Solicitud Requerimiento (Atencion) a un Empleado
     * @throws \RuntimeException
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function asignarPedido()
    {
        $user = $this->ValidarSesion_DatosUser();
        if (!$user['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $user['message']]);
        }
        $idAtencion = (int) $this->request->getPost('idatencion');
        $idUsuarioAsignado = (int) $this->request->getPost('idusuario_asignado');
        if ($idAtencion <= 0 || $idUsuarioAsignado <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos para asignación.']);
        }
        $idAreaAgencia = (int) $user['user']['idarea_agencia'];
        $idResponsable = (int) $user['user']['id'];
        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();
        $trackingModel = new TrackingModel();
        // Validar que la atención sí pertenece al área del responsable
        if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'La atención no pertenece a tu área.']);
        }
        // Validar que el usuario asignado sea empleado activo de la misma área
        $empleado = $usuarioModel->obtenerEmpleadoAsignable($idUsuarioAsignado, $idAreaAgencia);
        if (!$empleado) {
            return $this->response->setJSON(['success' => false, 'message' => 'El empleado no pertenece a tu área o está inactivo.']);
        }
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // 3) Asignar empleado y pasar a en_proceso
            $ok = $atencionModel->update($idAtencion, [
                'idempleado' => $idUsuarioAsignado,
                'estado' => 'en_proceso',
                'fechainicio' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);
            if (!$ok) {
                throw new \RuntimeException('No se pudo actualizar la atención.');
            }
            // 4) Tracking
            $trackingModel->insert([
                'idatencion' => $idAtencion,
                'idusuario' => $idResponsable,
                'accion' => "Proyecto en desarrollo.\nEl especialista " . trim($empleado['nombre'] . ' ' . $empleado['apellidos']) . " ha sido asignado para la elaboración de su requerimiento",
                'estado' => 'en_proceso',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);
            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Pedido asignado correctamente']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Error al asignar: ' . $e->getMessage()]);
        }
    }


    /**
     * Obtener detalles completos de un requerimiento para el responsable
     */
    public function obtenerDetalleRequerimiento()
    {
        $user = $this->ValidarSesion_DatosUser();
        if (!$user['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $user['message']]);
        }

        $idAtencion = (int) $this->request->getGet('id');
        if ($idAtencion <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de atención no válido']);
        }

        $atencionModel = new AtencionModel();
        $requerimientoModel = new RequerimientoModel();
        $archivoModel = new ArchivoModel();

        // 1. Validar que la atención pertenezca al área del responsable
        if (!$atencionModel->atencionPerteneceAArea($idAtencion, (int) $user['user']['idarea_agencia'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'El requerimiento no pertenece a tu área']);
        }

        // 2. Obtener la atención
        $atencion = $atencionModel->find($idAtencion);
        if (!$atencion) {
            return $this->response->setJSON(['success' => false, 'message' => 'Requerimiento no encontrado']);
        }

        // 3. Obtener detalles completos del requerimiento
        $detalle = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);
        if (!$detalle) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se encontraron detalles del requerimiento']);
        }

        // 4. Obtener archivos asociados
        $archivos = $archivoModel->where('idrequerimiento', $atencion['idrequerimiento'])->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $detalle,
            'archivos' => $archivos,
            'atencion' => $atencion
        ]);
    }

    private function ValidarSesion_DatosUser(): array
    {
        $user = $this->getActiveUser();
        $esResponsable = isset($user['esresponsable']) && ($user['esresponsable'] === 't' || $user['esresponsable'] === true || $user['esresponsable'] === 1);
        if (!$user || $user['rol'] !== 'empleado' || !$esResponsable) {
            return ['ok' => false, 'message' => 'Acceso denegado.'];
        }
        if (empty($user['idarea_agencia'])) {
            return ['ok' => false, 'message' => 'No tienes un área de agencia asignada.'];
        }
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);
        return [
            'ok' => true,
            'user' => $user,
            'userData' => $userData
        ];
    }
}