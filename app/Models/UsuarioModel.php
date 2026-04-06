<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombre',
        'apellidos',
        'correo',
        'telefono',
        'tipodoc',
        'numerodoc',
        'usuario',
        'clave',
        'rol',
        'idarea',
        'idarea_agencia',
        'esresponsable',
        'estado',
        'fechacreacion'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'fechacreacion'; 
    protected $updatedField  = '';
}