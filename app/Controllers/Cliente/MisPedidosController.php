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
                'dni' => $user['dni'] ?? 'No registrado',
                'celular' => $user['celular'],
                'correo' => $user['correo'],
                // Aquí mostrará el ID (ej: "5") porque NO es null
                'empresa' => $user['id_empresa'] ?? 'Agencia Interna',
                'idarea' => $user['idarea'] // Área específica dentro de su empresa
            ]
        ]);
    }
}