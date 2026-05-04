<?php

namespace App\Controllers\Cliente;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\ServicioModel;
use App\Controllers\BaseController;

class MisPedidosController extends BaseClienteController
{

    /**
     * Funcion que Renderiza el dashboard principal del cliente: "Mis Pedidos"
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        // Validación centralizada en el controlador base
        $auth = $this->ValidarSesion_DatosUser();
        
        // Si no es válido (ej: no es cliente), redirigir o mostrar error
        if (!$auth['ok']) {
            return redirect()->to(base_url('/'))->with('error', $auth['message']);
        }

        $user = $auth['user'];
        $userData = $auth['userData'];

        // Obtener métricas para mostrar contadores en la interfaz (opcional para el index)
        $metrics = $this->_getMetrics($user['id']);

        // Preparar datos para la vista (con datos seguros si fallan los joins)
        // Se formatea el array 'user' para que la vista lo procese fácilmente
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
            'metrics' => $metrics // Agregamos las métricas por si la vista las requiere
        ];

        // Retorna la vista de la bandeja de solicitudes del cliente
        return view('cliente/mis_solicitudes', $data);
    }

    /**
     * Endpoint API: Retorna todos los pedidos del cliente autenticado como JSON.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listar()
    {
        try {
            // Validación rápida de sesión
            $auth = $this->ValidarSesion_DatosUser();
            if (!$auth['ok']) {
                return $this->response->setJSON(['status' => 'ERROR', 'mensaje' => $auth['message']])->setStatusCode(401);
            }

            $idUsuario = $auth['user']['id'];

            // Instancia el modelo de atención para obtener los registros
            $model = new AtencionModel();
            
            // Obtiene la lista de pedidos formateada para el cliente
            $data = $model->getPedidosPorCliente($idUsuario);
            
            // Retorna la data en formato JSON
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            // Registro de error y respuesta de fallo controlada
            log_message('error', '[MisPedidosController::listar] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'ERROR',
                'detalle' => 'Ocurrió un error al cargar la lista de pedidos.'
            ])->setStatusCode(500);
        }
    }

    /**
     * Método para obtener los servicios activos para la seleccion del cliente
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function servicios()
    {
        // Validación de sesión
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return $this->response->setJSON([]);
        }

        // Instancia el modelo de servicios y obtiene solo los activos
        $model = new ServicioModel();
        $servicios = $model->getServiciosActivos();

        // Retorna la lista de servicios en JSON
        return $this->response->setJSON($servicios);
    }
}