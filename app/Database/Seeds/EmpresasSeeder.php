<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class EmpresasSeeder extends Seeder {
    public function run() {
        $data = [
            ['nombreempresa' => 'Universidad Autónoma de Ica',
             'ruc' => '20452777399',
             'correo' => 'uai@uai.edu.pe',
             'telefono' => '056123456'],
            ['nombreempresa' => 'Colegio Byron',
             'ruc' => '20452564727',
             'correo' => 'ada@byron.edu.pe',
             'telefono' => '056789012'],
        ];
        $this->db->table('empresas')->insertBatch($data);
    }
}