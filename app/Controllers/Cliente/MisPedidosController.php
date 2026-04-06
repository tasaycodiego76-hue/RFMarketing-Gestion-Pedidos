<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;

class MisPedidosController extends BaseController
{
    /**
     * Endpoint principal: muestra el dashboard del cliente (Lista Total de Pedidos Encargados)
     * Por Ahora Prueba, Para Redirigir por ID y Rol
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $user = $this->getActiveUser();

        if (!$user || $user['rol'] !== 'cliente') {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => 'Se requiere cuenta de Cliente.']);
        }

        // Datos para la vista
        $data = [
            'titulo' => 'Mis Pedidos',
            'user' => $user,
            'pendientes' => 1, // Esto luego lo traerás de un Modelo (Ejemplo)
            'notif_no_leidas' => 2 // Esto luego lo traerás de un Modelo (Ejemplo)
        ];

        return view('cliente/mis_solicitudes', $data);
    }
}