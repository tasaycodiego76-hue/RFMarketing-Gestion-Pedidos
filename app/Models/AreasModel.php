<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasModel extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['idempresa', 'nombre', 'descripcion', 'activo'];

    /**
     * Funcion que trae todas las areas activas de una empresa
     * @param int $idEmpresa
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarActivasPorEmpresa(int $idEmpresa): array
    {
        return $this->where('idempresa', $idEmpresa)
            ->where('activo', true)
            ->findAll();
    }

    /**
     * Funcion que devuelve todas las áreas (activas e inactivas) de una empresa.
     * @param int $idEmpresa
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarPorEmpresa(int $idEmpresa): array
    {
        return $this->where('idempresa', $idEmpresa)->findAll();
    }

    /**
     * Funcion que trae todas las areas activas sin importar la empresa
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function obtenerActivas(): array
    {
        return $this->where('activo', true)->findAll();
    }
}