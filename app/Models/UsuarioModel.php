<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Campos permitidos para INSERT y UPDATE
    protected $allowedFields    = [
        'nombre', 'correo', 'clave', 'rol', 
        'idarea', 'estado', 'telefono', 'numerodoc'
    ];

    // Fechas automáticas
    protected $useTimestamps    = true;
    protected $createdField     = 'fecha_registro';
    protected $updatedField     = ''; // Opcional
}