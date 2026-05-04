<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'activo'];

    /* CLIENTE */

    /**
     * Funcion que obtiene la lista de servicios disponibles para los clientes
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function getServiciosActivos()
    {
        return $this->where('activo', true)->findAll();
    }

    /**
     * Funcion que determina qué área de la agencia debe atender un servicio específico
     * @param int $idServicio
     * @return int
     */
    public function getAreaAgenciaByServicio(int $idServicio): ?int
    {
        $mapeoServicioArea = [
            1 => 1, // Diseño Gráfico            -> Área de Diseño
            2 => 2, // Audiovisual               -> Área de Audiovisual
            3 => 3, // Creación de Contenido     -> Área de Creación de Contenido
            4 => 4, // Fotografía                -> Área de Fotografía
            5 => 1, // Diseño de Pagina Web      -> Área de Diseño
        ];
        // Si existe el mapeo, usarlo
        if (isset($mapeoServicioArea[$idServicio])) {
            return $mapeoServicioArea[$idServicio];
        }

        // Si no está mapeado, se asume correspondencia directa por ID
        return $idServicio;
    }
}