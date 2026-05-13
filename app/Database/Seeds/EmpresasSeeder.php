<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class EmpresasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombreempresa' => 'UNIVERSIDAD AUTÓNOMA DE ICA',
                'ruc' => '20452777399',
                'correo' => 'admisionpregrado@autonomadeica.edu.pe',
                'telefono' => '017071251'
            ],
            [
                'nombreempresa' => 'COLEGIO ADA BYRON',
                'ruc' => '20452564727',
                'correo' => 'pagos@colegiobyron.edu.pe',
                'telefono' => '981023955'
            ],
            [
                'nombreempresa' => 'URBANO & MODERNO S.A.C.',
                'ruc' => '20607952648',
                'correo' => 'urbanoymoderno@outlook.com',
                'telefono' => '976684184'
            ]
        ];
        $this->db->table('empresas')->insertBatch($data);
    }
}