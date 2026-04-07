<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run()
    {
        $servicios = [
            [
                'nombre' => 'Diseño Gráfico',
                'descripcion' => 'Logos y flyers',
                'activo' => true,
            ],
            [
                'nombre' => 'Audiovisual',
                'descripcion' => 'Producción audiovisual y cortes',
                'activo' => true,
            ],
        ];

        $this->db->table('servicios')->insertBatch($servicios);

    }
}