<?php

namespace App\Controllers\Responsable;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\TrackingModel;
use App\Models\RequerimientoModel;
use App\Models\ArchivoModel;
use App\Models\ServicioModel;

class PedidosAreaController extends BaseResponsableController
{
    /**
     * Muestra el Dashboard del Responsable con resúmenes estadísticos, 
     * gráficos de carga de trabajo por empleado y métricas de prioridad.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        // Validación de sesión y rol
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok'])
            return redirect()->to('login');

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
        ], $metrics);

        return view('Responsable/dashboard', $data);
    }

    /**
     * Carga la vista principal de la bandeja de entrada
     * Los datos se cargan vía AJAX mediante el método bandeja().
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaBandeja()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok'])
            return redirect()->to('login');

        $metrics = $this->_getMetrics((int) $userS['user']['idarea_agencia']);

        $data = array_merge([
            'titulo' => 'Bandeja de Entrada',
            'tituloPagina' => 'Solicitudes Pendientes',
            'user' => $userS['userData']
        ], $metrics);

        return view('Responsable/bandeja', $data);
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
                'accion' => "Pedido delegado al técnico: " . trim($empleado['nombre'] . ' ' . $empleado['apellidos']) . ". Pendiente de inicio por parte del especialista.",
                'estado' => 'pendiente_asignado',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            $db->transCommit();
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

        return $this->response->setJSON(['success' => true, 'message' => '¡Información actualizada! Los cambios serán visibles para el técnico asignado.']);
    }

    /**
     * Endpoint API que construye el "Full Profile" de un pedido, uniendo datos de
     * atencion, requerimiento, archivos adjuntos y todo el historial de tracking.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function obtenerDetalleRequerimiento()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok'])
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);

        $idAtencion = (int) $this->request->getGet('id');
        $atencionModel = new AtencionModel();
        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

        if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes acceso a los detalles de este pedido.']);
        }

        // Unión de datos desde múltiples fuentes
        $atencion = $atencionModel->find($idAtencion);
        $requerimientoModel = new RequerimientoModel();
        $detalle = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);

        $usuarioModel = new UsuarioModel();
        $empleadoAsignado = !empty($atencion['idempleado']) ? $usuarioModel->find($atencion['idempleado']) : null;

        $archivoModel = new ArchivoModel();
        $archivos = $archivoModel->where('idrequerimiento', $atencion['idrequerimiento'])->findAll();

        $trackingModel = new TrackingModel();
        $tracking = $trackingModel->where('idatencion', $idAtencion)->orderBy('fecha_registro', 'DESC')->findAll();

        // Formatear respuesta amigable para el Frontend
        $dataCompleta = array_merge($atencion, $detalle, [
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

        // Solo el empleado asignado puede iniciar su propio trabajo (o el responsable si es el ejecutor)
        if (!$pedido || $pedido['idempleado'] != $userS['user']['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Solo el especialista asignado puede marcar el inicio de este trabajo.']);
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
                'accion' => '¡Trabajo iniciado! El especialista ha comenzado con la ejecución.',
                'estado' => 'en_proceso',
                'fecha_registro' => $data['fechainicio']
            ]);
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
            return $this->response->setJSON(['status' => 'success', 'message' => '¡Excelente! El pedido ha sido enviado a revisión final por administración.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error crítico al procesar la entrega: ' . $e->getMessage()]);
        }
    }
}