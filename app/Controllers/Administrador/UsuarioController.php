<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\UsuarioModel;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\AreasModel;
use App\Models\ResponsablesEmpresaModel;
use App\Models\ServicioModel;
use App\Models\RequerimientoModel;

class UsuarioController extends Controller
{
    public function index(): string
    {
        $areasAgenciaModel = new AreasAgenciaModel();
        $empresaModel = new EmpresaModel();

        return view('admin/usuarios', [
            'titulo' => 'Usuarios',
            'tituloPagina' => 'USUARIOS',
            'paginaActual' => 'usuarios',
            'areasAgencia' => $areasAgenciaModel->listarActivas(),
            'empresas' => $empresaModel->listarActivas(),
        ]);
    }


    /**
     * Retorna la lista de usuarios con su área asignada
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listar()
    {
        $search = trim((string) $this->request->getGet('search'));
        $model = new UsuarioModel();
        $usuarios = $model->listarConArea($search);

        foreach ($usuarios as &$u) {
            $u['estado'] = ($u['estado'] === true || $u['estado'] === 't' || $u['estado'] == 1) ? 1 : 0;
        }

        return $this->response->setJSON($usuarios);
    }

    /**
     *  Retorna las áreas activas para el modal de registro.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listarServicios()
    {
        $model = new AreasAgenciaModel();

        return $this->response->setJSON($model->listarActivas());
    }

    /**
     * Retorna las áreas de una empresa específica
     * @param int $idEmpresa
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listarAreasPorEmpresa($idEmpresa)
    {
        $model = new AreasModel();
        return $this->response->setJSON($model->listarActivasPorEmpresa((int) $idEmpresa));
    }

    /**
     * Verifica si un área de agencia ya tiene un responsable asignado.
     * @param int $idArea
     * @param int|null $excludeId ID de usuario a excluir de la búsqueda
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function verificarAreaResponsable($idArea, $excludeId = null)
    {
        $model = new UsuarioModel();
        $query = $model->where('idarea_agencia', $idArea)
            ->where('esresponsable', true)
            ->where('estado', true);

        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }

        $responsable = $query->first();

        return $this->response->setJSON([
            'ocupado' => !empty($responsable),
            'nombre' => $responsable ? ($responsable['nombre'] . ' ' . $responsable['apellidos']) : null
        ]);
    }


    /**
     * Registra un nuevo usuario. 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function registrar()
    {
        $model = new UsuarioModel();
        $datos = $this->request->getJSON(true);

        if ($model->where('correo', $datos['correo'])->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El correo ya está registrado']);
        }
        if ($model->where('usuario', $datos['usuario'])->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El usuario ya está en uso']);
        }

        $datos['clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT);

        // Si es Responsable de Área (Cliente), el idarea ya viene seleccionado
        if ($datos['rol'] === 'responsable_area') {
            
            // Verificar si el área ya tiene un responsable activo
            $responsableExistente = $model->where('idarea', $datos['idarea'])
                ->where('estado', true)
                ->first();

            if ($responsableExistente) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ya existe un responsable asignado a esta área: ' . $responsableExistente['nombre'] . ' ' . $responsableExistente['apellidos'] . '. Solo puede haber un responsable por área.',
                ]);
            }

            $datos['rol'] = 'cliente';
            $datos['esresponsable'] = true;
            $datos['estado'] = true;

            $id = $model->insert($datos, true);
            if (!$id) {
                return $this->response->setJSON(['success' => false, 'message' => 'Error al registrar el responsable']);
            }

            $responsablesModel = new ResponsablesEmpresaModel();
            $responsablesModel->asignarResponsable($id, $datos['idempresa']);

            return $this->response->setJSON(['success' => true, 'message' => 'Responsable de área registrado correctamente']);
        }

        // Automatización: El primer empleado en un área es el responsable
        if ($datos['rol'] === 'empleado' && !empty($datos['idarea_agencia'])) {
            $responsableActual = $model->where('idarea_agencia', $datos['idarea_agencia'])
                ->where('esresponsable', true)
                ->where('estado', true)
                ->first();

            $datos['esresponsable'] = ($responsableActual === null);
        } else if ($datos['rol'] === 'cliente') {
            $datos['esresponsable'] = true;
        } else {
            $datos['esresponsable'] = false;
        }


        $id = $model->insert($datos, true);

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al registrar']);
        }

        if ($datos['rol'] === 'cliente') {
            $empresaModel = new EmpresaModel();
            $responsablesModel = new ResponsablesEmpresaModel();

            $idEmpresa = $empresaModel->insert([
                'nombreempresa' => $datos['razonsocial'],
                'ruc' => $datos['numerodoc'] ?? '',
                'correo' => $datos['correo'],
                'telefono' => $datos['telefono'] ?? '',
            ], true);

            $responsablesModel->asignarResponsable($id, $idEmpresa);
        }

        // Regla nueva: crear servicio por área al registrar empleado
        if ($datos['rol'] === 'empleado' && !empty($datos['idarea_agencia'])) {
            $this->sincronizarServicioPorAreaAgencia((int) $datos['idarea_agencia']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Usuario registrado correctamente']);
    }

    /**
     * * Retorna los datos de un usuario por ID para el modal de edición.
     * @param mixed $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function obtener($id)
    {
        $model = new UsuarioModel();
        $u = $model->obtenerConArea((int) $id);

        if (!$u) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado']);
        }

        return $this->response->setJSON($u);
    }

    /**
     * Actualiza los datos de un usuario.
     * @param mixed $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function editar($id)
    {
        $model = new UsuarioModel();
        $datos = $this->request->getJSON(true);

        if ($model->where('correo', $datos['correo'])->where('id !=', $id)->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El correo ya está en uso']);
        }
        if ($model->where('usuario', $datos['usuario'])->where('id !=', $id)->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El usuario ya está en uso']);
        }

        if (!empty($datos['clave'])) {
            $datos['clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT);
        } else {
            unset($datos['clave']);
        }

        // Validación: Solo un responsable por área de agencia
        if ($datos['rol'] === 'empleado' && !empty($datos['esresponsable']) && !empty($datos['idarea_agencia'])) {
            $existeResponsable = $model->where('idarea_agencia', $datos['idarea_agencia'])
                ->where('esresponsable', true)
                ->where('id !=', $id)
                ->where('estado', true)
                ->first();

            if ($existeResponsable) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ya existe un responsable para esta área: ' . $existeResponsable['nombre'] . ' ' . $existeResponsable['apellidos'] . '. Para cambiar de responsable debe usar el botón de reasignar.',
                    'id_responsable_actual' => $existeResponsable['id'],
                    'requiere_reasignacion' => true
                ]);
            }
        }

        $model->update($id, $datos);

        // Si el usuario es empleado y tiene área de agencia, garantizar servicio asociado
        if (($datos['rol'] ?? null) === 'empleado' && !empty($datos['idarea_agencia'])) {
            $this->sincronizarServicioPorAreaAgencia((int) $datos['idarea_agencia']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    }

    /**
     * Activa o desactiva un usuario según el estado recibido.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function toggleEstado()
    {
        $model = new UsuarioModel();
        $datos = $this->request->getJSON(true);
        $id = $datos['id'];
        $nuevoEstado = (bool) $datos['estado'];

        $usuario = $model->find($id);
        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado']);
        }

        // Si se está habilitando, realizar validaciones críticas
        if ($nuevoEstado === true) {
            // 1. Verificar si el área de la agencia está activa antes de permitir habilitar al empleado
            if (!empty($usuario['idarea_agencia'])) {
                $areaAgenciaModel = new AreasAgenciaModel();
                $area = $areaAgenciaModel->find($usuario['idarea_agencia']);
                if ($area && !($area['activo'] === true || $area['activo'] === 't' || $area['activo'] == 1)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No se puede habilitar al usuario porque su área (' . $area['nombre'] . ') está deshabilitada.'
                    ]);
                }
            }

            // 2. Evitar conflictos de responsabilidad
            $esResponsable = ($usuario['esresponsable'] === true || $usuario['esresponsable'] === 't' || $usuario['esresponsable'] == 1);

            // Caso: Empleado de Agencia
            if (!empty($usuario['idarea_agencia']) && $esResponsable) {
                $otroResponsable = $model->where('idarea_agencia', $usuario['idarea_agencia'])
                    ->where('esresponsable', true)
                    ->where('estado', true)
                    ->where('id !=', $id)
                    ->first();

                if ($otroResponsable) {
                    $model->update($id, ['esresponsable' => false]);
                }
            }

            // Caso: Responsable de Área de Empresa (Cliente)
            if ($usuario['rol'] === 'cliente' && !empty($usuario['idarea'])) {
                $otroCliente = $model->where('idarea', $usuario['idarea'])
                    ->where('estado', true)
                    ->where('id !=', $id)
                    ->first();

                if ($otroCliente) {
                    $model->update($id, ['idarea' => null, 'esresponsable' => false]);
                }
            }
        }

        $model->update($id, ['estado' => $nuevoEstado]);

        $msg = $nuevoEstado ? 'habilitado' : 'deshabilitado';
        return $this->response->setJSON(['success' => true, 'message' => "Usuario $msg correctamente"]);
    }

    /**
     * Garantiza que exista un servicio para el área de agencia seleccionada.
     * @param int $idAreaAgencia
     * @return void
     */
    private function sincronizarServicioPorAreaAgencia(int $idAreaAgencia): void
    {
        $areasAgenciaModel = new AreasAgenciaModel();
        $servicioModel = new ServicioModel();

        $area = $areasAgenciaModel->find($idAreaAgencia);
        if (!$area || empty($area['nombre'])) {
            return;
        }

        $nombreArea = trim((string) $area['nombre']);
        $descripcionArea = trim((string) ($area['descripcion'] ?? ''));
        $clave = $this->normalizarTexto($nombreArea);

        $equivalencias = [
            'diseno' => 'Diseño Gráfico',
            'diseño' => 'Diseño Gráfico',
            'edicion y video' => 'AudioVisual',
            'edición y video' => 'AudioVisual',
            'audiovisual' => 'AudioVisual',
            'fotografia' => 'Fotografía',
            'fotografía' => 'Fotografía',
        ];

        $nombreServicio = $equivalencias[$clave] ?? $nombreArea;

        $existente = $servicioModel
            ->where('LOWER(nombre)', mb_strtolower($nombreServicio))
            ->first();

        if ($existente) {
            return;
        }

        $servicioModel->insert([
            'nombre' => $nombreServicio,
            'descripcion' => $descripcionArea !== '' ? $descripcionArea : 'Servicio creado automáticamente desde Área de Agencia',
            'activo' => true,
        ]);
    }

    /**
     * Normaliza texto para comparaciones internas.
     * @param string $texto
     * @return string
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = trim(mb_strtolower($texto));
        $texto = strtr($texto, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
        ]);
        return $texto;
    }

    /* ─── REASIGNACIÓN ─── */

    public function infoReasignar($id)
    {
        $userModel = new UsuarioModel();
        $u = $userModel->getDetalleUsuario((int) $id);

        if (!$u)
            return $this->response->setJSON(['success' => false, 'message' => 'No encontrado']);

        $respModel = new ResponsablesEmpresaModel();

        if ($u['rol'] === 'cliente') {
            $activo = $respModel->obtenerActivoPorUsuario((int) $id);

            if (!$activo && !empty($u['idarea'])) {
                $areasModel = new AreasModel();
                $area = $areasModel->find($u['idarea']);
                if ($area) {
                    $empModel = new EmpresaModel();
                    $emp = $empModel->find($area['idempresa']);
                    $activo = [
                        'id' => null,
                        'idusuario' => $id,
                        'idempresa' => $area['idempresa'],
                        'nombreempresa' => $emp['nombreempresa'] ?? 'Empresa Desconocida',
                        'ruc' => $emp['ruc'] ?? '',
                        'fecha_inicio' => $u['fechacreacion'] ?? date('Y-m-d H:i:s'),
                        'estado' => 'activo'
                    ];
                }
            }

            if (!$activo) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Este cliente no tiene una empresa asignada vinculada como responsable.'
                ]);
            }

            $activo['nombre_area'] = $u['nombre_area'] ?? 'General';

            $historial = $respModel->historialPorEmpresa((int) $activo['idempresa']);
            return $this->response->setJSON([
                'success' => true,
                'tipo' => 'cliente',
                'actual' => $activo,
                'historial' => $historial,
                'usuario' => $u
            ]);
        }

        $esResponsable = ($u['rol'] === 'empleado' && ($u['esresponsable'] === true || $u['esresponsable'] === 't' || $u['esresponsable'] == 1));

        if ($esResponsable) {
            if (empty($u['idarea_agencia'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'El responsable no tiene un área de agencia asignada']);
            }

            $areaAgenciaModel = new AreasAgenciaModel();
            $area = $areaAgenciaModel->find($u['idarea_agencia']);

            $asignables = $userModel->obtenerAsignablesPorAreaAgencia((int) $u['idarea_agencia']);
            return $this->response->setJSON([
                'success' => true,
                'tipo' => 'empleado',
                'actual' => $u,
                'area' => $area,
                'asignables' => $asignables
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'El usuario seleccionado no cumple con los requisitos para ser reasignado']);
    }

    public function reasignarCliente()
    {
        $datos = $this->request->getJSON(true);
        $respModel = new ResponsablesEmpresaModel();
        $userModel = new UsuarioModel();
        $reqModel = new RequerimientoModel();

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $nuevoUsuario = $userModel->where('correo', $datos['correo'])->first();
            $idArea = !empty($datos['id_area']) ? (int) $datos['id_area'] : null;

            if ($nuevoUsuario) {
                $idNuevo = $nuevoUsuario['id'];
                $userModel->update($idNuevo, [
                    'rol' => 'cliente',
                    'idarea' => $idArea,
                    'esresponsable' => true,
                    'estado' => true
                ]);
            } else {
                $idNuevo = $userModel->insert([
                    'nombre' => $datos['nombre'],
                    'apellidos' => $datos['apellidos'],
                    'correo' => $datos['correo'],
                    'telefono' => $datos['telefono'] ?? null,
                    'tipodoc' => $datos['tipodoc'] ?? 'DNI',
                    'numerodoc' => $datos['numerodoc'] ?? '',
                    'usuario' => $datos['usuario'],
                    'clave' => password_hash($datos['clave'], PASSWORD_DEFAULT),
                    'rol' => 'cliente',
                    'idarea' => $idArea,
                    'esresponsable' => true,
                    'estado' => true
                ], true);
            }

            $idActual = !empty($datos['id_registro_actual']) ? (int) $datos['id_registro_actual'] : null;
            $idUsuarioAnterior = (int) $datos['id_usuario_anterior'];

            $ok = $respModel->reasignar($idActual, $idNuevo, (int) $datos['id_empresa'], $idUsuarioAnterior);

            if (!$ok) {
                throw new \Exception('Error al procesar la reasignación');
            }

            $reqModel->transferirRequerimientos($idUsuarioAnterior, $idNuevo);
            $userModel->update($idUsuarioAnterior, ['estado' => false]);

            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Reasignación exitosa.'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function reasignarEmpleadoArea()
    {
        $datos = $this->request->getJSON(true);
        $userModel = new UsuarioModel();

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $userModel->update($datos['id_actual'], ['esresponsable' => false]);
            $userModel->update($datos['id_nuevo'], ['esresponsable' => true]);

            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Responsable de área actualizado']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

}