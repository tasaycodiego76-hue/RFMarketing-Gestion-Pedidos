<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\AtencionModel;

class Kanban extends Controller
{
    public function index($idEmpresa, $idAreaAgencia = null)
    {
        $empresaModel      = new EmpresaModel();
        $areasAgenciaModel = new AreasAgenciaModel();
        $db                = \Config\Database::connect();

        $empresas = $empresaModel->findAll();

        $empresa = $empresaModel->find($idEmpresa);
        if (!$empresa) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada');
        }

        $areasAgencia  = $areasAgenciaModel->listarActivas();
        $idAreaAgencia = $idAreaAgencia ?? ($areasAgencia[0]['id'] ?? null);
        $areaActual    = $areasAgenciaModel->find($idAreaAgencia);

        $sql = "
            SELECT 
                a.id, a.titulo, a.estado, a.prioridad, a.fechafin,
                a.fechainicio, a.fechacreacion, a.idempleado, a.idrequerimiento,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                r.idempresa, r.fecharequerida,
                e.nombreempresa,
                u.nombre AS empleado_nombre,
                u.apellidos AS empleado_apellidos,
                u.idarea_agencia
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN empresas e ON e.id = r.idempresa
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u ON u.id = a.idempleado
            WHERE r.idempresa = ?
              AND a.estado != 'cancelado'
              AND (
                  a.estado = 'pendiente_sin_asignar'
                  OR u.idarea_agencia = ?
              )
            ORDER BY a.fechainicio DESC
        ";

        $atenciones = $db->query($sql, [$idEmpresa, $idAreaAgencia])->getResultArray();

        $columnas = [
            'pendiente_sin_asignar' => ['label' => 'POR APROBAR',  'color' => '#eab308', 'items' => []],
            'en_proceso'            => ['label' => 'EN PROCESO',   'color' => '#a855f7', 'items' => []],
            'en_revision'           => ['label' => 'EN REVISIÓN',  'color' => '#f97316', 'items' => []],
            'finalizado'            => ['label' => 'ENTREGADO',    'color' => '#22c55e', 'items' => []],
        ];

        foreach ($atenciones as $a) {
            $estado = $a['estado'];
            if ($estado === 'pendiente_asignado') {
                $estado = 'en_proceso';
            }
            if (isset($columnas[$estado])) {
                $columnas[$estado]['items'][] = $a;
            }
        }

        $totalPorEmpresa = $db->query("
            SELECT 
                COUNT(CASE WHEN a.estado IN ('en_proceso', 'en_revision', 'pendiente_asignado') THEN 1 END) AS activos,
                COUNT(CASE WHEN a.estado = 'pendiente_sin_asignar' THEN 1 END) AS por_aprobar,
                COUNT(CASE WHEN a.estado = 'finalizado' THEN 1 END) AS completados
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            WHERE r.idempresa = ?
        ", [$idEmpresa])->getRowArray();

        return view('admin/kanban', [
            'titulo'        => 'Kanban - ' . $empresa['nombreempresa'],
            'tituloPagina'  => 'TABLERO KANBAN',
            'paginaActual'  => 'kanban',
            'empresas'      => $empresas,
            'empresa'       => $empresa,
            'idEmpresa'     => $idEmpresa,
            'areasAgencia'  => $areasAgencia,
            'areaActual'    => $areaActual,
            'idAreaAgencia' => $idAreaAgencia,
            'columnas'      => $columnas,
            'stats'         => $totalPorEmpresa,
        ]);
    }

    /**
     * Retorna empleados de un área de agencia (para el modal Asignar)
     */
    public function empleadosPorArea($idAreaAgencia)
    {
        $db = \Config\Database::connect();
        $empleados = $db->query("
            SELECT id, nombre, apellidos 
            FROM usuarios 
            WHERE idarea_agencia = ? AND rol = 'empleado' AND estado = true
            ORDER BY nombre
        ", [$idAreaAgencia])->getResultArray();

        return $this->response->setJSON($empleados);
    }

    /**
     * Detalle de una atención (para el modal Ver)
     */
    public function detalle($idAtencion)
    {
        $db = \Config\Database::connect();
        $data = $db->query("
            SELECT 
                a.*, 
                r.descripcion, r.objetivo_comunicacion, r.tipo_requerimiento,
                r.canales_difusion, r.publico_objetivo, r.formatos_solicitados,
                r.fecharequerida,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                e.nombreempresa,
                u.nombre AS empleado_nombre, u.apellidos AS empleado_apellidos
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN empresas e ON e.id = r.idempresa
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u ON u.id = a.idempleado
            WHERE a.id = ?
        ", [$idAtencion])->getRowArray();

        if (!$data) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Atención no encontrada']);
        }

        // Archivos vinculados
        $archivos = $db->query("SELECT * FROM archivos WHERE idatencion = ?", [$idAtencion])->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $data, 'archivos' => $archivos]);
    }

    /**
     * Asignar empleado a una atención
     * Cambia estado: pendiente_sin_asignar → pendiente_asignado
     */
    public function asignar()
    {
        $db   = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $idEmpleado = $json['idempleado'];
        $idAdmin    = $json['idadmin'] ?? 1;

        // Obtener empleado anterior (si hay)
        $atencion = $db->query("SELECT idempleado FROM atencion WHERE id = ?", [$idAtencion])->getRowArray();
        $empleadoAnterior = $atencion['idempleado'] ?? null;

        // Actualizar atencion
        $db->query("UPDATE atencion SET idempleado = ?, estado = 'pendiente_asignado' WHERE id = ?", [$idEmpleado, $idAtencion]);

        // Registrar en historial_asignaciones
        $db->query("
            INSERT INTO historial_asignaciones (idpedido, idempleado_anterior, idempleado, idadmin)
            VALUES (?, ?, ?, ?)
        ", [$idAtencion, $empleadoAnterior, $idEmpleado, $idAdmin]);

        // Registrar tracking
        $db->query("
            INSERT INTO tracking (idatencion, idusuario, accion, estado)
            VALUES (?, ?, 'Empleado asignado', 'pendiente_asignado')
        ", [$idAtencion, $idAdmin]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Empleado asignado correctamente']);
    }

    /**
     * Cambiar estado de una atención (Aprobar, Regresar, etc.)
     * Transiciones válidas:
     *   pendiente_asignado → en_proceso
     *   en_proceso → en_revision
     *   en_revision → finalizado (aprobar)
     *   en_revision → en_proceso (regresar)
     */
    public function cambiarEstado()
    {
        $db   = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion  = $json['idatencion'];
        $nuevoEstado = $json['estado'];
        $idAdmin     = $json['idadmin'] ?? 1;
        $accion      = $json['accion'] ?? 'Cambio de estado';

        // Validar que el nuevo estado sea válido en el ENUM
        $estadosValidos = ['pendiente_sin_asignar', 'pendiente_asignado', 'en_proceso', 'en_revision', 'finalizado', 'cancelado'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Estado no válido']);
        }

        // Campos extra según el estado
        $extra = '';
        if ($nuevoEstado === 'finalizado') {
            $extra = ", fechacompletado = NOW()";
        }

        $db->query("UPDATE atencion SET estado = '{$nuevoEstado}' {$extra} WHERE id = ?", [$idAtencion]);

        // Tracking
        $db->query("
            INSERT INTO tracking (idatencion, idusuario, accion, estado)
            VALUES (?, ?, ?, '{$nuevoEstado}')
        ", [$idAtencion, $idAdmin, $accion]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Estado actualizado']);
    }

    /**
     * Cancelar una atención
     */
    public function cancelar()
    {
        $db   = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $motivo     = $json['motivo'] ?? 'Sin motivo';
        $idAdmin    = $json['idadmin'] ?? 1;

        $db->query("
            UPDATE atencion 
            SET estado = 'cancelado', cancelacionmotivo = ?, fechacancelacion = NOW() 
            WHERE id = ?
        ", [$motivo, $idAtencion]);

        $db->query("
            INSERT INTO tracking (idatencion, idusuario, accion, estado)
            VALUES (?, ?, 'Pedido cancelado', 'cancelado')
        ", [$idAtencion, $idAdmin]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Pedido cancelado']);
    }
}
