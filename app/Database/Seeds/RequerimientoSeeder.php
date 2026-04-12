<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idusuarioempresa' => 8,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Reel Bienvenida',
                'objetivo_comunicacion' => 'Saludo institucional',
                'descripcion' => 'Video de 15 segundos para historias de bienvenida.',
                'tipo_requerimiento' => 'Creación de Vídeos (vídeos institucionales, reels, historias, etc)',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Comunidad universitaria',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Reel/Historia TikTok"]',
                'formato_otros' => '',
                'fecharequerida' => '2026-04-12 00:00:00',
                'prioridad' => 'Alta'
            ],
            [
                'idusuarioempresa' => 8,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Logo Aniversario UAI',
                'objetivo_comunicacion' => 'Atraer nuevos postulantes',
                'descripcion' => 'Logo dorado conmemorativo por los 10 años.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Página web", "Redes sociales"]',
                'publico_objetivo' => 'Juvenil',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Reels para Pauta (publicidad)"]',
                'formato_otros' => '',
                'fecharequerida' => '2026-04-15 00:00:00',
                'prioridad' => 'Media'
            ],
            [
                'idusuarioempresa' => 8,
                'idservicio' => null,
                'servicio_personalizado' => 'Campaña Intriga Verano',
                'titulo' => 'Lanzamiento Colección 2026',
                'objetivo_comunicacion' => 'Generar expectativa en redes',
                'descripcion' => 'Serie de 3 posts tipo puzzle para el feed.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Jóvenes 18-30 años',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'Puzzle de 3 piezas 1080x1080px',
                'fecharequerida' => '2026-04-25 00:00:00',
                'prioridad' => 'Alta'
            ],
            [
                'idusuarioempresa' => 9,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio de Ingeniería',
                'objetivo_comunicacion' => 'Señalética interna',
                'descripcion' => 'Diseño de banner para la puerta del laboratorio.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Banner físico"]',
                'publico_objetivo' => 'Estudiantes de Ingeniería',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'Lona de 2x1 metros',
                'fecharequerida' => '2026-04-20 00:00:00',
                'prioridad' => 'Media'
            ]
        ];

        $this->db->table('requerimiento')->insertBatch($data);
    }
}