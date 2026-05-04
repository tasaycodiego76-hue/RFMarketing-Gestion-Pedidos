<?php

namespace App\Controllers\Responsable;

use App\Models\UsuarioModel;
use App\Models\AtencionModel;
use App\Models\TrackingModel;
use App\Models\RequerimientoModel;
use App\Models\ArchivoModel;
use App\Models\ServicioModel;

/**
 * Controlador principal para la gestión operativa del Responsable de Área
 */
class PedidosAreaController extends BaseResponsableController
{
    /**
     * Renderiza el dashboard principal del Responsable
     */
    public function index()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');
        
        $user = $userS['user'];
        $userData = $userS['userData'];
        $idAreaAgencia = (int) $user['idarea_agencia'];

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();

        // Obtener métricas para el dashboard
        $metrics = $this->_getMetrics($idAreaAgencia);

        // Contar miembros del equipo
        $empleados = $usuarioModel->obtenerAsignablesPorAreaAgencia($idAreaAgencia);

        // Datos para gráficos
        $cargaEmpleados = [];
        foreach ($empleados as $emp) {
            $nombreCorto = explode(' ', $emp['nombre'])[0] ?? $emp['nombre'];
            $tareasActivas = $atencionModel->where('idempleado', $emp['id'])
                ->where('idarea_agencia', $idAreaAgencia)
                ->whereIn('estado', ['en_proceso', 'pendiente_asignado'])
                ->countAllResults();
            $tareasCompletadas = $atencionModel->where('idempleado', $emp['id'])
                ->where('idarea_agencia', $idAreaAgencia)
                ->where('estado', 'finalizado')
                ->countAllResults();
            $cargaEmpleados[] = [
                'nombre' => $nombreCorto,
                'activas' => $tareasActivas,
                'completadas' => $tareasCompletadas,
            ];
        }

        $prioridadAlta = $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('prioridad', 'Alta')->whereNotIn('estado', ['finalizado', 'cancelado'])->countAllResults();
        $prioridadMedia = $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('prioridad', 'Media')->whereNotIn('estado', ['finalizado', 'cancelado'])->countAllResults();
        $prioridadBaja = $atencionModel->where('idarea_agencia', $idAreaAgencia)->where('prioridad', 'Baja')->whereNotIn('estado', ['finalizado', 'cancelado'])->countAllResults();

        $totalActivo = $metrics['en_proceso'] + $metrics['pendientes_asignar'];
        $totalGeneral = max(1, $totalActivo + $metrics['enRevision'] + $metrics['completados']);

        $data = array_merge([
            'titulo' => 'Mis Pedidos - Area',
            'tituloPagina' => 'Dashboard',
            'user' => $userData,
            'cargaEmpleados' => json_encode($cargaEmpleados),
            'prioridadAlta' => $prioridadAlta,
            'prioridadMedia' => $prioridadMedia,
            'prioridadBaja' => $prioridadBaja,
            'totalActivo' => $totalActivo,
            'totalGeneral' => $totalGeneral,
        ], $metrics);

        return view('Responsable/dashboard', $data);
    }

    /**
     * Renderiza la vista de Bandeja de Entrada
     */
    public function vistaBandeja()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return redirect()->to('login');

        $metrics = $this->_getMetrics((int) $userS['user']['idarea_agencia']);

        $data = array_merge([
            'titulo' => 'Bandeja de Entrada',
            'tituloPagina' => 'Bandeja de Entrada',
            'user' => $userS['userData']
        ], $metrics);

        return view('Responsable/bandeja', $data);
    }

    /**
     * Datos JSON para la Bandeja de Entrada
     */
    public function bandeja()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }
        
        $atencionModel = new AtencionModel();
        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

        $itemsPendientes = $atencionModel->obtenerBandejaResponsable($idAreaAgencia);
        $itemsRevision = $atencionModel->obtenerDetalladoPorArea($idAreaAgencia, ['en_revision']);

        return $this->response->setJSON([
            'success' => true,
            'area' => [
                'id' => $idAreaAgencia,
                'nombre' => $userS['userData']['nombre_areaagencia'] ?? 'Área no asignada',
            ],
            'total_pendientes' => count($itemsPendientes),
            'total_revision' => count($itemsRevision),
            'data' => $itemsPendientes,
            'data_revision' => $itemsRevision
        ]);
    }

    /**
     * Lista de los Empleados, que se pueden asignar solicitudes del Area
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function empleadosMiAreaJson()
    {
        $user = $this->ValidarSesion_DatosUser();
        if (!$user['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $user['message']]);
        }

        $usuarioModel = new UsuarioModel();
        $atencionModel = new AtencionModel();

        $lista = $usuarioModel->obtenerAsignablesPorAreaAgencia((int) $user['user']['idarea_agencia']);

        $data = array_map(function ($u) use ($atencionModel) {
            $esResponsable = ($u['esresponsable'] === true || $u['esresponsable'] === 't' || $u['esresponsable'] == 1);

            // Obtener todas las tareas del empleado
            $tareas = $atencionModel->obtenerDetalladoPorEmpleado((int) $u['id']);

            $enProceso = 0;
            $completados = 0;
            $pendientes = 0;

            foreach ($tareas as $t) {
                if ($t['estado'] === 'en_proceso' || $t['estado'] === 'pendiente_asignado') {
                    $enProceso++;
                } elseif ($t['estado'] === 'finalizado' || $t['estado'] === 'completado') {
                    $completados++;
                } else {
                    $pendientes++;
                }
            }

            return [
                'id' => (int) $u['id'],
                'nombre_completo' => trim(($u['nombre'] ?? '') . ' ' . ($u['apellidos'] ?? '')),
                'esresponsable' => $esResponsable,
                'en_proceso' => $enProceso,
                'completados' => $completados,
                'pendientes' => $pendientes
            ];
        }, $lista);

        return $this->response->setJSON([
            'success' => true,
            'total' => count($data),
            'data' => $data
        ]);
    }

    /**
     * Asignar la Solicitud Requerimiento (Atencion) a un Empleado
     * @throws \RuntimeException
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function asignarPedido()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) {
            return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);
        }

        $idAtencion = (int) $this->request->getPost('idatencion');
        $idUsuarioAsignado = (int) $this->request->getPost('idusuario_asignado');

        if ($idAtencion <= 0 || $idUsuarioAsignado <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos para asignación.']);
        }

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $atencionModel = new AtencionModel();
        $usuarioModel = new UsuarioModel();
        $trackingModel = new TrackingModel();

        if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'La atención no pertenece a tu área.']);
        }

        $empleado = $usuarioModel->obtenerEmpleadoAsignable($idUsuarioAsignado, $idAreaAgencia);
        if (!$empleado) {
            return $this->response->setJSON(['success' => false, 'message' => 'El empleado no pertenece a tu área o está inactivo.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {

            // 3) Solo asignar empleado. El estado sigue pendiente_asignado.
            //    El empleado debe pulsar "Iniciar Trabajo" para pasar a en_proceso.
            $ok = $atencionModel->update($idAtencion, [
                'idempleado' => $idUsuarioAsignado,
            ]);
            if (!$ok) {
                throw new \RuntimeException('No se pudo actualizar la atención.');
            }
            // 4) Tracking
            $trackingModel->insert([
                'idatencion' => $idAtencion,
                'idusuario' => $userS['user']['id'],
                'accion' => "Empleado asignado al pedido.\nEl especialista " . trim($empleado['nombre'] . ' ' . $empleado['apellidos']) . " ha sido designado para este requerimiento. Pendiente de inicio.",
                'estado' => 'pendiente_asignado',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);
            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Pedido asignado correctamente']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Error al asignar: ' . $e->getMessage()]);
        }
    }

    public function listarServicios()
    {
        $servicioModel = new ServicioModel();
        $servicios = $servicioModel->where('activo', true)->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $servicios]);
    }

    public function actualizarRequerimiento()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);

        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];
        $idRequerimiento = (int) $this->request->getPost('idrequerimiento');

        $requerimientoModel = new RequerimientoModel();
        $atencionModel = new AtencionModel();
        
        $req = $requerimientoModel->find($idRequerimiento);
        if (!$req) return $this->response->setJSON(['success' => false, 'message' => 'Requerimiento no encontrado']);

        $atencion = $atencionModel->where('idrequerimiento', $idRequerimiento)->first();
        if (!$atencion || !$atencionModel->atencionPerteneceAArea($atencion['id'], $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tiene permiso para editar este requerimiento']);
        }

        $idServicio = (int) $this->request->getPost('idservicio');
        $data = [
            'idservicio'            => $idServicio,
            'tipo_requerimiento'    => $this->request->getPost('tipo_requerimiento'),
            'titulo'                => $this->request->getPost('titulo'),
            'descripcion'           => $this->request->getPost('descripcion'),
            'fecharequerida'        => $this->request->getPost('fecharequerida') ?: null,
            'objetivo_comunicacion' => $this->request->getPost('objetivo_comunicacion'),
            'publico_objetivo'      => $this->request->getPost('publico_objetivo'),
            'canales_difusion'      => $this->request->getPost('canales_difusion'),
            'formatos_solicitados'  => $this->request->getPost('formatos_solicitados'),
            'url_subida'            => $this->request->getPost('url_subida'),
        ];

        $db = \Config\Database::connect();
        $db->transStart();
        $requerimientoModel->update($idRequerimiento, $data);

        $archivosSubidos = $this->request->getFiles();
        if (!empty($archivosSubidos['archivos_responsable'])) {
            $archivoModel = new ArchivoModel();
            $uploadPath = FCPATH . 'uploads/requerimientos/' . $idRequerimiento . '/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
            foreach ($archivosSubidos['archivos_responsable'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $nombreGuardado = $file->getRandomName();
                    $file->move($uploadPath, $nombreGuardado);
                    $archivoModel->insert([
                        'idatencion'      => $atencion['id'],
                        'idrequerimiento' => $idRequerimiento,
                        'nombre'          => $file->getClientName(),
                        'ruta'            => 'uploads/requerimientos/' . $idRequerimiento . '/' . $nombreGuardado,
                        'tipo'            => $file->getClientMimeType(),
                        'tamano'          => $file->getSize(),
                    ]);
                }
            }
        }

        $trackingModel = new TrackingModel();
        $trackingModel->insert([
            'idatencion' => $atencion['id'], 'idusuario' => $userS['user']['id'],
            'accion' => "Se actualizaron los detalles del requerimiento (Edición operativa).",
            'estado' => $atencion['estado'],
            'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar los cambios']);
        return $this->response->setJSON(['success' => true, 'message' => '¡Requerimiento actualizado correctamente!']);
    }

    public function obtenerDetalleRequerimiento()
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return $this->response->setJSON(['success' => false, 'message' => $userS['message']]);

        $idAtencion = (int) $this->request->getGet('id');
        $atencionModel = new AtencionModel();
        $idAreaAgencia = (int) $userS['user']['idarea_agencia'];

        if (!$atencionModel->atencionPerteneceAArea($idAtencion, $idAreaAgencia)) {
            return $this->response->setJSON(['success' => false, 'message' => 'La atención no pertenece a tu área.']);
        }

        $atencion = $atencionModel->find($idAtencion);
        $requerimientoModel = new RequerimientoModel();
        $detalle = $requerimientoModel->getDetalleCompleto($atencion['idrequerimiento']);
        
        $usuarioModel = new UsuarioModel();
        $empleadoAsignado = !empty($atencion['idempleado']) ? $usuarioModel->find($atencion['idempleado']) : null;

        $archivoModel = new ArchivoModel();
        $archivos = $archivoModel->where('idrequerimiento', $atencion['idrequerimiento'])->findAll();

        $trackingModel = new TrackingModel();
        $tracking = $trackingModel->where('idatencion', $idAtencion)->orderBy('fecha_registro', 'DESC')->findAll();

        $dataCompleta = array_merge($atencion, $detalle, [
            'empleado_asignado' => $empleadoAsignado,
            'empleado_nombre' => $empleadoAsignado ? trim($empleadoAsignado['nombre'] . ' ' . $empleadoAsignado['apellidos']) : '---',
            'servicio' => $detalle['nombre_servicio'] ?? $detalle['servicio_personalizado'] ?? 'N/A',
            'nombre_cliente' => trim(($detalle['nombre_cliente'] ?? '') . ' ' . ($detalle['apellidos_cliente'] ?? '')),
        ]);

        return $this->response->setJSON(['success' => true, 'data' => $dataCompleta, 'archivos' => $archivos, 'tracking' => $tracking]);
    }

    public function vistaPrevia($id)
    {
        $archivoModel = new ArchivoModel();
        $archivo = $archivoModel->find($id);
        if (!$archivo) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $rutaAbsoluta = FCPATH . $archivo['ruta'];
        if (!file_exists($rutaAbsoluta)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return $this->response->setHeader('Content-Type', $archivo['tipo'])->setBody(file_get_contents($rutaAbsoluta));
    }

    public function iniciarPedido($id)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return $this->response->setJSON(['status' => 'error', 'message' => $userS['message']]);

        $atencionModel = new AtencionModel();
        $pedido = $atencionModel->find($id);

        if (!$pedido || $pedido['idempleado'] != $userS['user']['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no asignado a ti']);
        }

        $data = ['estado' => 'en_proceso', 'fechainicio' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')];
        if ($atencionModel->update($id, $data)) {
            $trackingModel = new TrackingModel();
            $trackingModel->insert(['idatencion' => $id, 'idusuario' => $userS['user']['id'], 'accion' => 'Trabajo iniciado', 'estado' => 'en_proceso', 'fecha_registro' => $data['fechainicio']]);
            return $this->response->setJSON(['status' => 'success', 'message' => '¡Trabajo iniciado!']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Error al iniciar']);
    }

    public function entregarPedido($id)
    {
        $userS = $this->ValidarSesion_DatosUser();
        if (!$userS['ok']) return $this->response->setJSON(['status' => 'error', 'message' => $userS['message']]);

        $url_entrega = $this->request->getPost('url_entrega');
        $notas = $this->request->getPost('notas');
        $archivosSubidos = $this->request->getFiles();

        // Validar que se envíe al menos algo: URL o Archivos
        $hasFiles = !empty($archivosSubidos['archivos_entrega']) && count(array_filter($archivosSubidos['archivos_entrega'], fn($f) => $f->isValid()));
        
        if (empty($url_entrega) && !$hasFiles) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Debes proporcionar al menos un link o un archivo de entrega']);
        }

        $atencionModel = new AtencionModel();
        $archivoModel = new ArchivoModel();
        $trackingModel = new TrackingModel();

        $atencion = $atencionModel->find($id);
        if (!$atencion) return $this->response->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado']);

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // 1. Actualizar estado y URL (si existe)
            $dataUpdate = [
                'estado' => 'en_revision',
                'url_entrega' => $url_entrega ?: null,
                'observacion_revision' => $notas ?: null // Guardamos notas de entrega aquí
            ];
            $atencionModel->update($id, $dataUpdate);

            // 2. Procesar archivos físicos
            if ($hasFiles) {
                $idReq = $atencion['idrequerimiento'];
                $uploadPath = FCPATH . "uploads/requerimientos/{$idReq}/entrega/";
                if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

                foreach ($archivosSubidos['archivos_entrega'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move($uploadPath, $newName);
                        $archivoModel->insert([
                            'idatencion'      => $id,
                            'idrequerimiento' => $idReq,
                            'nombre'          => $file->getClientName(),
                            'ruta'            => "uploads/requerimientos/{$idReq}/entrega/{$newName}",
                            'tipo'            => $file->getClientMimeType(),
                            'tamano'          => $file->getSize(),
                        ]);
                    }
                }
            }

            // 3. Registrar en Tracking
            $trackingModel->insert([
                'idatencion' => $id,
                'idusuario' => $userS['user']['id'],
                'accion' => "Entrega realizada." . ($notas ? "\nNotas: $notas" : ""),
                'estado' => 'en_revision',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s')
            ]);

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => '¡Pedido entregado correctamente!']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }
}