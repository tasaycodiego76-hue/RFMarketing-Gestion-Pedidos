<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResponsablesEmpresaSeeder extends Seeder
{
    public function run()
    {
    
        $this->db->table('responsables_empresa')->truncate();

        $data = [
            [
                'idusuario'    => 3, 
                'idempresa'    => 1, 
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ],
            [
                'idusuario'    => 4, 
                'idempresa'    => 2, 
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ],
        ];

        $this->db->table('responsables_empresa')->insertBatch($data);
    }
}