<?php namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder {
    public function run() {
        $data = [[
            'idempresa' => 1, 
            'idservicio' => 1,
            'titulo' => 'Logo Aniversario UAI',
            'servicio_personalizado' => null,
            'objetivo_comunicacion' => 'Atraer nuevos postulantes',
            'descripcion' => 'Logo dorado conmemorativo',
            'tipo_requerimiento' => 'Diseño', 
            'canales_difusion' => 'Web', 
            'publico_objetivo' => 'Juvenil',
            'tiene_materiales' => false,
            'formatos_solicitados' => 'PNG, JPG', 
            'fecharequerida' => '2026-04-15',
            'prioridad' => 'Media'
        ]];
        $this->db->table('requerimiento')->insertBatch($data);
    }
}