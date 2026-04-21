<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE requerimiento RESTART IDENTITY CASCADE");
        $data = [
            [
                'idusuarioempresa' => 13,
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
                'fecharequerida' => '2026-03-20 00:00:00',
                'prioridad' => 'Media'
            ],
            [
                'idusuarioempresa' => 14,
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
                'fecharequerida' => '2026-03-30 00:00:00',
                'prioridad' => 'Media'
            ],
            [
                'idusuarioempresa' => 13,
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
                'fecharequerida' => '2026-04-05 00:00:00',
                'prioridad' => 'Alta'
            ],
            [
                'idusuarioempresa' => 13,
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
                'idusuarioempresa' => 14,
                'idservicio' => 4,
                'servicio_personalizado' => null,
                'titulo' => 'Sesión de Fotos Corporativas',
                'objetivo_comunicacion' => 'Renovar bio de directivos',
                'descripcion' => 'Sesión de fotos en interiores para 5 directivos.',
                'tipo_requerimiento' => 'Sesión Fotográfica',
                'canales_difusion' => '["Web Institucional", "LinkedIn"]',
                'publico_objetivo' => 'Inversionistas',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'Fotos en alta resolución',
                'fecharequerida' => '2026-05-10 00:00:00',
                'prioridad' => 'Media'
            ],
            [
                'idusuarioempresa' => 13,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Tutorial Campus',
                'objetivo_comunicacion' => 'Guiar a alumnos nuevos',
                'descripcion' => 'Video de 2 minutos explicando el uso de los laboratorios.',
                'tipo_requerimiento' => 'Creación de Vídeos',
                'canales_difusion' => '["Web", "YouTube"]',
                'publico_objetivo' => 'Nuevos ingresantes',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'Full HD 1920x1080',
                'fecharequerida' => '2026-05-15 00:00:00',
                'prioridad' => 'Media'
            ],
            [
                'idusuarioempresa' => 14,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Infografía Académica',
                'objetivo_comunicacion' => 'Explicar proceso de matrícula',
                'descripcion' => 'Diseño limpio y claro del paso a paso de matrícula.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales", "Email"]',
                'publico_objetivo' => 'Estudiantes',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'JPEG 1200x1200px',
                'fecharequerida' => '2026-04-30 00:00:00',
                'prioridad' => 'Media'
            ]
        ];

        $this->db->table('requerimiento')->insertBatch($data);
    }
}