<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasModel extends Model
{
    protected $table      = 'areas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['idempresa', 'nombre', 'descripcion', 'activo'];

    /**
     * Devuelve todas las áreas activas de una empresa.
     */
    public function listarActivasPorEmpresa(int $idEmpresa): array
    {
        return $this->where('idempresa', $idEmpresa)
                    ->where('activo', true)
                    ->findAll();
    }

    /**
     * Devuelve todas las áreas (activas e inactivas) de una empresa.
     */
    public function listarPorEmpresa(int $idEmpresa): array
    {
        return $this->where('idempresa', $idEmpresa)->findAll();
    }
}