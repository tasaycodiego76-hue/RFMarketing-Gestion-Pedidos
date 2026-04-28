<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsuarioModel;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Inicializa el controlador con dependencias inyectadas por CodeIgniter
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Cargar helpers aquí (antes de parent::initController)
        // $this->helpers = ['form', 'url'];

        parent::initController($request, $response, $logger);

        // Precargar modelos y librerías aquí
        // $this->session = service('session');
    }

    /**
     * Obtiene el usuario autenticado desde la sesión
     * @return array|null Datos del usuario o null
     */
    public function getActiveUser()
    {
        // Obtener ID de usuario desde la sesión de autenticación
        $usuarioId = session()->get('usuario_id');

        // Si no hay ID de usuario en sesión, devolvemos null
        if (!$usuarioId) {
            return null;
        }

        // Buscamos el usuario en la BD con ese ID
        $userModel = new UsuarioModel();
        $user = $userModel->find($usuarioId);

        // Si el usuario existe, lo devolvemos
        if ($user) {
            return $user;
        }

        return null;
    }
}
