<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;
use App\Models\ArchivoModel;
use App\Models\AtencionModel;
use App\Models\RetroalimentacionModel;
use App\Models\SesionesTrabajosModel;
use App\Models\TrackingModel;
use App\Models\UsuarioModel;

class MisPedidosController extends BaseController
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new \App\Services\PusherService();
    }

    /**
     * Endpoint principal: muestra lista de pedidos del empleado
     */
    public function index()
    {
        $user = $this->getActiveUser();

        if (!$user || $user['rol'] !== 'empleado') {
            return redirect()->to(base_url('login'));
        }

        $userModel = new UsuarioModel();
        $userData = $userModel->getDetalleUsuario($user['id']);

        $atencionModel = new AtencionModel();
        $stats = [
            'nuevos' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'pendiente_asignado'])->countAllResults(),
            'proceso' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_proceso'])->countAllResults(),
            'revision' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_revision'])->countAllResults(),
            'historial' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'finalizado'])->countAllResults(),
            'retro_count' => (new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id']) ? count((new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id'])) : 0,
        ];

        $pedidos = $atencionModel->obtenerDetalladoPorEmpleado((int) $user['id'], ['pendiente_asignado', 'en_proceso', 'en_revision']);

        return $this->response->setBody(view('empleado/mis_pedidos', [
            'titulo' => 'Mis Pedidos',
            'tituloPagina' => 'MIS PEDIDOS',
            'paginaActual' => 'mis_pedidos',
            'user' => $userData,
            'stats' => $stats,
            'pedidos' => $pedidos
        ]));
    }

    public function dashboard()
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado')
            return redirect()->to(base_url('login'));

        $userModel = new UsuarioModel();
        $userData = $userModel->getDetalleUsuario($user['id']);

        $atencionModel = new AtencionModel();
        $stats = [
            'nuevos' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'pendiente_asignado'])->countAllResults(),
            'proceso' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_proceso'])->countAllResults(),
            'revision' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_revision'])->countAllResults(),
            'historial' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'finalizado'])->countAllResults(),
            'retro_count' => (new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id']) ? count((new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id'])) : 0,
        ];

        return view('empleado/dashboard', [
            'titulo' => 'Dashboard',
            'tituloPagina' => 'DASHBOARD',
            'paginaActual' => 'dashboard',
            'user' => $userData,
            'stats' => $stats,
            'pedidos_recientes' => $this->obtenerPedidosRecientes($user['id']),
            'pedidos_revision' => $this->obtenerPedidosRevision($user['id'])
        ]);
    }

    public function historial()
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado')
            return redirect()->to(base_url('login'));

        $userModel = new UsuarioModel();
        $userData = $userModel->getDetalleUsuario($user['id']);

        $atencionModel = new AtencionModel();
        $stats = [
            'nuevos' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'pendiente_asignado'])->countAllResults(),
            'proceso' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_proceso'])->countAllResults(),
            'revision' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_revision'])->countAllResults(),
            'historial' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'finalizado'])->countAllResults(),
            'retro_count' => (new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id']) ? count((new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id'])) : 0,
        ];

        return view('empleado/historial', [
            'titulo' => 'Historial',
            'tituloPagina' => 'MI HISTORIAL',
            'paginaActual' => 'historial',
            'user' => $userData,
            'stats' => $stats,
            'pedidos' => $atencionModel->obtenerDetalladoPorEmpleado((int) $user['id'], ['finalizado'])
        ]);
    }

    /**
     * Obtiene el detalle completo de una atención para el empleado
     */
    public function detalle($id)
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $db = \Config\Database::connect();

        // Query similar al de Kanban Administrador pero filtrado por idempleado
        $data = $db->query("
            SELECT
                a.id, a.titulo, a.estado, a.prioridad,
                a.fechainicio, a.fechafin, a.fechacompletado,
                a.url_entrega, a.observacion_revision,
                r.id as id_requerimiento, r.descripcion, r.objetivo_comunicacion, r.tipo_requerimiento,
                r.canales_difusion, r.publico_objetivo, r.formatos_solicitados,
                r.fecharequerida, r.prioridad AS prioridad_cliente, r.url_subida,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                e.nombreempresa
            FROM atencion a
            LEFT JOIN requerimiento r  ON r.id = a.idrequerimiento
            LEFT JOIN usuarios u_sol   ON u_sol.id = r.idusuarioempresa
            LEFT JOIN areas ar         ON ar.id = u_sol.idarea
            LEFT JOIN empresas e       ON e.id = ar.idempresa
            LEFT JOIN servicios s      ON s.id = a.idservicio
            WHERE a.id = ? AND a.idempleado = ?
        ", [$id, $user['id']])->getRowArray();

        if (!$data) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se encontró el detalle']);
        }

        // Obtener archivos adjuntos del requerimiento (tanto del cliente como del empleado)
        $archivoModel = new ArchivoModel();

        // Obtener archivos del cliente (idrequerimiento sin idatencion)
        $archivosCliente = $archivoModel->where('idrequerimiento', $data['id_requerimiento'])
            ->where('idatencion IS NULL')
            ->findAll();

        // Obtener archivos del empleado (con idatencion)
        $archivosEmpleado = $archivoModel->where('idatencion', $id)->findAll();

        // Combinar todos los archivos
        $archivos = array_merge($archivosCliente, $archivosEmpleado);

        // Tracking del pedido (historial de acciones)
        $trackingModel = new TrackingModel();
        $tracking = $trackingModel
            ->where('idatencion', $id)
            ->orderBy('fecha_registro', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'status'            => 'success',
            'data'              => $data,
            'archivos'          => $archivos,
            'archivos_cliente'  => $archivosCliente,
            'archivos_empleado' => $archivosEmpleado,
            'tracking'          => $tracking
        ]);
    }

    public function iniciarPedido($id)
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);

        if (!$pedido || $pedido['idempleado'] != $user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado o no asignado']);
        }

        $now = (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s');
        $data = [
            'estado' => 'en_proceso',
            'fechareanudacion' => $now
        ];

        if (empty($pedido['fechainicio'])) {
            $data['fechainicio'] = $now;
        }

        if ($atencionModel->update($id, $data)) {
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $user['id'],
                'accion' => 'Trabajo iniciado por el empleado',
                'estado' => 'en_proceso',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            $this->pusher->notificarCambioEstado($id, 'en_proceso');

            // Iniciar la sesión de trabajo para que el cronómetro arranque automáticamente
            $sesionesModel = new SesionesTrabajosModel();
            $sesionesModel->iniciarSesion((int)$id, (int)$user['id']);

            return $this->response->setJSON(['status' => 'success', 'message' => '¡Trabajo iniciado correctamente!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo actualizar el estado']);
    }

    public function entregarPedido($id)
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);

        if (!$pedido || $pedido['idempleado'] != $user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado']);
        }

        $link = $this->request->getPost('url_entrega');
        $notas = $this->request->getPost('notas');

        $tiempoAcumulado = (int)($pedido['tiempo_trabajado_segundos'] ?? 0);
        $startField = !empty($pedido['fechareanudacion']) ? $pedido['fechareanudacion'] : ($pedido['fechainicio'] ?? null);
        if (!empty($startField)) {
            $fechaInicio = new \DateTime($startField);
            $ahora = new \DateTime('now', new \DateTimeZone('America/Lima'));
            $diferencia = $ahora->getTimestamp() - $fechaInicio->getTimestamp();
            if ($diferencia > 0) {
                $tiempoAcumulado += $diferencia;
            }
        }

        $data = [
            'estado'                    => 'en_revision',
            'url_entrega'               => $link,
            'observacion_revision'      => $notas,
            'tiempo_trabajado_segundos' => $tiempoAcumulado,
            'fechacompletado'           => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ];

        if ($atencionModel->update($id, $data)) {
            // Limpiar archivos anteriores si hay una revisión
            $this->limpiarArchivosAnteriores((int) $id);

            // Guardar archivos nuevos si existen
            $this->guardarArchivosEntrega((int) $id, (int) $pedido['idrequerimiento']);

            $trackingModel = new \App\Models\TrackingModel();
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $user['id'],
                'accion' => "En fase de revisión de calidad.\nEl entregable ha sido completado y se encuentra en proceso de validación por nuestro equipo supervisor.",
                'estado' => 'en_revision',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            // Detener el cronómetro automáticamente al entregar
            $sesionesModel = new SesionesTrabajosModel();
            $sesionesModel->pausarSesion((int)$id, (int)$user['id'], '');

            $this->pusher->notificarCambioEstado($id, 'en_revision');
            return $this->response->setJSON(['status' => 'success', 'message' => '¡Entrega realizada con éxito!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Error al procesar la entrega']);
    }

    /**
     * Guarda los archivos adjuntos de la entrega
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

    private function obtenerPedidosRecientes($userId)
    {
        $atencionModel = new AtencionModel();
        // Solo obtenemos tareas nuevas asignadas o en desarrollo (excluyendo en_revision que ya tiene su propia sección)
        return $atencionModel->obtenerDetalladoPorEmpleado((int)$userId, ['pendiente_asignado', 'en_proceso']);
    }

    private function obtenerPedidosRevision($userId)
    {
        $atencionModel = new AtencionModel();
        return $atencionModel->obtenerDetalladoPorEmpleado((int) $userId, ['en_revision']);
    }

    private function obtenerPedidos($userId)
    {
        $atencionModel = new AtencionModel();
        return $atencionModel->obtenerDetalladoPorEmpleado((int) $userId, ['pendiente_asignado', 'en_proceso', 'en_revision']);
    }

    /**
     * Limpia archivos anteriores de una atención cuando se vuelve a entregar
     * @param int $idAtencion
     */
    private function limpiarArchivosAnteriores(int $idAtencion): void
    {
        $archivoModel = new ArchivoModel();

        // Obtener archivos anteriores del empleado
        $archivosAnteriores = $archivoModel->where('idatencion', $idAtencion)->findAll();

        foreach ($archivosAnteriores as $archivo) {
            // Eliminar archivo físico del servidor
            $rutaCompleta = FCPATH . $archivo['ruta'];
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            // Eliminar registro de la base de datos
            $archivoModel->delete($archivo['id']);
        }
    }

    /**
     * Inicia o reanuda el cronómetro de trabajo para una atención.
     * Si es el primer play, también setea atencion.fechainicio.
     */
    public function iniciarSesion(int $id)
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $atencionModel   = new AtencionModel();
        $sesionesModel   = new SesionesTrabajosModel();

        $pedido = $atencionModel->find($id);
        if (!$pedido || $pedido['idempleado'] != $user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado']);
        }

        // No permitir si ya hay sesión activa (evitar doble-play)
        if ($sesionesModel->getSesionActiva((int)$id, (int)$user['id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ya tienes una sesión activa para este pedido']);
        }

        // Si es el primer Play ever, setear fechainicio en atencion
        if (empty($pedido['fechainicio'])) {
            $now = (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s');
            $atencionModel->update($id, ['fechainicio' => $now]);
        }

        $sesionId = $sesionesModel->iniciarSesion((int)$id, (int)$user['id']);

        $this->pusher->notificarCambioEstado((int)$id, 'en_proceso');

        return $this->response->setJSON([
            'status'    => 'success',
            'sesion_id' => $sesionId,
            'message'   => 'Sesión iniciada'
        ]);
    }

    /**
     * Pausa el cronómetro activo para una atención.
     * Recibe: motivo_pausa (opcional).
     */
    public function pausarSesion($id)
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);
        if (!$pedido || $pedido['idempleado'] != $user['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado']);
        }

        $motivo = trim($this->request->getPost('motivo_pausa') ?? '');

        $sesionesModel = new SesionesTrabajosModel();
        $ok = $sesionesModel->pausarSesion((int)$id, (int)$user['id'], $motivo);

        if ($ok) {
            // Notificar al admin/responsables que la tarea fue pausada
            try {
                $this->pusher->emitir(
                    'kanban-admin',
                    'sesion.pausada',
                    [
                        'idatencion'   => (int)$id,
                        'motivo_pausa' => $motivo,
                        'empleado'     => $pedido['idempleado'],
                        'titulo'       => $pedido['titulo'] ?? 'Tarea #'.$id,
                    ]
                );
            } catch (\Exception $e) {
                log_message('error', 'Pusher sesion.pausada: ' . $e->getMessage());
            }

            $this->pusher->notificarCambioEstado((int)$id, 'en_proceso');

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Sesión pausada',
                'segundos_totales' => $sesionesModel->getTotalSegundos((int)$id)
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se encontró sesión activa']);
    }

    /**
     * Retorna el estado del cronómetro para que el JS lo sincronice al cargar la página.
     */
    public function estadoSesion($id)
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $sesionesModel = new SesionesTrabajosModel();
        $sesionActiva  = $sesionesModel->getSesionActiva((int)$id, (int)$user['id']);
        $totalSegundos = $sesionesModel->getTotalSegundos((int)$id);
        $ultimaPausa   = $sesionesModel->getUltimaSessionPausada((int)$id);

        return $this->response->setJSON([
            'status'             => 'success',
            'activa'             => $sesionActiva !== null,
            'hora_inicio_sesion' => $sesionActiva ? $sesionActiva['hora_inicio'] : null,
            'segundos_totales'   => $totalSegundos,
            'motivo_pausa'       => $ultimaPausa ? ($ultimaPausa['motivo_pausa'] ?? null) : null,
        ]);
    }

    public function retroalimentacion()
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado')
            return redirect()->to(base_url('login'));

        $userModel = new UsuarioModel();
        $userData = $userModel->getDetalleUsuario($user['id']);

        $atencionModel = new AtencionModel();
        $stats = [
            'nuevos' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'pendiente_asignado'])->countAllResults(),
            'proceso' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_proceso'])->countAllResults(),
            'revision' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'en_revision'])->countAllResults(),
            'historial' => $atencionModel->where(['idempleado' => $user['id'], 'estado' => 'finalizado'])->countAllResults(),
            'retro_count' => (new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id']) ? count((new RetroalimentacionModel())->getRetroalimentacionPorEmpleado($user['id'])) : 0,
        ];

        $retroModel = new RetroalimentacionModel();
        $retroalimentacion = $retroModel->getRetroalimentacionPorEmpleado($user['id']);

        return view('empleado/retroalimentacion', [
            'titulo' => 'Retroalimentación',
            'tituloPagina' => 'RETROALIMENTACIÓN',
            'paginaActual' => 'retroalimentacion',
            'user' => $userData,
            'stats' => $stats,
            'retroalimentacion' => $retroalimentacion
        ]);
    }

    /**
     * Endpoint API JSON: Devuelve la retroalimentación del empleado para carga dinámica (Pusher).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function retroalimentacionJson()
    {
        $user = $this->getActiveUser();
        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])->setStatusCode(401);
        }

        $retroModel = new RetroalimentacionModel();
        $retroalimentacion = $retroModel->getRetroalimentacionPorEmpleado($user['id']);

        return $this->response->setJSON([
            'success' => true,
            'data' => $retroalimentacion,
            'count' => count($retroalimentacion)
        ]);
    }
}