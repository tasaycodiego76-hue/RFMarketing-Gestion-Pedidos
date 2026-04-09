<?php

namespace App\Models;

use CodeIgniter\Model;

class RequerimientoModel extends Model
{
    protected $table = 'requerimiento';
    protected $primaryKey = 'id';
    // Definimos qué columnas se pueden devolver o manipular
    protected $allowedFields = [
        'idempresa',
        'idservicio',
        'servicio_personalizado',
        'titulo',
        'objetivo_comunicacion',
        'descripcion',
        'tipo_requerimiento',
        'canales_difusion',
        'publico_objetivo',
        'tiene_materiales',
        'formatos_solicitados',
        'formato_otros',
        'fecharequerida',
        'prioridad'
    ];

    /**
     * Obtiene el detalle completo de un requerimiento con sus datos relacionados
     * @param mixed $RequerimientoID
     * @return array|null
     */
    public function getDetalleCompleto($RequerimientoID)
    {
        $sql = "
            SELECT 
                r.*,
                a.estado,
                a.prioridad AS atn_prioridad,
                s.nombre AS nombre_servicio 
            FROM requerimiento r
            INNER JOIN atencion a ON a.idrequerimiento = r.id
            LEFT JOIN servicios s ON s.id = r.idservicio
            WHERE r.id = ?
            ";

        return $this->db->query($sql, [$RequerimientoID])->getRowArray();
    }
}