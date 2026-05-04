<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasModel extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['idempresa', 'nombre', 'descripcion', 'activo'];

    /* CLIENTE | RESPONSABLE_AREA */

    /**
     * Obtiene las áreas activas vinculadas a una empresa específica
     * @param int $idEmpresa
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarActivasPorEmpresa(int $idEmpresa): array
    {
        return $this->where('idempresa', $idEmpresa)
            ->where('activo', true)
            ->findAll();
    }

    /* ADMINISTRADOR */

    /**
     * Funcion que obtiene todas las áreas de una empresa, sin importar su estado
     * @param int $idEmpresa
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarPorEmpresa(int $idEmpresa): array
    {
        return $this->where('idempresa', $idEmpresa)->findAll();
    }

    /**
     * Funcion que obtiene todas las áreas marcadas como activas de todo el sistema
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function obtenerActivas(): array
    {
        return $this->where('activo', true)->findAll();
    }
}