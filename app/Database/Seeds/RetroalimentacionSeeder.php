<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class RetroalimentacionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idatencion' => 1,
                'idevaluador' => 4,
                'contenido' => 'Me gusta el avance, sigan así.',
                'fecha' => date('Y-m-d H:i:s')
            ]
        ];
        $this->db->table('retroalimentacion')->insertBatch($data);
    }
}