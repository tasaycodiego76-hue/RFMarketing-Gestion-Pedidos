<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;
use App\Models\ArchivoModel;
use App\Models\AtencionModel;
use App\Models\RetroalimentacionModel;
use App\Models\TrackingModel;
use App\Models\UsuarioModel;

class MisPedidosController extends BaseController
{
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

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data,
            'archivos' => $archivos,
            'archivos_cliente' => $archivosCliente,
            'archivos_empleado' => $archivosEmpleado
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

        $data = [
            'estado' => 'en_proceso',
            'fechainicio' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ];

        if ($atencionModel->update($id, $data)) {
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $user['id'],
                'accion' => 'Trabajo iniciado por el empleado',
                'estado' => 'en_proceso',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

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

        $data = [
            'estado' => 'en_revision',
            'url_entrega' => $link,
            'observacion_revision' => $notas
        ];

        if ($atencionModel->update($id, $data)) {
            // Limpiar archivos anteriores si hay una revisión
            $this->limpiarArchivosAnteriores((int) $id);

            // Guardar archivos nuevos si existen
            $this->guardarArchivosEntrega((int) $id, (int) $pedido['idrequerimiento']);

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
}