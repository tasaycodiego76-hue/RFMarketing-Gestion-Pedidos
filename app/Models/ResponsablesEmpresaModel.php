<?php

namespace App\Models;

use CodeIgniter\Model;

class ResponsablesEmpresaModel extends Model
{
    protected $table      = 'responsables_empresa';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['idusuario', 'idempresa', 'fecha_inicio', 'estado'];

    /**
     * Asigna un usuario como responsable de una empresa.
     */
    public function asignarResponsable(int $idUsuario, int $idEmpresa): void
    {
        $this->insert([
            'idusuario'    => $idUsuario,
            'idempresa'    => $idEmpresa,
            'fecha_inicio' => date('Y-m-d H:i:s'),
            'estado'       => 'activo',
        ]);
    }
}