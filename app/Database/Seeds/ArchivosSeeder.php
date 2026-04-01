<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class ArchivosSeeder extends Seeder {
    public function run() {
        $data = [[
            'idpedido' => 1,
            'idformulario' => 1,
            'nombre' => 'brief_uai.pdf',
            'ruta' => 'uploads/docs/brief_uai.pdf',
            'tipo' => 'application/pdf',
            'tamano' => 2048
        ]];
        $this->db->table('archivos')->insertBatch($data);
    }
}