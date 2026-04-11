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
        'fechafin',
        'fechacompletado',
        'fechacreacion',
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
            SELECT 
                a.id AS atencion_id, 
                a.titulo, 
                a.estado, 
                a.prioridad, 
                r.fechacreacion,
                a.idrequerimiento,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                e.nombreempresa AS empresa
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN empresas e ON e.id = r.idempresa
            LEFT JOIN servicios s ON s.id = a.idservicio
            INNER JOIN areas ar ON ar.id = (SELECT idarea FROM usuarios WHERE id = ?)
            WHERE e.id = ar.idempresa
            ORDER BY a.idrequerimiento DESC 
        ";

        return $this->db->query($sql, [$usuarioId])->getResultArray();
    }
public function contarPorEstado(string $estado): int
{
    return $this->where('estado', $estado)->countAllResults();
}

public function contarActivos(): int
{
    return $this->whereIn('estado', ['pendiente_asignado', 'en_proceso'])->countAllResults();
}

public function contarPorEstadoEmpresa(string $estado, int $idEmpresa): int
{
    return $this->db->table('atencion a')
        ->join('requerimiento r', 'r.id = a.idrequerimiento')
        ->where('a.estado', $estado)
        ->where('r.idempresa', $idEmpresa)
        ->countAllResults();
}

public function contarActivosEmpresa(int $idEmpresa): int
{
    return $this->db->table('atencion a')
        ->join('requerimiento r', 'r.id = a.idrequerimiento')
        ->whereIn('a.estado', ['pendiente_asignado', 'en_proceso'])
        ->where('r.idempresa', $idEmpresa)
        ->countAllResults();
}
}