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
        // 1. Mapeo explícito para servicios core (Retrocompatibilidad)
        $mapeoServicioArea = [
            1 => 1, // Diseño Gráfico            -> Área de Diseño
            2 => 2, // Audiovisual               -> Área de Audiovisual
            3 => 3, // Creación de Contenido     -> Área de Creación de Contenido
            4 => 4, // Fotografía                -> Área de Fotografía
        ];

        if (isset($mapeoServicioArea[$idServicio])) {
            return $mapeoServicioArea[$idServicio];
        }

        // 2. Intento de emparejamiento dinámico por nombre
        // Buscamos el nombre del servicio actual
        $servicio = $this->find($idServicio);
        if ($servicio) {
            $nombreServicio = mb_strtolower(trim($servicio['nombre']));
            
            // Buscar un área que coincida exactamente con el nombre del servicio
            $db = \Config\Database::connect();
            $area = $db->table('areas_agencia')
                ->where('LOWER(nombre)', $nombreServicio)
                ->get()->getRow();
            
            if ($area) {
                return (int) $area->id;
            }

            // Búsqueda por aproximación: ¿El nombre del servicio contiene el nombre de algún área?
            // Ejemplo: "Diseño Gráfico" contiene "Diseño"
            $todasAreas = $db->table('areas_agencia')->where('activo', true)->get()->getResultArray();
            foreach ($todasAreas as $a) {
                $nombreArea = mb_strtolower(trim($a['nombre']));
                if (strpos($nombreServicio, $nombreArea) !== false) {
                    return (int) $a['id'];
                }
            }
        }

        // 3. Si no hay coincidencia, retornar el ID del servicio como fallback (comportamiento original)
        return $idServicio;
    }
}