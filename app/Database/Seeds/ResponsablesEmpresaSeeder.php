<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResponsablesEmpresaSeeder extends Seeder
{
    public function run()
    {
        $ana  = $this->db->table('usuarios')->where('correo', '62345678@uai.edu.pe')->get()->getRow();
        $luis = $this->db->table('usuarios')->where('correo', '62345678@byron.edu.pe')->get()->getRow();

        $uai   = $this->db->table('empresas')->where('nombreempresa', 'Universidad Autónoma de Ica')->get()->getRow();
        $byron = $this->db->table('empresas')->where('nombreempresa', 'Colegio Byron')->get()->getRow();

        $data = [];

        if ($ana && $uai) {
            $data[] = [
                'idusuario'    => $ana->id,
                'idempresa'    => $uai->id,
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ];
        }

        if ($luis && $byron) {
            $data[] = [
                'idusuario'    => $luis->id,
                'idempresa'    => $byron->id,
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ];
        }

        if (!empty($data)) {
            $this->db->table('responsables_empresa')->insertBatch($data);
        }
    }
}