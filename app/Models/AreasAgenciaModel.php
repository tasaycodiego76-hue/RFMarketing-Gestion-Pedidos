<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasAgenciaModel extends Model
{
    protected $table      = 'areas_agencia';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nombre', 'descripcion', 'activo'];

    /**
     * Devuelve todas las áreas activas.
     */
    public function listarActivas(): array
    {
        return $this->where('activo', true)->findAll();
    }
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