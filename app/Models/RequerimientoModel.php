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
        'idusuarioempresa',
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
            COALESCE(a.estado, 'pendiente_sin_asignar') AS estado,
            a.num_modificaciones,
            a.url_entrega,
            a.fechainicio,
            a.fechafin,
            a.fechacompletado,
            COALESCE(s.nombre, r.servicio_personalizado) AS nombre_servicio,
            u_sol.nombre AS nombre_cliente,
            u_sol.apellidos AS apellidos_cliente,
            u_sol.telefono AS telefono_cliente,
            u_sol.correo AS correo_cliente,
            e.nombreempresa AS nombre_empresa,
            ar.nombre AS nombre_area,
            CONCAT(u_emp.nombre, ' ', u_emp.apellidos) AS empleado_nombre
        FROM requerimiento r
        LEFT JOIN atencion a ON a.idrequerimiento = r.id
        LEFT JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa
        LEFT JOIN areas ar ON ar.id = u_sol.idarea
        LEFT JOIN empresas e ON e.id = ar.idempresa
        LEFT JOIN servicios s ON s.id = r.idservicio
        LEFT JOIN usuarios u_emp ON u_emp.id = a.idempleado
        WHERE r.id = ?
        ";
        return $this->db->query($sql, [$RequerimientoID])->getRowArray();
    }

    public function DetalleCompletoget($RequerimientoID)
    {
        $sql = "
        SELECT 
            r.*,
            a.estado,
            COALESCE(a.prioridad, r.prioridad) AS prioridad,
            a.num_modificaciones,
            a.url_entrega,
            a.fechainicio,
            a.fechafin,
            a.fechacreacion,
            a.fechacompletado,
            s.nombre AS nombre_servicio,
            u.nombre AS empleado_nombre
        FROM requerimiento r
        INNER JOIN atencion a ON a.idrequerimiento = r.id
        LEFT JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa  -- quien solicitó
        LEFT JOIN areas ar ON ar.id = u_sol.idarea
        LEFT JOIN empresas e ON e.id = ar.idempresa
        LEFT JOIN servicios s ON s.id = r.idservicio
        LEFT JOIN usuarios u ON u.id = a.idempleado
        WHERE r.id = ?
        ";
        return $this->db->query($sql, [$RequerimientoID])->getRowArray();
    }

}