<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder {
    public function run() {
        $data = [
            ['nombre' => 'Diseño Gráfico',
             'descripcion' => 'Logos, flyers y manuales',
              'activo' => true],
            ['nombre' => 'Social Media',
             'descripcion' => 'Post de Facebook e Instagram',
              'activo' => true],
        ];
        $this->db->table('servicios')->insertBatch($data);
    }
}