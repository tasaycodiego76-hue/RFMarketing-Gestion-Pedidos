<?php

namespace App\Controllers\Responsable;

use App\Controllers\BaseController;

class PedidosAreaController extends BaseController
{
    /**
     * Endpoint principal: dashboard del responsable de área.
     * Por Ahora Prueba, Para Redirigir por ID y Rol
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $user = $this->getActiveUser();

        // Comparamos directamente con el string que manda Postgres
        $es_responsable = ($user['esresponsable'] === 't' || $user['esresponsable'] === true);

        if (!$user || $user['rol'] !== 'empleado' || !$es_responsable) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Acceso Denegado. Solo para Jefes de Área.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'SUCCESS',
            'actor' => 'RESPONSABLE DE ÁREA',
            'detalles' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellidos' => $user['apellidos'],
                'correo' => $user['correo'],
                'telefono' => $user['telefono'],
                'documento' => $user['numerodoc'],
                'rol' => $user['rol'],
                'idarea' => $user['idarea'],
                'es_responsable' => true,
                'creado_el' => $user['fechacreacion'],
                'permisos' => 'Gestión de pedidos y personal del área asignada'
            ]
        ]);
    }
}