<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResponsablesEmpresaSeeder extends Seeder
{
    public function run()
    {
        $userAna  = $this->db->table('usuarios')->where('correo', '62345678@uai.edu.pe')->get()->getRow();
        $userLuis = $this->db->table('usuarios')->where('correo', '62345678@byron.edu.pe')->get()->getRow();

        $empresaUAI   = $this->db->table('empresas')->where('nombreempresa', 'Universidad Autónoma de Ica')->get()->getRow();
        $empresaByron = $this->db->table('empresas')->where('nombreempresa', 'Colegio Byron')->get()->getRow();

        $data = [];

        if ($userAna && $empresaUAI) {
            $data[] = [
                'idusuario'    => $userAna->id,
                'idempresa'    => $empresaUAI->id,
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ];
        }

        if ($userLuis && $empresaByron) {
            $data[] = [
                'idusuario'    => $userLuis->id,
                'idempresa'    => $empresaByron->id,
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ];
        }

        if (!empty($data)) {
            $this->db->table('responsables_empresa')->insertBatch($data);
        }
    }
}