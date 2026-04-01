<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HistorialAsignacionesSeeder extends Seeder {
    public function run() {
        $data = [
            [
                'idpedido'            => 1, 
                'idempleado'          => 3, 
                'idempleado_anterior' => null, 
                'idadmin'             => 1, 
                'fecha_asignacion'    => date('Y-m-d H:i:s'),
                'motivo_cambio'       => 'Asignación inicial del proyecto'
            ]
        ];

        $this->db->table('historial_asignaciones')->insertBatch($data);
    }
}