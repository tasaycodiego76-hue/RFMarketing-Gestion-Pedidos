<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Endpoint principal del dashboard del administrador
     * Por Ahora Prueba, Para Redirigir por ID y Rol
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        // Obtener usuario activo (simulado vía ?test_user=ID)
        $user = $this->getActiveUser();

        // Verificar que el usuario exista y sea administrador
        if (!$user || $user['rol'] !== 'administrador') {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Acceso Denegado. Se requiere rol de Administrador.',
                'tu_id' => $user['id'] ?? 'Ninguno'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'SUCCESS',
            'actor' => 'ADMINISTRADOR GENERAL',
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
                'idarea' => $user['idarea'] ?? 'Dirección General',
                'es_jefe' => (bool) $user['esresponsable'],
                'creado_el' => $user['fechacreacion'],
                'permisos' => 'Acceso total a Usuarios, Empresas y Kanban Global'
            ]
        ]);
    }
}