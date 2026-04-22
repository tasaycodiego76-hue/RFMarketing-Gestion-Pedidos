<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE requerimiento RESTART IDENTITY CASCADE");
        $data = [
            // Requerimiento 1: Más antiguo - Campaña Verano (Estado: finalizado)
            [
                'idusuarioempresa' => 13,
                'idservicio' => 3,
                'servicio_personalizado' => null,
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
                'fechacreacion' => '2026-03-01 09:00:00',
                'fecharequerida' => '2026-03-10 00:00:00',
                'prioridad' => 'Baja'
            ],
            // Requerimiento 2: Intermedio - Video Reel Bienvenida (Estado: en_revision)
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
                'fechacreacion' => '2026-03-15 11:00:00',
                'fecharequerida' => '2026-03-17 00:00:00',
                'prioridad' => 'Alta'
            ],
            // Requerimiento 3: Más nuevo - Logo Aniversario UAI (Estado: en_proceso)
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
                'fechacreacion' => '2026-03-21 09:00:00',
                'fecharequerida' => '2026-03-22 00:00:00',
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
                'fechacreacion' => '2026-03-25 10:00:00',
                'fecharequerida' => '2026-03-30 00:00:00',
                'prioridad' => 'Media'
            ]
        ];

        $this->db->table('requerimiento')->insertBatch($data);
    }
}