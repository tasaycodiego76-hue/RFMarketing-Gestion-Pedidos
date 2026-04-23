<?php

namespace App\Controllers\Responsable;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\AtencionModel;

class EquipoController extends BaseController
{
    /**
     * Obtiene el detalle de un miembro del equipo y sus pedidos asignados
     * @param int $idEmpleado
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detalleMiembro($idEmpleado)
    {
        $user = $this->getActiveUser();
        $esResponsable = isset($user['esresponsable']) && ($user['esresponsable'] === 't' || $user['esresponsable'] === true || $user['esresponsable'] === 1);
        
        if (!$user || $user['rol'] !== 'empleado' || !$esResponsable) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        $idAreaAgencia = (int) $user['idarea_agencia'];
        
        $usuarioModel = new UsuarioModel();
        // Validar que el empleado pertenece a la misma área
        $empleado = $usuarioModel->find($idEmpleado);
        
        if (!$empleado || $empleado['idarea_agencia'] != $idAreaAgencia) {
            return $this->response->setJSON(['success' => false, 'message' => 'Empleado no encontrado o no pertenece a tu área.']);
        }

        $atencionModel = new AtencionModel();
        // Usamos el método que ya tienes en AtencionModel para obtener sus tareas
        $tareas = $atencionModel->obtenerDetalladoPorEmpleado($idEmpleado);

        return $this->response->setJSON([
            'success' => true,
            'empleado' => [
                'id' => $empleado['id'],
                'nombre_completo' => trim($empleado['nombre'] . ' ' . $empleado['apellidos']),
                'correo' => $empleado['correo'],
                'rol' => $empleado['rol'],
                'esresponsable' => $empleado['esresponsable']
            ],
            'tareas' => $tareas
        ]);
    }
}
