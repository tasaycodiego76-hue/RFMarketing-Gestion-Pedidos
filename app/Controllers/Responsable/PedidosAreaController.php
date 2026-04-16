<?php

namespace App\Controllers\Responsable;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class PedidosAreaController extends BaseController
{

    /**
     * Renderiza el dashboard principal del Responsable x Area (Empleado)
     * @return string|\CodeIgniter\HTTP\ResponseInterface
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

        //Usar el Modelo para traer la información completa
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        $data = [
            'titulo' => 'Mis Pedidos - Area',
            'user' => [
                'id' => $userData['id'],
                'nombre' => $userData['nombre'],
                'apellidos' => $userData['apellidos'],
                'correo' => $userData['correo'],
                'telefono' => $userData['telefono'],
                'documento' => $userData['numerodoc'],
                'rol' => $userData['rol'],
                'nombre_area' => $userData['nombre_areaagencia'] ?? 'Área no asignada',
                'es_responsable' => true
            ]
        ];

        return view('Responsable/dashboard', $data);
    }
}