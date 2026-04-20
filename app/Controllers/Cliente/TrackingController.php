<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\TrackingModel;
use App\Models\RequerimientoModel;
use App\Models\UsuarioModel;

class TrackingController extends BaseController
{
    /**
     * Obtener los Datos (Seguimiento del Requerimiento)
     * @param mixed $idAtencion
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function seguimiento($idAtencion = null)
    {
        // Obtiene los datos del usuario autenticado en sesión
        $user = $this->getActiveUser();
        // Validación obligatoria: usuario debe existir y ser cliente
        if (!is_array($user) || $user['rol'] !== 'cliente') {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Se requiere cuenta de Cliente. Acceso denegado.'
            ]);
        }

        // Validación básica de entrada
        if (!$idAtencion) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'ID no válido'
            ])->setStatusCode(400);
        }

        $model = new TrackingModel();
        $data = $model->getHistorialCompleto($idAtencion);

        // Si no hay datos, enviamos un estado informativo
        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'empty',
                'msg' => 'No se encontraron registros de seguimiento.',
                'data' => []
            ]);
        }

        // Respuesta exitosa con la data del historial
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Renderiza la vista de seguimiento de un requerimiento
     * @param mixed $idRequerimiento
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaSeguimiento($idRequerimiento = null)
    {
        $user = $this->getActiveUser();

        if (!is_array($user) || $user['rol'] !== 'cliente') {
            return redirect()->to(base_url('/'))->with('error', 'Acceso denegado.');
        }

        if (!$idRequerimiento) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'ID no válido.');
        }

        // Traer datos del usuario para el Sidebar/TopBar
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        // Obtener datos del requerimiento
        $reqModel = new RequerimientoModel();
        $requerimiento = $reqModel->getDetalleCompleto($idRequerimiento);

        if (!$requerimiento) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'Requerimiento no encontrado.');
        }

        // Verificar que el requerimiento pertenezca al usuario
        if ($requerimiento['idusuarioempresa'] != $user['id']) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'No tiene permiso para ver este seguimiento.');
        }

        // Obtener el historial de tracking por idatencion
        $trackingModel = new TrackingModel();
        $historial = $trackingModel->getHistorialCompleto($requerimiento['idatencion'] ?? $requerimiento['id']);

        return view('cliente/seguimiento_requerimiento', [
            'requerimiento' => $requerimiento,
            'historial' => $historial,
            'user' => $userData
        ]);
    }

    /**
     * Muestra las Ultimas 20 Notifcaciones de los Requerimientos de un Usuario
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function notificaciones()
    {
        $user = $this->getActiveUser();

        // Validación de cliente (la que ya tienes)
        if (!is_array($user) || $user['rol'] !== 'cliente') {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => 'Acceso denegado.']);
        }

        $model = new TrackingModel();

        // Directo al grano: tráeme lo que le pertenece a ESTE usuario
        $data = $model->getNotificacionesPorUsuario($user['id']);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }
}