<?php

namespace App\Controllers\Responsable;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\TrackingModel;
use App\Models\RequerimientoModel;
use App\Models\ArchivoModel;
use App\Models\ServicioModel;
use App\Models\EmpresaModel;

class PedidosAreaController extends BaseResponsableController
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new \App\Services\PusherService();
    }

    /**
     * Muestra el Dashboard del Responsable con resúmenes estadísticos, 
     * gráficos de carga de trabajo por empleado y métricas de prioridad.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        // Validación de sesión y rol
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to('/auth/logout');
        }

        $user = $userS['user'];
        $userData = $userS['userData'];
        $idAreaAgencia = (int) $user['idarea_agencia'];

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();

        // Recolectar métricas generales
        $metrics = $this->_getMetrics($idAreaAgencia);

        // Obtener el equipo para calcular la carga de trabajo individual
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia);

        // Preparar datos para el gráfico de barras (Carga por técnico)
        $cargaEmpleados = [];
        foreach ($empleados as $emp) {
            $nombreCorto = explode(' ', $emp['nombre'])[0] ?? $emp['nombre'];

            // Tareas que ya tiene en manos (Iniciadas o Asignadas)
            $tareasActivas = $atencionModel->where('idempleado', $emp['id'])
                ->where('idarea_agencia', $idAreaAgencia)
                ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
                ->countAllResults();

            // Histórico de éxito del técnico
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

        // Métricas de Prioridad (Para el gráfico de pastel/pie)
        $prioridadAlta = $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('prioridad', 'Alta')->whereNotIn('estado', ['finalizado', 'cancelado'])->countAllResults();
        $prioridadMedia = $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('prioridad', 'Media')->whereNotIn('estado', ['finalizado', 'cancelado'])->countAllResults();
        $prioridadBaja = $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('prioridad', 'Baja')->whereNotIn('estado', ['finalizado', 'cancelado'])->countAllResults();

        // Totales calculados para porcentajes
        $totalActivo = $metrics['en_proceso'] + $metrics['pendientes_asignar'];
        $totalGeneral = max(1, $totalActivo + $metrics['enRevision'] + $metrics['completados']);

        // Datos adicionales para los filtros del reporte
        $empresaModel = new EmpresaModel();
        $servicioModel = new ServicioModel();

        $data = array_merge([
            'titulo' => 'Panel de Control - Gestión de Pedidos',
            'tituloPagina' => 'Resumen Operativo',
            'user' => $userData,
            'cargaEmpleados' => json_encode($cargaEmpleados),
            'prioridadAlta' => $prioridadAlta,
            'prioridadMedia' => $prioridadMedia,
            'prioridadBaja' => $prioridadBaja,
            'totalActivo' => $totalActivo,
            'totalGeneral' => $totalGeneral,
            'empresas' => $empresaModel->where('estado', true)->findAll(),
            'servicios' => $servicioModel->where('activo', true)->findAll(),
            'empleados' => $empleados,
        ], $metrics);

        return view('responsable/dashboard', $data);
    }

    /**
     * Endpoint: Recopila las 4 métricas de análisis del equipo,
     * para alimentar los gráficos del Dashboard del Responsable.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function getMetricasDashboard()
    {
        // Validación de sesión y rol
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            if (isset($userS['unauthorized']) && $userS['unauthorized'] === true) {
                return redirect()->back()->with('error', $userS['message']);
            }
            return redirect()->to(base_url('/'))->with('error', $userS['message']);
        }

        $idAreaAgencia = $userS['user']['idarea_agencia'];
        $atencion_model = new AtencionModel();

        //Metricas para Graficos
        $MProductividad = $atencion_model->getMetricasProductividad($idAreaAgencia);
        $MDistribucionCarga = $atencion_model->getMetricasDistribucionCarga($idAreaAgencia);
        $MTendenciasSemanal = $atencion_model->getMetricasTendenciaSemanal($idAreaAgencia);
        $MTiempoPromedio = $atencion_model->getMetricasTiempoPromedio($idAreaAgencia);

        return $this->response->setJSON([
            'success' => true,
            'productividad' => $MProductividad,
            'distribucion_carga' => $MDistribucionCarga,
            'tendencias_Semanal' => $MTendenciasSemanal,
            'tiempopromedio' => $MTiempoPromedio
        ]);

    }

    /**
     * Carga la vista principal de la bandeja de entrada
     * Los datos se cargan vía AJAX mediante el método bandeja().
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaBandeja()
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
            'titulo' => 'Bandeja de Entrada',
            'tituloPagina' => 'Solicitudes Pendientes',
            'user' => $userS['userData']
        ], $metrics);

        return view('responsable/bandeja', $data);
    }

    /**
     * Endpoint API JSON que devuelve la lista de requerimientos por asignar
     * y los requerimientos que están en revisión por parte del responsable.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function bandeja()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $atencionModel = new AtencionModel();
        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

        // Pedidos Pendientes (que el Admin ya aprobó pero el Responsable no ha delegado)
        $itemsPendientes = $atencionModel->obtenerBandejaResponsable($idAreaAgencia);

        // Pedidos terminados por técnicos que requieren check final
        $itemsRevision = $atencionModel->obtenerDetalladoPorArea($idAreaAgencia, ['en_revision']);

        return $this->response->setJSON([
            'success' => true,
            'area' => [
                'id' => $idAreaAgencia,
                'nombre' => $userS['userData']['nombre_areaagencia'] ?? 'Área no asignada',
            ],
            'total_pendientes' => count($itemsPendientes),
            'total_revision' => count($itemsRevision),
            'data' => $itemsPendientes,
            'data_revision' => $itemsRevision
        ]);
    }

    /**
     * Proceso crítico de delegación. Vincula un empleado
     * a una atención específica. Registra el hito en el tracking.
     * @throws \RuntimeException
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function asignarPedido()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $idAtencion = (int) $this->request->getPost('idatencion');
        $idUsuarioAsignado = (int) $this->request->getPost('idusuario_asignado');

        // Validación básica de parámetros
        if ($idAtencion <= 0 || $idUsuarioAsignado <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos para la asignación.']);
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();
        $trackingModel = new TrackingModel();

        // Verificar que el pedido pertenezca al área del responsable
        if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Seguridad: Este pedido no pertenece a tu área.']);
        }

        // Verificar que el técnico sea realmente parte de su equipo
        $empleado = $usuarioModel->obtenerEmpleadoAsignable($idUsuarioAsignado, $idAreaAgencia);
        if (!$empleado) {
            return $this->response->setJSON(['success' => false, 'message' => 'El técnico seleccionado no es válido para esta asignación.']);
        }

        // Transacción para asegurar integridad de datos
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Actualizar la atención con el técnico asignado
            $ok = $atencionModel->update($idAtencion, [
                'idempleado' => $idUsuarioAsignado,
            ]);

            if (!$ok)
                throw new \RuntimeException('Error al actualizar el registro de atención.');

            // Insertar entrada en el historial de seguimiento (Tracking)
            $trackingModel->insert([
                'idatencion' => $idAtencion,
                'idusuario' => $userS['user']['id'],
                'accion' => "Su Solicitud fue delegado.\n Estado Pendiente de inicio por parte del Empleado.",
                'estado' => 'pendiente_asignado',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            $db->transCommit();
            $this->pusher->notificarCambioEstado($idAtencion, 'pendiente_asignado');
            return $this->response->setJSON(['success' => true, 'message' => '¡Delegación exitosa! El técnico ha sido notificado en su bandeja.']);

        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Error técnico al asignar: ' . $e->getMessage()]);
        }
    }

    /**
     * Permite al responsable corregir o ampliar la información enviada por el cliente.
     * Útil cuando el "brief" original está incompleto o requiere ajustes técnicos.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function actualizarRequerimiento()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok'])
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $idRequerimiento = (int) $this->request->getPost('idrequerimiento');

        $requerimientoModel = new RequerimientoModel();
        $atencionModel = new AtencionModel();

        // Validar existencia y pertenencia
        $req = $requerimientoModel->find($idRequerimiento);
        if (!$req)
            return $this->response->setJSON(['success' => false, 'message' => 'El requerimiento no existe.']);

        $atencion = $atencionModel->where('idrequerimiento', $idRequerimiento)->first();
        if (!$atencion || !$atencionModel->atencionPerteneceAArea($atencion['id'], $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos para editar este requerimiento.']);
        }

        // Restricción Solo el asignado puede editar
        if ($atencion['idempleado'] != $userS['user']['id']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Seguridad: Solo el especialista asignado a este pedido puede modificar sus detalles técnicos.']);
        }

        // Mapeo de datos del formulario
        $data = [
            'idservicio' => (int) $this->request->getPost('idservicio'),
            'tipo_requerimiento' => $this->request->getPost('tipo_requerimiento'),
            'titulo' => $this->request->getPost('titulo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'fecharequerida' => $this->request->getPost('fecharequerida') ?: null,
            'objetivo_comunicacion' => $this->request->getPost('objetivo_comunicacion'),
            'publico_objetivo' => $this->request->getPost('publico_objetivo'),
            'canales_difusion' => $this->request->getPost('canales_difusion'),
            'formatos_solicitados' => $this->request->getPost('formatos_solicitados'),
            'url_subida' => $this->request->getPost('url_subida'),
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        // Actualizar tabla principal de Requerimiento
        $requerimientoModel->update($idRequerimiento, $data);

        // Actualizar también la tabla Atencion para que las vistas de listas (Dashboard, Tablas) reflejen el cambio
        $atencionModel->update($atencion['id'], [
            'titulo' => $data['titulo'],
            'idservicio' => $data['idservicio']
        ]);

        // Gestión de archivos adicionales subidos por el responsable (material de apoyo)
        $archivosSubidos = $this->request->getFiles();
        if (!empty($archivosSubidos['archivos_responsable'])) {
            $archivoModel = new ArchivoModel();
            $uploadPath = FCPATH . 'uploads/requerimientos/' . $idRequerimiento . '/';
            if (!is_dir($uploadPath))
                mkdir($uploadPath, 0755, true);

            foreach ($archivosSubidos['archivos_responsable'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $nombreGuardado = $file->getRandomName();
                    $file->move($uploadPath, $nombreGuardado);
                    $archivoModel->insert([
                        'idatencion' => $atencion['id'],
                        'idrequerimiento' => $idRequerimiento,
                        'nombre' => $file->getClientName(),
                        'ruta' => 'uploads/requerimientos/' . $idRequerimiento . '/' . $nombreGuardado,
                        'tipo' => $file->getClientMimeType(),
                        'tamano' => $file->getSize(),
                    ]);
                }
            }
        }

        // Dejar constancia en el tracking de que hubo una edición
        $trackingModel = new TrackingModel();
        $trackingModel->insert([
            'idatencion' => $atencion['id'],
            'idusuario' => $userS['user']['id'],
            'accion' => "El responsable ha actualizado los detalles técnicos del requerimiento.",
            'estado' => $atencion['estado'],
            'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ]);

        $db->transComplete();
        if ($db->transStatus() === false)
            return $this->response->setJSON(['success' => false, 'message' => 'Error crítico al intentar guardar los cambios.']);

        $this->pusher->notificarCambioEstado($atencion['id'], $atencion['estado']);
        return $this->response->setJSON(['success' => true, 'message' => '¡Información actualizada! Los cambios serán visibles para el técnico asignado.']);
    }

    /**
     * Endpoint API que construye todo de un pedido, uniendo datos de
     * atencion, requerimiento, archivos adjuntos y todo el historial de tracking.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function obtenerDetalleRequerimiento()
    {
        try {
            $userS = $this->ValidarSesion_DatosUser();
            if (!$userS['ok'])
                return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);

            $idAtencion = (int) $this->request->getGet('id');
            if ($idAtencion <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'ID de atención inválido.']);
            }

            $atencionModel = new AtencionModel();
            $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

            if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No tienes acceso a los detalles de este pedido.']);
            }

            // Unión de datos desde múltiples fuentes
            $atencion = $atencionModel->find($idAtencion);
            if (!$atencion) {
                return $this->response->setJSON(['success' => false, 'message' => 'Atención no encontrada.']);
            }

            $requerimientoModel = new RequerimientoModel();
            $detalle = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);

            if (!$detalle) {
                return $this->response->setJSON(['success' => false, 'message' => 'Detalles del requerimiento no encontrados.']);
            }

            $usuarioModel = new UsuarioModel();
            $empleadoAsignado = !empty($atencion['idempleado']) ? $usuarioModel->find($atencion['idempleado']) : null;

            $archivoModel = new ArchivoModel();
            $archivos = $archivoModel->where('idrequerimiento', $atencion['idrequerimiento'])->findAll();

            $trackingModel = new TrackingModel();
            $tracking = $trackingModel->where('idatencion', $idAtencion)->orderBy('fecha_registro', 'DESC')->findAll();

            // Formatear respuesta amigable para el Frontend
            // Aseguramos que el 'id' final sea el de la atención, no el del requerimiento
            $dataCompleta = array_merge((array) $detalle, (array) $atencion, [
                'idatencion' => $atencion['id'],
                'idrequerimiento' => $atencion['idrequerimiento'],
                'empleado_asignado' => $empleadoAsignado,
                'empleado_nombre' => $empleadoAsignado ? trim($empleadoAsignado['nombre'] . ' ' . $empleadoAsignado['apellidos']) : '---',
                'servicio' => $detalle['nombre_servicio'] ?? $detalle['servicio_personalizado'] ?? 'N/A',
                'nombre_cliente' => trim(($detalle['nombre_cliente'] ?? '') . ' ' . ($detalle['apellidos_cliente'] ?? '')),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'data' => $dataCompleta,
                'archivos' => $archivos,
                'tracking' => $tracking
            ]);
        } catch (\Throwable $th) {
            log_message('error', '[obtenerDetalleRequerimiento] ' . $th->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno al procesar el expediente: ' . $th->getMessage()
            ]);
        }
    }

    /**
     * Accion de inicio de trabajo. Cambia el estado de 'pendiente_asignado' a 'en_proceso'.
     * Usado cuando el responsable se auto-asigna tareas o fuerza el inicio.
     * @param mixed $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function iniciarPedido($id)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok'])
            return $this->response->setJSON(['status' => 'error', 'message' => $userS['message']]);

        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);

        // Validar si es el empleado asignado O si es el responsable del área del pedido
        // Agregamos [DEBUG] para entender exactamente por qué falla si hay conflicto de credenciales
        $idUsuarioSesion = (int) $userS['user']['id'];
        $idAreaSesion = (int) $userS['user']['idarea_agencia'];
        $idAreaPedido = (int) $pedido['idarea_agencia'];
        $idEmpleadoPedido = (int) $pedido['idempleado'];

        $esAsignado = ($idEmpleadoPedido === $idUsuarioSesion);
        $esJefeDelArea = ($idAreaPedido === $idAreaSesion);

        if (!$pedido || (!$esAsignado && !$esJefeDelArea)) {
            $debugInfo = "SesionID: $idUsuarioSesion, AreaSesion: $idAreaSesion | Pedido_EmpID: $idEmpleadoPedido, AreaPedido: $idAreaPedido";
            return $this->response->setJSON(['status' => 'error', 'message' => "Conflicto de Identidad. No tienes permisos. [$debugInfo]"]);
        }

        $data = [
            'estado' => 'en_proceso',
            'fechainicio' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ];

        if ($atencionModel->update($id, $data)) {
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $userS['user']['id'],
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => $data['fechainicio']
            ]);

            $this->pusher->notificarCambioEstado($id, 'en_proceso');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Cronómetro iniciado. ¡A trabajar!']);
            
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Error al intentar iniciar el cronómetro.']);
    }

    /**
     * Proceso de finalización técnica. Sube archivos de entrega o links
     * y pone el pedido en estado 'en_revision' para que el administrador lo valide finalmente.
     * @param mixed $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function entregarPedido($id)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok'])
            return $this->response->setJSON(['status' => 'error', 'message' => $userS['message']]);

        $url_entrega = $this->request->getPost('url_entrega');
        $notas = $this->request->getPost('notas');
        $archivosSubidos = $this->request->getFiles();

        // Validacion una prueba de entrega (Link o Archivo)
        $hasFiles = !empty($archivosSubidos['archivos_entrega']) && count(array_filter($archivosSubidos['archivos_entrega'], fn($f) => $f->isValid()));

        if (empty($url_entrega) && !$hasFiles) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Debes proporcionar al menos un link o un archivo como evidencia de entrega.']);
        }

        $atencionModel = new AtencionModel();
        $archivoModel = new ArchivoModel();
        $trackingModel = new TrackingModel();

        $atencion = $atencionModel->find($id);
        if (!$atencion)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no localizado en el sistema.']);

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Actualizar estado y metadatos de entrega
            $dataUpdate = [
                'estado' => 'en_revision',
                'url_entrega' => $url_entrega ?: null,
                'observacion_revision' => $notas ?: null
            ];
            $atencionModel->update($id, $dataUpdate);

            // Guardar archivos físicos en carpeta de entregas
            if ($hasFiles) {
                $idReq = $atencion['idrequerimiento'];
                $uploadPath = FCPATH . "uploads/requerimientos/{$idReq}/entrega/";
                if (!is_dir($uploadPath))
                    mkdir($uploadPath, 0755, true);

                foreach ($archivosSubidos['archivos_entrega'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move($uploadPath, $newName);
                        $archivoModel->insert([
                            'idatencion' => $id,
                            'idrequerimiento' => $idReq,
                            'nombre' => $file->getClientName(),
                            'ruta' => "uploads/requerimientos/{$idReq}/entrega/{$newName}",
                            'tipo' => $file->getClientMimeType(),
                            'tamano' => $file->getSize(),
                        ]);
                    }
                }
            }

            // Registrar el cierre en el Tracking
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $userS['user']['id'],
                'accion' => "¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado." . ($notas ? "\nComentario adjunto: $notas" : ""),
                'estado' => 'en_revision',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            $db->transCommit();
            $this->pusher->notificarCambioEstado($id, 'en_revision');
            return $this->response->setJSON(['status' => 'success', 'message' => '¡Excelente! El pedido ha sido enviado a revisión final por administración.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error crítico al procesar la entrega: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista todos los servicios activos disponibles en el sistema.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listarServicios()
    {
        $servicioModel = new ServicioModel();
        $servicios = $servicioModel->where('activo', true)->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $servicios]);
    }

    /**
     * Sirve el archivo físico para vista previa en el navegador.
     * Valida permisos básicos y que el archivo exista en disco.
     * @param int $idArchivo
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaPrevia($idArchivo)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Sesión expirada o inválida.']);
        }

        $archivoModel = new ArchivoModel();
        $archivo = $archivoModel->find($idArchivo);

        if (!$archivo) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'El archivo no existe en la base de datos.']);
        }

        // Construir la ruta absoluta usando FCPATH
        $rutaAbsoluta = FCPATH . $archivo['ruta'];

        if (!file_exists($rutaAbsoluta)) {
            // Reintentar si la ruta en DB no tiene el prefijo de FCPATH pero es relativa
            if (!is_file($rutaAbsoluta)) {
                return $this->response->setStatusCode(404)->setJSON(['message' => 'El archivo físico no se encuentra en el servidor.']);
            }
        }

        // Obtener el tipo MIME para servirlo correctamente
        $mimeType = $archivo['tipo'] ?? 'application/octet-stream';

        // Servir el archivo directamente al navegador para previsualización (inline)
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $archivo['nombre'] . '"')
            ->setHeader('Cache-Control', 'max-age=3600')
            ->setBody(file_get_contents($rutaAbsoluta));
    }

    /**
     * Endpoint API: Cuenta las notificaciones del responsable (bandeja, en proceso, retroalimentación).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function contarNotificaciones()
    {
        // Validación rápida de sesión
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => $auth['message']])->setStatusCode(401);
        }

        $idAreaAgencia = (int) $auth['user']['idarea_agencia'];
        $metrics = $this->_getMetrics($idAreaAgencia);

        return $this->response->setJSON([
            'status' => 'success',
            'pendientes_asignar' => $metrics['pendientes_asignar'],
            'en_proceso' => $metrics['en_proceso'],
            'devoluciones' => $metrics['devoluciones']
        ]);
    }
}