<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;

class MisPedidosController extends BaseController
{
    /**
     * Endpoint principal: muestra el dashboard del cliente (Lista Total de Pedidos Encargados)
     * Por Ahora Prueba, Para Redirigir por ID y Rol
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $user = $this->getActiveUser();

        if (!$user || $user['rol'] !== 'cliente') {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => 'Se requiere cuenta de Cliente.']);
        }

        return $this->response->setJSON([
            'status' => 'SUCCESS',
            'actor' => 'CLIENTE',
            'detalles' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellidos' => $user['apellidos'],
                'correo' => $user['correo'],
                'telefono' => $user['telefono'],
                'tipo_doc' => $user['tipodoc'] ?? 'S/D',
                'documento' => $user['numerodoc'] ?? 'S/D',
                'usuario' => $user['usuario'],
                'rol' => $user['rol'],
                'creado_el' => $user['fechacreacion'],
                'permisos' => 'Creación de solicitudes y seguimiento de pedidos propios'
            ]
        ]);
    }
}