<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\AtencionModel;
use App\Models\ArchivoModel;

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
     * Detalle de una atención (FULL DATA)
     */
    public function detalle($idAtencion)
    {
        $db = \Config\Database::connect();

        // 1. Consulta principal 
        $data = $db->query("
            SELECT
                a.id, a.titulo, a.estado, a.num_modificaciones,
                CAST(a.prioridad AS TEXT) AS prioridad_admin,
                a.idempleado, a.idrequerimiento, a.idarea_agencia,
                a.fechainicio, a.fechafin, a.fechacompletado,
                a.observacion_revision, a.url_entrega,
                r.descripcion, r.objetivo_comunicacion, r.tipo_requerimiento,
                r.canales_difusion, r.publico_objetivo, r.formatos_solicitados, r.formato_otros,
                r.fecharequerida, r.fechacreacion AS r_fechacreacion, r.prioridad AS prioridad_cliente,
                r.url_subida,
                COALESCE(s.nombre, a.servicio_personalizado) AS servicio,
                e.nombreempresa,
                u_sol.nombre AS cliente_nombre,
                ar.nombre AS area_solicitante_nombre,
                aa.nombre AS area_nombre,
                u.nombre    AS empleado_nombre,
                u.apellidos AS empleado_apellidos
            FROM atencion a
            LEFT JOIN requerimiento r  ON r.id     = a.idrequerimiento
            LEFT JOIN usuarios u_sol   ON u_sol.id = r.idusuarioempresa
            LEFT JOIN areas ar         ON ar.id    = u_sol.idarea
            LEFT JOIN empresas e       ON e.id     = ar.idempresa
            LEFT JOIN servicios s      ON s.id     = a.idservicio
            LEFT JOIN usuarios u       ON u.id     = a.idempleado
            LEFT JOIN areas_agencia aa ON aa.id    = a.idarea_agencia
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

        // 4. Retorno de respuesta (archivos ya vienen con url_completa arriba)
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data,
            'archivos' => $archivos,
        ]);
    }

    /**
     * Asignar área a una atención (Flujo Admin)
     */
    public function asignarArea()
    {
        $json = $this->request->getJSON(true);
        $idAtencion = $json['idatencion'];
        $idArea = $json['idareaagencia'];
        $idAdmin = session()->get('id') ?? 1;

        // Depuración: Ver qué datos llegan
        log_message('debug', 'Datos recibidos en asignarArea: ' . json_encode($json));
        log_message('debug', 'ID Atención: ' . $idAtencion);
        log_message('debug', 'ID Área: ' . $idArea);

        // Validar que el ID del área no sea nulo o vacío
        if (!$idArea || $idArea === 'null' || $idArea === '') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'ID de área no válido']);
        }

        $atencionModel = new AtencionModel();

        // Verificar si es un servicio personalizado
        $atencion = $atencionModel->find($idAtencion);
        if (!$atencion) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Atención no encontrada']);
        }

        $esServicioPersonalizado = (!empty($atencion['servicio_personalizado']));

        // Obtener nombre del área antes de usarlo
        $nombreArea = $this->getNombreArea($idArea);
        log_message('debug', 'Nombre del área obtenido: ' . $nombreArea);

        // Actualizar el área
        $atencionModel->update($idAtencion, [
            'idarea_agencia' => $idArea,
            'estado' => $esServicioPersonalizado ? 'pendiente_asignado' : $atencion['estado']
        ]);

        // Crear tracking para servicios personalizados
        if ($esServicioPersonalizado) {
            $trackingModel = new \App\Models\TrackingModel();
            $trackingModel->insert([
                'idatencion' => $idAtencion,
                'idusuario' => $idAdmin,
                'accion' => "Requerimiento asignado al área: {$nombreArea}.\nSu solicitud ha sido aprobada y derivada al departamento correspondiente para su gestión.",
                'estado' => 'pendiente_asignado',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'),
            ]);
        }

        $msg = $esServicioPersonalizado
            ? 'Servicio personalizado asignado correctamente al área'
            : 'Área asignada';

        return $this->response->setJSON(['status' => 'success', 'msg' => $msg]);
    }

    private function getNombreArea($idArea)
    {
        $areasModel = new AreasAgenciaModel();
        $area = $areasModel->find($idArea);
        return $area ? $area['nombre'] : 'Área desconocida';
    }

    /**
     * Asignar empleado a una atención (Flujo Responsable)
     */
    public function asignarEmpleado()
    {
        $json = $this->request->getJSON(true);
        $idAtencion = $json['idatencion'];
        $idEmpleado = $json['idempleado'];
        $idUsuario = session()->get('id') ?? 1;

        $db = \Config\Database::connect();

        // Solo asigna el empleado. El estado queda como está (pendiente_asignado).
        // NO se toca fechainicio ni se cambia a en_proceso todavía.
        $db->query("
        UPDATE atencion
        SET idempleado = ?
        WHERE id = ?
    ", [$idEmpleado, $idAtencion]);

        $db->query("
        INSERT INTO tracking (idatencion, idusuario, accion, estado)
        SELECT ?, ?, 'Empleado asignado por responsable de área', estado
        FROM atencion WHERE id = ?
    ", [$idAtencion, $idUsuario, $idAtencion]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Empleado asignado correctamente']);
    }

    public function iniciarTrabajo()
    {
        $json = $this->request->getJSON(true);
        $idAtencion = $json['idatencion'];
        $idUsuario = session()->get('id') ?? 1;

        $db = \Config\Database::connect();

        $db->query("
        UPDATE atencion
        SET estado = 'en_proceso', fechainicio = NOW()
        WHERE id = ?
    ", [$idAtencion]);

        $db->query("
        INSERT INTO tracking (idatencion, idusuario, accion, estado)
        VALUES (?, ?, 'Trabajo iniciado por responsable/empleado', 'en_proceso')
    ", [$idAtencion, $idUsuario]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Trabajo iniciado correctamente']);
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
        $idAdmin = session()->get('id') ?? 1;
        $accion = $json['accion'] ?? 'Cambio de estado';
        $idAreaAgencia = $json['idareaagencia'] ?? null;

        $estadosValidos = ['pendiente_sin_asignar', 'pendiente_asignado', 'en_proceso', 'en_revision', 'finalizado', 'cancelado'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Estado no válido']);
        }

        $actual = $db->query("SELECT estado FROM atencion WHERE id = ?", [$idAtencion])->getRowArray();
        $estadoActual = $actual['estado'] ?? null;

        // Regla de flujo:
        // - Admin aprueba (desde pendiente_sin_asignar) y lo envía al área -> pendiente_asignado
        // - "En proceso" real solo ocurre cuando el responsable/empleado inicia trabajo.
        if ($estadoActual === 'pendiente_sin_asignar' && $nuevoEstado === 'en_proceso' && $idAreaAgencia) {
            $nuevoEstado = 'pendiente_asignado';
        }

        if ($nuevoEstado === 'pendiente_asignado' && $idAreaAgencia) {
            // Al enviar al área, NO se asigna empleado ni se marca inicio de trabajo.
            $db->query("
                UPDATE atencion
                SET estado = ?, idarea_agencia = ?, idempleado = NULL
                WHERE id = ?
            ", [$nuevoEstado, $idAreaAgencia, $idAtencion]);
        } elseif ($nuevoEstado === 'finalizado') {
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

    public function cancelar()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $motivo = $json['motivo'] ?? 'Sin motivo';
        $idAdmin = session()->get('id') ?? 1;

        $db->query("
            UPDATE atencion 
            SET estado = 'cancelado', cancelacionmotivo = ?, fechacancelacion = NOW() 
            WHERE id = ?
        ", [$motivo, $idAtencion]);

        $db->query("
            INSERT INTO tracking (idatencion, idusuario, accion, estado)
            VALUES (?, ?, 'Pedido cancelado', 'cancelado')
        ", [$idAtencion, $idAdmin]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Solicitud cancelada, El Motivo:' . $motivo . ' Si tienes dudas, contáctanos']);
    }

    public function areasAgencia()
    {
        $areasAgenciaModel = new AreasAgenciaModel();
        return $this->response->setJSON($areasAgenciaModel->listarActivas());
    }


    public function cambiarPrioridad()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $idAtencion = $json['idatencion'];
        $prioridad = $json['prioridad'];

        $db->query("UPDATE atencion SET prioridad = ? WHERE id = ?", [$prioridad, $idAtencion]);
        return $this->response->setJSON(['status' => 'success', 'msg' => 'Prioridad actualizada']);
    }
}