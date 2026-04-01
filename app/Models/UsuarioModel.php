<?php

namespace App\Models;

use CodeIgniter\Model;



class UsuarioModel extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'usuarios';
    // Nombre de la columna clave primaria (ID único del usuario)
    protected $primaryKey = 'id';

    // Tipo de datos retornado: 'array' , 'object', o entidad personalizada
    protected $returnType = 'array';

    // Campos permitidos para operaciones de creación y actualización
    // Esto previene inyección de datos no autorizados
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
        'esresponsable',
        'estado',
        'fechacreacion'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'fechacreacion'; 
    protected $updatedField  = '';
}