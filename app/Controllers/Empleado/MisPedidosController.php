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
            return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => 'No eres un empleado.']);
        }

        return $this->response->setJSON([
            'status' => 'SUCCESS',
            'actor' => $user['esresponsable'] ? 'RESPONSABLE DE ÁREA' : 'EMPLEADO',
            'detalles' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'dni' => $user['dni'],
                'celular' => $user['celular'],
                'correo' => $user['correo'],
                'idarea' => $user['idarea'],
                // LÓGICA DINÁMICA: Si id_empresa es null, devuelve "Agencia Interna"
                'empresa' => $user['id_empresa'] ?? 'Agencia Interna',
                'es_jefe' => (bool) $user['esresponsable']
            ]
        ]);
    }
}