<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasAgenciaModel extends Model
{
    protected $table = 'areas_agencia';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nombre', 'descripcion', 'activo'];

    /* ADMINISTRADOR */

    /**
     * Funcion que obtiene una lista de áreas que están marcadas como activas.
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarActivas(): array
    {
        return $this->where('activo', true)->findAll();
    }

    /**
     * Funcion que obtiene todas las áreas incluyendo el nombre del responsable asignado.
     * @return array
     */
    public function listarConResponsable(): array
    {
        $sql = "
            SELECT a.id, a.nombre, a.descripcion, a.activo,
                CONCAT(u.nombre, ' ', u.apellidos) AS responsable
            FROM areas_agencia AS a
            LEFT JOIN usuarios AS u 
                ON u.idarea_agencia = a.id 
                AND u.esresponsable = true
        ";

        return $this->db->query($sql)->getResultArray();
    }
}