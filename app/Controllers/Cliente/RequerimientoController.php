<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\ArchivoModel;
use App\Models\RequerimientoModel;
use App\Models\AtencionModel;
use App\Models\UsuarioModel;
use App\Models\ServicioModel;

class RequerimientoController extends BaseController
{
    /**
     * Funcion que Renderiza la Vista de la Plantilla e Insertara los Detalles del Requerimiento
     * @param mixed $id
     * @return string|\CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function vistaDetalle($id)
    {
        // Obtiene el usuario de la sesión
        $user = $this->getActiveUser();

        // Validación de Seguridad
        if (!is_array($user) || $user['rol'] !== 'cliente') {
            // Si no es cliente, lo mandamos al login o inicio con un flashdata
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
            'requerimiento' => $detalle, // Datos del centro de la página
            'archivos' => $archivos,      // Lista de archivos
            'user' => $userData       // Informacion para el Sidebar / Topbar (Plantilla)
        ]);
    }

    /**
     * Guarda un nuevo requerimiento con su atención y archivos adjuntos.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function guardar()
    {
        // VALIDAR SESIÓN
        $userSession = $this->getActiveUser();
        $idUsuario = is_array($userSession) ? $userSession['id'] : $userSession;

        if (!$idUsuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Sesión no válida.']);
        }

        // ========== VALIDACIONES DE CAMPOS ==========
        $errores = [];

        // Servicio
        $idServicio = $this->request->getPost('idservicio');
        $idServicio = (!empty($idServicio) && $idServicio != '0') ? (int) $idServicio : null;
        $servicioPersonalizado = $this->request->getPost('servicio_personalizado');

        // Validar servicio personalizado si es requerido
        if ($idServicio === null && (empty($servicioPersonalizado) || trim($servicioPersonalizado) === '')) {
            $errores[] = 'El nombre del servicio personalizado es obligatorio.';
        }

        // Título
        $titulo = $this->request->getPost('titulo');
        if (empty($titulo) || trim($titulo) === '') {
            $errores[] = 'El título del requerimiento es obligatorio.';
        }

        // Objetivo de comunicación
        $objetivo = $this->request->getPost('objetivo_comunicacion');
        if (empty($objetivo) || trim($objetivo) === '') {
            $errores[] = 'El objetivo de comunicación es obligatorio.';
        }

        // Descripción
        $descripcion = $this->request->getPost('descripcion');
        if (empty($descripcion) || trim($descripcion) === '') {
            $errores[] = 'La descripción es obligatoria.';
        }

        // Tipo de requerimiento
        $tipoReq = $this->request->getPost('tipo_requerimiento');
        if (empty($tipoReq) || trim($tipoReq) === '') {
            $errores[] = 'El tipo de requerimiento es obligatorio.';
        }

        // Canales de difusión - mínimo 1, máximo 3
        $canalesRaw = $this->request->getPost('canales_difusion');
        $canales = [];
        if (!empty($canalesRaw)) {
            $canales = json_decode($canalesRaw, true) ?: [];
        }
        $cantCanales = count($canales);
        if ($cantCanales === 0) {
            $errores[] = 'Debe seleccionar al menos un canal de difusión.';
        } elseif ($cantCanales > 3) {
            $errores[] = 'No puede seleccionar más de 3 canales de difusión.';
        }

        // Público objetivo
        $publico = $this->request->getPost('publico_objetivo');
        if (empty($publico) || trim($publico) === '') {
            $errores[] = 'El público objetivo es obligatorio.';
        }

        // Materiales
        $tieneMateriales = ($this->request->getPost('tiene_materiales') === '1');
        $urlSubida = $this->request->getPost('url_subida');
        $archivos = $this->request->getFiles();
        $tieneArchivos = !empty($archivos['documentos']) && $this->hayArchivosValidos($archivos['documentos']);

        if ($tieneMateriales && !$tieneArchivos && (empty($urlSubida) || trim($urlSubida) === '')) {
            $errores[] = 'Si indica que tiene materiales, debe subir al menos un archivo o proporcionar una URL de referencia.';
        }

        // Formatos solicitados - mínimo 1
        $formatosRaw = $this->request->getPost('formatos_solicitados');
        $formatos = [];
        if (!empty($formatosRaw)) {
            $formatos = json_decode($formatosRaw, true) ?: [];
        }
        if (count($formatos) === 0) {
            $errores[] = 'Debe seleccionar al menos un formato solicitado.';
        }

        // Formato "Otros" - si está seleccionado, el campo formato_otros es obligatorio
        $formatoOtros = $this->request->getPost('formato_otros');
        if (in_array('Otros', $formatos) && (empty($formatoOtros) || trim($formatoOtros) === '')) {
            $errores[] = 'Si selecciona "Otros" en formatos, debe especificar el formato deseado.';
        }

        // Fecha requerida - validar según tipo de requerimiento
        $fechaRaw = $this->request->getPost('fecharequerida') ?? $this->request->getPost('fecha_entrega');
        if (empty($fechaRaw)) {
            $errores[] = 'La fecha de entrega requerida es obligatoria.';
        } else {
            try {
                $fechaRequerida = new \DateTime($fechaRaw);
                $fechaRequerida->setTime(0, 0, 0);
                $hoy = new \DateTime();
                $hoy->setTime(0, 0, 0);

                // Calcular fecha mínima según tipo de requerimiento
                $diasHabiles = $this->obtenerDiasHabilesPorTipo($tipoReq);
                $fechaMinima = $this->calcularFechaMinima($diasHabiles);

                if ($fechaRequerida < $fechaMinima) {
                    $errores[] = "La fecha de entrega debe ser al menos dentro de {$diasHabiles} días hábiles ({$fechaMinima->format('d/m/Y')}) según el tipo de requerimiento '{$tipoReq}'.";
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

        // Si hay errores, retornarlos
        if (!empty($errores)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Por favor corrija los siguientes errores:',
                'errores' => $errores
            ]);
        }

        // ========== PREPARAR DATOS PARA INSERTAR ==========
        $fechaObj = new \DateTime($fechaRaw);

        // Armar array con todos los campos del requerimiento
        $dataReq = [
            'idusuarioempresa' => (int) $idUsuario,
            'idservicio' => $idServicio,
            'servicio_personalizado' => $idServicio === null ? trim($servicioPersonalizado) : null,
            'titulo' => trim($titulo),
            'objetivo_comunicacion' => trim($objetivo),
            'descripcion' => trim($descripcion),
            'tipo_requerimiento' => $tipoReq,
            'canales_difusion' => json_encode($canales),
            'publico_objetivo' => trim($publico),
            'tiene_materiales' => $tieneMateriales,
            'url_subida' => !empty($urlSubida) ? trim($urlSubida) : null,
            'formatos_solicitados' => json_encode($formatos),
            'formato_otros' => in_array('Otros', $formatos) ? trim($formatoOtros) : '',
            'fecharequerida' => $fechaObj->format('Y-m-d H:i:s'),
            'prioridad' => $prioridad,
        ];

        // INSERTAR EN LA BD (dentro de una transacción) 
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insertar Requerimiento
            $reqModel = new RequerimientoModel();
            $idReq = $reqModel->insert($dataReq);
            $idReq = $this->obtenerIdInsertado($db, $idReq); // Fallback para PostgreSQL IDENTITY

            if (!$idReq) {
                throw new \RuntimeException('No se pudo obtener el ID del requerimiento.');
            }

            // Obtener el area_agencia según el servicio seleccionado
            $servicioModel = new ServicioModel();
            $idAreaAgencia = $servicioModel->getAreaAgenciaByServicio((int)$idServicio);

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

            if (!$idAtn) {
                throw new \RuntimeException('No se pudo obtener el ID de la atención.');
            }

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
     * @param string $tipo Tipo de requerimiento
     * @return int Días hábiles requeridos
     */
    private function obtenerDiasHabilesPorTipo($tipo)
    {
        $mapaDias = [
            'Adaptación de Arte' => 2,
            'Creación de Arte' => 4,
            'Creación de Videos' => 7,
            'Trabajo editorial' => 7,
        ];
        return $mapaDias[$tipo] ?? 2; // Default 2 días si no coincide
    }

    /**
     * Verifica si hay archivos válidos subidos
     * @param array $archivos Array de archivos
     * @return bool
     */
    private function hayArchivosValidos($archivos)
    {
        if (empty($archivos)) {
            return false;
        }

        foreach ($archivos as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fallback para obtener el ID insertado en PostgreSQL
     * @return int|null  ID insertado o null si falló
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
     */
    private function guardarArchivos(int $idReq, int $idAtn): void
    {
        $archivos = $this->request->getFiles();

        // Si no hay archivos con el name="documentos[]", salir
        if (empty($archivos['documentos'])) {
            return;
        }

        $archivoModel = new ArchivoModel();
        $carpeta = WRITEPATH . 'uploads/requerimientos';

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

                // Registrar en la BD
                $archivoModel->insert([
                    'idrequerimiento' => $idReq,
                    'idatencion' => $idAtn,
                    'nombre' => $file->getClientName(),          // Nombre original
                    'ruta' => 'uploads/requerimientos/' . $nombreNuevo,  // Ruta relativa
                    'tipo' => $file->getClientMimeType(),      // Ej: image/png, application/pdf
                    'tamano' => $file->getSize(),                // Tamaño en bytes
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error al guardar archivo: ' . $e->getMessage());
            }
        }
    }

    //Funcion para Falidar la Fecha Minima Segun el Tipo de Requerimiento
    private function calcularFechaMinima($diasHabiles)
    {
        $fecha = new \DateTime();
        $cont = 0;

        while ($cont < $diasHabiles) {
            $fecha->modify('+1 day');
            // 6 = Sábado, 7 = Domingo
            if ($fecha->format('N') < 6) {
                $cont++;
            }
        }
        return $fecha;
    }

    /**
     * Valida un archivo individual
     * @param $file Archivo a validar
     * @return array ['valido' => bool, 'error' => string|null]
     */
    private function validarArchivo($file)
    {
        // Tamaño máximo: 500MB
        $tamanoMaximo = 100 * 1024 * 1024;

        if (!$file->isValid() || $file->hasMoved()) {
            return ['valido' => false, 'error' => 'Archivo inválido o ya procesado'];
        }

        if ($file->getSize() > $tamanoMaximo) {
            return ['valido' => false, 'error' => 'Archivo excede 500MB'];
        }

        return ['valido' => true, 'error' => null];
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

        // Traer todos los archivos vinculados a este requerimiento
        $archivos = $archivoModel->where('idrequerimiento', $RequerimientoID)->findAll();

        // Retornar respuesta JSON con éxito y toda la información
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'requerimiento' => $data,        // Datos del requerimiento + estado + servicio
                'archivos' => $archivos          // Array con archivos asociados
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
        // Limpieza de seguridad
        // basename() elimina cualquier ruta (ej: "uploads/archivo.pdf" → "archivo.pdf")
        $nombreArchivo = basename($nombreArchivo);

        // Construir la ruta completa del archivo en el servidor
        // WRITEPATH es la carpeta writable de CodeIgniter (fuera del directorio público)
        $rutaCompleta = WRITEPATH . 'uploads/requerimientos/' . $nombreArchivo;

        // Validar que el archivo existe y es un archivo (no una carpeta)
        if (!is_file($rutaCompleta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "El archivo no existe o no puede ser accedido."
            );
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