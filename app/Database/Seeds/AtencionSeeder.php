<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AtencionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idrequerimiento' => 1,
                'idadmin' => 1,
                'idempleado' => 3,
                'idservicio' => 1,
                'idarea_agencia' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Logo Aniversario UAI',
                'prioridad' => 'Media',
                'estado' => 'finalizado',
                'num_modificaciones' => 1,
                'observacion_revision' => 'Ajustar tono dorado',
                'url_entrega' => 'https://dropbox.com/s/logo_uai_final.png',
                'fechacreacion' => '2026-02-25 10:00:00',
                'fechainicio' => '2026-03-02 09:00:00',
                'fechafin' => '2026-03-13 18:00:00',
                'fechacompletado' => '2026-03-13 18:30:00'
            ],
            [
                'idrequerimiento' => 2,
                'idadmin' => 1,
                'idempleado' => 4,
                'idservicio' => 1,
                'idarea_agencia' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio de Ingeniería',
                'prioridad' => 'Media',
                'estado' => 'finalizado',
                'num_modificaciones' => 0,
                'observacion_revision' => null,
                'url_entrega' => 'https://dropbox.com/s/banner_ing_final.pdf',
                'fechacreacion' => '2026-03-04 15:20:00',
                'fechainicio' => '2026-03-09 14:00:00',
                'fechafin' => '2026-03-20 17:00:00',
                'fechacompletado' => '2026-03-20 17:15:00'
            ],
            [
                'idrequerimiento' => 3,
                'idadmin' => 1,
                'idempleado' => 6,
                'idservicio' => 2,
                'idarea_agencia' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Reel Bienvenida',
                'prioridad' => 'Alta',
                'estado' => 'en_revision',
                'num_modificaciones' => 2,
                'observacion_revision' => 'Corregir música de fondo',
                'url_entrega' => 'https://dropbox.com/s/video_bienvenida_v2.mp4',
                'fechacreacion' => '2026-03-10 09:00:00',
                'fechainicio' => '2026-03-16 08:30:00',
                'fechafin' => '2026-04-05 18:00:00',
                'fechacompletado' => null
            ],
            [
                'idrequerimiento' => 4,
                'idadmin' => 1,
                'idempleado' => 3,
                'idservicio' => null,
                'idarea_agencia' => 1,
                'servicio_personalizado' => 'Campaña Intriga Verano',
                'titulo' => 'Lanzamiento Colección 2026',
                'prioridad' => 'Alta',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'observacion_revision' => null,
                'url_entrega' => null,
                'fechacreacion' => '2026-04-08 10:00:00',
                'fechainicio' => '2026-04-10 09:00:00',
                'fechafin' => '2026-04-25 18:00:00',
                'fechacompletado' => null
            ]
        ];

        $this->db->table('atencion')->insertBatch($data);
    }
}