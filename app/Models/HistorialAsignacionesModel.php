<?php

namespace App\Models;
use CodeIgniter\Model;

class HistorialAsignacionesModel extends Model
{
    protected $table      = 'historial_asignaciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'idatencion',
        'idempleado_anterior',
        'idempleado',
        'idadmin',
        'fecha_asignacion',
        'fecha_fin',
        'motivo_cambio',
    ];

    /**
     * Obtiene el historial completo de reasignaciones de una tarea
     * @param int $idAtencion
     * @return array
     */
    public function obtenerHistorialPorAtencion(int $idAtencion): array
    {
        $sql = "
            SELECT
                ha.id,
                ha.fecha_asignacion,
                ha.motivo_cambio,
                u_ant.nombre  AS nombre_anterior,
                u_ant.apellidos AS apellidos_anterior,
                u_new.nombre  AS nombre_nuevo,
                u_new.apellidos AS apellidos_nuevo,
                u_resp.nombre AS nombre_responsable,
                u_resp.apellidos AS apellidos_responsable
            FROM historial_asignaciones ha
            LEFT JOIN usuarios u_ant  ON u_ant.id  = ha.idempleado_anterior
            LEFT JOIN usuarios u_new  ON u_new.id  = ha.idempleado
            LEFT JOIN usuarios u_resp ON u_resp.id = ha.idadmin
            WHERE ha.idatencion = ?
            ORDER BY ha.fecha_asignacion DESC
        ";

        return $this->db->query($sql, [$idAtencion])->getResultArray();
    }
}
