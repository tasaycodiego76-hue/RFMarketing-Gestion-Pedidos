<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;

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
}