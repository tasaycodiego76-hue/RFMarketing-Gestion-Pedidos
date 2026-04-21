<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE servicios RESTART IDENTITY CASCADE");

        $servicios = [
            [
                'nombre' => 'Diseño Gráfico',
                'descripcion' => 'Creación de artes, logos, flyers e identidad visual',
                'activo' => true,
            ],
            [
                'nombre' => 'Audiovisual',
                'descripcion' => 'Producción y edición de video, reels y animaciones',
                'activo' => true,
            ],
            [
                'nombre' => 'Gestión de Contenido',
                'descripcion' => 'Planificación, redacción y gestión de redes sociales',
                'activo' => true,
            ],
            [
                'nombre' => 'Fotografía',
                'descripcion' => 'Sesiones fotográficas, cobertura de eventos y retoque digital',
                'activo' => true,
            ],
        ];

        $this->db->table('servicios')->insertBatch($servicios);

    }
}