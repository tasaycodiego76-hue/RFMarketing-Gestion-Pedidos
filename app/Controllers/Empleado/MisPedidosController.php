<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;

class MisPedidosController extends BaseController
{
    /**
     * Endpoint principal: muestra lista de pedidos del empleado
     * Por Ahora Prueba, Para Redirigir por ID y Rol
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $user = $this->getActiveUser();

        if (!$user || $user['rol'] !== 'empleado') {
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => 'No autorizado.']);
        }

        // Identificamos si es responsable comparando el string de Postgres
        $es_responsable = ($user['esresponsable'] === 't' || $user['esresponsable'] === true);

        return $this->response->setJSON([
            'status' => 'SUCCESS',
            'actor' => $es_responsable ? 'RESPONSABLE (Viendo sus pedidos)' : 'EMPLEADO OPERATIVO',
            'detalles' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellidos' => $user['apellidos'],
                'correo' => $user['correo'],
                'telefono' => $user['telefono'],
                'documento' => $user['numerodoc'],
                'rol' => $user['rol'],
                'idarea' => $user['idarea'],
                'es_responsable' => $es_responsable,
                'creado_el' => $user['fechacreacion'],
                'permisos' => 'Visualización y actualización de tareas asignadas'
            ]
        ]);
    }
}