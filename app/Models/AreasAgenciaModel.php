<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasAgenciaModel extends Model
{
    protected $table      = 'areas_agencia';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nombre', 'activo'];

    /**
     * Devuelve todas las áreas activas.
     */
    public function listarActivas(): array
    {
        return $this->where('activo', true)->findAll();
    }
}