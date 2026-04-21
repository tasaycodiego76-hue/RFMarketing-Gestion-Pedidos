<?php

namespace App\Controllers\Cliente;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\ServicioModel;
use App\Controllers\BaseController;


class MisPedidosController extends BaseController
{
    /**
     * Renderiza el dashboard principal del cliente: "Mis Pedidos"
     * @return string|\CodeIgniter\HTTP\ResponseInterface 
     */
    public function index()
    {
        // Obtiene el usuario actual de la sesión autenticada
        $user = $this->getActiveUser();

        // Validación obligatoria: usuario debe existir y ser cliente
        if (!is_array($user) || $user['rol'] !== 'cliente') {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'mensaje' => 'Se requiere cuenta de Cliente. Acceso denegado.'
            ]);
        }

        //Usar el Modelo para traer la información completa
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        // Preparar datos para la vista (con datos seguros si fallan los joins)
        $data = [
            'titulo' => 'Mis Pedidos',
            'user' => [
                'id' => $userData['id'],
                'nombre' => $userData['nombre'] ?? 'Sin nombre',
                'apellidos' => $userData['apellidos'] ?? '',
                'rol' => $userData['rol'] ?? 'cliente',
                'area' => $userData['nombre_area'] ?? 'Sin Área',
                'empresa' => $userData['nombre_empresa'] ?? 'Sin Empresa'
            ],
        ];

        return view('cliente/mis_solicitudes', $data);
    }

    /**
     * Endpoint API: Retorna todos los pedidos del cliente autenticado como JSON
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listar()
    {
        try {
            // Obtiene los datos del usuario autenticado en sesión
            $user = $this->getActiveUser();

            // Si $user es un array, extrae el valor de 'id'; si es primitivo, lo usa directo
            // Esto asegura compatibilidad tanto si getActiveUser() retorna array o ID directo
            $idUsuario = is_array($user) ? $user['id'] : $user;

            $model = new AtencionModel();
            $data = $model->getPedidosPorCliente($idUsuario);
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'detalle' => $e->getMessage()
            ])->setStatusCode(500);
        }

    }

    /**
     * Metodo para Obtener Servicios 'Activos'
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function servicios()
    {
        $model = new ServicioModel();
        $servicios = $model->findAll();
        return $this->response->setJSON($servicios);
    }
}