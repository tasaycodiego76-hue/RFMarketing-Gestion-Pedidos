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

        return view('admin/usuarios', [
            'titulo'       => 'Usuarios',
            'tituloPagina' => 'USUARIOS',
            'paginaActual' => 'usuarios',
            'areasAgencia' => $areasAgenciaModel->findAll(),
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
}