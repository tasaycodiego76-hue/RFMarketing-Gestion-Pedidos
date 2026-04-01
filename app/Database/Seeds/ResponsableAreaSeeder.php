<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class ResponsableAreaSeeder extends Seeder {
    public function run() {
        $data = [
            ['idarea' => 1, 'idusuario' => 3,
             'fecha_inicio' => date('Y-m-d H:i:s'),
              'estado' => 'activo'],
            ['idarea' => 2,
             'idusuario' => 2,
             'fecha_inicio' => date('Y-m-d H:i:s'),
             'estado' => 'activo'],
        ];
        $this->db->table('responsables_area')->insertBatch($data);
    }
}