<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\UsuarioModel;
use App\Models\EmpresaModel;
use App\Models\AreasAgenciaModel;
use App\Models\ResponsablesEmpresaModel;

class UsuarioController extends Controller
{
    public function index(): string
{
    $areasAgenciaModel = new AreasAgenciaModel();
    $empresaModel      = new EmpresaModel();

    return view('admin/usuarios', [
        'titulo'       => 'Usuarios',
        'tituloPagina' => 'USUARIOS',
        'paginaActual' => 'usuarios',
        'areasAgencia' => $areasAgenciaModel->findAll(),
        'empresas'     => $empresaModel->findAll(),
    ]);
}


    /**
     * Retorna la lista de usuarios con su área asignada
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listar()
    {
        $model    = new UsuarioModel();
        $usuarios = $model->listarConArea();

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
   * Registra un nuevo usuario. Si es cliente, también crea su empresa y lo asigna como responsable.
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

      // Si es área con responsable, usamos el flujo especial
      if ($datos['rol'] === 'responsable_area') {
          return $this->registrarAreaResponsable($datos);
      }

        // Validación: Solo un responsable por área de agencia (solo para empleados)
  if ($datos['rol'] === 'empleado' && !empty($datos['esresponsable']) && !empty($datos['idarea_agencia'])) {
      $existeResponsable = $model->where('idarea_agencia', $datos['idarea_agencia'])
          ->where('esresponsable', true)
          ->where('estado', true)
          ->first();

      if ($existeResponsable) {
          return $this->response->setJSON([
              'success' => false,
              'message' => 'Ya existe un responsable para esta área: ' . $existeResponsable['nombre'] . ' ' .
  $existeResponsable['apellidos']
          ]);
      }
  }

  $id = $model->insert($datos, true);
      $id = $model->insert($datos, true);

      if (!$id) {
          return $this->response->setJSON(['success' => false, 'message' => 'Error al registrar']);
      }

      if ($datos['rol'] === 'cliente') {
          $empresaModel      = new EmpresaModel();
          $responsablesModel = new ResponsablesEmpresaModel();

          $idEmpresa = $empresaModel->insert([
              'nombreempresa' => $datos['razonsocial'],
              'ruc' => $datos['numerodoc'] ?? '',
              'correo' => $datos['correo'],
              'telefono' => $datos['telefono'] ?? '',
          ], true);

          $responsablesModel->asignarResponsable($id, $idEmpresa);
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
        $u     = $model->obtenerConArea((int) $id);

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
                  'message' => 'Ya existe un responsable para esta área. Desactive al responsable actual primero.'
              ]);
          }
      }

      $model->update($id, $datos);

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

        $model->update($datos['id'], ['estado' => (bool) $datos['estado']]);

        $msg = $datos['estado'] ? 'habilitado' : 'deshabilitado';
        return $this->response->setJSON(['success' => true, 'message' => "Usuario $msg correctamente"]);
    }
    
  /**
   * Registra un área nueva con su responsable (flujo especial).
   * @param array $datos Datos del usuario y área
   * @return \CodeIgniter\HTTP\ResponseInterface
   */
  private function registrarAreaResponsable(array $datos)
  {
      $db = \Config\Database::connect();
      $db->transBegin();

      try {
          // 1. Crear el usuario como cliente
          $usuarioModel = new UsuarioModel();
          $datosUsuario = [
              'nombre'      => $datos['nombre'],
              'apellidos'   => $datos['apellidos'],
              'correo'      => $datos['correo'],
              'telefono'    => $datos['telefono'],
              'tipodoc'     => $datos['tipodoc'],
              'numerodoc'   => $datos['numerodoc'],
              'usuario'     => $datos['usuario'],
              'clave'       => $datos['clave'],
              'rol'         => 'cliente',
              'idarea'      => null,
              'idarea_agencia' => null,
              'esresponsable' => true,
              'estado'      => true,
          ];

          $idUsuario = $usuarioModel->insert($datosUsuario, true);

          if (!$idUsuario) {
              throw new \Exception('Error al crear el usuario');
          }

          // 2. Crear el área para la empresa seleccionada
          $areasModel = new \App\Models\AreasModel();
          $datosArea = [
              'idempresa'   => $datos['idempresa'],
              'nombre'      => $datos['nombre_area'],
              'descripcion' => $datos['descripcion_area'] ?? 'Área creada desde registro de responsable',
              'activo'      => true,
          ];

          $idArea = $areasModel->insert($datosArea, true);

          if (!$idArea) {
              throw new \Exception('Error al crear el área');
          }

          // 3. Actualizar el usuario con el idarea asignado
          $usuarioModel->update($idUsuario, ['idarea' => $idArea]);

          // 4. Asignar como responsable de la empresa
          $responsablesModel = new ResponsablesEmpresaModel();
          $responsablesModel->asignarResponsable($idUsuario, $datos['idempresa']);

          $db->transCommit();

          return $this->response->setJSON([
              'success' => true,
              'message' => 'Área y responsable registrados correctamente'
          ]);

      } catch (\Exception $e) {
          $db->transRollback();
          return $this->response->setJSON([
              'success' => false,
              'message' => $e->getMessage()
          ]);
      }
  }

}