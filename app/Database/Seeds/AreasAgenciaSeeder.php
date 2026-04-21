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
                'nombre' => 'Diseño Gráfico',
                'descripcion' => 'Servicio de diseño y artes visuales',
                'activo' => true,
            ],
            [
                'nombre' => 'Edición y Video',
                'descripcion' => 'Servicio de producción y post-producción audiovisual',
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

        $this->db->table('areas_agencia')->insertBatch($data);

    }
}