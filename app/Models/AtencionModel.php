<?php

namespace App\Models;

use CodeIgniter\Model;

class AtencionModel extends Model
{
    protected $table = 'atencion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

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
        'url_entrega',
        'idarea_agencia'
    ];

    /**
     * Obtiene todos los pedidos de atención asociados a un cliente específico
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
            ->join('usuarios u', 'u.id = r.idusuarioempresa')
            ->join('areas ar', 'ar.id = u.idarea')
            ->where('a.estado', $estado)
            ->where('ar.idempresa', $idEmpresa)
            ->countAllResults();
    }

    public function contarActivosEmpresa(int $idEmpresa): int
    {
        return $this->db->table('atencion a')
            ->join('requerimiento r', 'r.id = a.idrequerimiento')
            ->join('usuarios u', 'u.id = r.idusuarioempresa')
            ->join('areas ar', 'ar.id = u.idarea')
            ->whereIn('a.estado', ['pendiente_asignado', 'en_proceso'])
            ->where('ar.idempresa', $idEmpresa)
            ->countAllResults();
    }

    /**

     * Estadísticas para el kanban por Empresa
     * @param int $idEmpresa
     * @return array|array{activos: int, completados: int, en_revision: int, por_aprobar: int|null}
     * Estadísticas para el kanban

     */
    public function estadisticasPorEmpresa(int $idEmpresa): array
    {
        $result = $this->db->query("
            SELECT
                COUNT(CASE WHEN a.estado IN ('en_proceso', 'pendiente_asignado') THEN 1 END) AS activos,
                COUNT(CASE WHEN a.estado = 'en_revision' THEN 1 END) AS en_revision,
                COUNT(CASE WHEN a.estado IN ('pendiente_sin_asignar') THEN 1 END) AS por_aprobar,
                COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) AS completados
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u ON u.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u.idarea
            WHERE ar.idempresa = ?
        ", [$idEmpresa])->getRowArray();

        return $result ?: ['activos' => 0, 'en_revision' => 0, 'por_aprobar' => 0, 'completados' => 0];
    }

    /**
     * Obtener Requerimiento y su Atencion para mostrar en el kanban
     * @param int $idEmpresa
     * @param int $idAreaAgencia
     * @return array
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
                r.fecharequerida,
                r.prioridad AS prioridad_cliente,
                e.nombreempresa,
                u.nombre AS empleado_nombre,
                u.apellidos AS empleado_apellidos,
                CASE 
                    WHEN a.servicio_personalizado IS NOT NULL THEN 1 
                    ELSE 0 
                END AS es_servicio_personalizado
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u_sol.idarea
            INNER JOIN empresas e ON e.id = ar.idempresa
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u ON u.id = a.idempleado
            WHERE ar.idempresa = ?
              AND a.estado != 'cancelado'
              AND (a.idarea_agencia = ? OR a.servicio_personalizado IS NOT NULL)
            ORDER BY es_servicio_personalizado ASC, a.fechainicio DESC
        ";

        return $this->db->query($sql, [$idEmpresa, $idAreaAgencia])->getResultArray();
    }

    /**
     * Asignar un Area vinculada al Requerimiento para su Atencion (Ejecucion de la Solicitud)
     * @param int $idAtencion
     * @param int $idAreaAgencia
     * @param int $idAdmin
     * @return bool

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
            INSERT INTO tracking (idatencion, idusuario, accion, estado, fecha_registro)
            VALUES (?, ?, 'Área asignada', 'pendiente_asignado', ?)
        ", [$idAtencion, $idAdmin, (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')]);

        return true;
    }

    /**
     * Obtener pedidos para el responsable de un área específica
     * @param int $idAreaAgencia
     * @return array
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
            INNER JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u_sol.idarea
            INNER JOIN empresas e ON e.id = ar.idempresa
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u ON u.id = a.idempleado
            WHERE a.idarea_agencia = ?
              AND a.estado != 'cancelado'
              AND a.estado != 'finalizado'
            ORDER BY a.prioridad DESC, a.fechacreacion ASC
        ";

        return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
    }

    /**

     * Obtiene los pedidos asignados a su área y que aun este sin asignar (Empleado)
     * @param int $idAreaAgencia
     * @return array
     */
    public function obtenerBandejaResponsable(int $idAreaAgencia): array
    {
        $sql = "
        SELECT
            a.id AS idatencion,
            a.*,
            r.fecharequerida,
            COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
            CONCAT(u.nombre, ' ', u.apellidos) AS cliente_nombre,
            e.nombreempresa
        FROM atencion a
        INNER JOIN requerimiento r ON r.id = a.idrequerimiento
        INNER JOIN usuarios u ON u.id = r.idusuarioempresa
        LEFT JOIN areas ar ON ar.id = u.idarea
        LEFT JOIN empresas e ON e.id = ar.idempresa
        LEFT JOIN servicios s ON s.id = a.idservicio
        WHERE a.idarea_agencia = ?
          AND a.estado = 'pendiente_asignado'
          AND (a.idempleado IS NULL OR a.idempleado = 0)
        ORDER BY
            CASE a.prioridad
                WHEN 'Alta' THEN 1
                WHEN 'Media' THEN 2
                WHEN 'Baja' THEN 3
                ELSE 4
            END,
            a.fechacreacion ASC
    ";
        return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
    }

    /**
     * Valida que una atención pertenezca al área del responsable
     * @param int $idAtencion
     * @param int $idAreaAgencia
     * @return bool
     */
    public function atencionPerteneceAArea(int $idAtencion, int $idAreaAgencia): bool
    {
        $sql = "
            SELECT id 
            FROM atencion 
            WHERE id = ? 
              AND idarea_agencia = ? 
            LIMIT 1";

        // Ejecutamos la consulta pasando los parámetros en un array
        $query = $this->db->query($sql, [$idAtencion, $idAreaAgencia]);

        // Retornamos true si encontró al menos una fila, false si no
        return $query->getNumRows() > 0;
    }
    /*
         * Obtener pedidos detallados para un empleado específico
         */
        public function obtenerDetalladoPorEmpleado(int $idEmpleado, array $estados = []): array
        {
            $builder = $this->db->table('atencion a')
                ->select('a.*, r.id as id_requerimiento, r.fechacreacion as fecha_req, 
                          e.nombreempresa as empresa_nombre, 
                          u_emp.nombre as empleado_nombre,
                          u_cliente.nombre as cliente_nombre,
                          ar_cliente.nombre as area_nombre,
                          COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre')
                ->join('requerimiento r', 'r.id = a.idrequerimiento')
                ->join('usuarios u_cliente', 'u_cliente.id = r.idusuarioempresa')
                ->join('areas ar_cliente', 'ar_cliente.id = u_cliente.idarea')
                ->join('empresas e', 'e.id = ar_cliente.idempresa')
                ->join('usuarios u_emp', 'u_emp.id = a.idempleado', 'left')
                ->join('servicios s', 's.id = a.idservicio', 'left')
                ->where('a.idempleado', $idEmpleado);
    
            if (!empty($estados)) {
                $builder->whereIn('a.estado', $estados);
            }
    
            return $builder->orderBy('a.prioridad', 'DESC')
                           ->orderBy('a.fechacreacion', 'DESC')
                           ->get()->getResultArray();
        }

        /**
         * Obtener pedidos detallados para un área específica
         * @param int $idAreaAgencia
         * @param array $estados
         * @return array
         */
        public function obtenerDetalladoPorArea(int $idAreaAgencia, array $estados = []): array
        {
            $builder = $this->db->table('atencion a')
                ->select('a.*, r.id as id_requerimiento, r.fechacreacion as fecha_req, 
                          e.nombreempresa as empresa_nombre, 
                          u_emp.nombre as empleado_nombre,
                          u_cliente.nombre as cliente_nombre,
                          ar_cliente.nombre as area_nombre,
                          COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre')
                ->join('requerimiento r', 'r.id = a.idrequerimiento')
                ->join('usuarios u_cliente', 'u_cliente.id = r.idusuarioempresa')
                ->join('areas ar_cliente', 'ar_cliente.id = u_cliente.idarea')
                ->join('empresas e', 'e.id = ar_cliente.idempresa')
                ->join('usuarios u_emp', 'u_emp.id = a.idempleado', 'left')
                ->join('servicios s', 's.id = a.idservicio', 'left')
                ->where('a.idarea_agencia', $idAreaAgencia);
    
            if (!empty($estados)) {
                $builder->whereIn('a.estado', $estados);
            }
    
            return $builder->orderBy('a.prioridad', 'DESC')
                           ->orderBy('a.fechacreacion', 'DESC')
                           ->get()->getResultArray();
        }

        /**
         * Obtener tareas de un empleado por estado específico
         * @param int $idEmpleado
         * @param string $estado
         * @return array
         */
        public function obtenerTareasPorEmpleadoEstado(int $idEmpleado, string $estado): array
        {
            return $this->where('idempleado', $idEmpleado)
                       ->where('estado', $estado)
                       ->orderBy('prioridad', 'DESC')
                       ->orderBy('fechainicio', 'DESC')
                       ->findAll();
        }

        /**
         * Obtener tareas devueltas (con observaciones) para un área específica
         * @param int $idAreaAgencia
         * @return array
         */
        public function obtenerRetroalimentacionPorArea(int $idAreaAgencia): array
        {
            return $this->db->table('atencion a')
                ->select('a.*, r.id as id_requerimiento, r.fechacreacion as fecha_req, 
                          e.nombreempresa as empresa_nombre, 
                          u_emp.nombre as empleado_nombre,
                          u_emp.apellidos as empleado_apellidos,
                          u_cliente.nombre as cliente_nombre,
                          ar_cliente.nombre as area_nombre,
                          COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre')
                ->join('requerimiento r', 'r.id = a.idrequerimiento')
                ->join('usuarios u_cliente', 'u_cliente.id = r.idusuarioempresa')
                ->join('areas ar_cliente', 'ar_cliente.id = u_cliente.idarea')
                ->join('empresas e', 'e.id = ar_cliente.idempresa')
                ->join('usuarios u_emp', 'u_emp.id = a.idempleado', 'left')
                ->join('servicios s', 's.id = a.idservicio', 'left')
                ->where('a.idarea_agencia', $idAreaAgencia)
                ->where('a.observacion_revision IS NOT NULL')
                ->where("a.observacion_revision != ''")
                ->whereIn('a.estado', ['en_proceso', 'pendiente_asignado', 'en_revision'])
                ->orderBy('a.fechacreacion', 'DESC')
                ->get()->getResultArray();
        }
}

