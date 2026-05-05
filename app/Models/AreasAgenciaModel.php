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
    /**
     * Cambia el estado de un área y actualiza recursivamente a sus empleados y servicios vinculados.
     * @param int $idArea
     * @param bool $nuevoEstado
     * @return bool
     */
    public function cambiarEstado(int $idArea, bool $nuevoEstado): bool
    {
        $this->db->transStart();

        // 1. Actualizar estado del área
        $this->update($idArea, ['activo' => $nuevoEstado]);

        // 2. Actualizar estado de los usuarios vinculados a esta área
        $this->db->table('usuarios')
            ->where('idarea_agencia', $idArea)
            ->update(['estado' => $nuevoEstado]);

        // 3. Actualizar estado del servicio vinculado (basado en el nombre del área)
        $area = $this->find($idArea);
        if ($area) {
            $this->db->table('servicios')
                ->where('LOWER(nombre)', mb_strtolower($area['nombre']))
                ->update(['activo' => $nuevoEstado]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}