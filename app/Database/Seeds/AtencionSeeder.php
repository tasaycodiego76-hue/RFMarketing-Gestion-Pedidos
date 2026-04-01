<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class AtencionSeeder extends Seeder {
    public function run() {
        $data = [[
            'idformpedido' => 1,
            'idadmin' => 1,
            'idempleado' => 3,
            'idservicio' => 1,
            'titulo' => 'Logo Aniversario UAI',
            'prioridad' => 'Media',
            'estado' => 'en_proceso',
            'num_modificaciones' => 0,
            'respuestatexto' => 'Se inicia el diseño base.',
            'fechainicio' => date('Y-m-d H:i:s'),
            'fechafin' => '2026-04-10'
        ]];
        $this->db->table('atencion')->insertBatch($data);
    }
}