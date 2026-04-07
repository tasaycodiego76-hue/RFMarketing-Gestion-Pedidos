<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreasAgenciaSeeder extends Seeder
{
    public function run()
    {
        
        $this->db->query("TRUNCATE TABLE areas_agencia RESTART IDENTITY CASCADE");

        $data = [
            [
                'id'          => 1,
                'idservicio'  => 1, 
                'nombre'      => 'DISEÑO GRÁFICO',
                'activo'      => true,
            ],
            [
                'id'          => 2,
                'idservicio'  => 2, 
                'nombre'      => 'EDICIÓN Y VIDEO',
                'activo'      => true,
            ],
        ];

        $this->db->table('areas_agencia')->insertBatch($data);
        
    }
}