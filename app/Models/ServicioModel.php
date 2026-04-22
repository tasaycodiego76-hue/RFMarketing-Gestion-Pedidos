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

    // Obtener servicios según el área de agencia del usuario
    public function getServiciosPorAreaAgencia(int $idAreaAgencia)
    {
        return $this->where('activo', true)->findAll();
    }

    // Obtener el área de agencia según el ID del servicio (para compatibilidad)
    public function getAreaAgenciaByServicio(int $idServicio): ?int
    {
        // Mapeo de servicios a áreas de agencia
        $mapeoServicioArea = [
            1 => 1, // Diseño Gráfico -> Área de Diseño
            2 => 2, // Audiovisual -> Área de Audiovisual
            3 => 3, // Creación de Contenido -> Área de Creación de Contenido
            4 => 4, // Fotografía -> Área de Fotografía
        ];
        
        // Si existe el mapeo, usarlo
        if (isset($mapeoServicioArea[$idServicio])) {
            return $mapeoServicioArea[$idServicio];
        }

        // Si no existe en el mapeo, asumir que es un servicio/área personalizado
        // y usar el mismo ID del servicio como ID de área
        return $idServicio;
    }

    // Obtener el área de agencia según el servicio y la agencia del usuario
    public function getAreaAgenciaPorServicioYAgencia(int $idServicio, int $idAreaAgencia): ?int
    {
        return $idAreaAgencia;
    }
}
