<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run()
    {
        // 1. Insertamos los Servicios
        $servicios = [
            ['nombre' => 'Diseño Gráfico', 'descripcion' => 'Diseño visual', 'activo' => true],
            ['nombre' => 'Edición y Video', 'descripcion' => 'Producción audiovisual', 'activo' => true],
        ];
        $this->db->table('servicios')->insertBatch($servicios);

        // 2. Obtenemos los IDs que Postgres acaba de generar
        $idDiseno = $this->db->table('servicios')->where('nombre', 'Diseño Gráfico')->get()->getRow()->id;
        $idVideo  = $this->db->table('servicios')->where('nombre', 'Edición y Video')->get()->getRow()->id;

        // 3. Insertamos las Áreas 
        $areas = [
            [
                'id'          => 1, 
                'idservicio'  => $idDiseno,
                'nombre'      => 'Área de Diseño',
                'descripcion' => 'Dpto de Diseño',
                'activo'      => true
            ],
            [
                'id'          => 2, 
                'idservicio'  => $idVideo,
                'nombre'      => 'Área de Video',
                'descripcion' => 'Dpto de Video',
                'activo'      => true
            ],
        ];
        $this->db->table('areas_agencia')->insertBatch($areas);
    }
}