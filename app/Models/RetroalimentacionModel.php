<?php

namespace App\Models;

use CodeIgniter\Model;

class RetroalimentacionModel extends Model
{
    protected $table            = 'retroalimentacion';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['idatencion', 'idevaluador', 'contenido', 'fecha'];

    // Dates
    protected $useTimestamps = false; 

    /* EMPLEADO */

    /**
     * Obtiene los comentarios de retroalimentación dirigidos a un empleado específico
     * @param mixed $idEmpleado
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function getRetroalimentacionPorEmpleado($idEmpleado)
    {
        $db = \Config\Database::connect();

        // Subconsulta: obtener el ID más reciente de retroalimentación por cada atención
        $subquery = $db->table('retroalimentacion r_sub')
            ->select('MAX(r_sub.id) as max_id')
            ->join('atencion a_sub', 'a_sub.id = r_sub.idatencion')
            ->where('a_sub.idempleado', $idEmpleado)
            ->where('a_sub.estado', 'en_proceso')
            ->groupBy('r_sub.idatencion')
            ->getCompiledSelect();

        return $this->select('retroalimentacion.*, atencion.id as id_atencion, requerimiento.titulo as pedido_titulo,
                             usuarios.nombre as evaluador_nombre, usuarios.apellidos as evaluador_apellidos,
                             COALESCE(servicios.nombre, atencion.servicio_personalizado) as servicio_nombre,
                             empresas.nombreempresa as empresa_nombre')
            ->join('atencion', 'atencion.id = retroalimentacion.idatencion')
            ->join('requerimiento', 'requerimiento.id = atencion.idrequerimiento')
            ->join('usuarios', 'usuarios.id = retroalimentacion.idevaluador')
            ->join('servicios', 'servicios.id = atencion.idservicio', 'left')
            ->join('usuarios as u_sol', 'u_sol.id = requerimiento.idusuarioempresa', 'left')
            ->join('areas', 'areas.id = u_sol.idarea', 'left')
            ->join('empresas', 'empresas.id = areas.idempresa', 'left')
            ->where("retroalimentacion.id IN ($subquery)")
            ->orderBy('retroalimentacion.fecha', 'DESC')
            ->findAll();
    }
}