<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\TrackingModel;
use App\Models\RequerimientoModel;
use App\Models\UsuarioModel;

class TrackingController extends BaseClienteController
{
    /**
     * Endpoint API: Obtiene el historial de seguimiento (tracking) de una atención (Requerimiento) específica.
     * @param mixed $idAtencion
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function seguimiento($idAtencion = null)
    {
        // Validación de sesión centralizada
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => $auth['message']])->setStatusCode(401);
        }

        // Validación básica de entrada
        if (!$idAtencion) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'ID de atención no proporcionado.'])->setStatusCode(400);
        }

        $model = new TrackingModel();
        // Obtiene todos los hitos registrados para esta atención (asignaciones, cambios de estado, notas)
        $data = $model->getHistorialCompleto($idAtencion);

        // Si no hay registros de seguimiento aún
        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'empty',
                'msg' => 'No se encontraron registros de seguimiento para esta solicitud.',
                'data' => []
            ]);
        }

        // Respuesta exitosa con la data del historial completa
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Renderiza la vista de seguimiento gráfico/detallado de un requerimiento.
     * @param mixed $idRequerimiento
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaSeguimiento($idRequerimiento = null)
    {
        // Validación de sesión y rol
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return redirect()->to(base_url('/'))->with('error', $auth['message']);
        }

        if (!$idRequerimiento) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'ID de requerimiento no válido.');
        }

        $userData = $auth['userData'];

        // Obtener datos del requerimiento para validar pertenencia y mostrar encabezados
        $reqModel = new RequerimientoModel();
        $requerimiento = $reqModel->getDetalleCompleto($idRequerimiento);

        // Verificar que el requerimiento exista y realmente pertenezca al cliente autenticado
        if (!$requerimiento) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'Requerimiento no encontrado.');
        }
        if ($requerimiento['idusuarioempresa'] != $auth['user']['id']) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'No tiene permisos para visualizar este seguimiento.');
        }

        // Obtener el historial de tracking usando el idatencion (o el id del requerimiento como fallback)
        $trackingModel = new TrackingModel();
        $historial = $trackingModel->getHistorialCompleto($requerimiento['idatencion'] ?? $requerimiento['id']);

        // Carga la vista de seguimiento con los datos del requerimiento y su historial
        return view('cliente/seguimiento_requerimiento', [
            'requerimiento' => $requerimiento,
            'historial' => $historial,
            'user' => $userData
        ]);
    }

    /**
     * Renderiza la página de notificaciones del cliente.
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function notificaciones()
    {
        // Validación de sesión
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return redirect()->to(base_url('/'))->with('error', $auth['message']);
        }

        $userData = $auth['userData'];

        $trackingModel = new TrackingModel();
        $notificaciones = $trackingModel->getNotificacionesPorUsuario($auth['user']['id']);

        // Retorna la vista de notificaciones con la data cargada
        return view('cliente/notificaciones', [
            'titulo' => 'Mis Notificaciones',
            'notificaciones' => $notificaciones,
            'user' => [
                'id' => $userData['id'],
                'nombre' => $userData['nombre'] ?? 'Sin nombre',
                'apellidos' => $userData['apellidos'] ?? '',
                'rol' => $userData['rol'] ?? 'cliente',
                'area' => $userData['nombre_area'] ?? 'Sin Área',
                'empresa' => $userData['nombre_empresa'] ?? 'Sin Empresa'
            ]
        ]);
    }

    /**
     * Endpoint API: Obtiene las últimas notificaciones (tracking) del usuario para el centro de notificaciones.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function notificacionesJson()
    {
        // Validación rápida de sesión
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => $auth['message']])->setStatusCode(401);
        }

        $model = new TrackingModel();

        // Obtiene las notificaciones específicas filtradas por el ID del usuario cliente
        $data = $model->getNotificacionesPorUsuario($auth['user']['id']);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }
}