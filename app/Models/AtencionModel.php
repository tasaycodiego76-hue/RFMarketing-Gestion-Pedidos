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
        'url_entrega'
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
                a.idrequerimiento,
                a.titulo, 
                a.estado, 
                a.prioridad, 
                r.fechacreacion,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                e.nombreempresa AS empresa
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u ON u.id = r.idusuarioempresa
            LEFT JOIN areas ar ON ar.id = u.idarea
            LEFT JOIN empresas e ON e.id = ar.idempresa
            WHERE r.idusuarioempresa = ?
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
    /**
       * Estadísticas para el kanban
       */
      public function estadisticasPorEmpresa(int $idEmpresa): array
      {
          return $this->db->query("
              SELECT
                  COUNT(CASE WHEN a.estado IN ('en_proceso', 'en_revision',
  'pendiente_asignado') THEN 1 END) AS activos,
                  COUNT(CASE WHEN a.estado = 'pendiente_sin_asignar' THEN 1 END) AS
  por_aprobar,
                  COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) AS completados
              FROM atencion a
              INNER JOIN requerimiento r ON r.id = a.idrequerimiento
              WHERE r.idempresa = ?
          ", [$idEmpresa])->getRowArray();
      }

      /**
       * Obtener atenciones para el kanban
       */
      public function obtenerParaKanban(int $idEmpresa, int $idAreaAgencia): array
  {
      $sql = "
          SELECT
              a.id, a.titulo, a.estado, a.prioridad, a.fechafin,
              a.fechainicio, a.fechacreacion, a.idempleado, a.idrequerimiento,
  a.idarea_agencia,
              COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
              r.idempresa, r.fecharequerida,
              e.nombreempresa,
              u.nombre AS empleado_nombre,
              u.apellidos AS empleado_apellidos
          FROM atencion a
          INNER JOIN requerimiento r ON r.id = a.idrequerimiento
          INNER JOIN empresas e ON e.id = r.idempresa
          LEFT JOIN servicios s ON s.id = a.idservicio
          LEFT JOIN usuarios u ON u.id = a.idempleado
          WHERE r.idempresa = ?
            AND a.estado != 'cancelado'
            AND (
                a.idarea_agencia = ?
                OR (a.idarea_agencia IS NULL AND a.estado = 'pendiente_sin_asignar')
            )
          ORDER BY a.fechainicio DESC
      ";

      return $this->db->query($sql, [$idEmpresa, $idAreaAgencia])->getResultArray();
  }
 /**
       * Asignar área a una atención
       */
      public function asignarArea(int $idAtencion, int $idAreaAgencia, int $idAdmin): bool
      {
          $this->db->query("
              UPDATE atencion
              SET idarea_agencia = ?, estado = 'pendiente_asignado'
              WHERE id = ?
          ", [$idAreaAgencia, $idAtencion]);

          $this->db->query("
              INSERT INTO tracking (idatencion, idusuario, accion, estado)
              VALUES (?, ?, 'Área asignada', 'pendiente_asignado')
          ", [$idAtencion, $idAdmin]);

          return true;
      }

  /**
   * Obtener pedidos para el responsable de un área específica
   */
  public function obtenerParaResponsable(int $idAreaAgencia): array
  {
      $sql = "
          SELECT
              a.id, a.titulo, a.estado, a.prioridad, a.fechafin,
              a.fechainicio, a.fechacreacion, a.idempleado, a.idrequerimiento,
              COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
              r.fecharequerida,
              e.nombreempresa,
              u.nombre AS empleado_nombre,
              u.apellidos AS empleado_apellidos
          FROM atencion a
          INNER JOIN requerimiento r ON r.id = a.idrequerimiento
          INNER JOIN empresas e ON e.id = r.idempresa
          LEFT JOIN servicios s ON s.id = a.idservicio
          LEFT JOIN usuarios u ON u.id = a.idempleado
          WHERE a.idarea_agencia = ?
            AND a.estado != 'cancelado'
            AND a.estado != 'finalizado'
          ORDER BY a.prioridad DESC, a.fechacreacion ASC
      ";

      return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
  }
}