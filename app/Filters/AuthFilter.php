<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar si el usuario está autenticado
        $session = session();
        if (!$session->get('usuario_id')) {
            return redirect()->to('/auth/login')->with('error', 'Debes iniciar session');
        }

        //El usuario si inicio session, pero trata de ingresar a un perfil incorrecto
        if ($arguments) {
            // Identificacion el nivel de acceso = administrador | invitado
            $nivelRequerido = $arguments[0] ?? null;
            // Ya tenemos identificado el nivel, ¿es el mismo que el almacenado en la session?
            if ($nivelRequerido && $session->get('nivelacceso') !== $nivelRequerido) {
                return redirect()->to('/sin-permiso');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No necesitamos lógica después de la solicitud
    }
}