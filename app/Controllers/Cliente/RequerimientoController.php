<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
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
        if (!$idUsuario) { return $this->response->setJSON(['status' => 'error', 'msg' => 'Sesión no válida.']); }

        // Buscamos a qué empresa pertenece este usuario/área
        $usuarioModel = new UsuarioModel();
        $userData = $usuarioModel->getDatosEmpresaUsuario($idUsuario);
        // Error si el usuario no tiene área o empresa asignada
        if (!$userData || empty($userData['idempresa'])) {
            return $this->response->setJSON([ 'status' => 'error', 'msg' => 'No se encontró una empresa asociada a tu perfil de usuario.' ]);
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

        //Validar que NO estén los dos llenos al mismo tiempo
        if (!empty($idServicio) && !empty($servPerso)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'No puede seleccionar un servicio del catálogo y escribir uno personalizado a la vez.'
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

        /**
         * Finaliza la Transaccion
         * transComplete() confirma todos los cambios en la base de datos
         * Si salió bien, ejecuta un COMMIT (guarda todos los cambios) | Si ocurrió algún error, ejecuta un ROLLBACK (revierte todo)
         */
        $db->transComplete();

        /**
         * Verificar Estado Transaccion
         * transStatus() devuelve FALSE si algo salió mal durante los INSERT | Devuelve TRUE si la transacción se completó exitosamente
         */
        if ($db->transStatus() === false) { 
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al procesar el requerimiento en la base de datos.']); 
        }

        //Respuesta de Exito
        return $this->response->setJSON([
            'status' => 'success',
            'msg' => '¡Requerimiento enviado con éxito!',
            'id_req' => $idGenerado
        ]);
    }
}