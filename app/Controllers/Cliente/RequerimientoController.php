<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\ArchivoModel;
use App\Models\RequerimientoModel;
use App\Models\AtencionModel;
use App\Models\UsuarioModel;
use \App\Models\TrackingModel;
use App\Models\ServicioModel;

class RequerimientoController extends BaseClienteController
{
    /**
     * Renderiza la vista de detalle de un requerimiento específico.
     * @param mixed $id
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaDetalle($id)
    {
        // Validación centralizada de sesión y rol
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return redirect()->to(base_url('/'))->with('error', $auth['message']);
        }

        $userData = $auth['userData'];

        // Instanciar modelos necesarios
        $reqModel = new RequerimientoModel();
        $archivoModel = new ArchivoModel();

        // Obtener la información completa del requerimiento (joins con atencion, servicios, etc.)
        $detalle = $reqModel->getDetalleCompleto($id);

        // Validar que el requerimiento exista
        if (!$detalle) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'Requerimiento no encontrado.');
        }

        // Obtener archivos adjuntos asociados a este requerimiento
        $archivos = $archivoModel->where('idrequerimiento', $id)->findAll();

        // Renderizar la vista de detalle con la información obtenida
        return view('cliente/detalle_requerimiento', [
            'requerimiento' => $detalle,
            'archivos' => $archivos,
            'user' => $userData
        ]);
    }

    /**
     * Guarda un nuevo requerimiento completo; incluye la atención, archivos adjuntos y el primer tracking.
     * @throws \RuntimeException
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function guardar()
    {
        // Validar sesión del usuario
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $auth['message']]);
        }

        $idUsuario = $auth['user']['id'];

        // Arreglo para acumular posibles errores de validación de entrada
        $errores = [];

        /* CAPTURA Y VALIDACION DE DATOS DEL FORMULARIO */
        // Servicio seleccionado
        $idServicio = $this->request->getPost('idservicio');
        $idServicio = (!empty($idServicio) && $idServicio != '0') ? (int) $idServicio : null;
        $servicioPersonalizado = $this->request->getPost('servicio_personalizado');

        // Verificar si el servicio es "Creación de Contenido" para aplicar lógica consultiva
        $servicioUiNombre = (string) ($this->request->getPost('servicio_ui_nombre') ?? '');
        $esConsultivo = $this->esServicioConsultivo($servicioUiNombre);

        // Validaciones (CAMPOS OBLIGATORIOS)
        if ($idServicio === null && (empty($servicioPersonalizado) || trim($servicioPersonalizado) === '')) {
            $errores[] = 'El nombre del servicio personalizado es obligatorio.';
        }
        $titulo = $this->request->getPost('titulo');
        if (empty($titulo) || trim($titulo) === '') {
            $errores[] = 'El título del requerimiento es obligatorio.';
        }
        $objetivo = $this->request->getPost('objetivo_comunicacion');
        if (!$esConsultivo && (empty($objetivo) || trim($objetivo) === '')) {
            $errores[] = 'El objetivo de comunicación es obligatorio.';
        }
        $descripcion = $this->request->getPost('descripcion');
        if (empty($descripcion) || trim($descripcion) === '') {
            $errores[] = 'La descripción es obligatoria.';
        }
        $tipoReq = $this->request->getPost('tipo_requerimiento');
        if (!$esConsultivo && (empty($tipoReq) || trim($tipoReq) === '')) {
            $errores[] = 'El tipo de requerimiento es obligatorio.';
        }
        // Procesar canales de difusión (vienen como string JSON del frontend)
        $canalesRaw = $this->request->getPost('canales_difusion');
        $canales = [];
        if (!empty($canalesRaw)) {
            $canales = json_decode($canalesRaw, true) ?: [];
        }
        $cantCanales = count($canales);
        if (!$esConsultivo) {
            if ($cantCanales === 0) {
                $errores[] = 'Debe seleccionar al menos un canal de difusión.';
            } elseif ($cantCanales > 3) {
                $errores[] = 'No puede seleccionar más de 3 canales de difusión.';
            }
        }
        $publico = $this->request->getPost('publico_objetivo');
        if (!$esConsultivo && (empty($publico) || trim($publico) === '')) {
            $errores[] = 'El público objetivo es obligatorio.';
        }
        // Validación de materiales y archivos
        $tieneMateriales = ($this->request->getPost('tiene_materiales') === '1');
        $urlSubida = $this->request->getPost('url_subida');
        $archivosSubidos = $this->request->getFiles();
        $tieneArchivos = !empty($archivosSubidos['documentos']) && $this->hayArchivosValidos($archivosSubidos['documentos']);
        if ($tieneMateriales && !$tieneArchivos && (empty($urlSubida) || trim($urlSubida) === '')) {
            $errores[] = 'Si indica que tiene materiales, debe subir al menos un archivo o proporcionar una URL de referencia.';
        }
        // Formatos solicitados
        $formatosRaw = $this->request->getPost('formatos_solicitados');
        $formatos = [];
        if (!empty($formatosRaw)) {
            $formatos = json_decode($formatosRaw, true) ?: [];
        }
        if (!$esConsultivo && count($formatos) === 0) {
            $errores[] = 'Debe seleccionar al menos un formato solicitado.';
        }
        $formatoOtros = $this->request->getPost('formato_otros');
        if (!$esConsultivo && in_array('Otros', $formatos) && (empty($formatoOtros) || trim($formatoOtros) === '')) {
            $errores[] = 'Si selecciona "Otros" en formatos, debe especificar el formato deseado.';
        }
        // Validación de fecha requerida (considerando días hábiles mínimos)
        $fechaRaw = $this->request->getPost('fecharequerida');
        if (empty($fechaRaw)) {
            $errores[] = 'La fecha de entrega requerida es obligatoria.';
        } else {
            try {
                $fechaRequerida = new \DateTime($fechaRaw, new \DateTimeZone('America/Lima'));
                $fechaRequerida->setTime(0, 0, 0); 
                $hoy = new \DateTime('now', new \DateTimeZone('America/Lima'));
                $hoy->setTime(0, 0, 0); 
                // Cálculo de días hábiles mínimos según el tipo de trabajo
                if (empty($tipoReq)) {
                    $diasHabiles = 2;
                } else {
                    $diasHabiles = $this->obtenerDiasHabilesPorTipo($tipoReq ?? '');
                }

                $fechaMinima = $this->calcularFechaMinima($diasHabiles);
                if ($fechaRequerida < $fechaMinima) {
                    $errores[] = "La fecha de entrega debe ser al menos dentro de {$diasHabiles} días hábiles ({$fechaMinima->format('d/m/Y')}).";
                }
                if ($fechaRequerida < $hoy) {
                    $errores[] = 'La fecha de entrega no puede ser anterior a hoy.';
                }
            } catch (\Exception $e) {
                $errores[] = 'La fecha de entrega no es válida.';
            }
        }
        // Prioridad
        $prioridad = ucfirst(strtolower($this->request->getPost('prioridad') ?? 'Media'));
        $prioridadesPermitidas = ['Baja', 'Media', 'Alta'];
        if (!in_array($prioridad, $prioridadesPermitidas)) {
            $errores[] = 'La prioridad seleccionada no es válida.';
        }
        // Si existen errores acumulados, detener y retornar
        if (!empty($errores)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Por favor corrija los siguientes errores:',
                'errores' => $errores
            ]);
        }
        // Preparar Datos para la Inserción
        $fechaObj = new \DateTime($fechaRaw, new \DateTimeZone('America/Lima'));
        $dataReq = [
            'idusuarioempresa'       => (int) $idUsuario,
            'idservicio'             => $idServicio,
            'servicio_personalizado' => $idServicio === null ? trim($servicioPersonalizado) : null,
            'titulo'                 => trim($titulo),
            'objetivo_comunicacion'  => $esConsultivo ? '' : trim((string) $objetivo),
            'descripcion'            => trim($descripcion),
            'tipo_requerimiento'     => $esConsultivo ? '' : $tipoReq,
            'canales_difusion'       => json_encode($esConsultivo ? [] : $canales),
            'publico_objetivo'       => $esConsultivo ? '' : trim((string) $publico),
            'tiene_materiales'       => $tieneMateriales ?? false,
            'url_subida'             => !empty($urlSubida) ? trim($urlSubida) : null,
            'formatos_solicitados'   => json_encode($esConsultivo ? [] : $formatos),
            'formato_otros'          => (!$esConsultivo && in_array('Otros', $formatos)) ? trim((string) $formatoOtros) : '',
            'fecharequerida'         => $fechaObj->format('Y-m-d H:i:s'),
            'prioridad'              => $prioridad,
        ];

        // TRANSACCION EN LA BD (Requerimiento - Atencion)
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $reqModel = new RequerimientoModel();

            // 1. Insertar el Requerimiento
            $idReq = $reqModel->insert($dataReq);
            $idReq = $this->obtenerIdInsertado($db, $idReq);
            if (!$idReq) {
                log_message('error', 'Errores requerimiento: ' . json_encode($reqModel->errors()));
                throw new \RuntimeException('No se pudo procesar el requerimiento. Intente nuevamente.');
            }

            // 2. Determinar el área de la agencia encargada del servicio
            $servicioModel = new ServicioModel();
            if ($idServicio === null) {
                // Áreas especiales para servicios no tipificados
                $idAreaAgencia = 1; // Por defecto Diseño
            } else {
                $idAreaAgencia = $servicioModel->getAreaAgenciaByServicio((int) $idServicio);
            }

            // 3. Crear el registro de Atención (Entidad que sigue el flujo operativo)
            $atencionModel = new AtencionModel();
            $idAtn = $atencionModel->insert([
                'idrequerimiento'        => $idReq,
                'idadmin'                => 1, // Admin por defecto que recibe la notificación
                'idservicio'             => $idServicio,
                'servicio_personalizado' => $dataReq['servicio_personalizado'],
                'titulo'                 => $dataReq['titulo'],
                'prioridad'              => $dataReq['prioridad'],
                'estado'                 => 'pendiente_sin_asignar',
                'fechafin'               => $fechaObj->format('Y-m-d'),
                'idarea_agencia'         => $idAreaAgencia,
            ]);

            $idAtn = $this->obtenerIdInsertado($db, $idAtn);
            if (!$idAtn) {
                log_message('error', 'Errores atención: ' . json_encode($atencionModel->errors()));
                throw new \RuntimeException('No se pudo registrar la atención operativa.');
            }

            // 4. Registrar el primer hito en el Historial (Tracking)
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion'     => $idAtn,
                'idusuario'      => $idUsuario,
                'accion'         => "Solicitud registrada exitosamente.\nSu requerimiento ha sido recibido y se encuentra en cola de asignación.\nRecibirá notificación cuando sea procesado.",
                'estado'         => 'pendiente_sin_asignar',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'),
            ]);

            // 5. Procesar y guardar archivos físicos adjuntos
            $this->guardarArchivos($idReq, $idAtn);

        } catch (\Exception $e) {
            // Revertir cambios en caso de error
            $db->transRollback();
            log_message('error', '[RequerimientoController::guardar] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => $e->getMessage(),
            ]);
        }

        // Finalizar transacción
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Ocurrió un error al confirmar la transacción en BD.']);
        }

        // Éxito
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => '¡Requerimiento enviado con éxito!',
            'id_req' => $idReq,
        ]);
    }

    /**
     * Obtiene los días hábiles mínimos requeridos según el tipo de trabajo seleccionado.
     * @param mixed $tipo
     * @return int
     */
    private function obtenerDiasHabilesPorTipo($tipo)
    {
        $mapaDias = [
            'Adaptación de Arte'      => 2,
            'Creación de Arte'        => 4,
            'Creación de editorial'   => 7,
            'Adaptación de editorial' => 7,
            'Creación de Videos'      => 7,
            'Trabajo editorial'       => 7,
        ];
        return $mapaDias[$tipo] ?? 2;
    }

    /**
     * Verifica si el array de archivos subidos contiene al menos uno válido.
     * @param mixed $archivos
     * @return bool
     */
    private function hayArchivosValidos($archivos)
    {
        if (empty($archivos)) return false;
        foreach ($archivos as $file) {
            if ($file->isValid()) return true;
        }
        return false;
    }

    /**
     * Helper para manejar la obtención del ID insertado (Compatible con PostgreSQL/MySQL).
     * @param mixed $db
     * @param mixed $insertId
     */
    private function obtenerIdInsertado($db, $insertId)
    {
        if ($insertId === 0 || $insertId === true) {
            $row = $db->query("SELECT lastval() AS id")->getRowArray();
            return $row['id'] ?? null;
        }
        return $insertId ?: null;
    }

    /**
     * Gestiona la subida de archivos físicos al servidor y su registro en la BD.
     * @param int $idReq
     * @param int $idAtn
     * @return void
     */
    private function guardarArchivos(int $idReq, int $idAtn): void
    {
        $archivos = $this->request->getFiles();

        if (empty($archivos['documentos'])) return;

        $archivoModel = new ArchivoModel();
        // Ruta de almacenamiento estructurada por ID de requerimiento
        $carpeta = FCPATH . 'uploads/requerimientos/' . $idReq . '/materiales-referencia';

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        foreach ($archivos['documentos'] as $file) {
            if (!$file->isValid() || $file->hasMoved()) continue;
            
            try {
                // Generar nombre aleatorio para evitar colisiones y caracteres especiales
                $nombreNuevo = $file->getRandomName();
                $file->move($carpeta, $nombreNuevo);

                // Registrar metadatos en la tabla de archivos
                $archivoModel->insert([
                    'idrequerimiento' => $idReq,
                    'idatencion'      => null, 
                    'nombre'          => $file->getClientName(),
                    'ruta'            => 'uploads/requerimientos/' . $idReq . '/materiales-referencia/' . $nombreNuevo,
                    'tipo'            => $file->getClientMimeType(),
                    'tamano'          => $file->getSize(),
                ]);
            } catch (\Exception $e) {
                log_message('error', '[guardarArchivos] Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Calcula la fecha mínima de entrega saltando fines de semana.
     * @param mixed $diasHabiles
     * @return \DateTime
     */
    private function calcularFechaMinima($diasHabiles)
    {
        $fecha = new \DateTime('now', new \DateTimeZone('America/Lima'));
        $fecha->setTime(0, 0, 0); 
        $cont = 0;

        while ($cont < $diasHabiles) {
            $fecha->modify('+1 day');
            // N (1:Lunes, ..., 5:Viernes, 6:Sábado, 7:Domingo)
            if ($fecha->format('N') < 6) {
                $cont++;
            }
        }
        // Margen adicional de 1 día para procesamiento inicial
        $fecha->modify('+1 day');
        return $fecha;
    }

    /**
     * Identifica servicios que requieren un flujo de aprobación o información diferente.
     * @param string $nombreServicio
     * @return bool
     */
    private function esServicioConsultivo(string $nombreServicio): bool
    {
        $normalizado = trim(mb_strtolower($nombreServicio));
        return $normalizado === 'creación de contenido' || $normalizado === 'creacion de contenido';
    }

    /**
     * Endpoint API: Retorna el detalle completo de un requerimiento para modales o vistas AJAX.
     * @param mixed $RequerimientoID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detalle($RequerimientoID)
    {
        // Validación de sesión
        $auth = $this->ValidarSesion_DatosUser();
        if (!$auth['ok']) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $auth['message']]);
        }

        $reqModel = new RequerimientoModel();
        $archivoModel = new ArchivoModel();

        // Obtener datos consolidados
        $data = $reqModel->getDetalleCompleto($RequerimientoID);

        if (!$data) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Requerimiento no encontrado']);
        }

        // Seguridad: El cliente solo puede ver sus propios requerimientos
        if ($data['idusuarioempresa'] != $auth['user']['id']) {
             return $this->response->setJSON(['status' => 'error', 'msg' => 'Acceso no autorizado a este registro.']);
        }

        $estado = $data['estado'] ?? '';

        // Filtrar archivos por origen (Cliente vs Empleado)
        $archivosCliente = $archivoModel->where('idrequerimiento', $RequerimientoID)
            ->where('idatencion IS NULL')
            ->findAll();

        $archivosEmpleado = [];
        // Solo mostrar archivos de entrega del empleado si el estado es finalizado
        if ($estado === 'finalizado') {
            $archivosEmpleado = $archivoModel->where('idrequerimiento', $RequerimientoID)
                ->where('idatencion IS NOT NULL')
                ->findAll();
        }

        $archivos = array_merge($archivosCliente, $archivosEmpleado);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'requerimiento'     => $data,
                'archivos'          => $archivos,
                'archivos_cliente'  => $archivosCliente,
                'archivos_empleado' => $archivosEmpleado,
                'url_subida'        => $data['url_subida'] ?? null,
                'url_entrega'       => ($estado === 'finalizado') ? ($data['url_entrega'] ?? null) : null,
            ]
        ]);
    }

    /**
     * Sirve un archivo adjunto del requerimiento de forma segura.
     * Valida la existencia física del archivo antes de enviarlo.
     * @param mixed $idArchivo
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function verArchivo($idArchivo)
    {
        $archivoModel = new ArchivoModel();
        $archivo = $archivoModel->find($idArchivo);

        if (!$archivo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("El registro del archivo no existe.");
        }

        $rutaCompleta = FCPATH . $archivo['ruta'];

        if (!is_file($rutaCompleta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("El archivo físico no se encuentra en el servidor.");
        }

        // Retornar el archivo con el tipo de contenido correcto para previsualización en navegador
        return $this->response
            ->setHeader('Content-Type', $archivo['tipo'])
            ->setHeader('Content-Disposition', 'inline; filename="' . $archivo['nombre'] . '"')
            ->setHeader('Cache-Control', 'max-age=31536000')
            ->setBody(file_get_contents($rutaCompleta));
    }
}