<?php

namespace App\Controllers\Administrador;

use CodeIgniter\Controller;

class UsuarioController extends Controller
{
    /**
     * Un método para listar los usuarios con su área y estado en formato JSON.
     */
    public function listar()
    {
        $db = \Config\Database::connect();
        
        
        $usuarios = $db->table('usuarios u')
            ->select('u.id, u.nombre, u.correo, u.rol, u.estado, a.nombre as area_nombre')
            ->join('areas a', 'a.id = u.idarea', 'left')
            ->get()->getResultArray();


        foreach ($usuarios as &$u) {
            $u['estado'] = ($u['estado'] === true || $u['estado'] === 't' || $u['estado'] == 1) ? 1 : 0;
        }

        return $this->response->setJSON($usuarios);
    }
}