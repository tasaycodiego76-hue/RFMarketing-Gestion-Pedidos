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
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');
        
        $user = $userS['user'];
        $userData = $userS['userData'];
        $idAreaAgencia = (int) $user['idarea_agencia'];

        //Usar el Modelo para traer la información completa
        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();

        // Obtener métricas para el dashboard
        $metrics = $this->_getMetrics($idAreaAgencia);

        // Contar miembros del equipo
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia);

        // ── Datos para gráficos ──

        // 1. Carga de trabajo por empleado
        $cargaEmpleados = [];
        foreach ($empleados as $emp) {
            $nombreCorto = explode(' ', $emp['nombre'])[0] ?? $emp['nombre'];
            $tareasActivas = $atencionModel->where('idempleado', $emp['id'])
                ->where('idarea_agencia', $idAreaAgencia)
                ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
                ->countAllResults();
            $tareasCompletadas = $atencionModel->where('idempleado', $emp['id'])
                ->where('idarea_agencia', $idAreaAgencia)
                ->where('estado', 'finalizado')
                ->countAllResults();
            $cargaEmpleados[] = [
                'nombre' => $nombreCorto,
                'activas' => $tareasActivas,
                'completadas' => $tareasCompletadas,
            ];
        }

        // 2. Distribución por prioridad
        $prioridadAlta = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('prioridad', 'Alta')
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->countAllResults();
        $prioridadMedia = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('prioridad', 'Media')
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->countAllResults();
        $prioridadBaja = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->where('prioridad', 'Baja')
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->countAllResults();

        // 3. Totales para estado general (doughnut)
        $totalActivo = $metrics['en_proceso'] + $metrics['pendientes_asignar'];
        $totalGeneral = max(1, $totalActivo + $metrics['enRevision'] + $metrics['completados']);

        $data = array_merge([
            'titulo' => 'Mis Pedidos - Area',
            'tituloPagina' => 'Dashboard',
            'user' => $userData,
            // Datos para gráficos
            'cargaEmpleados' => json_encode($cargaEmpleados),
            'prioridadAlta' => $prioridadAlta,
            'prioridadMedia' => $prioridadMedia,
            'prioridadBaja' => $prioridadBaja,
            'totalActivo' => $totalActivo,
            'totalGeneral' => $totalGeneral,
        ], $metrics);

        return view('Responsable/dashboard', $data);
    }

    /**
     * Helper para métricas comunes del Responsable
     */
    private function _getMetrics($idAreaAgencia)
    {
        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();

        return [
            'pendientes_asignar' => count($atencionModel->obtenerBandejaResponsable($idAreaAgencia)),
            'en_proceso' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'en_proceso')->countAllResults(),
            'enRevision' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'en_revision')->countAllResults(),
            'completados' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'finalizado')->countAllResults(),
            'totalMiembros' => count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia)),
            'devoluciones' => count($atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia))
        ];
    }

    /**
     * Renderiza la vista de Bandeja de Entrada
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaBandeja()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $metrics = $this->_getMetrics((int) $userS['user']['idarea_agencia']);

        $data = array_merge([
            'titulo' => 'Bandeja de Entrada',
            'tituloPagina' => 'Bandeja de Entrada',
            'user' => $userS['userData']
        ], $metrics);

        return view('Responsable/bandeja', $data);
    }

    /**
     * Renderiza la vista de Mi Equipo
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaEquipo()
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
     * Renderiza la vista de Tareas En Proceso
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaTareasEnProceso()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);

        // Obtener datos estáticos de empleados para la vista
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
     * Obtener tareas de un empleado específico
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function tareasPorEmpleado($idEmpleado = null)
    {
        $user = $this->getActiveUser();
        $esResponsable = isset($user['esresponsable']) && ($user['esresponsable'] === 't' || $user['esresponsable'] === true || $user['esresponsable'] === 1);

        if (!$user || $user['rol'] !== 'empleado' || !$esResponsable) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        if (empty($user['idarea_agencia'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes un área de agencia asignada.']);
        }

        if ($idEmpleado === null || $idEmpleado <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de empleado no válido']);
        }

        $atencionModel = new AtencionModel();
        $requerimientoModel = new RequerimientoModel();

        // Obtener tareas en proceso de este empleado
        $tareas = $atencionModel->obtenerTareasPorEmpleadoEstado($idEmpleado, 'en_proceso');

        $tareasDetalladas = [];
        foreach ($tareas as $tarea) {
            $detalle = $requerimientoModel->getDetalleCompleto($tarea['idrequerimiento']);
            if ($detalle) {
                $tareaConDetalle = array_merge($tarea, $detalle);
                // Agregar identificador único
                $tareaConDetalle['identificador_unico'] = 'EMP_' . $idEmpleado . '_TAREA_' . $tarea['id'] . '_' . date('His');
                $tareasDetalladas[] = $tareaConDetalle;
            }
        }

        // Ordenar por prioridad y fecha
        $prioridadOrden = ['alta' => 1, 'media' => 2, 'baja' => 3];
        usort($tareasDetalladas, function ($a, $b) use ($prioridadOrden) {
            $prioA = $prioridadOrden[strtolower($a['prioridad'] ?? 'media')] ?? 2;
            $prioB = $prioridadOrden[strtolower($b['prioridad'] ?? 'media')] ?? 2;

            if ($prioA != $prioB) {
                return $prioA - $prioB;
            }

            $fechaA = strtotime($a['fechaasignacion'] ?? '1970-01-01');
            $fechaB = strtotime($b['fechaasignacion'] ?? '1970-01-01');
            return $fechaB - $fechaA;
        });

        return $this->response->setJSON([
            'success' => true,
            'data' => $tareasDetalladas,
            'total_tareas' => count($tareasDetalladas)
        ]);
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
        $idAreaAgencia = (int) $user['user']['idarea_agencia'];

        // Items por asignar (pendientes)
        $itemsPendientes = $atencionModel->obtenerBandejaResponsable($idAreaAgencia);

        // Items en revisión
        $itemsRevision = $atencionModel->obtenerDetalladoPorArea($idAreaAgencia, ['en_revision']);

        return $this->response->setJSON([
            'success' => true,
            'area' => [
                'id' => $idAreaAgencia,
                'nombre' => $user['userData']['nombre_areaagencia'] ?? 'Área no asignada',
            ],
            'total_pendientes' => count($itemsPendientes),
            'total_revision' => count($itemsRevision),
            'data' => $itemsPendientes,
            'data_revision' => $itemsRevision
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
        $requerimientoModel = new RequerimientoModel();

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

    public function actualizarRequerimiento()
    {
        $userSession = $this->ValidarSesion_DatosUser();
        if (!$userSession['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $user = $userSession['user'];
        $idAreaAgencia = (int) $user['idarea_agencia'];
        $idRequerimiento = (int) $this->request->getPost('idrequerimiento');

        $requerimientoModel = new RequerimientoModel();
        $atencionModel = new AtencionModel();
        
        $req = $requerimientoModel->find($idRequerimiento);

        if (!$req) {
            return $this->response->setJSON(['success' => false, 'message' => 'Requerimiento no encontrado']);
        }

        // Validar que la atención pertenezca al área
        $atencion = $atencionModel->where('idrequerimiento', $idRequerimiento)->first();
        if (!$atencion || !$atencionModel->atencionPerteneceAArea($atencion['id'], $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tiene permiso para editar este requerimiento']);
        }

        // Solo permitir editar si es Creación de Contenido
        $servicioModel = new \App\Models\ServicioModel();
        $servicio = $servicioModel->find($req['idservicio']);
        
        if (!$servicio || $servicio['nombre'] !== 'Creación de Contenido') {
             // return $this->response->setJSON(['success' => false, 'message' => 'Solo se permite editar requerimientos de Creación de Contenido']);
        }

        // Campos a actualizar en requerimiento
        $data = [
            'titulo'                => $this->request->getPost('titulo'),
            'descripcion'           => $this->request->getPost('descripcion'),
            'fecharequerida'        => $this->request->getPost('fecharequerida') ?: null,
            'objetivo_comunicacion' => $this->request->getPost('objetivo_comunicacion'),
            'publico_objetivo'      => $this->request->getPost('publico_objetivo'),
            'canales_difusion'      => $this->request->getPost('canales_difusion'),
            'formatos_solicitados'  => $this->request->getPost('formatos_solicitados'),
            'url_subida'            => $this->request->getPost('url_subida'),
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $requerimientoModel->update($idRequerimiento, $data);

        // Manejo de archivos adjuntos adicionales subidos por el responsable
        $archivosSubidos = $this->request->getFiles();
        if (!empty($archivosSubidos['archivos_responsable'])) {
            $archivoModel = new ArchivoModel();
            $uploadPath = WRITEPATH . 'uploads/requerimientos/' . $idRequerimiento . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($archivosSubidos['archivos_responsable'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $tipoMime = $file->getClientMimeType();
                    $nombreOriginal = $file->getClientName();
                    $nombreGuardado = $file->getRandomName();
                    $file->move($uploadPath, $nombreGuardado);
                    $archivoModel->insert([
                        'idatencion'      => $atencion['id'],
                        'idrequerimiento' => $idRequerimiento,
                        'nombre'          => $nombreOriginal,
                        'ruta'            => 'uploads/requerimientos/' . $idRequerimiento . '/' . $nombreGuardado,
                        'tipo'            => $tipoMime,
                        'tamano'          => $file->getSize(),
                    ]);
                }
            }
        }

        // Registrar tracking del cambio
        $trackingModel = new \App\Models\TrackingModel();
        $trackingModel->insert([
            'idatencion' => $atencion['id'],
            'idusuario' => $user['id'],
            'accion' => "Se actualizaron los detalles del requerimiento (Edición operativa).",
            'estado' => $atencion['estado'],
            'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar los cambios']);
        }

        return $this->response->setJSON(['success' => true, 'message' => '¡Requerimiento actualizado correctamente!']);
    }

    /**
     * Obtener detalles completos de un requerimiento para el responsable
     * @return \CodeIgniter\HTTP\ResponseInterface
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
        $usuarioModel = new UsuarioModel();

        // Validar que la atención pertenezca al área del responsable
        $idAreaAgencia = (int) $user['user']['idarea_agencia'];
        if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'La atención no pertenece a tu área.']);
        }

        // Obtener datos de la atencion
        $atencion = $atencionModel->find($idAtencion);
        if (!$atencion) {
            return $this->response->setJSON(['success' => false, 'message' => 'Atención no encontrada']);
        }

        // Obtener detalles completos del requerimiento (ya incluye info del cliente y empresa)
        $detalle = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);
        if (!$detalle) {
            return $this->response->setJSON(['success' => false, 'message' => 'Requerimiento no encontrado']);
        }

        // Obtener información del empleado asignado
        $empleadoAsignado = null;
        if (!empty($atencion['idempleado'])) {
            $empleadoAsignado = $usuarioModel->find($atencion['idempleado']);
        }

        // Obtener archivos
        $archivos = $archivoModel->where('idrequerimiento', $atencion['idrequerimiento'])->findAll();

        // Agregar nombre_original y URL de vista previa a los archivos
        foreach ($archivos as &$archivo) {
            $archivo['nombre_original'] = $archivo['nombre'] ?? 'archivo';
            $archivo['url_vista_previa'] = base_url('responsable/archivos/vista-previa/' . $archivo['id']);
        }

        // Obtener tracking completo
        $trackingModel = new TrackingModel();
        $tracking = $trackingModel->where('idatencion', $idAtencion)->orderBy('fecha_registro', 'DESC')->findAll();

        // Combinar toda la información
        $dataCompleta = array_merge($atencion, $detalle, [
            'empleado_asignado' => $empleadoAsignado,
            'empleado_nombre' => $empleadoAsignado ? trim($empleadoAsignado['nombre'] . ' ' . $empleadoAsignado['apellidos']) : '---',
            'servicio' => $detalle['nombre_servicio'] ?? $detalle['servicio_personalizado'] ?? 'N/A',
            'nombre_servicio' => $detalle['nombre_servicio'] ?? $detalle['servicio_personalizado'] ?? 'N/A',
            'fecha_formateada' => $this->formatearFecha($atencion['fechacreacion'] ?? null),
            'fecha_inicio_formateada' => $this->formatearFecha($atencion['fechainicio'] ?? null),
            'fecha_requerida_formateada' => $this->formatearFecha($detalle['fecharequerida'] ?? null),
            // Información del cliente (viene del JOIN en getDetalleCompleto)
            'nombre_cliente' => trim(($detalle['nombre_cliente'] ?? '') . ' ' . ($detalle['apellidos_cliente'] ?? '')),
            'telefono_cliente' => $detalle['telefono_cliente'] ?? 'N/A',
            'correo_cliente' => $detalle['correo_cliente'] ?? 'N/A',
            'nombre_empresa' => $detalle['nombre_empresa'] ?? 'N/A',
            // Tipo de requerimiento
            'tipo_requerimiento' => $detalle['tipo_requerimiento'] ?? 'N/A'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'data' => $dataCompleta,
            'archivos' => $archivos,
            'tracking' => $tracking,
            'total_archivos' => count($archivos),
            'total_tracking' => count($tracking)
        ]);
    }

    /**
     * Formatear fecha para visualización
     */
    private function formatearFecha($fecha)
    {
        if (!$fecha)
            return null;

        try {
            $date = new \DateTime($fecha);
            return $date->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return $fecha;
        }
    }

    /**
     * Calcular días transcurridos
     */
    private function calcularDiasTranscurridos($fechaCreacion)
    {
        if (!$fechaCreacion)
            return null;

        try {
            $creacion = new \DateTime($fechaCreacion);
            $hoy = new \DateTime();
            return $hoy->diff($creacion)->days;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calcular días restantes
     */
    private function calcularDiasRestantes($fechaLimite)
    {
        if (!$fechaLimite)
            return null;

        try {
            $limite = new \DateTime($fechaLimite);
            $hoy = new \DateTime();

            if ($hoy > $limite) {
                return -($hoy->diff($limite)->days); // Negativo si pasó la fecha
            }

            return $hoy->diff($limite)->days;
        } catch (\Exception $e) {
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // NUEVOS MÉTODOS PARA EL RESPONSABLE (FLUJO DE EMPLEADO)
    // -------------------------------------------------------------------------


    /**
     * Vista de Retroalimentación (Pedidos devueltos)
     */
    public function vistaRetroalimentacion()
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

    /**
     * Historial de tareas (Propias y del Área)
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

    public function iniciarPedido($id)
    {
        $userSession = $this->ValidarSesion_DatosUser();
        if (!$userSession['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $user = $userSession['user'];
        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);

        if (!$pedido || $pedido['idempleado'] != $user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado o no asignado a ti']);
        }

        $data = [
            'estado' => 'en_proceso',
            'fechainicio' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ];

        if ($atencionModel->update($id, $data)) {
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $user['id'],
                'accion' => 'Trabajo iniciado por el responsable',
                'estado' => 'en_proceso',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => '¡Trabajo iniciado correctamente!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo actualizar el estado']);
    }

    public function entregarPedido($id)
    {
        $userSession = $this->ValidarSesion_DatosUser();
        if (!$userSession['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $user = $userSession['user'];
        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);

        if (!$pedido || $pedido['idempleado'] != $user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado']);
        }

        $link = $this->request->getPost('url_entrega');
        $notas = $this->request->getPost('notas');

        $data = [
            'estado' => 'en_revision',
            'url_entrega' => $link,
            'observacion_revision' => $notas
        ];

        if ($atencionModel->update($id, $data)) {
            // Limpiar archivos anteriores si hay una revisión
            $this->limpiarArchivosAnteriores((int)$id);

            // Guardar archivos nuevos si existen
            $this->guardarArchivosEntrega((int)$id, (int)$pedido['idrequerimiento']);

            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $user['id'],
                'accion' => "En fase de revisión de calidad.\nEl entregable ha sido completado y se encuentra en proceso de validación por nuestro equipo supervisor.",
                'estado' => 'en_revision',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => '¡Entrega realizada con éxito!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Error al procesar la entrega']);
    }

    /**
     * Guarda los archivos adjuntos de la entrega (Adaptado de Empleado)
     */
    private function guardarArchivosEntrega(int $idAtn, int $idReq): void
    {
        $archivos = $this->request->getFiles();

        if (empty($archivos['archivos_entrega'])) {
            return;
        }

        $archivoModel = new ArchivoModel();
        $carpeta = FCPATH . 'uploads/entregables';

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        foreach ($archivos['archivos_entrega'] as $file) {
            if (!$file->isValid() || $file->hasMoved()) {
                continue;
            }

            try {
                $nombreNuevo = $file->getRandomName();
                $file->move($carpeta, $nombreNuevo);

                $archivoModel->insert([
                    'idrequerimiento' => $idReq,
                    'idatencion' => $idAtn,
                    'nombre' => $file->getClientName(),
                    'ruta' => 'uploads/entregables/' . $nombreNuevo,
                    'tipo' => $file->getClientMimeType(),
                    'tamano' => $file->getSize(),
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error al guardar archivo entrega: ' . $e->getMessage());
            }
        }
    }

    /**
     * Limpia archivos anteriores de una atención cuando se vuelve a entregar
     */
    private function limpiarArchivosAnteriores(int $idAtencion): void
    {
        $archivoModel = new ArchivoModel();
        $archivosAnteriores = $archivoModel->where('idatencion', $idAtencion)->findAll();

        foreach ($archivosAnteriores as $archivo) {
            $rutaCompleta = FCPATH . $archivo['ruta'];
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
            $archivoModel->delete($archivo['id']);
        }
    }

    /**
     * Servir archivo para vista previa en nueva pestaña del navegador
     * Soporta imágenes (jpg, png, gif, webp, svg) y PDFs inline
     */
    public function vistaPrevia($idArchivo)
    {
        $archivoModel = new ArchivoModel();
        $archivo = $archivoModel->find($idArchivo);

        if (!$archivo) {
            return $this->response->setStatusCode(404)->setBody('Archivo no encontrado');
        }

        $nombreArchivo = $archivo['nombre'] ?? 'archivo';
        $ruta = $archivo['ruta'] ?? '';
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Mapeo de extensiones a MIME types
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'pdf'  => 'application/pdf',
        ];

        // Buscar el archivo en las rutas posibles
        $rutasPosibles = [
            FCPATH . $ruta,
            FCPATH . 'uploads/' . $ruta,
            FCPATH . 'uploads/materiales-referencia/' . $ruta,
            WRITEPATH . 'uploads/' . $ruta,
            FCPATH . 'uploads/' . $nombreArchivo,
        ];

        $rutaArchivo = null;
        foreach ($rutasPosibles as $posible) {
            if (file_exists($posible)) {
                $rutaArchivo = $posible;
                break;
            }
        }

        if (!$rutaArchivo) {
            return $this->response->setStatusCode(404)->setBody('Archivo no encontrado en el servidor');
        }

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Servir inline para que el navegador lo muestre directamente
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"')
            ->setHeader('Content-Length', (string) filesize($rutaArchivo))
            ->setHeader('Cache-Control', 'no-cache')
            ->setBody(file_get_contents($rutaArchivo));
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