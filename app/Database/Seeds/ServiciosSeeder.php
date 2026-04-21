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
            [
                'nombre' => 'Creacion de Contenido',
                'descripcion' => 'Servicio de redacción, planificación y gestión de contenido digital',
                'activo' => true,
            ],
            [
                'nombre' => 'Fotografia',
                'descripcion' => 'Servicio de captura y retoque de imagen profesional',
                'activo' => true,
            ],
        ];

        $this->db->table('servicios')->insertBatch($servicios);

    }
}