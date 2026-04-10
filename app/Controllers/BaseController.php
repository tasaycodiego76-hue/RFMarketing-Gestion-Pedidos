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
     * Obtiene el usuario (Parametro = ID / URL) activo simulado para la solicitud actual
     * @return array|null Datos del usuario o null
     */
    public function getActiveUser()
    {
        // Intentamos obtener el ID de la URL
        $testUserId = $this->request->getGet('test_user');

        // Si NO hay ID en la URL, lo buscamos en la Sesión
        if (!$testUserId) {
            $testUserId = session()->get('test_user_id');
        }

        // Si no hay ID en ningún lado, devolvemos null
        if (!$testUserId) {
            return null;
        }

        // Buscamos el usuario en la BD con ese ID
        $userModel = new UsuarioModel();
        $user = $userModel->find($testUserId);

        // Si el usuario existe, lo guardamos en sesión para "recordarlo" en la siguiente página
        if ($user) {
            session()->set('test_user_id', $user['id']);
            return $user;
        }

        return null;
    }
}
