<?php

namespace App\Controllers\Responsable;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\AtencionModel;

/**
 * Controlador Base para el rol de Responsable
 * Proporciona métodos comunes de validación y métricas
 */
class BaseResponsableController extends BaseController
{
    /**
     * Valida la sesión y obtiene los datos del usuario responsable
     */
    protected function ValidarSesion_DatosUser(): array
    {
        $user = $this->getActiveUser();
        
        // El responsable en BD tiene rol 'empleado' pero 'esresponsable' = true
        $esResponsable = isset($user['esresponsable']) && 
                        ($user['esresponsable'] === 't' || 
                         $user['esresponsable'] === true || 
                         $user['esresponsable'] === 1 ||
                         $user['esresponsable'] === '1');

        if (!$user || $user['rol'] !== 'empleado' || !$esResponsable) {
            return ['ok' => false, 'message' => 'Acceso denegado. Solo responsables pueden acceder.'];
        }

        if (empty($user['idarea_agencia'])) {
            return ['ok' => false, 'message' => 'No tienes un área de agencia asignada.'];
        }

        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        return [
            'ok' => true,
            'user' => $user,
            'userData' => $userData
        ];
    }

    /**
     * Obtiene métricas comunes del área para los contadores del sidebar/dashboard
     */
    protected function _getMetrics($idAreaAgencia)
    {
        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();

        return [
            'pendientes_asignar' => count($atencionModel->obtenerBandejaResponsable($idAreaAgencia)),
            'en_proceso' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->whereIn('estado', ['en_proceso', 'pendiente_asignado'])->where('idempleado >', 0)->countAllResults(),
            'enRevision' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'en_revision')->countAllResults(),
            'completados' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'finalizado')->countAllResults(),
            'totalMiembros' => count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia)),
            'devoluciones' => count($atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia))
        ];
    }
}
