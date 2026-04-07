<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id';

    // Definimos qué columnas se pueden devolver o manipular
    protected $allowedFields = ['nombre', 'descripcion', 'activo'];

    // Devolver solo los servicios activos
    public function getActivos()
    {
        return $this->where('activo', true)->findAll();
    }
}