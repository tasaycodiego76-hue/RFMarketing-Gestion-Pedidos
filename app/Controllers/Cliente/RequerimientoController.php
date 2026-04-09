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
    public function guardar(){
        // Obtener usuario de la sesión para seguridad
        $userSession = $this->getActiveUser();
        $idUsuario = is_array($userSession) ? $userSession['id'] : $userSession;
        // Validacion
        if (!$idUsuario) { return $this->response->setJSON(['status' => 'error', 'msg' => 'Sesión no válida.']); }

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
        $dataReq = [
            'idempresa' => $userData['idempresa'], // Tomado del detalle del usuario
            'idservicio' => !empty($idServicio) ? $idServicio : null,
            'servicio_personalizado' => !empty($servPerso) ? $servPerso : null,
            'titulo' => $this->request->getPost('titulo'),
            'objetivo_comunicacion' => $this->request->getPost('objetivo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'tipo_requerimiento' => $this->request->getPost('tipo_req'),
            'canales_difusion' => $this->request->getPost('canales'),
            'publico_objetivo' => $this->request->getPost('publico'),
            'tiene_materiales' => $this->request->getPost('materiales') === 'true' ? true : false,
            'formatos_solicitados' => $this->request->getPost('formatos'),
            'formato_otros' => $this->request->getPost('formato_otros') ?? '',
            'fecharequerida' => $this->request->getPost('fecha_entrega'),
            'prioridad' => $this->request->getPost('prioridad') ?? 'Media'
        ];

        // Inserta el requerimiento y obtiene el ID generado automáticamente
        $reqModel->insert($dataReq);

        /**
         * insertID() obtiene el ID del último registro insertado
         * Este ID es necesario para vincular la tabla 'atencion' con 'requerimiento' a través de la clave foránea 'idrequerimiento'.
         */
        $idGenerado = $db->insertID();

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
            'fechafin' => $dataReq['fecharequerida']
        ];

        // Inserta el registro de atención
        $atencionModel->insert($dataAtn);
        $idAtnGenerado = $db->insertID(); //Captura el ID Atencion para los Archivos

        /**
         * VERIFICAR ESTADO DE LA TRANSACCIÓN
         * Si transStatus() devuelve FALSE significa que algo falló en los INSERT anteriores.
         * En ese caso, ejecutamos transRollback() para deshacer todos los cambios
         * (tanto del requerimiento como de la atención) y devolver la BD a su estado anterior.
         */
        if ($db->transStatus() === false) {
            // ROLLBACK: Revierte todos los cambios en caso de error
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al crear el requerimiento en BD.']);
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
                        'idatencion'      => $idAtnGenerado, //Id de la Atencion
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

    /**
     * Valida un archivo individual
     * @param $file Archivo a validar
     * @return array ['valido' => bool, 'error' => string|null]
     */
    private function validarArchivo($file){
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
}