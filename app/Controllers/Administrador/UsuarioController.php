<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\UsuarioModel;
use App\Models\EmpresaModel;

class UsuarioController extends Controller
{
 public function index(): string
{
    $db = \Config\Database::connect();
    return view('admin/usuarios', [
        'titulo'       => 'Usuarios',
        'tituloPagina' => 'USUARIOS',
        'paginaActual' => 'usuarios',
        'areas' => $db->table('areas_agencia')->get()->getResultArray()
    ]);
}

    /**
     * Devuelve la lista de usuarios con su área de agencia en JSON.
     */
    public function listar()
    {
        $db       = \Config\Database::connect();
     $usuarios = $db->table('usuarios u')
    ->select('u.*, a.nombre as area_nombre')
    ->join('areas_agencia a', 'a.id = u.idarea_agencia', 'left')
    ->get()->getResultArray();

        foreach ($usuarios as &$u) {
            $u['estado'] = ($u['estado'] === true || $u['estado'] === 't' || $u['estado'] == 1) ? 1 : 0;
        }

        return $this->response->setJSON($usuarios);
    }

    /**
     * Lista las áreas de la agencia
     */
    public function listarServicios() 
    {
        $db        = \Config\Database::connect();
        // Aquí ya lo tenías bien como 'areas_agencia'
        $servicios = $db->table('areas_agencia') 
            ->where('activo', true)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($servicios);
    }
    

    /**
     * Registra un nuevo usuario. Si el rol es cliente,
     * también crea la empresa y lo asigna como responsable.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function registrar()
    {
        $model          = new UsuarioModel();
        $datos          = $this->request->getJSON(true);

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
            $empresaModel = new EmpresaModel();
            $idEmpresa    = $empresaModel->insert([
                'nombreempresa' => $datos['razonsocial'],
                'ruc'           => $datos['numerodoc'] ?? '',
                'correo'        => $datos['correo'],
                'telefono'      => $datos['telefono'] ?? '',
            ], true);

            $db = \Config\Database::connect();
            $db->table('responsables_empresa')->insert([
                'idusuario'    => $id,
                'idempresa'    => $idEmpresa,
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Usuario registrado correctamente']);
    }

    /**
     * Devuelve los datos de un usuario por ID incluyendo
     * el nombre de su área de agencia (para rellenar el modal de edición).
     * @param mixed $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function obtener($id)
{
    $db = \Config\Database::connect();
    $u  = $db->table('usuarios u')
        ->select('u.*, a.nombre as area_nombre')
        ->join('areas_agencia a', 'a.id = u.idarea_agencia', 'left')
        ->where('u.id', $id)
        ->get()->getRowArray();

    if (!$u) {
        return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado']);
    }

    return $this->response->setJSON($u);
}

    /**
     * Actualiza los datos de un usuario. Valida correo y usuario duplicado
     * excluyendo el propio registro. Solo hashea la clave si viene en los datos.
     * @param mixed $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function editar($id)
    {
        $model = new UsuarioModel();
        $datos = $this->request->getJSON(true);

        // Verificar correo duplicado (excluyendo el mismo usuario)
        $correoExiste = $model->where('correo', $datos['correo'])->where('id !=', $id)->first();
        if ($correoExiste) {
            return $this->response->setJSON(['success' => false, 'message' => 'El correo ya está en uso']);
        }

        $usuarioExiste = $model->where('usuario', $datos['usuario'])->where('id !=', $id)->first();
        if ($usuarioExiste) {
            return $this->response->setJSON(['success' => false, 'message' => 'El usuario ya está en uso']);
        }

        // Solo hashear clave si viene en los datos
        if (!empty($datos['clave'])) {
            $datos['clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT);
        } else {
            unset($datos['clave']);
        }

        $model->update($id, $datos);

        return $this->response->setJSON(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    }


    /**
     * Cambia el estado activo/inactivo de un usuario.
     * Usa cast a bool para compatibilidad con PostgreSQL.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function toggleEstado()
    {
        $model = new UsuarioModel();
        $datos = $this->request->getJSON(true);

        // Cast a bool para compatibilidad con PostgreSQL (guarda 't'/'f')
        $model->update($datos['id'], ['estado' => (bool)$datos['estado']]);

        $msg = $datos['estado'] ? 'habilitado' : 'deshabilitado';
        return $this->response->setJSON(['success' => true, 'message' => "Usuario $msg correctamente"]);
    }
 }