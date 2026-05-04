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
     * Valida la Session (Usuario Logeado, Rol Respectivo, etc) y Obtiene sus Datos
     * @return array{message: string, ok: bool|array{ok: bool, user: array, userData: array<bool|float|int|object|string|null>|object|null}}
     */
    protected function ValidarSesion_DatosUser(): array
    {
        $user = $this->getActiveUser();

        // El responsable en la base de datos se identifica como un empleado 
        // que tiene la marca 'esresponsable' habilitada. Validamos varios tipos de verdad (PostgreSQL/MySQL).
        $esResponsable = isset($user['esresponsable']) &&
            ($user['esresponsable'] === 't' ||
                $user['esresponsable'] === true ||
                $user['esresponsable'] === 1 ||
                $user['esresponsable'] === '1');

        // Denegamos acceso si no cumple los requisitos de rol
        if (!$user || $user['rol'] !== 'empleado' || !$esResponsable) {
            return [
                'ok' => false,
                'message' => 'Acceso denegado. Solo los Jefes de Área/Responsables pueden acceder a esta sección.'
            ];
        }

        // Verificamos que tenga un área asignada para filtrar sus pedidos correctamente
        if (empty($user['idarea_agencia'])) {
            return [
                'ok' => false,
                'message' => 'Error de perfil: No tienes un área de agencia vinculada a tu cuenta.'
            ];
        }

        // Cargamos detalles extendidos (empresa, área, nombres completos) para la interfaz
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
     * @param mixed $idAreaAgencia
     * @return array{completados: int|string, devoluciones: int, enRevision: int|string, en_proceso: int|string, pendientes_asignar: int, totalMiembros: int}
     */
    protected function _getMetrics($idAreaAgencia)
    {
        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();

        return [
            // Solicitudes nuevas que no han sido asignadas a nadie todavía
            'pendientes_asignar' => count($atencionModel->obtenerBandejaResponsable($idAreaAgencia)),

            // Tareas que ya están en manos de un técnico (en cola o iniciadas)
            'en_proceso' => $atencionModel->where('idarea_agencia', $idAreaAgencia)
                ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
                ->where('idempleado >', 0)
                ->countAllResults(),

            // Tareas que el técnico ya terminó y están esperando aprobación del responsable
            'enRevision' => $atencionModel->where('idarea_agencia', $idAreaAgencia)
                ->where('estado', 'en_revision')
                ->countAllResults(),

            // Histórico de tareas finalizadas con éxito
            'completados' => $atencionModel->where('idarea_agencia', $idAreaAgencia)
                ->where('estado', 'finalizado')
                ->countAllResults(),

            // Cantidad de personas a cargo en el equipo
            'totalMiembros' => count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia)),

            // Requerimientos que han sido observados o devueltos para correcciones
            'devoluciones' => count($atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia))
        ];
    }
}
