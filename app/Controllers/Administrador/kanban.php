<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\AtencionModel;

class kanban extends Controller
{
    public function index($idEmpresa, $idAreaAgencia = null)
    {
        $empresaModel = new EmpresaModel();
        $areasAgenciaModel = new AreasAgenciaModel();
        $atencionModel = new AtencionModel();

        $empresa = $empresaModel->find($idEmpresa);
        if (!$empresa) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada');
        }

        $areasAgencia = $areasAgenciaModel->listarActivas();
        $idAreaAgencia = $idAreaAgencia ?? ($areasAgencia[0]['id'] ?? null);
        $areaActual = $areasAgenciaModel->find($idAreaAgencia);

        $atenciones = $atencionModel->obtenerParaKanban((int) $idEmpresa, (int) $idAreaAgencia);

        $columnas = [
            'pendiente_sin_asignar' => ['label' => 'POR APROBAR', 'color' => '#eab308', 'items' => []],
            'en_proceso' => ['label' => 'EN PROCESO', 'color' => '#a855f7', 'items' => []],
            'en_revision' => ['label' => 'EN REVISIÓN', 'color' => '#f97316', 'items' => []],
            'finalizado' => ['label' => 'ENTREGADO', 'color' => '#22c55e', 'items' => []],
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

        $stats = $atencionModel->estadisticasPorEmpresa((int) $idEmpresa);

        return view('admin/kanban', [
            'titulo' => 'Kanban - ' . $empresa['nombreempresa'],
            'tituloPagina' => 'TABLERO KANBAN',
            'paginaActual' => 'kanban',
            'empresas' => $empresaModel->listarActivas(),
            'empresa' => $empresa,
            'idEmpresa' => $idEmpresa,
            'areasAgencia' => $areasAgencia,
            'areaActual' => $areaActual,
            'idAreaAgencia' => $idAreaAgencia,
            'columnas' => $columnas,
            'stats' => $stats,
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

        // 1. Consulta principal (sin cambios aquí)
        $data = $db->query("
        SELECT
            a.id, a.titulo, a.estado,
            CAST(a.prioridad AS TEXT) AS prioridad_admin,
            a.idrequerimiento, a.fechainicio, a.fechafin, a.fechacompletado,
            a.url_entrega, a.num_modificaciones,
            r.descripcion, r.objetivo_comunicacion, r.tipo_requerimiento,
            r.canales_difusion, r.publico_objetivo, r.formatos_solicitados,
            r.fecharequerida, r.prioridad AS prioridad_cliente,
            r.tiene_materiales, r.url_subida, r.formato_otros,
            e.nombreempresa,
            aa.nombre AS area_nombre,
            u.nombre AS empleado_nombre, u.apellidos AS empleado_apellidos
        FROM atencion a
        LEFT JOIN requerimiento r  ON r.id = a.idrequerimiento
        LEFT JOIN usuarios u_sol   ON u_sol.id = r.idusuarioempresa
        LEFT JOIN areas ar         ON ar.id = u_sol.idarea
        LEFT JOIN empresas e       ON e.id = ar.idempresa
        LEFT JOIN usuarios u       ON u.id = a.idempleado
        LEFT JOIN areas_agencia aa ON aa.id = a.idarea_agencia
        WHERE a.id = ?
    ", [$idAtencion])->getRowArray();

        if (!$data) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Atención no encontrada']);
        }

        // 2. Consulta de archivos
        $archivos = $db->table('archivos')
            ->select('nombre, ruta')
            ->where('idatencion', $idAtencion)
            ->orWhere('idrequerimiento', $data['idrequerimiento'])
            ->get()
            ->getResultArray();

        // --- SOLUCIÓN PARA LA CARPETA PUBLIC/UPLOADS ---
        // Recorremos los archivos para generar la URL pública
        foreach ($archivos as &$archivo) {
            // base_url() generará: http://localhost/proyecto/public/uploads/nombre.ext
            // Esto asume que en tu DB la ruta se guarda como: uploads/archivo.jpg
            $archivo['url_completa'] = base_url($archivo['ruta']);
        }

        // 3. Procesamiento de datos adicionales
        $data['canales_difusion'] = json_decode($data['canales_difusion'] ?? '[]', true);
        $data['formatos_solicitados'] = json_decode($data['formatos_solicitados'] ?? '[]', true);

        foreach (['fecharequerida', 'fechainicio', 'fechafin', 'fechacompletado'] as $campo) {
            $data[$campo] = !empty($data[$campo]) ? date('d M Y', strtotime($data[$campo])) : '—';
        }

        $data['empleado_fullname'] = trim(($data['empleado_nombre'] ?? '') . ' ' . ($data['empleado_apellidos'] ?? '')) ?: 'Sin asignar';

        // 4. Retorno de respuesta
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data,
            'archivos' => $archivos // El JS ahora usará 'url_completa'
        ]);
    }

    /**
     * Asignar área a una atención
     */
    public function asignarArea()
    {
        $json = $this->request->getJSON(true);
        $idAtencion = $json['idatencion'];
        $idArea = $json['idareaagencia'];
        $idAdmin = $json['idadmin'] ?? 1;

        $atencionModel = new AtencionModel();
        $atencionModel->asignarArea($idAtencion, $idArea, $idAdmin);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Área asignada']);
    }

    /**
     * Cambiar estado de una atención
     */
    public function cambiarEstado()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $nuevoEstado = $json['estado'];
        $idAdmin = $json['idadmin'] ?? 1;
        $accion = $json['accion'] ?? 'Cambio de estado';

        $estadosValidos = ['pendiente_sin_asignar', 'pendiente_asignado', 'en_proceso', 'en_revision', 'finalizado', 'cancelado'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Estado no válido']);
        }

        if ($nuevoEstado === 'finalizado') {
            $db->query("UPDATE atencion SET estado = ?, fechacompletado = NOW() WHERE id = ?", [$nuevoEstado, $idAtencion]);
        } else {
            $db->query("UPDATE atencion SET estado = ? WHERE id = ?", [$nuevoEstado, $idAtencion]);
        }

        $db->query("
            INSERT INTO tracking (idatencion, idusuario, accion, estado)
            VALUES (?, ?, ?, ?)
        ", [$idAtencion, $idAdmin, $accion, $nuevoEstado]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Estado actualizado']);
    }

    /**
     * Cancelar una atención
     */
    public function cancelar()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $motivo = $json['motivo'] ?? 'Sin motivo';
        $idAdmin = $json['idadmin'] ?? 1;

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
        $areasAgenciaModel = new AreasAgenciaModel();
        $areas = $areasAgenciaModel->listarActivas();

        if (empty($areas)) {
            return $this->response->setJSON(['error' => 'No hay áreas activas']);
        }

        return $this->response->setJSON($areas);
    }

    /**
     * Kanban del Responsable
     */
    public function responsable()
    {
        $session = session();
        $idResponsable = $session->get('id') ?? 1;

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
        $atencionModel = new AtencionModel();
        $pedidos = $atencionModel->obtenerParaResponsable($idAreaAgencia);

        return view('responsable/kanban', [
            'pedidos' => $pedidos,
            'idArea' => $idAreaAgencia
        ]);
    }

    /**
     * Cambiar prioridad de una atención
     */
    public function cambiarPrioridad()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $prioridad = $json['prioridad'];

        $validas = ['Baja', 'Media', 'Alta'];
        if (!in_array($prioridad, $validas)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Prioridad no válida']);
        }

        $db->query("UPDATE atencion SET prioridad = ? WHERE id = ?", [$prioridad, $idAtencion]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Prioridad actualizada']);
    }
}