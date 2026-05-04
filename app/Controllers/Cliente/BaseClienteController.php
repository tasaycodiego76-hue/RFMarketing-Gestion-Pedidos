<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\AtencionModel;

class BaseClienteController extends BaseController
{
    /**
     * Valida la sesión y obtiene los datos detallados del usuario cliente.
     * Centraliza la lógica de seguridad para todos los controladores de cliente.
     * @return array{message: string, ok: bool|array{ok: bool, user: array, userData: array<bool|float|int|object|string|null>|object|null}}
     */
    protected function ValidarSesion_DatosUser(): array
    {
        // Obtener usuario básico de la sesión
        $user = $this->getActiveUser();
        
        // Verificar si existe el usuario y si tiene el rol de cliente
        if (!$user || $user['rol'] !== 'cliente') {
            return [
                'ok' => false, 
                'message' => 'Acceso denegado. Se requiere cuenta de Cliente.'
            ];
        }

        // Obtener detalles extendidos (empresa, área, etc.) mediante el modelo
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        return [
            'ok' => true,
            'user' => $user,
            'userData' => $userData
        ];
    }

    /**
     * Obtiene métricas del cliente para los contadores del sidebar/dashboard.
     * Permite mostrar al cliente cuántos pedidos tiene en cada etapa.
     * @param mixed $idUsuario
     * @return array{en_proceso: int|string, en_revision: int|string, finalizados: int|string, pendientes: int|string, total: int|string}
     */
    protected function _getMetrics($idUsuario): array
    {
        $atencionModel = new AtencionModel();
        
        // Base de la consulta filtrada por el usuario cliente actual
        // Se une con requerimiento para filtrar por idusuarioempresa
        $baseQuery = $atencionModel->db->table('atencion a')
            ->join('requerimiento r', 'r.id = a.idrequerimiento')
            ->where('r.idusuarioempresa', $idUsuario);

        return [
            'total'        => (clone $baseQuery)->countAllResults(),
            'pendientes'   => (clone $baseQuery)->whereIn('a.estado', ['pendiente_sin_asignar', 'pendiente_asignado'])->countAllResults(),
            'en_proceso'   => (clone $baseQuery)->where('a.estado', 'en_proceso')->countAllResults(),
            'en_revision'  => (clone $baseQuery)->where('a.estado', 'en_revision')->countAllResults(),
            'finalizados'  => (clone $baseQuery)->where('a.estado', 'finalizado')->countAllResults(),
        ];
    }
}