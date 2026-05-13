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

    /**
     * Cuenta las notificaciones recientes para el cliente.
     * Útil para mostrar el indicador de notificaciones no leídas en la campana.
     * @param mixed $idUsuario
     * @return int
     */
    public function countNotificacionesRecientes($idUsuario)
    {
        $session = session();
        $ultimaVez = $session->get('ultima_vez_visto_notificaciones');

        $sql = "
            SELECT COUNT(t.id) as total
            FROM tracking t
            INNER JOIN atencion a ON a.id = t.idatencion
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            WHERE r.idusuarioempresa = ? AND t.idusuario != ?
        ";

        if ($ultimaVez) {
            $sql .= " AND t.fecha_registro > ?";
            $row = $this->db->query($sql, [$idUsuario, $idUsuario, $ultimaVez])->getRowArray();
        } else {
            // Fallback de 24 horas si es su primer inicio de sesión
            $sql .= " AND t.fecha_registro >= NOW() - INTERVAL '24 HOURS'";
            $row = $this->db->query($sql, [$idUsuario, $idUsuario])->getRowArray();
        }

        return $row ? (int)$row['total'] : 0;
    }
}
