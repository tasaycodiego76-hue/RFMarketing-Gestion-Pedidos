<?php

namespace App\Models;

use CodeIgniter\Model;

class RequerimientoModel extends Model
{
    protected $table = 'requerimiento';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $skipValidation = true;

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
        'url_subida',
        'formatos_solicitados',
        'formato_otros',
        'fecharequerida',
        'prioridad'
    ];

    public function getDetalleCompleto($RequerimientoID)
    {
        $sql = "
        SELECT 
            r.*,
            a.estado,
            a.prioridad AS atn_priority,
            a.num_modificaciones,
            a.url_entrega,
            a.fechainicio,
            a.fechafin,
            s.nombre AS nombre_servicio,
            u.nombre AS empleado_nombre
        FROM requerimiento r
        INNER JOIN atencion a ON a.idrequerimiento = r.id
        LEFT JOIN servicios s ON s.id = r.idservicio
        LEFT JOIN usuarios u ON u.id = a.idempleado
        WHERE r.id = ?
        ";
        return $this->db->query($sql, [$RequerimientoID])->getRowArray();
    }
}