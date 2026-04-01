<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class TrackingSeeder extends Seeder {
    public function run() {
        $data = [[
            'idatencion' => 1,
            'idusuario' => 3,
            'accion' => 'El diseñador aceptó el pedido.',
            'estado' => 'en_proceso'
        ]];
        $this->db->table('tracking')->insertBatch($data);
    }
}