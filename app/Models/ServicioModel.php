<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'activo'];

    /**
     * Funcion que devuelve todos los Servicios Activos 
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function getServiciosActivos()
    {
        return $this->where('activo', true)->findAll();
    }

    /**
     * Diccionario de Mapeo, el cual relaciona un servicio con su área de agencia. 
     * Si se crea un nuevo servicio lo Vincula con la nueva Area de la Agencia
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

        // Si no, asumir que es un servicio/área (Creadas al Mismo tiempo) e usar el mismo ID del servicio como ID de área
        return $idServicio;
    }
}
