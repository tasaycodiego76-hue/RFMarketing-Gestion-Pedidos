<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingModel extends Model
{
    protected $table = 'tracking';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['idatencion', 'idusuario', 'accion', 'estado', 'fecha_registro'];

    /* ADMINISTRADOR | EMPLEADO */

    /**
     * Obtiene la línea de tiempo completa de una atención específica.
     * @param mixed $idAtencion
     * @return array
     */
    public function getHistorialCompleto($idAtencion)
    {
        $sql = "
                SELECT 
                    t.id, 
                    t.accion, 
                    t.estado, 
                    t.fecha_registro,
                    u.nombre AS usuario_nombre, 
                    u.apellidos AS usuario_apellido,
                    a.titulo AS atencion_titulo
                FROM tracking t
                INNER JOIN usuarios u ON u.id = t.idusuario
                INNER JOIN atencion a ON a.id = t.idatencion
                WHERE t.idatencion = ?
                ORDER BY t.fecha_registro DESC";
        return $this->db->query($sql, [$idAtencion])->getResultArray();
    }

    /* CLIENTE */

    /**
     * Obtiene las últimas acciones relevantes para un cliente (notificaciones virtuales).
     * @param mixed $idUsuario
     * @return array
     */
    public function getNotificacionesPorUsuario($idUsuario)
    {
        $sql = "
            SELECT 
                t.id, 
                t.accion, 
                t.estado, 
                t.fecha_registro,
                a.titulo AS atencion_titulo,
                u_admin.nombre AS realizado_por
            FROM tracking t
            INNER JOIN atencion a ON a.id = t.idatencion
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u_admin ON u_admin.id = t.idusuario
            WHERE r.idusuarioempresa = ?
            ORDER BY t.fecha_registro DESC 
            LIMIT 20";

        return $this->db->query($sql, [$idUsuario])->getResultArray();
    }
}
