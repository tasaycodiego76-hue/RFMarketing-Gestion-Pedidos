<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use APP\Models\UsuarioModel;
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
        // Obtener el ID de la URL (?test_user=X)
        $testUserId = $this->request->getGet('test_user');

        // Si no viene en la URL, intentamos sacarlo de la Sesión (para persistencia)
        if (!$testUserId) { return $testUserId = session()->get('test_user_id'); }

        // Si después de buscar en ambos lados seguimos sin ID, salimos de una vez
        if (!$testUserId) { return null; }

        //Buscamos el usuario en la BD
        $userModel = new UsuarioModel();
        $user = $userModel->find($testUserId);

        if (!$user) { return null; }
        // Guardamos en sesión para que persista mientras navegamos
            session()->set('test_user_id', $user['id']);
        // Si no hay ID ni sesión, devuelve null
        return null;
    }
}
