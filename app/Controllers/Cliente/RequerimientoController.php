<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\ArchivoModel;
use App\Models\RequerimientoModel;
use App\Models\AtencionModel;
use App\Models\UsuarioModel;
use \App\Models\TrackingModel;
use App\Models\ServicioModel;


class RequerimientoController extends BaseController
{
    /**
     * Funcion que Renderiza la Vista de la Plantilla e Insertara los Detalles del Requerimiento
     * @param mixed $id
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function vistaDetalle($id)
    {
        $user = $this->getActiveUser();
        // Validación de Seguridad (Solo Clientes)
        if (!is_array($user) || $user['rol'] !== 'cliente') {
            return redirect()->to(base_url('/'))->with('error', 'Acceso denegado.');
        }

        // Traer datos del usuario para el Sidebar/TopBar
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDetalleUsuario($user['id']);

        // Instanciar modelos de requerimientos
        $reqModel = new RequerimientoModel();
        $archivoModel = new ArchivoModel();

        // Obtener el requerimiento
        $detalle = $reqModel->getDetalleCompleto($id);

        // Validar existencia
        if (!$detalle) {
            return redirect()->to(base_url('cliente/mis_solicitudes'))->with('error', 'No encontrado.');
        }

        // Obtener archivos
        $archivos = $archivoModel->where('idrequerimiento', $id)->findAll();

        // Renderizar Vista
        return view('cliente/detalle_requerimiento', [
            'requerimiento' => $detalle,
            'archivos' => $archivos,
            'user' => $userData
        ]);
    }

    /**
     * Guarda un nuevo requerimiento Completo; con su atención y archivos adjuntos, ademas de Crear el Primer Tracking (Seguimiento)
     * @throws \RuntimeException
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function guardar()
    {
        // Validar Sesion
        $userSession = $this->getActiveUser();
        $idUsuario = is_array($userSession) ? $userSession['id'] : $userSession;

        if (!$idUsuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Sesión no válida.']);
        }

        // Validaciones de Entrada
        $errores = [];

        // Servicio 
        $idServicio = $this->request->getPost('idservicio');
        $idServicio = (!empty($idServicio) && $idServicio != '0') ? (int) $idServicio : null;
        $servicioPersonalizado = $this->request->getPost('servicio_personalizado');

        // Servicio "Creacion de Contenido"
        $servicioUiNombre = (string) ($this->request->getPost('servicio_ui_nombre') ?? '');
        $esConsultivo = $this->esServicioConsultivo($servicioUiNombre);

        // Validaciones de Campos, si es error agregar al arreglo $errores[]
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
        $tieneMateriales = ($this->request->getPost('tiene_materiales') === '1');
        $urlSubida = $this->request->getPost('url_subida');
        $archivos = $this->request->getFiles();
        $tieneArchivos = !empty($archivos['documentos']) && $this->hayArchivosValidos($archivos['documentos']);
        if ($tieneMateriales && !$tieneArchivos && (empty($urlSubida) || trim($urlSubida) === '')) {
            $errores[] = 'Si indica que tiene materiales, debe subir al menos un archivo o proporcionar una URL de referencia.';
        }
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
        $fechaRaw = $this->request->getPost('fecharequerida');
        if (empty($fechaRaw)) {
            $errores[] = 'La fecha de entrega requerida es obligatoria.';
        } else {
            try {
                $fechaRequerida = new \DateTime($fechaRaw, new \DateTimeZone('America/Lima'));
                $fechaRequerida->setTime(0, 0, 0); //Limpiar la Hora (Dia Completos)
                $hoy = new \DateTime('now', new \DateTimeZone('America/Lima'));
                $hoy->setTime(0, 0, 0); //Limpiar la Hora (Dia Completos)
                // Para Creación de Contenido, usar 2 días hábiles como mínimo por defecto o calcular según tipo si existe
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
        $prioridad = ucfirst(strtolower($this->request->getPost('prioridad') ?? 'Media'));
        $prioridadesPermitidas = ['Baja', 'Media', 'Alta'];
        if (!in_array($prioridad, $prioridadesPermitidas)) {
            $errores[] = 'La prioridad seleccionada no es válida.';
        }

        // Si hay errores, retornarlos
        if (!empty($errores)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Por favor corrija los siguientes errores:',
                'errores' => $errores
            ]);
        }

        // Preparar Datos a Insertar
        $fechaObj = new \DateTime($fechaRaw, new \DateTimeZone('America/Lima'));

        // Array con datos del Requerimiento (Obligatorios, excepto cuando el servicio en 'Creacion de Contenido    ')
        $dataReq = [
            'idusuarioempresa' => (int) $idUsuario,
            'idservicio' => $idServicio,
            'servicio_personalizado' => $idServicio === null ? trim($servicioPersonalizado) : null,
            'titulo' => trim($titulo),
            'objetivo_comunicacion' => $esConsultivo ? '' : trim((string) $objetivo),
            'descripcion' => trim($descripcion),
            'tipo_requerimiento' => $esConsultivo ? '' : $tipoReq,
            'canales_difusion' => json_encode($esConsultivo ? [] : $canales),
            'publico_objetivo' => $esConsultivo ? '' : trim((string) $publico),
            'tiene_materiales' => $tieneMateriales ?? false,
            'url_subida' => !empty($urlSubida) ? trim($urlSubida) : null,
            'formatos_solicitados' => json_encode($esConsultivo ? [] : $formatos),
            'formato_otros' => (!$esConsultivo && in_array('Otros', $formatos)) ? trim((string) $formatoOtros) : '',
            'fecharequerida' => $fechaObj->format('Y-m-d H:i:s'),
            'prioridad' => $prioridad,
        ];

        // Iniciar Transaccion (Insertar Requerimiento) 
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insertar Requerimiento
            $reqModel = new RequerimientoModel();

            $idReq = $reqModel->insert($dataReq);
            $idReq = $this->obtenerIdInsertado($db, $idReq);

            // Validacion
            if (!$idReq) {
                log_message('error', 'Errores requerimiento: ' . json_encode($reqModel->errors()));
                throw new \RuntimeException('No se pudo obtener el ID del requerimiento.');
            }

            // Obtener el area_agencia según el servicio seleccionado
            $servicioModel = new ServicioModel();

            // Para servicios personalizados, usar área especial "Por Asignar"
            if ($idServicio === null) {
                $idAreaAgencia = 1; // Por defecto área de Diseño para servicios personalizados
            } else {
                $idAreaAgencia = $servicioModel->getAreaAgenciaByServicio((int) $idServicio);
            }

            // Insertar Atención vinculada al requerimiento
            $atencionModel = new AtencionModel();

            $idAtn = $atencionModel->insert([
                'idrequerimiento' => $idReq,
                'idadmin' => 1,
                'idservicio' => $idServicio,
                'servicio_personalizado' => $dataReq['servicio_personalizado'],
                'titulo' => $dataReq['titulo'],
                'prioridad' => $dataReq['prioridad'],
                'estado' => 'pendiente_sin_asignar',
                'fechafin' => $fechaObj->format('Y-m-d'),
                'url_entrega' => null,
                'idarea_agencia' => $idAreaAgencia,
            ]);

            $idAtn = $this->obtenerIdInsertado($db, $idAtn);
            // Validacion
            if (!$idAtn) {
                log_message('error', 'Errores atención: ' . json_encode($atencionModel->errors()));
                throw new \RuntimeException('No se pudo obtener el ID de la atención.');
            }

            // Creacion del Primer Tracking (Notificacion)
            $trackingModel = new TrackingModel();
            $trackingModel->insert([
                'idatencion' => $idAtn,
                'idusuario' => $idUsuario,
                'accion' => "Solicitud registrada exitosamente.\nSu requerimiento ha sido recibido y se encuentra en cola de asignación.\nRecibirá notificación cuando sea procesado.",
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'),
            ]);

            // Guardar archivos adjuntos (si el usuario subió alguno)
            $this->guardarArchivos($idReq, $idAtn);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error al guardar requerimiento: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => $e->getMessage(),
            ]);
        }

        // CONFIRMAR TRANSACCIÓN 
        $db->transComplete();
        //Validar Error en Transaccion
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error en transacción.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'msg' => '¡Requerimiento enviado con éxito!',
            'id_req' => $idReq,
        ]);
    }

    /**
     * Obtiene los días hábiles requeridos según el tipo de requerimiento
     * @param mixed $tipo
     * @return int
     */
    private function obtenerDiasHabilesPorTipo($tipo)
    {
        $mapaDias = [
            'Adaptación de Arte' => 2,
            'Creación de Arte' => 4,
            'Creación de editorial' => 7,
            'Adaptación de editorial' => 7,
            'Creación de Videos' => 7,
            'Trabajo editorial' => 7,
        ];
        return $mapaDias[$tipo] ?? 2;
    }

    /**
     * Verifica si hay archivos válidos subidos
     * @param mixed $archivos
     * @return bool
     */
    private function hayArchivosValidos($archivos)
    {
        if (empty($archivos)) {
            return false;
        }
        foreach ($archivos as $file) {
            if ($file->isValid()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fallback para obtener el ID insertado en PostgreSQL
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
     * Guarda los archivos adjuntos del formulario en disco y en la BD
     * @param int $idReq
     * @param int $idAtn
     * @return void
     */
    private function guardarArchivos(int $idReq, int $idAtn): void
    {
        $archivos = $this->request->getFiles();

        // Si no hay archivos con el name="documentos[]", salir
        if (empty($archivos['documentos'])) {
            return;
        }

        $archivoModel = new ArchivoModel();
        $carpeta = FCPATH . 'uploads/materiales-referencia';

        // Crear la carpeta si no existe
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        foreach ($archivos['documentos'] as $file) {
            // Saltar archivos inválidos o ya procesados
            if (!$file->isValid() || $file->hasMoved()) {
                continue;
            }
            try {
                // Generar nombre único y mover el archivo
                $nombreNuevo = $file->getRandomName();
                $file->move($carpeta, $nombreNuevo);

                // Registrar en la BD (archivos del cliente solo tienen idrequerimiento)
                $archivoModel->insert([
                    'idrequerimiento' => $idReq,
                    'idatencion' => null,  // Los archivos del cliente no tienen idatencion
                    'nombre' => $file->getClientName(),          // Nombre original
                    'ruta' => 'uploads/materiales-referencia/' . $nombreNuevo,  // Ruta relativa
                    'tipo' => $file->getClientMimeType(),      // Ej: image/png, application/pdf
                    'tamano' => $file->getSize(),                // Tamaño en bytes
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error al guardar archivo: ' . $e->getMessage());
            }
        }
    }

    /**
     * Funcion para Falidar la Fecha Minima Segun el Tipo de Requerimiento
     * @param mixed $diasHabiles
     * @return \DateTime
     */
    private function calcularFechaMinima($diasHabiles)
    {
        // Usar zona horaria de Peru (Coincidencia Fronted)
        $fecha = new \DateTime('now', new \DateTimeZone('America/Lima'));
        $fecha->setTime(0, 0, 0); //Limpiar la Hora (Dia Completos)
        $cont = 0;

        while ($cont < $diasHabiles) {
            $fecha->modify('+1 day');
            // 6 = Sábado, 7 = Domingo
            if ($fecha->format('N') < 6) {
                $cont++;
            }
        }
        // +1 día adicional (No cuenta el Dia de la Solicitud)
        $fecha->modify('+1 day');
        return $fecha;
    }

    /**
     * Determina si el servicio debe usar validación consultiva
     * @param string $nombreServicio
     * @return bool
     */
    private function esServicioConsultivo(string $nombreServicio): bool
    {
        $normalizado = trim(mb_strtolower($nombreServicio));
        return $normalizado === 'creación de contenido' || $normalizado === 'creacion de contenido';
    }

    /**
     * Endpoint: Obtiene el detalle completo de un requerimiento en formato JSON
     * @param mixed $RequerimientoID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detalle($RequerimientoID)
    {
        // Instanciar modelos necesarios
        $reqModel = new RequerimientoModel();
        $archivoModel = new ArchivoModel();

        // Obtener datos completos del requerimiento
        $data = $reqModel->getDetalleCompleto($RequerimientoID);

        // Validación - Si no existe el requerimiento, retornar error
        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Requerimiento no encontrado'
            ]);
        }

        // Obtener estado del requerimiento para filtrar archivos
        $estado = $data['estado'] ?? '';

        // Obtener archivos del cliente (siempre visibles)
        $archivosCliente = $archivoModel->where('idrequerimiento', $RequerimientoID)
            ->where('idatencion IS NULL')
            ->findAll();

        // Obtener archivos del empleado (solo si está finalizado)
        $archivosEmpleado = [];
        if ($estado === 'finalizado') {
            $archivosEmpleado = $archivoModel->where('idrequerimiento', $RequerimientoID)
                ->where('idatencion IS NOT NULL')
                ->findAll();
        }

        // Combinar archivos
        $archivos = array_merge($archivosCliente, $archivosEmpleado);

        // Obtener URLs solo si está finalizado
        $urlSubida = $data['url_subida'] ?? null;
        $urlEntrega = ($estado === 'finalizado') ? ($data['url_entrega'] ?? null) : null;

        // Retornar respuesta JSON con éxito y toda la información
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'requerimiento' => $data,
                'archivos' => $archivos,
                'archivos_cliente' => $archivosCliente,
                'archivos_empleado' => $archivosEmpleado,
                'url_subida' => $urlSubida,
                'url_entrega' => $urlEntrega,
            ]
        ]);
    }

    /**
     * Visualiza/Descarga un archivo de requerimiento de forma segura
     * @param mixed $nombreArchivo
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function verArchivo($nombreArchivo)
    {
        // LIMPIEZA: se elimina cualquier ruta (ej: "uploads/archivo.pdf" → "archivo.pdf")
        $nombreArchivo = basename($nombreArchivo);
        // Construir la ruta completa del archivo en el servidor
        $rutaCompleta = FCPATH . 'uploads/materiales-referencia/' . $nombreArchivo;

        // Validar que el archivo existe y es un archivo (no una carpeta)
        if (!is_file($rutaCompleta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("El archivo no existe o no puede ser accedido.");
        }

        // Obtener información del archivo
        $mimeType = mime_content_type($rutaCompleta);
        $size = filesize($rutaCompleta);

        // Servir el archivo con headers HTTP apropiados
        return $this->response
            ->setHeader('Content-Type', $mimeType) //Tipo de Contenido (PDF, imagen, DOCX, etc)
            // Controla como mostrar el archivo:
            //  - 'inline' → Muestra en el navegador si es posible (PDFs, imágenes)
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"')
            ->setHeader('Cache-Control', 'max-age=31536000')// Instrucción de CACHÉ 
            // setBody(): Inserta el contenido binario del archivo en la respuesta HTTP
            // file_get_contents() lee TODO el archivo en memoria
            ->setBody(file_get_contents($rutaCompleta));
    }
}