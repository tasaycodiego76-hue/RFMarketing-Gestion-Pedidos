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

    /* CLIENTE */

    /**
     * Obtiene pedidos ACTIVOS (excluye finalizados) para la bandeja principal del cliente.
     * @param mixed $usuarioId
     * @return array
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
              AND a.estado != 'finalizado'
            ORDER BY a.idrequerimiento DESC
        ";

        return $this->db->query($sql, [$usuarioId])->getResultArray();
    }

    /**
     * Cuenta el total de pedidos finalizados del cliente, con búsqueda opcional
     * @param mixed $usuarioId
     * @param mixed $busqueda
     * @return int
     */
    public function countPedidosHistorialCliente($usuarioId, $busqueda = '')
    {
        $sql = "
            SELECT COUNT(a.id) AS total
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            LEFT JOIN servicios s ON s.id = a.idservicio
            WHERE r.idusuarioempresa = ?
              AND a.estado = 'finalizado'
        ";
        $params = [$usuarioId];

        if (!empty($busqueda)) {
            $sql .= " AND (a.titulo LIKE ? 
                        OR CAST(a.prioridad AS text) LIKE ? 
                        OR s.nombre LIKE ? 
                        OR a.servicio_personalizado LIKE ? 
                        OR CAST(r.fechacreacion AS text) LIKE ? 
                        OR CAST(a.fechacompletado AS text) LIKE ?)";
            $busquedaParams = '%' . $busqueda . '%';
            $params = array_merge($params, [
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams
            ]);
        }

        $row = $this->db->query($sql, $params)->getRowArray();
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Obtiene los pedidos finalizados del cliente con paginación y búsqueda
     * @param mixed $usuarioId
     * @param mixed $limit
     * @param mixed $offset
     * @param mixed $busqueda
     * @return array
     */
    public function getPedidosHistorialClientePaginado($usuarioId, $limit = 10, $offset = 0, $busqueda = '')
    {
        $sql = "
            SELECT
                a.id AS atencion_id,
                a.idrequerimiento,
                a.titulo,
                a.estado,
                a.prioridad,
                a.fechacompletado,
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
              AND a.estado = 'finalizado'
        ";
        $params = [$usuarioId];

        if (!empty($busqueda)) {
            $sql .= " AND (a.titulo LIKE ? 
                        OR CAST(a.prioridad AS text) LIKE ? 
                        OR s.nombre LIKE ? 
                        OR a.servicio_personalizado LIKE ? 
                        OR CAST(r.fechacreacion AS text) LIKE ? 
                        OR CAST(a.fechacompletado AS text) LIKE ?)";
            $busquedaParams = '%' . $busqueda . '%';
            $params = array_merge($params, [
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams, 
                $busquedaParams
            ]);
        }

        $sql .= " ORDER BY a.fechacompletado DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Cuenta registros por estado filtrando por una empresa específica.
     * @param string $estado
     * @param int $idEmpresa
     * @return int|string
     */
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

    /**
     * Cuenta requerimientos activos para una empresa específica.
     * @param int $idEmpresa
     * @return int|string
     */
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

    /* ADMINISTRADOR */

    /**
     * Devuelve el número total de registros en un estado específico (Dashboard Global).
     * @param string $estado
     * @return int|string
     */
    public function contarPorEstado(string $estado): int
    {
        return $this->where('estado', $estado)->countAllResults();
    }

    /**
     * Devuelve la cantidad total de requerimientos activos (en proceso o asignados).
     * @return int|string
     */
    public function contarActivos(): int
    {
        return $this->whereIn('estado', ['pendiente_asignado', 'en_proceso'])->countAllResults();
    }

    /**
     * Cuenta requerimientos por aprobar (pendiente_sin_asignar) agrupados por área para una empresa.
     * ← APORTE rama-diego
     * @param int $idEmpresa
     * @return array
     */
    public function contarPorAprobarPorAreaEmpresa(int $idEmpresa): array
    {
        $rows = $this->db->table('atencion a')
            ->select('a.idarea_agencia, COUNT(*) as total')
            ->join('requerimiento r', 'r.id = a.idrequerimiento')
            ->join('usuarios u', 'u.id = r.idusuarioempresa')
            ->join('areas ar', 'ar.id = u.idarea')
            ->where('a.estado', 'pendiente_sin_asignar')
            ->where('ar.idempresa', $idEmpresa)
            ->groupBy('a.idarea_agencia')
            ->get()->getResultArray();

        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['idarea_agencia']] = (int) $row['total'];
        }
        return $stats;
    }

    /**
     * Genera estadísticas agrupadas para el Kanban de una empresa
     * @param int $idEmpresa
     * @return array|array{activos: int, completados: int, en_revision: int, por_aprobar: int|null}
     */
    public function estadisticasPorEmpresa(int $idEmpresa): array
    {
        $hoy = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));

        $result = $this->db->query("
            SELECT
                COUNT(CASE WHEN a.estado IN ('en_proceso', 'pendiente_asignado') THEN 1 END) AS activos,
                COUNT(CASE WHEN a.estado = 'en_revision' THEN 1 END) AS en_revision,
                COUNT(CASE WHEN a.estado IN ('pendiente_sin_asignar') THEN 1 END) AS por_aprobar,
                COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) AS completados,
                -- Métricas de SLA (Solo para los que no han finalizado)
                COUNT(CASE WHEN a.estado NOT IN ('finalizado', 'cancelado') AND CAST(r.fecharequerida AS DATE) < ? THEN 1 END) AS atrasados,
                COUNT(CASE WHEN a.estado NOT IN ('finalizado', 'cancelado') AND CAST(r.fecharequerida AS DATE) = ? THEN 1 END) AS hoy,
                COUNT(CASE WHEN a.estado NOT IN ('finalizado', 'cancelado') AND CAST(r.fecharequerida AS DATE) = ? THEN 1 END) AS manana,
                COUNT(CASE WHEN a.estado NOT IN ('finalizado', 'cancelado') AND CAST(r.fecharequerida AS DATE) > ? THEN 1 END) AS en_tiempo
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u ON u.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u.idarea
            WHERE ar.idempresa = ?
        ", [$hoy, $hoy, $manana, $manana, $idEmpresa])->getRowArray();

        return $result ?: [
            'activos' => 0, 'en_revision' => 0, 'por_aprobar' => 0, 'completados' => 0,
            'atrasados' => 0, 'hoy' => 0, 'manana' => 0, 'en_tiempo' => 0
        ];
    }

    /**
     * Obtiene los datos necesarios para renderizar las tarjetas en el tablero Kanban.
     * ← Se usa la versión de rama-diego: agrega a.fechacompletado en el SELECT
     * @param int $idEmpresa
     * @param int $idAreaAgencia
     * @return array
     */
    public function obtenerParaKanban(int $idEmpresa, int $idAreaAgencia): array
    {
        $sql = "
            SELECT
                a.id, a.titulo, a.estado, a.prioridad, a.fechafin, a.num_modificaciones,
                a.fechainicio, a.fechacreacion, a.fechacompletado, a.idempleado, a.idrequerimiento,
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
              AND (a.idarea_agencia = ? OR (a.idarea_agencia IS NULL AND a.servicio_personalizado IS NOT NULL))
            ORDER BY es_servicio_personalizado ASC, a.fechainicio DESC
        ";

        return $this->db->query($sql, [$idEmpresa, $idAreaAgencia])->getResultArray();
    }

    /**
     * Asigna un área de la agencia a una atención y registra el primer hito en el tracking.
     * @param int $idAtencion
     * @param int $idAreaAgencia
     * @param int $idAdmin
     * @return bool
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

    /* RESPONSABLE_AREA */

    /**
     * Obtiene pedidos para la vista de resumen del Responsable de Área.
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
     * Obtiene los pedidos que han sido asignados al área,
     * pero que aún no tienen un empleado asignado.
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
            e.nombreempresa,
            ar.nombre AS nombre_area
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
     * Verifica si una atención específica pertenece realmente al área del responsable.
     * @param int $idAtencion
     * @param int $idAreaAgencia
     * @return bool
     */
    public function atencionPerteneceAArea(int $idAtencion, int $idAreaAgencia): bool
    {
        $sql = "
            SELECT a.id 
            FROM atencion a
            LEFT JOIN usuarios u ON u.id = a.idempleado
            WHERE a.id = ? 
              AND (a.idarea_agencia = ? OR u.idarea_agencia = ?) 
            LIMIT 1";

        $query = $this->db->query($sql, [$idAtencion, $idAreaAgencia, $idAreaAgencia]);

        return $query->getNumRows() > 0;
    }

    /**
     * Obtiene todos los pedidos detallados de un área específica.
     * @param int $idAreaAgencia
     * @param array $estados
     * @return array
     */
    public function obtenerDetalladoPorArea(int $idAreaAgencia, array $estados = []): array
    {
        $builder = $this->db->table('atencion a')
            ->select('a.*, r.id as id_requerimiento, r.fechacreacion as fecha_req, r.fecharequerida,
                          e.nombreempresa as empresa_nombre, 
                          u_emp.nombre as empleado_nombre,
                          u_cliente.nombre as cliente_nombre,
                          ar_cliente.nombre as nombre_area,
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
            ->orderBy('a.fechacompletado', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Obtiene las tareas que han sido devueltas por el Admin u Observadas en Revisión.
     * @param int $idAreaAgencia
     * @return array
     */
    public function obtenerRetroalimentacionPorArea(int $idAreaAgencia): array
    {
        return $this->db->table('atencion a')
            ->select('a.*, r.id as id_requerimiento, r.fechacreacion as fecha_req, r.fecharequerida,
                          e.nombreempresa as empresa_nombre, 
                          u_emp.nombre as empleado_nombre,
                          u_emp.apellidos as empleado_apellidos,
                          u_cliente.nombre as cliente_nombre,
                          ar_cliente.nombre as area_nombre,
                          COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre,
                          (SELECT MAX(fecha_registro) FROM tracking WHERE idatencion = a.id) as fecha_retro')
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
            ->orderBy('fecha_retro', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Obtiene datos de productividad por empleado (En Proceso | Completados).
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @return array
     */
    public function getMetricasProductividad(int $idAreaAgencia): array
    {
        $sql = "
            SELECT 
                u.nombre,
                u.apellidos,
                COUNT(CASE WHEN a.estado IN ('en_proceso', 'pendiente_asignado') THEN 1 END) as total_proceso,
                COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) as total_completado,
                COUNT(a.id) as total_tareas
            FROM usuarios u
            LEFT JOIN atencion a ON a.idempleado = u.id
            WHERE u.idarea_agencia = ?
              AND u.rol = 'empleado'
              AND (u.estado = true OR u.estado = 't' OR u.estado = '1')
            GROUP BY u.id, u.nombre, u.apellidos
            ORDER BY total_tareas DESC";

        return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
    }

    /**
     * Obtiene la distribución de carga actual (Tareas activas por empleado).
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @return array
     */
    public function getMetricasDistribucionCarga(int $idAreaAgencia): array
    {
        $sql = "
            SELECT 
                CONCAT(u.nombre, ' ' ,u.apellidos) AS nombre_completo,
                COUNT(a.id) as cantidad_tareas
            FROM usuarios u
            LEFT JOIN atencion a ON a.idempleado = u.id AND a.estado NOT IN ('finalizado', 'cancelado')
            WHERE u.idarea_agencia = ? 
              AND (u.estado = true OR u.estado = 't' OR u.estado = '1')
              AND u.rol = 'empleado'
            GROUP BY u.id, u.nombre, u.apellidos";

        return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
    }

    /**
     * Obtiene la tendencia de tareas completadas en los últimos 7 días.
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @return array
     */
    public function getMetricasTendenciaSemanal(int $idAreaAgencia): array
    {
        $sql = "
            SELECT 
                EXTRACT(ISODOW FROM fechacompletado) as numero_dia,
                COUNT(*) as total_finalizados
            FROM atencion
            WHERE idarea_agencia = ?
              AND estado = 'finalizado'
              AND fechacompletado >= date_trunc('week', CURRENT_DATE)
            GROUP BY numero_dia
            ORDER BY numero_dia ASC";

        return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
    }

    /**
     * Obtiene el tiempo promedio de resolución por empleado en horas.
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @return array
     */
    public function getMetricasTiempoPromedio(int $idAreaAgencia): array
    {
        $sql = "
            SELECT 
                u.nombre,
                COALESCE(ROUND(AVG(EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600)::numeric, 2), 0) as promedio_horas
            FROM usuarios u
            LEFT JOIN atencion a ON a.idempleado = u.id 
                AND a.estado = 'finalizado'
                AND a.fechainicio IS NOT NULL 
                AND a.fechacompletado IS NOT NULL
            WHERE u.idarea_agencia = ?
              AND u.rol = 'empleado'
              AND (u.estado = true OR u.estado = 't' OR u.estado = '1')
            GROUP BY u.id, u.nombre
            ORDER BY promedio_horas ASC";

        return $this->db->query($sql, [$idAreaAgencia])->getResultArray();
    }

    /**
     * Obtiene el listado de Requerimientos para el reporte detallado, agrupado por empresa.
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @param string|null $desde
     * @param string|null $hasta
     * @param array $filtros
     * @return array
     */
    public function obtenerReporteDetallado(int $idAreaAgencia, ?string $desde = null, ?string $hasta = null, array $filtros = []): array
    {
        $params = [$idAreaAgencia];
        $where = " WHERE a.idarea_agencia = ? ";

        if (!empty($desde)) {
            $where .= " AND a.fechacreacion >= ? ";
            $params[] = $desde . ' 00:00:00';
        }
        if (!empty($hasta)) {
            $where .= " AND a.fechacreacion <= ? ";
            $params[] = $hasta . ' 23:59:59';
        }

        if (!empty($filtros['idempresa'])) {
            $where .= " AND e.id = ? ";
            $params[] = $filtros['idempresa'];
        }
        if (!empty($filtros['idempleado'])) {
            $where .= " AND a.idempleado = ? ";
            $params[] = $filtros['idempleado'];
        }
        if (!empty($filtros['idservicio'])) {
            $where .= " AND a.idservicio = ? ";
            $params[] = $filtros['idservicio'];
        }
        if (!empty($filtros['solo_completados'])) {
            $where .= " AND a.estado = 'finalizado' ";
        }
        if (!empty($filtros['solo_retrasos'])) {
            $where .= " AND (
                (a.estado = 'pendiente_asignado' AND a.fechacreacion < CURRENT_DATE - INTERVAL '3 days') OR
                (a.estado = 'en_proceso' AND r.fecharequerida < CURRENT_DATE)
            ) ";
        }
        if (empty($filtros['incluir_cancelados'])) {
            $where .= " AND a.estado != 'cancelado' ";
        }

        $sql = "
            SELECT 
                a.id, a.titulo, a.estado, a.prioridad, a.fechacreacion, a.fechainicio, a.fechacompletado, 
                a.observacion_revision, a.idempleado,
                COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre,
                e.nombreempresa as empresa_nombre,
                u_emp.nombre as empleado_nombre,
                u_emp.apellidos as empleado_apellidos,
                CASE 
                    WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL 
                    THEN ROUND(EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600, 2)
                    ELSE 0 
                END as horas_usadas
            FROM atencion a
            JOIN requerimiento r ON r.id = a.idrequerimiento
            JOIN usuarios u_cli ON r.idusuarioempresa = u_cli.id
            JOIN areas ar_cli ON u_cli.idarea = ar_cli.id
            JOIN empresas e ON ar_cli.idempresa = e.id
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u_emp ON u_emp.id = a.idempleado
            $where
            ORDER BY e.nombreempresa ASC, a.fechacreacion DESC
        ";

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Obtiene métricas de rendimiento por técnico en un periodo.
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @param string|null $desde
     * @param string|null $hasta
     * @return array
     */
    public function obtenerMetricasTecnicosReporte(int $idAreaAgencia, ?string $desde = null, ?string $hasta = null): array
    {
        $whereFecha = "";
        $params = [];

        if (!empty($desde) && !empty($hasta)) {
            $whereFecha = " AND a.fechacreacion >= ? AND a.fechacreacion <= ? ";
            $params[] = $desde . ' 00:00:00';
            $params[] = $hasta . ' 23:59:59';
        }

        $params[] = $idAreaAgencia;

        $sql = "
            SELECT 
                u.nombre, u.apellidos,
                COUNT(a.id) as asignados,
                COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) as completados,
                COUNT(CASE WHEN a.estado = 'en_proceso' THEN 1 END) as en_proceso,
                CASE 
                    WHEN COUNT(a.id) > 0 THEN ROUND((COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END)::numeric / COUNT(a.id)) * 100, 1)
                    ELSE 0 
                END as eficiencia,
                COALESCE(SUM(CASE 
                    WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL 
                    THEN EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600
                    ELSE 0 
                END), 0) as horas_totales
            FROM usuarios u
            LEFT JOIN atencion a ON a.idempleado = u.id $whereFecha
            WHERE u.idarea_agencia = ? 
              AND u.rol = 'empleado'
              AND (u.estado = true OR u.estado = 't' OR u.estado = '1')
            GROUP BY u.id, u.nombre, u.apellidos
            ORDER BY asignados DESC, eficiencia DESC
        ";

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Identifica casos críticos (retrasos o falta de info) para la sección de Alertas.
     * ← APORTE HEAD (tuyo)
     * @param int $idAreaAgencia
     * @param string|null $desde
     * @param string|null $hasta
     * @return array
     */
    public function obtenerAlertasReporte(int $idAreaAgencia, ?string $desde = null, ?string $hasta = null): array
    {
        $params = [$idAreaAgencia];
        $whereFecha = "";

        if (!empty($desde) && !empty($hasta)) {
            $whereFecha = " AND a.fechacreacion >= ? AND a.fechacreacion <= ? ";
            $params[] = $desde . ' 00:00:00';
            $params[] = $hasta . ' 23:59:59';
        }

        $sql = "
            SELECT 
                a.id, a.titulo, a.estado,
                e.nombreempresa as empresa,
                a.fechacreacion, 
                r.fecharequerida,
                CASE 
                    WHEN a.estado = 'pendiente_asignado' AND a.fechacreacion < CURRENT_DATE - INTERVAL '3 days' THEN 'Esperando más de 3 días por asignación'
                    WHEN a.estado = 'en_proceso' AND r.fecharequerida < CURRENT_DATE THEN 'Pedido con fecha vencida'
                    WHEN a.observacion_revision IS NOT NULL AND a.estado != 'finalizado' THEN 'Requiere correcciones técnicas'
                    ELSE 'Revisión pendiente'
                END as motivo_alerta
            FROM atencion a
            JOIN requerimiento r ON r.id = a.idrequerimiento
            JOIN usuarios u ON u.id = r.idusuarioempresa
            JOIN areas ar ON ar.id = u.idarea
            JOIN empresas e ON e.id = ar.idempresa
            WHERE a.idarea_agencia = ?
              $whereFecha
              AND a.estado NOT IN ('finalizado', 'cancelado')
              AND (
                (a.estado = 'pendiente_asignado' AND a.fechacreacion < CURRENT_DATE - INTERVAL '3 days') OR
                (a.estado = 'en_proceso' AND r.fecharequerida < CURRENT_DATE) OR
                (a.observacion_revision IS NOT NULL AND a.observacion_revision != '')
              )
            LIMIT 10
        ";

        return $this->db->query($sql, $params)->getResultArray();
    }

    /* EMPLEADO */

    /**
     * Obtiene pedidos detallados para un empleado, permitiendo filtrar por estado.
     * @param int $idEmpleado
     * @param array $estados
     * @return array
     */
    public function obtenerDetalladoPorEmpleado(int $idEmpleado, array $estados = []): array
    {
        $builder = $this->db->table('atencion a')
            ->select('a.*, r.id as id_requerimiento, r.fechacreacion as fecha_req, r.fecharequerida, 
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
            ->orderBy('a.fechacompletado', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Obtiene tareas de un empleado filtradas por un estado exacto.
     * @param int $idEmpleado
     * @param string $estado
     * @return array<array<bool|float|int|object|string|null>|object>
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
     * Obtiene el historial de pedidos finalizados con información detallada de empresa, área y empleado.
     * @return array
     */
    public function obtenerHistorialFinalizado(): array
    {
        $sql = "
            SELECT 
                a.id, a.titulo, a.fechacompletado,
                e.id as empresa_id,
                e.nombreempresa as empresa_nombre,
                ar_ag.nombre as area_nombre,
                u.nombre as empleado_nombre,
                u.apellidos as empleado_apellidos,
                COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u_sol.idarea
            INNER JOIN empresas e ON e.id = ar.idempresa
            LEFT JOIN areas_agencia ar_ag ON ar_ag.id = a.idarea_agencia
            LEFT JOIN usuarios u ON u.id = a.idempleado
            LEFT JOIN servicios s ON s.id = a.idservicio
            WHERE a.estado = 'finalizado'
            ORDER BY a.fechacompletado DESC
        ";

        return $this->db->query($sql)->getResultArray();
    }

    /**
     * Obtiene la carga de trabajo (pedidos venciendo) para hoy y mañana.
     * ← APORTE rama-diego
     * @param int $idAreaAgencia
     * @return array
     */
    public function obtenerCargaPorFecha(int $idAreaAgencia): array
    {
        $hoy = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));

        $sql = "
            SELECT 
                COUNT(CASE WHEN CAST(r.fecharequerida AS DATE) = ? THEN 1 END) as hoy,
                COUNT(CASE WHEN CAST(r.fecharequerida AS DATE) = ? THEN 1 END) as manana
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            WHERE a.idarea_agencia = ? 
              AND a.estado != 'cancelado'
              AND a.estado != 'finalizado'
        ";

        return $this->db->query($sql, [$hoy, $manana, $idAreaAgencia])->getRowArray();
    }

    /**
     * [NOTIFICACIONES ADMIN]
     * Esta función busca todos los pedidos que los empleados acaban de enviar a revisión.
     * Sirve para llenar la campana de notificaciones de la parte superior.
     */
    public function getRevisionesParaNotificaciones(): array
    {
        return $this->db->table('atencion a')
            ->select('a.id, a.titulo, a.prioridad, a.fechacreacion, 
                      e.nombreempresa as empresa, e.id as idempresa,
                      aa.nombre as area_nombre, aa.id as idarea_agencia,
                      u_emp.nombre as empleado_nombre,
                      u_emp.apellidos as empleado_apellidos')
            ->join('requerimiento r', 'r.id = a.idrequerimiento')
            ->join('usuarios u_cli', 'u_cli.id = r.idusuarioempresa')
            ->join('areas ar_cli', 'ar_cli.id = u_cli.idarea')
            ->join('empresas e', 'e.id = ar_cli.idempresa')
            ->join('areas_agencia aa', 'aa.id = a.idarea_agencia', 'left')
            ->join('usuarios u_emp', 'u_emp.id = a.idempleado', 'left')
            ->where('a.estado', 'en_revision')
            ->orderBy('a.fechacreacion', 'DESC') // O usar la fecha de entrega si existiera
            ->get()->getResultArray();
    }

    /**
     * Obtiene el listado detallado de atenciones para el reporte de Administrador.
     * @param string|null $desde
     * @param string|null $hasta
     * @param array $filtros
     * @return array
     */
    public function obtenerReporteDetalladoAdmin(?string $desde = null, ?string $hasta = null, array $filtros = []): array
    {
        $params = [];
        $where = " WHERE 1=1 ";

        if (!empty($filtros['idarea_int'])) {
            $where .= " AND a.idarea_agencia = ? ";
            $params[] = (int)$filtros['idarea_int'];
        }
        if (!empty($filtros['idempresa'])) {
            $where .= " AND e.id = ? ";
            $params[] = (int)$filtros['idempresa'];
        }
        if (!empty($filtros['idempleado'])) {
            $where .= " AND a.idempleado = ? ";
            $params[] = (int)$filtros['idempleado'];
        }
        if (!empty($desde)) {
            $where .= " AND a.fechacreacion >= ? ";
            $params[] = $desde . ' 00:00:00';
        }
        if (!empty($hasta)) {
            $where .= " AND a.fechacreacion <= ? ";
            $params[] = $hasta . ' 23:59:59';
        }
        if (!empty($filtros['solo_completados'])) {
            $where .= " AND a.estado = 'finalizado' ";
        }

        $sql = "SELECT a.id, a.titulo, a.estado, a.fechacreacion, e.nombreempresa as empresa_nombre,
                       aa.nombre as area_agencia_nombre,
                       u_emp.nombre as empleado_nombre, u_emp.apellidos as empleado_apellidos,
                       COALESCE(s.nombre, a.servicio_personalizado) as servicio_nombre,
                       CASE WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL 
                       THEN ROUND(EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600, 2) ELSE 0 END as horas_usadas
                FROM atencion a
                JOIN requerimiento r ON r.id = a.idrequerimiento
                JOIN usuarios u_cli ON r.idusuarioempresa = u_cli.id
                JOIN areas ar_cli ON u_cli.idarea = ar_cli.id
                JOIN empresas e ON ar_cli.idempresa = e.id
                JOIN areas_agencia aa ON a.idarea_agencia = aa.id
                LEFT JOIN servicios s ON s.id = a.idservicio
                LEFT JOIN usuarios u_emp ON u_emp.id = a.idempleado
                $where 
                ORDER BY aa.nombre ASC, e.nombreempresa ASC, a.fechacreacion DESC";

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Obtiene métricas del equipo técnico para el Administrador.
     * @param int|null $idAreaInt
     * @return array
     */
    public function obtenerMetricasTecnicosAdmin(?int $idAreaInt = null): array
    {
        $whereMet = $idAreaInt ? " AND u.idarea_agencia = ? " : "";
        $params = $idAreaInt ? [$idAreaInt] : [];

        $sql = "SELECT u.nombre, u.apellidos, COUNT(a.id) as asignados,
                       COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) as completados,
                       CASE WHEN COUNT(a.id) > 0 THEN ROUND((COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END)::numeric / COUNT(a.id)) * 100, 1) ELSE 0 END as eficiencia,
                       COALESCE(SUM(CASE WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL THEN EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600 ELSE 0 END), 0) as horas_totales
                FROM usuarios u 
                LEFT JOIN atencion a ON a.idempleado = u.id
                WHERE u.rol = 'empleado' $whereMet 
                GROUP BY u.id, u.nombre, u.apellidos 
                ORDER BY eficiencia DESC";

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Obtiene los datos necesarios para la vista previa ajax del Administrador.
     * @param string|null $desde
     * @param string|null $hasta
     * @param array $filtros
     * @return array
     */
    public function obtenerVistaPreviaAdmin(?string $desde = null, ?string $hasta = null, array $filtros = []): array
    {
        $params = [];
        $where = " WHERE 1=1 ";

        if (!empty($filtros['idarea_int']) && $filtros['idarea_int'] > 0) {
            $where .= " AND a.idarea_agencia = ? ";
            $params[] = (int)$filtros['idarea_int'];
        }
        if (!empty($filtros['idempresa'])) {
            $where .= " AND e.id = ? ";
            $params[] = (int)$filtros['idempresa'];
        }
        if (!empty($desde)) {
            $where .= " AND a.fechacreacion >= ? ";
            $params[] = $desde . ' 00:00:00';
        }
        if (!empty($hasta)) {
            $where .= " AND a.fechacreacion <= ? ";
            $params[] = $hasta . ' 23:59:59';
        }

        $sql = "SELECT a.id, a.estado, 
                       CASE WHEN a.fechacompletado IS NOT NULL AND a.fechainicio IS NOT NULL 
                       THEN ROUND(EXTRACT(EPOCH FROM (a.fechacompletado - a.fechainicio)) / 3600, 2) ELSE 0 END as horas
                FROM atencion a
                JOIN requerimiento r ON r.id = a.idrequerimiento
                JOIN usuarios u_cli ON r.idusuarioempresa = u_cli.id
                JOIN areas ar_cli ON u_cli.idarea = ar_cli.id
                JOIN empresas e ON ar_cli.idempresa = e.id
                $where";

        return $this->db->query($sql, $params)->getResultArray();
    }
}