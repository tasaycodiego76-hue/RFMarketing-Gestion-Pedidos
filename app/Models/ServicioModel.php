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

     * Funcion que devuelve todos los Servicios Activos 
     * Solo devuelve servicios si el área asociada tiene un responsable asignado.
     * @return array
     */
    public function getServiciosActivos()
    {
        $db = \Config\Database::connect();
        
        // Obtenemos todos los servicios activos base
        $servicios = $this->where('activo', true)->findAll();
        $filtrados = [];

        foreach ($servicios as $s) {
            $idArea = $this->getAreaAgenciaByServicio((int)$s['id']);
            $nombreServicio = $s['nombre'];
            
            // Verificar si el área (por ID o por nombre coincidente) tiene al menos un responsable activo
            $tieneResponsable = $db->table('usuarios u')
                ->join('areas_agencia aa', 'aa.id = u.idarea_agencia')
                ->where('u.esresponsable', true)
                ->where('u.estado', true)
                ->groupStart()
                    ->where('aa.id', $idArea)
                    ->orWhere('LOWER(aa.nombre)', mb_strtolower($nombreServicio))
                ->groupEnd()
                ->countAllResults() > 0;

            if ($tieneResponsable) {
                $filtrados[] = $s;
            }
        }

        return $filtrados;
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