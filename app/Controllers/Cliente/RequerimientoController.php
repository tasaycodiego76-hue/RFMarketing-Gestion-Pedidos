<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\ArchivoModel;
use App\Models\RequerimientoModel;
use App\Models\AtencionModel;
use App\Models\UsuarioModel;

class RequerimientoController extends BaseController
{
    /**
     * Procesa el guardado de un nuevo requerimiento y su respectiva atención
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function guardar()
    {
        // Obtener usuario de la sesión para seguridad
        $userSession = $this->getActiveUser();
        $idUsuario = is_array($userSession) ? $userSession['id'] : $userSession;
        // Validacion
        if (!$idUsuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Sesión no válida.']);
        }

        // Buscamos a qué empresa pertenece este usuario/área
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDatosEmpresaUsuario($idUsuario);
        // Error si el usuario no tiene área o empresa asignada
        if (!$userData || empty($userData['idempresa'])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No se encontró una empresa asociada a tu perfil de usuario.']);
        }

        // Obtener la instancia de conexión a la base de datos
        $db = \Config\Database::connect();

        // Recoger datos del Requerimiento (Formulario)
        $idServicio = $this->request->getPost('idservicio');
        $servPerso = $this->request->getPost('servicio_personalizado');

        /**
         * Validación para Sevicios DB / Servicio Personalizado
         */
        if (empty($idServicio) && empty($servPerso)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Debe seleccionar un servicio o especificar uno personalizado.'
            ]);
        }

        /**
         * VALIDACIÓN: No permitir ambas opciones simultáneamente
         * El usuario DEBE elegir (No Ambos):
         *   - Un servicio del catálogo (idServicio), O
         *   - Especificar un servicio personalizado (servPerso)
         */
        if (!empty($idServicio) && !empty($servPerso)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'No puede seleccionar un servicio del catálogo y escribir uno personalizado a la vez.'
            ]);
        }

        /**
         * Inicia la Transaccion de BD
         * Una transacción garantiza que todos los INSERT tengan una ejecucion exitosa, o en caso de error, se revierten todos los cambios (rollback)
         * Esto es importante porque insertamos en 2 tablas (requerimiento y atención)
         */
        $db->transStart();

        // Insertar en tabla 'requerimiento'
        $reqModel = new RequerimientoModel();

        // Asegurar formato de fecha para PostgreSQL TIMESTAMP
        $fechaEntregaRaw = $this->request->getPost('fecha_entrega');
        $fechaObjeto = new \DateTime($fechaEntregaRaw);

        $dataReq = [
            'idempresa' => (int) $userData['idempresa'],
            'idservicio' => !empty($idServicio) ? (int) $idServicio : null,
            'servicio_personalizado' => !empty($servPerso) ? $servPerso : null,
            'titulo' => $this->request->getPost('titulo'),
            'objetivo_comunicacion' => $this->request->getPost('objetivo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'tipo_requerimiento' => $this->request->getPost('tipo_requerimiento'),
            'canales_difusion' => $this->request->getPost('canales'),
            'publico_objetivo' => $this->request->getPost('publico'),
            'tiene_materiales' => filter_var($this->request->getPost('materiales'), FILTER_VALIDATE_BOOLEAN),
            'formatos_solicitados' => $this->request->getPost('formatos'),
            'formato_otros' => $this->request->getPost('formato_otros') ?? '',
            'fecharequerida' => $fechaObjeto->format('Y-m-d H:i:s'),
            'prioridad' => ucfirst(strtolower($this->request->getPost('prioridad') ?? 'Media'))
        ];

        // El método insert() ya devuelve el ID generado por defecto
        $idGenerado = $reqModel->insert($dataReq);

        // Capturamos el valor booleano correctamente
        $tieneMateriales = filter_var($this->request->getPost('materiales'), FILTER_VALIDATE_BOOLEAN);

        // Obtenemos los archivos
        $archivos = $this->request->getFiles();
        $hayArchivos = !empty($archivos['documentos']) && $archivos['documentos'][0]->isValid();

        //Validacion
        if (!$tieneMateriales && $hayArchivos) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'No puedes subir archivos si marcaste que no tienes materiales.'
            ]);
        }

        //Creacion para Validar Requerimiento Fecha Minima
        $tiemposPorTipo = [
            'Adaptación de Arte' => 7,
            'Creación de Arte' => 10,
            'Creación de Videos' => 20, // Ajusta al nombre exacto que envías
            'Trabajo editorial' => 20
        ];
        $tipoReq = $this->request->getPost('tipo_requerimiento');
        $diasNecesarios = $tiemposPorTipo[$tipoReq] ?? 7;

        //Validacion /Fecha Requerida no debe ser pasada
        $fechaMinima = $this->calcularFechaMinima($diasNecesarios);
        $fechaEntrega = new \DateTime($fechaEntregaRaw);

        // Validamos si la fecha del cliente es menor a la permitida
        if ($fechaEntrega < $fechaMinima) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => "Para el tipo '$tipoReq', la fecha mínima de entrega es el " . $fechaMinima->format('d/m/Y') . " (requiere $diasNecesarios días hábiles)."
            ]);
        }

        // Campos Vacios Oligatorios
        $requeridos = ['titulo', 'objetivo', 'descripcion', 'tipo_requerimiento', 'fecha_entrega'];
        foreach ($requeridos as $campo) {
            if (empty($this->request->getPost($campo))) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'msg' => "El campo '$campo' es obligatorio."
                ]);
            }
        }

        //Prioridad Valido
        $prioridadesValidas = ['Alta', 'Media', 'Baja'];
        $prioridad = ucfirst(strtolower($this->request->getPost('prioridad') ?? 'Media'));
        if (!in_array($prioridad, $prioridadesValidas)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Prioridad inválida.'
            ]);
        }

        if (!$idGenerado) {
            $db->transRollback();
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Fallo al insertar Requerimiento.',
                'errors' => $reqModel->errors(), // Esto te dirá si hay un error de validación
                'data_enviada' => $dataReq      // Para comparar con tus allowedFields
            ]);
        }

        // Insertar en tabla 'atencion' (Espejo para gestión)
        $atencionModel = new AtencionModel();
        $dataAtn = [
            'idrequerimiento' => $idGenerado,
            'idadmin' => 1, // Admin asignado por defecto
            'idservicio' => $dataReq['idservicio'],
            'servicio_personalizado' => $dataReq['servicio_personalizado'],
            'titulo' => $dataReq['titulo'],
            'prioridad' => $dataReq['prioridad'],
            'estado' => 'pendiente_sin_asignar',
            'respuestatexto' => '',
            'fechafin' => $fechaObjeto->format('Y-m-d')
        ];

        // Inserta el registro de atención y captura el ID directamente del modelo
        $idAtnGenerado = $atencionModel->insert($dataAtn);

        /**
         * VERIFICAR ESTADO DE LA TRANSACCIÓN
         * Si transStatus() devuelve FALSE significa que algo falló en los INSERT anteriores.
         * En ese caso, ejecutamos transRollback() para deshacer todos los cambios
         * (tanto del requerimiento como de la atención) y devolver la BD a su estado anterior.
         */
        if ($db->transStatus() === false) {
            // LOG DE ERRORES PARA DEPURAR (Revisa writable/logs/log-xxxx.php)
            log_message('error', 'Fallo en DB: ' . json_encode($db->error()));

            $db->transRollback();
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Error al crear el requerimiento en BD.',
                'debug' => $db->error() // Solo para desarrollo, quítalo después
            ]);
        }

        /**
         * Este bloque permite que los clientes suban archivos junto con su requerimiento.
         * Los archivos se guardan en el servidor y se registran en la BD para referencia futura.
         */
        $archivos = $this->request->getFiles();

        // Verificar si el cliente envió archivos en la key 'documentos'
        if (!empty($archivos['documentos'])) {
            $archivoModel = new ArchivoModel();
            // Procesar cada archivo enviado
            foreach ($archivos['documentos'] as $file) {
                // Llamar Funcion y Validar archivo
                $validacion = $this->validarArchivo($file);
                if (!$validacion['valido']) {
                    //Revertir Cambios BD (Transaccion Fallida y ERROR)
                    $db->transRollback();
                    return $this->response->setJSON(['status' => 'error', 'msg' => $validacion['error']]);
                }
                try {
                    // Definir ruta de destino para guardar archivo, Y crear Carpeta si no Existe
                    $carpeta = WRITEPATH . 'uploads/requerimientos';
                    if (!is_dir($carpeta)) {
                        mkdir($carpeta, 0755, true);
                    }
                    //Nombre Aleatorio y Mover Archivo a Carpeta de Destino
                    $nombreNuevo = $file->getRandomName();
                    $file->move($carpeta, $nombreNuevo);
                    // Registrar info del archivo en tabla 'archivos' para rastreo
                    $archivoModel->insert([
                        'idrequerimiento' => $idGenerado, //Id del Requerimiento
                        'idatencion' => $idAtnGenerado, //Id de la Atencion
                        'nombre' => $file->getClientName(), //Nombre Original del Archivo
                        'ruta' => 'uploads/requerimientos/' . $nombreNuevo,  //Ruta para Descarga (Futuro)
                        'tipo' => $file->getClientMimeType(),  //Tipo MIME (PDF, .doc, PNG, etc)
                        'tamano' => $file->getSize() //Tamaño en BYTES
                    ]);
                } catch (\Exception $e) {
                    $db->transRollback();
                    return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al guardar archivo: ' . $e->getMessage()]);
                }
            }
        }

        /**
         * Finalizar Transaccion
         * En CodeIgniter, transComplete() ejecuta automáticamente un COMMIT si todo está bien.
         */
        $db->transComplete();

        //Respuesta de Exito
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => '¡Requerimiento enviado con éxito!',
            'id_req' => $idGenerado
        ]);
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
        $tamanoMaximo = 500 * 1024 * 1024;

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