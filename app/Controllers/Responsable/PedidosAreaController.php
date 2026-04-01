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

        if (!$user || $user['rol'] !== 'empleado' || !$user['esresponsable']) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Solo los Responsables de Área (Jefes) pueden ver este endpoint.'
            ]);
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