<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idempresa' => 1,
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
                'idempresa' => 1,
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
                'idempresa' => 2,
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
                'idempresa' => 1,
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
            ],
            [
                'idempresa' => 1,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio Sistemas',
                'objetivo_comunicacion' => 'Identificación de ambiente',
                'descripcion' => 'Banner de lona para la entrada del lab 302.',
                'tipo_requerimiento' => 'Adaptación de Arte (si ya se hizo antes en el 2025).',
                'canales_difusion' => '["Banner físico"]',
                'publico_objetivo' => 'Alumnos Ingeniería',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'PDF para impresión 1.5x1m',
                'fecharequerida' => '2026-04-20 00:00:00',
                'prioridad' => 'Alta'
            ],
            [
                'idempresa' => 1,
                'idservicio' => null,
                'servicio_personalizado' => 'Publicidad Online',
                'titulo' => 'Flyer Taller Autoestima',
                'objetivo_comunicacion' => 'Atraer alumnos de psicología',
                'descripcion' => 'Diseño para post de Instagram sobre salud mental.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Estudiantes',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Historia Facebook/Instagram"]',
                'formato_otros' => '',
                'fecharequerida' => '2026-04-15 00:00:00',
                'prioridad' => 'Media'
            ]
        ];

        $this->db->table('requerimiento')->insertBatch($data);
    }
}