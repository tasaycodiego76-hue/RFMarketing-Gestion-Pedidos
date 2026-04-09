<?php

namespace App\Models;

use CodeIgniter\Model;

class ArchivoModel extends Model
{
    protected $table = 'archivos';
    protected $primaryKey = 'id';   
    protected $returnType = 'array';
    protected $allowedFields = [
        'idatencion',
        'idrequerimiento',
        'nombre',
        'ruta',
        'tipo',
        'tamano'
    ];
}