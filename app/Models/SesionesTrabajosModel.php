<?php

namespace App\Models;

use CodeIgniter\Model;

class SesionesTrabajosModel extends Model
{
    protected $table            = 'sesiones_trabajo';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'idatencion',
        'idusuario',
        'hora_inicio',
        'hora_fin',
        'motivo_pausa',
    ];

    /**
     * Inicia una nueva sesión de trabajo (Play / Reanudar).
     * Crea un registro con hora_inicio = ahora y hora_fin = null.
     *
     * @param int $idAtencion
     * @param int $idUsuario
     * @return int  ID de la nueva sesión
     */
    public function iniciarSesion(int $idAtencion, int $idUsuario): int
    {
        $this->insert([
            'idatencion'  => $idAtencion,
            'idusuario'   => $idUsuario,
            'hora_inicio' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'),
            'hora_fin'    => null,
            'motivo_pausa' => null,
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Pausa la sesión activa (la que tiene hora_fin en NULL).
     * Cierra el registro colocando hora_fin = ahora y guardando el motivo.
     *
     * @param int    $idAtencion
     * @param int    $idUsuario
     * @param string $motivoPausa  Mensaje que el empleado escribe al pausar
     * @return bool
     */
    public function pausarSesion(int $idAtencion, int $idUsuario, string $motivoPausa = ''): bool
    {
        $sesionActiva = $this->where('idatencion', $idAtencion)
                             ->where('idusuario', $idUsuario)
                             ->where('hora_fin IS NULL', null, false)
                             ->orderBy('hora_inicio', 'DESC')
                             ->first();

        if (!$sesionActiva) {
            return false; // No hay sesión activa que pausar
        }

        return $this->update($sesionActiva['id'], [
            'hora_fin'     => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'),
            'motivo_pausa' => $motivoPausa ?: null,
        ]);
    }

    /**
     * Devuelve la sesión activa (sin hora_fin) de un empleado para una atención.
     * Si no hay ninguna, retorna null → el cronómetro está pausado.
     *
     * @param int $idAtencion
     * @param int $idUsuario
     * @return array|null
     */
    public function getSesionActiva(int $idAtencion, int $idUsuario): ?array
    {
        return $this->where('idatencion', $idAtencion)
                    ->where('idusuario', $idUsuario)
                    ->where('hora_fin IS NULL', null, false)
                    ->orderBy('hora_inicio', 'DESC')
                    ->first();
    }

    /**
     * Calcula el total de segundos trabajados para una atención.
     * Suma todas las sesiones cerradas + la sesión activa hasta ahora.
     *
     * @param int $idAtencion
     * @return float  Segundos totales trabajados
     */
    public function getTotalSegundos(int $idAtencion): float
    {
        $sql = "
            SELECT COALESCE(SUM(
                EXTRACT(EPOCH FROM (COALESCE(hora_fin, CURRENT_TIMESTAMP) - hora_inicio))
            ), 0) AS total_segundos
            FROM sesiones_trabajo
            WHERE idatencion = ?
        ";

        $row = $this->db->query($sql, [$idAtencion])->getRowArray();
        return (float) ($row['total_segundos'] ?? 0);
    }

    /**
     * Calcula el total de horas trabajadas (para mostrar en reportes).
     *
     * @param int $idAtencion
     * @return float  Horas totales redondeadas a 2 decimales
     */
    public function getTotalHoras(int $idAtencion): float
    {
        return round($this->getTotalSegundos($idAtencion) / 3600, 2);
    }

    /**
     * Devuelve el historial completo de sesiones de una atención (para el admin/reporte).
     *
     * @param int $idAtencion
     * @return array
     */
    public function getHistorialPorAtencion(int $idAtencion): array
    {
        return $this->where('idatencion', $idAtencion)
                    ->orderBy('hora_inicio', 'ASC')
                    ->findAll();
    }

    /**
     * Devuelve la última sesión que fue pausada (hora_fin NOT NULL) con motivo.
     * Útil para mostrar en el detalle de la tarea el motivo de la última pausa.
     *
     * @param int $idAtencion
     * @return array|null
     */
    public function getUltimaSessionPausada(int $idAtencion): ?array
    {
        $ultimaSesion = $this->where('idatencion', $idAtencion)
                             ->orderBy('id', 'DESC')
                             ->first();
                             
        if ($ultimaSesion && $ultimaSesion['hora_fin'] !== null && $ultimaSesion['motivo_pausa'] !== null) {
            return $ultimaSesion;
        }
        return null;
    }

    /**
     * Devuelve todas las sesiones pausadas (con motivo_pausa registrado) para una atención.
     * Ordenadas cronológicamente (ASC) para mostrar el historial completo de pausas.
     * Incluye el cálculo de duración real de pausa (hora_fin de sesión pausada hasta hora_inicio de siguiente sesión).
     *
     * @param int $idAtencion
     * @return array
     */
    public function getAllPausas(int $idAtencion): array
    {
        $pausas = $this->where('idatencion', $idAtencion)
                        ->where('hora_fin IS NOT NULL', null, false)
                        ->where('motivo_pausa IS NOT NULL', null, false)
                        ->where("motivo_pausa != ''")
                        ->orderBy('hora_inicio', 'ASC')
                        ->findAll();

        // Obtener todas las sesiones del requerimiento ordenadas para calcular duraciones de pausa
        $todasSesiones = $this->where('idatencion', $idAtencion)
                              ->orderBy('hora_inicio', 'ASC')
                              ->findAll();

        // Crear un mapa de sesiones por ID para fácil acceso
        $sesionesPorId = [];
        foreach ($todasSesiones as $sesion) {
            $sesionesPorId[$sesion['id']] = $sesion;
        }

        // Calcular duración de cada pausa: desde hora_fin de sesión pausada hasta hora_inicio de siguiente sesión
        foreach ($pausas as &$pausa) {
            $pausa['duracion_segundos'] = 0;
            $pausa['hora_reinicio'] = null;

            // Buscar la siguiente sesión después de esta sesión pausada
            $encontradaSiguiente = false;
            foreach ($todasSesiones as $sesion) {
                if ($encontradaSiguiente) {
                    // Esta es la sesión siguiente
                    $pausa['hora_reinicio'] = $sesion['hora_inicio'];
                    if (!empty($pausa['hora_fin']) && !empty($sesion['hora_inicio'])) {
                        $pausa['duracion_segundos'] = max(0, strtotime($sesion['hora_inicio']) - strtotime($pausa['hora_fin']));
                    }
                    break;
                }
                if ($sesion['id'] == $pausa['id']) {
                    $encontradaSiguiente = true;
                }
            }
        }

        return $pausas;
    }
}