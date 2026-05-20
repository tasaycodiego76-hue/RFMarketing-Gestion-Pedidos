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

        // Si no existe usuario en sesión (No ha iniciado sesión / Sesión expirada)
        if (!$user) {
            return [
                'ok' => false,
                'message' => 'Acceso denegado. Solo los Jefes de Área/Responsables pueden acceder a esta sección.'
            ];
        }

        // El responsable en la base de datos se identifica como un empleado 
        // que tiene la marca 'esresponsable' habilitada. Validamos varios tipos de verdad (PostgreSQL/MySQL).
        $esResponsable = isset($user['esresponsable']) &&
            ($user['esresponsable'] === 't' ||
                $user['esresponsable'] === true ||
                $user['esresponsable'] === 1 ||
                $user['esresponsable'] === '1');

        // Si el usuario ya inició sesión pero tiene un rol diferente a responsable (Sin autorización)
        if ($user['rol'] !== 'empleado' || !$esResponsable) {
            return [
                'ok' => false,
                'unauthorized' => true,
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

        // Pedidos SIN delegación (en bandeja de entrada del responsable)
        $pendientesSinDelegar = count($atencionModel->obtenerBandejaResponsable($idAreaAgencia));
        
        // Pedidos DELEGADOS (ya están en manos del responsable/técnico)
        // Solo contar los que han sido asignados a un técnico y están pendientes o en proceso
        $pendientesDelegados = $atencionModel->where('idarea_agencia', $idAreaAgencia)
            ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
            ->where('idempleado >', 0)
            ->countAllResults();

        return [
            'pendientes_asignar' => $pendientesSinDelegar,
            'porAsignar' => $pendientesSinDelegar, // Alias para el Dashboard
            'pendientes' => $pendientesDelegados,  // Cambio CRÍTICO: Ahora muestra solo delegados
            'en_proceso' => $pendientesDelegados,
            'enProceso' => $pendientesDelegados,  // Alias para el Dashboard
            'enRevision' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'en_revision')->countAllResults(),
            'completados' => $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('estado', 'finalizado')->countAllResults(),
            'totalMiembros' => count($usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia)),
            'devoluciones' => count($atencionModel->obtenerRetroalimentacionPorArea($idAreaAgencia))
        ];
    }
}
