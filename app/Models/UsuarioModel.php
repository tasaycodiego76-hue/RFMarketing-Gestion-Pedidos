<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombre', 'apellidos', 'correo', 'telefono',
        'tipodoc', 'numerodoc', 'usuario', 'clave',
        'rol', 'idarea', 'idarea_agencia',
        'esresponsable', 'estado', 'fechacreacion'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'fechacreacion';
    protected $updatedField  = '';

    /**
     * Devuelve todos los usuarios con el nombre de su área resuelta
     * (area_agencia tiene prioridad sobre area).
     */
    public function listarConArea(): array
    {
        return $this->db->table('usuarios u')
            ->select('u.*')
            ->select("COALESCE(aa.nombre, ae.nombre, '-') as area_nombre")
            ->join('areas_agencia aa', 'aa.id = u.idarea_agencia', 'left')
            ->join('areas ae',         'ae.id = u.idarea',         'left')
            ->orderBy('u.rol',    'ASC')
            ->orderBy('u.nombre', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Devuelve un usuario por ID junto con el nombre de su área de agencia.
     */
    public function obtenerConArea(int $id): ?array
    {
        return $this->db->table('usuarios u')
            ->select('u.*, a.nombre as area_nombre')
            ->join('areas_agencia a', 'a.id = u.idarea_agencia', 'left')
            ->where('u.id', $id)
            ->get()->getRowArray() ?: null;
    }
}