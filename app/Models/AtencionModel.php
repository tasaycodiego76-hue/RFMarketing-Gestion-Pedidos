<?php

namespace App\Models;

use CodeIgniter\Model;

class AtencionModel extends Model
{
    protected $table = 'atencion';      // Tabla de la BD que el Modelo Domina
    protected $primaryKey = 'id';      // Id Unico de Tabla
    protected $returnType = 'array';   // Retorna como Array, No Objeto

    // Campos permitidos para manipulación de datos (Mass Assignment Protection)
    protected $allowedFields = [
        'idrequerimiento',
        'idadmin',
        'idempleado',
        'idservicio',
        'servicio_personalizado',
        'titulo',
        'prioridad',
        'estado',
        'num_modificaciones',
        'observacion_revision',
        'fechainicio',
        'horainicio',
        'fechafin',
        'horafin',
        'fechacompletado',
        'cancelacionmotivo',
        'fechacancelacion',
        'respuestatexto'
    ];

    /**
     * Obtiene todos los pedidos de atención asociados a un cliente específico
     * @param mixed ID del usuario en sesión (obtenido de la sesión activa)
     * @return array Array con los pedidos encontrados. Si no hay pedidos, retorna array vacío.
     */
    public function getPedidosPorCliente($usuarioId)
    {
        $sql = "
            SELECT DISTINCT
                a.id, 
                a.titulo, 
                a.estado, 
                a.prioridad, 
                r.fechacreacion,
                a.idrequerimiento,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                e.nombreempresa AS empresa
            FROM atencion a
            LEFT JOIN servicios s ON s.id = a.idservicio
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN empresas e ON e.id = r.idempresa
            INNER JOIN areas ar ON ar.idempresa = e.id
            INNER JOIN usuarios u ON u.idarea = ar.id
            WHERE u.id = ? 
            ORDER BY a.idrequerimiento DESC 
        ";

        return $this->db->query($sql, [$usuarioId])->getResultArray();
    }
}