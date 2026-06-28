<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResponsablesEmpresaSeeder extends Seeder
{
    public function run()
    {

        $this->db->table('responsables_empresa')->truncate();

        $data = [
        ];

        /* $this->db->table('responsables_empresa')->insertBatch($data); */
    }
}