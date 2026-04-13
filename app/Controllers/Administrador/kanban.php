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
      $atencionModel     = new AtencionModel();

      $empresa = $empresaModel->find($idEmpresa);
      if (!$empresa) {
          throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no
  encontrada');
      }

      $areasAgencia  = $areasAgenciaModel->listarActivas();
      $idAreaAgencia = $idAreaAgencia ?? ($areasAgencia[0]['id'] ?? null);
      $areaActual    = $areasAgenciaModel->find($idAreaAgencia);

      // Obtener atenciones del área específica (las que ya tienen área asignada)
      $atenciones = $atencionModel->obtenerParaKanban($idEmpresa, $idAreaAgencia);

      // Columnas ORIGINALES (4 nada más)
      $columnas = [
          'pendiente_sin_asignar' => ['label' => 'POR APROBAR',  'color' => '#eab308',
  'items' => []],
          'en_proceso'            => ['label' => 'EN PROCESO',   'color' => '#a855f7',
  'items' => []],
          'en_revision'           => ['label' => 'EN REVISIÓN',  'color' => '#f97316',
  'items' => []],
          'finalizado'            => ['label' => 'ENTREGADO',    'color' => '#22c55e',
  'items' => []],
      ];

      foreach ($atenciones as $a) {
          $estado = $a['estado'];

          // Asignado -> en proceso visualmente
          if ($estado === 'pendiente_asignado') {
              $estado = 'en_proceso';
          }

          if (isset($columnas[$estado])) {
              $columnas[$estado]['items'][] = $a;
          }
      }

      $stats = $atencionModel->estadisticasPorEmpresa($idEmpresa);

      return view('admin/kanban', [
          'titulo'        => 'Kanban - ' . $empresa['nombreempresa'],
          'tituloPagina'  => 'TABLERO KANBAN',
          'paginaActual'  => 'kanban',
          'empresas'      => $empresaModel->findAll(),
          'empresa'       => $empresa,
          'idEmpresa'     => $idEmpresa,
          'areasAgencia'  => $areasAgencia,
          'areaActual'    => $areaActual,
          'idAreaAgencia' => $idAreaAgencia,
          'columnas'      => $columnas,
          'stats'         => $stats,
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
                a.id, a.titulo, a.estado, CAST(a.prioridad AS TEXT) AS prioridad_admin,
                a.idempleado, a.idrequerimiento, a.idarea_agencia,
                a.fechainicio, a.fechafin, a.fechacompletado, a.respuestatexto,
                a.observacion_revision,
                r.descripcion, r.objetivo_comunicacion, r.tipo_requerimiento,
                r.canales_difusion, r.publico_objetivo, r.formatos_solicitados,
                r.fecharequerida, r.prioridad AS prioridad_cliente,
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
      $archivos = [];

        return $this->response->setJSON(['status' => 'success', 'data' => $data, 'archivos' => $archivos]);
    }

    /**
     * Asignar empleado a una atención
     * Cambia estado: pendiente_sin_asignar → pendiente_asignado
     */
    public function asignarArea()
  {
      $json       = $this->request->getJSON(true);
      $idAtencion = $json['idatencion'];
      $idArea     = $json['idareaagencia'];
      $idAdmin    = $json['idadmin'] ?? 1;

      $atencionModel = new AtencionModel();

      // Asigna área y cambia estado
      $atencionModel->asignarArea($idAtencion, $idArea, $idAdmin);

      return $this->response->setJSON(['status' => 'success', 'msg' => 'Área asignada']);
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
    
  /**
   * Retorna áreas de agencia activas
   */
   public function areasAgencia()
  {
      $areasAgenciaModel = new \App\Models\AreasAgenciaModel();
      $areas = $areasAgenciaModel->listarActivas();

      // Debug: verifica que tenga datos
      if (empty($areas)) {
          return $this->response->setJSON(['error' => 'No hay áreas activas']);
      }

      return $this->response->setJSON($areas);
  }
 
  /**
   * Kanban del Responsable - Ve solo pedidos de SU área
   */
  public function responsable()
  {
      // Obtener el usuario logueado (responsable)
      $session = session();
      $idResponsable = $session->get('id') ?? 1; // Ajusta según tu sesión

      // Obtener el área del responsable
      $db = \Config\Database::connect();
      $responsable = $db->query("
          SELECT idarea_agencia
          FROM usuarios
          WHERE id = ? AND (rol = 'responsable' OR esresponsable = true)
      ", [$idResponsable])->getRowArray();

      if (!$responsable || !$responsable['idarea_agencia']) {
          return $this->response->setJSON(['error' => 'No tienes área asignada']);
      }

      $idAreaAgencia = $responsable['idarea_agencia'];

      // Obtener pedidos de esta área
      $atencionModel = new AtencionModel();
      $pedidos = $atencionModel->obtenerParaResponsable($idAreaAgencia);

      return view('responsable/kanban', [
          'pedidos' => $pedidos,
          'idArea' => $idAreaAgencia
      ]);
  }
  public function cambiarPrioridad()
{
    $db   = \Config\Database::connect();
    $json = $this->request->getJSON(true);

    $idAtencion = $json['idatencion'];
    $prioridad  = $json['prioridad'];

    $validas = ['Baja', 'Media', 'Alta'];
    if (!in_array($prioridad, $validas)) {
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Prioridad no válida']);
    }

    $db->query("UPDATE atencion SET prioridad = ? WHERE id = ?", [$prioridad, $idAtencion]);

    return $this->response->setJSON(['status' => 'success', 'msg' => 'Prioridad actualizada']);
}
}
