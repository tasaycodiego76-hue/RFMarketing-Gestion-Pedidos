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
    protected $useTimestamps = false; // The migration uses a default CURRENT_TIMESTAMP for 'fecha'

    /**
     * Obtiene la retroalimentación para un empleado específico.
     * Se une con 'atencion' y 'requerimientos' para filtrar por el empleado asignado.
     */
    public function getRetroalimentacionPorEmpleado($idEmpleado)
    {
        return $this->select('retroalimentacion.*, atencion.id as id_atencion, requerimiento.titulo as pedido_titulo, usuarios.nombre as evaluador_nombre, usuarios.apellidos as evaluador_apellidos')
            ->join('atencion', 'atencion.id = retroalimentacion.idatencion')
            ->join('requerimiento', 'requerimiento.id = atencion.idrequerimiento')
            ->join('usuarios', 'usuarios.id = retroalimentacion.idevaluador')
            ->where('atencion.idempleado', $idEmpleado)
            ->where('atencion.estado', 'en_proceso') // Solo mostrar si sigue en proceso
            ->orderBy('retroalimentacion.fecha', 'DESC')
            ->findAll();
    }
}
