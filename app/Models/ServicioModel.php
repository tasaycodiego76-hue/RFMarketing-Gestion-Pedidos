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

    // Mapeo de servicios a áreas de agencia
    // AJUSTA ESTOS IDs según tu tabla areas_agencia
    private $mapeoServicioArea = [
        'Diseño Gráfico' => 1,  // ID del área Diseño
        'Audiovisual' => 2,     // ID del área Edición
    ];

    // Obtener el área de agencia según el ID del servicio
    public function getAreaAgenciaByServicio(int $idServicio): ?int
    {
        $servicio = $this->find($idServicio);

        if ($servicio && isset($this->mapeoServicioArea[$servicio['nombre']])) {
            return $this->mapeoServicioArea[$servicio['nombre']];
        }

        return null;
    }
}
