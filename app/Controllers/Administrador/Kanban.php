<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\AtencionModel;
use App\Models\ArchivoModel;
use App\Models\RetroalimentacionModel;
use App\Models\TrackingModel;
use App\Libraries\EmailService;
use App\Models\RequerimientoModel;
use App\Models\SesionesTrabajosModel;
use App\Services\PusherService;

class Kanban extends Controller
{
     private PusherService $pusher;
       public function __construct()  // ← AGREGA TODO EL CONSTRUCTOR
    {
        $this->pusher = new PusherService();
    }
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
            'pendiente_sin_asignar' => ['label' => 'NUEVAS SOLICITUDES', 'color' => '#eab308', 'items' => []],
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
        $statsAreas = $atencionModel->contarPorAprobarPorAreaEmpresa((int) $idEmpresa);
        $cargaDiaria = $atencionModel->obtenerCargaPorFecha((int) $idAreaAgencia);

        // Pedidos atrasados de TODA la empresa (todas las áreas)
        $db = \Config\Database::connect();
        $hoy = date('Y-m-d');
        $atrasados = $db->query("
            SELECT a.id, a.titulo, a.prioridad, a.estado, a.fechacreacion,
                   r.fecharequerida, aa.nombre as nombre_area, e.nombreempresa
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u ON u.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u.idarea
            INNER JOIN empresas e ON e.id = ar.idempresa
            LEFT JOIN areas_agencia aa ON aa.id = a.idarea_agencia
            WHERE ar.idempresa = ?
              AND r.fecharequerida < ?
              AND a.estado NOT IN ('finalizado', 'cancelado')
            ORDER BY r.fecharequerida ASC
        ", [$idEmpresa, $hoy])->getResultArray();

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
            'stats_areas' => $statsAreas,
            'carga_diaria' => $cargaDiaria,
            'atrasados' => $atrasados,
        ]);
    }

    /**
     * Retorna el HTML renderizado de una sola tarjeta para actualizaciones en tiempo real (Pusher)
     */
    public function tarjetaHTML($idAtencion)
    {
        $db = \Config\Database::connect();
        $p = $db->query("
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
                (SELECT st.motivo_pausa FROM sesiones_trabajo st WHERE st.idatencion = a.id ORDER BY st.id DESC LIMIT 1) AS ultimo_motivo_pausa,
                CASE 
                    WHEN a.servicio_personalizado IS NOT NULL THEN 1 
                    ELSE 0 
                END AS es_servicio_personalizado,
                CAST(a.prioridad AS TEXT) AS prioridad_admin
            FROM atencion a
            INNER JOIN requerimiento r ON r.id = a.idrequerimiento
            INNER JOIN usuarios u_sol ON u_sol.id = r.idusuarioempresa
            INNER JOIN areas ar ON ar.id = u_sol.idarea
            INNER JOIN empresas e ON e.id = ar.idempresa
            LEFT JOIN servicios s ON s.id = a.idservicio
            LEFT JOIN usuarios u ON u.id = a.idempleado
            WHERE a.id = ?
        ", [$idAtencion])->getRowArray();

        if (!$p) return "";

        $estado = $p['estado'];
        if ($estado === 'pendiente_asignado') {
            $estado = 'en_proceso'; // Ajuste visual
        }

        return view('admin/_tarjeta', ['p' => $p, 'estado' => $estado]);
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
                u_sol.nombre    AS cliente_nombre,
                u_sol.apellidos AS cliente_apellidos,
                u_sol.correo    AS cliente_correo,
                u_sol.telefono  AS cliente_telefono,
                ar.nombre       AS area_solicitante_nombre,
                aa.nombre       AS area_nombre,
                u.nombre        AS empleado_nombre,
                u.apellidos     AS empleado_apellidos
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

        // 2. Consulta de archivos separados
        $archivosCliente = $db->table('archivos')
            ->select('nombre, ruta')
            ->where('idrequerimiento', $data['idrequerimiento'])
            ->where('idatencion IS NULL') // Los archivos del cliente no tienen idatencion vinculada inicialmente
            ->get()
            ->getResultArray();

        $archivosEmpleado = $db->table('archivos')
            ->select('nombre, ruta')
            ->where('idatencion', $idAtencion)
            ->get()
            ->getResultArray();

        // Generar URL completa para ambos
        foreach ($archivosCliente as &$archivo) {
            $archivo['url_completa'] = base_url($archivo['ruta']);
        }
        foreach ($archivosEmpleado as &$archivo) {
            $archivo['url_completa'] = base_url($archivo['ruta']);
        }

        // 3. Procesamiento de datos adicionales
        // NO decodificar JSON aquí, dejar que el JS lo maneje con _parseList

        foreach (['fecharequerida', 'fechainicio', 'fechafin', 'fechacompletado', 'r_fechacreacion'] as $campo) {
            $data[$campo] = !empty($data[$campo]) ? date('Y-m-d H:i', strtotime($data[$campo])) : '—';
        }

        $data['empleado_fullname'] = trim(($data['empleado_nombre'] ?? '') . ' ' . ($data['empleado_apellidos'] ?? '')) ?: 'Sin asignar';

        $sesionesModel = new SesionesTrabajosModel();
        $ultimaPausa = $sesionesModel->getUltimaSessionPausada($idAtencion);
        $data['ultimo_motivo_pausa'] = $ultimaPausa ? ($ultimaPausa['motivo_pausa'] ?? null) : null;

        // 4. Retorno de respuesta (archivos ya vienen con url_completa arriba)
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data,
            'archivos_cliente' => $archivosCliente,
            'archivos_empleado' => $archivosEmpleado,
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
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $idAtencion,
                'idusuario' => $idAdmin,
                'accion' => "Requerimiento asignado al área: {$nombreArea}.\nSu solicitud ha sido aprobada y derivada al departamento correspondiente para su gestión.",
                'estado' => 'pendiente_asignado',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'),
            ]);
        }

        $estadoFinal = $esServicioPersonalizado ? 'pendiente_asignado' : $atencion['estado'];
        $this->pusher->notificarCambioEstado($idAtencion, $estadoFinal);

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
        // Limpiamos observacion_revision para evitar que vea mensajes del empleado anterior
        $db->query("
        UPDATE atencion
        SET idempleado = ?, observacion_revision = NULL
        WHERE id = ?
    ", [$idEmpleado, $idAtencion]);

        $db->query("
        INSERT INTO tracking (idatencion, idusuario, accion, estado)
        SELECT ?, ?, 'Empleado asignado por responsable de área', estado
        FROM atencion WHERE id = ?
    ", [$idAtencion, $idUsuario, $idAtencion]);

        // Notificar asignación (mantiene estado)
        $this->pusher->notificarCambioEstado($idAtencion, 'pendiente_asignado');

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

        $this->pusher->notificarCambioEstado($idAtencion, 'en_proceso');

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
            $db->query("UPDATE atencion SET estado = ?, fechacompletado = NOW(), observacion_revision = NULL WHERE id = ?", [$nuevoEstado, $idAtencion]);
            $accion = 'Requerimiento finalizado y entregado con éxito.';
        } else {
            // Cambios de estado genéricos (sin sobrescribir observacion_revision)
            $db->query("UPDATE atencion SET estado = 'en_proceso', num_modificaciones = num_modificaciones + 1, url_entrega = NULL WHERE id = ?", [$nuevoEstado, $idAtencion]);
            
            // Si el admin regresa el pedido, registrar en retroalimentacion
            if ($nuevoEstado === 'en_proceso') {
                $retroModel = new RetroalimentacionModel();
                $retroModel->insert([
                    'idatencion' => $idAtencion,
                    'idevaluador' => $idAdmin,
                    'contenido' => 'Corrección solicitada por Administración',
                    'fecha' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
                ]);

                $archivoModel = new ArchivoModel();
                $archivosAnteriores = $archivoModel->where('idatencion', $idAtencion)->findAll();
                foreach ($archivosAnteriores as $archivo) {
                    $rutaCompleta = FCPATH . $archivo['ruta'];
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                    $archivoModel->delete($archivo['id']);
                }
            }
        }

        $db->query("
            INSERT INTO tracking (idatencion, idusuario, accion, estado)
            VALUES (?, ?, ?, ?)
        ", [$idAtencion, $idAdmin, $accion, $nuevoEstado]);

        // Enviar notificación por correo al cliente si se finaliza (aprueba) el pedido
        if ($nuevoEstado === 'finalizado') {
            try {
                $atencionModel = new AtencionModel();
                $atencion = $atencionModel->find($idAtencion);
                if ($atencion) {
                    $requerimientoModel = new RequerimientoModel();
                    $detalleReq = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);
                    if ($detalleReq && !empty($detalleReq['correo_cliente'])) {
                        // Obtener archivos cargados para esta entrega
                        $archivoModel = new ArchivoModel();
                        $archivos = $archivoModel->where('idatencion', $idAtencion)->findAll();

                        $emailService = new EmailService();
                        $emailService->notificarFinalizado(
                            $detalleReq['correo_cliente'],
                            $detalleReq['nombre_cliente'],
                            $detalleReq['titulo'],
                            $atencion['url_entrega'],
                            $archivos
                        );
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'Error al enviar correo de finalizacion (admin): ' . $e->getMessage());
            }
        }

        $this->pusher->notificarCambioEstado($idAtencion, $nuevoEstado);


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
            VALUES (?, ?, 'El pedido ha sido cancelado por la Administración. Motivo: ' || ?, 'cancelado')
        ", [$idAtencion, $idAdmin, $motivo]);


        // Enviar notificación por correo al cliente
        try {
            $atencionModel = new AtencionModel();
            $atencion = $atencionModel->find($idAtencion);
            if ($atencion) {
                $requerimientoModel = new RequerimientoModel();
                $detalleReq = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);
                if ($detalleReq && !empty($detalleReq['correo_cliente'])) {
                    $emailService = new EmailService();
                    $emailService->notificarCancelado(
                        $detalleReq['correo_cliente'],
                        $detalleReq['nombre_cliente'],
                        $detalleReq['titulo'],
                        $motivo
                    );
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error al enviar correo de cancelacion: ' . $e->getMessage());
        }

        $this->pusher->notificarCambioEstado($idAtencion, 'cancelado');

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

    public function regresarAProceso()
    {
        $json = $this->request->getJSON(true);
        $idAtencion = $json['idatencion'];
        $mensaje = $json['mensaje'];
        $idAdmin = session()->get('id') ?? 1;

        $db = \Config\Database::connect();
        $atencionModel = new AtencionModel();
        $retroModel = new RetroalimentacionModel();
        $trackingModel = new TrackingModel();

        $atencion = $atencionModel->find($idAtencion);
        if (!$atencion) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Pedido no encontrado']);
        }

        // 1. Actualizar estado del pedido a 'en_proceso' e incrementar modificaciones
        $db->query("UPDATE atencion SET estado = 'en_proceso', num_modificaciones = num_modificaciones + 1, url_entrega = NULL WHERE id = ?", [$idAtencion]);

        // 1.5. Limpiar los archivos entregados anteriormente (para no mostrarlos de nuevo)
        $archivoModel = new ArchivoModel();
        $archivosAnteriores = $archivoModel->where('idatencion', $idAtencion)->findAll();
        foreach ($archivosAnteriores as $archivo) {
            $rutaCompleta = FCPATH . $archivo['ruta'];
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
            $archivoModel->delete($archivo['id']);
        }

        // 2. Registrar en la tabla de retroalimentación (correcciones del admin)
        $retroModel->insert([
            'idatencion' => $idAtencion,
            'idevaluador' => $idAdmin,
            'contenido' => $mensaje,
            'fecha' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ]);

        $trackingModel->insert([
            'idatencion' => $idAtencion,
            'idusuario' => $idAdmin,
            'accion' => "El Requerimiento a regresado al Area Correspondiente para su Correción",
            'estado' => 'en_proceso',
            'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ]);
        
        $this->pusher->notificarCambioEstado($idAtencion, 'en_proceso');

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Pedido regresado correctamente con retroalimentación']);
    }
}