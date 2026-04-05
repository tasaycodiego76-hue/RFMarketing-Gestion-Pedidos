<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;
use App\Models\UsuarioModel;
use App\Models\EmpresaModel;

class UsuarioController extends Controller
{
    /**
     * Muestra la vista principal de gestión de usuarios.
     * @return string
     */
    public function index(): string
{
    return view('admin/usuarios', [
        'titulo'       => 'Usuarios',
        'tituloPagina' => 'USUARIOS',
        'paginaActual' => 'usuarios',
        'empresas'     => [],
    ]);
}

/**
 * Devuelve la lista de usuarios con su servicio en JSON.
 * @return \CodeIgniter\HTTP\ResponseInterface
 */
public function listar()
{
    $db       = \Config\Database::connect();
    $usuarios = $db->table('usuarios u')
        ->select('u.*, s.nombre as servicio_nombre')
        ->join('servicios s', 's.id = u.idservicio', 'left')
        ->get()->getResultArray();

    foreach ($usuarios as &$u) {
        $u['estado'] = ($u['estado'] === true || $u['estado'] === 't' || $u['estado'] == 1) ? 1 : 0;
    }

    return $this->response->setJSON($usuarios);
}
public function listarServicios()
    {
        $db        = \Config\Database::connect();
        $servicios = $db->table('servicios')
            ->where('activo', true)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($servicios);
    }

    public function registrar()
    {
        $model          = new UsuarioModel();
        $datos          = $this->request->getJSON(true);

        // Verificar duplicados
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

        // Si es cliente, crear empresa y responsable
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
}