<?php

namespace App\Controllers\Cliente;

use App\Models\AtencionModel;
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
        if (!$user || $user['rol'] !== 'cliente') {
            return $this->response->setJSON([
                'status' => 'ERROR', 
                'mensaje' => 'Se requiere cuenta de Cliente. Acceso denegado.'
            ]);
        }

        // Preparación de contexto para la vista
        $data = [
            'titulo' => 'Mis Pedidos',              // Título de la página
            'user' => $user,                        // Datos del usuario (nombre, apellidos, rol, id)
            'pendientes' => 1,                      // Ejemplo (Notificaciones)
            'notif_no_leidas' => 2                  // Ejemplo (Notificaciones)
        ];

        // Retorna la vista renderizada con los datos
        return view('cliente/mis_solicitudes', $data);
    }

    /**
     * Endpoint API: Retorna todos los pedidos del cliente autenticado como JSON
     * @return \CodeIgniter\HTTP\ResponseInterface Respuesta JSON con array de pedidos
     *                                              o array vacío si no hay pedidos
     */
    public function listar()
    {
        // Obtiene los datos del usuario autenticado en sesión
        $user = $this->getActiveUser();

        // Si $user es un array, extrae el valor de 'id'; si es primitivo, lo usa directo
        // Esto asegura compatibilidad tanto si getActiveUser() retorna array o ID directo
        $idUsuario = is_array($user) ? $user['id'] : $user;

        // Instancia el modelo de Atención
        $model = new AtencionModel();
        
        // Ejecuta la consulta que filtra pedidos por el usuario actual
        $data = $model->getPedidosPorCliente($idUsuario);

        // Retorna los pedidos como JSON para consumo del cliente (JavaScript)
        return $this->response->setJSON($data);
    }
}