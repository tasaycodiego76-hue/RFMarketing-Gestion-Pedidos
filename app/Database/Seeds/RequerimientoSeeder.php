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
            ],

            // REQUERIMIENTOS DE PRUEBA

            // Requerimiento 1
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Web Promoción Mayo',
                'objetivo_comunicacion' => 'Promocionar descuentos de temporada',
                'descripcion' => 'Banner animado para slider principal del sitio web con descuentos del 40%.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Página web"]',
                'publico_objetivo' => 'Clientes actuales y potenciales',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'Banner 1920x600px PNG',
                'fechacreacion' => '2026-05-05 08:00:00',
                'fecharequerida' => '2026-05-05 18:00:00',
                'prioridad' => 'Alta'
            ],

            // Requerimiento 2
            [
                'idusuarioempresa' => 14,
                'idservicio' => 3,
                'servicio_personalizado' => null,
                'titulo' => 'Post Anuncio Becas 2026',
                'objetivo_comunicacion' => 'Informar sobre programa de becas',
                'descripcion' => 'Post simple con información de becas completas y medias becas para el ciclo 2026-II.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Postulantes de bajos recursos',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Post Instagram", "Post Facebook"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-05 09:30:00',
                'fecharequerida' => '2026-05-05 17:00:00',
                'prioridad' => 'Media'
            ],

            // Requerimiento 3
            [
                'idusuarioempresa' => 13,
                'idservicio' => 4,
                'servicio_personalizado' => null,
                'titulo' => 'Sesión Fotográfica Producto Nuevo',
                'objetivo_comunicacion' => 'Mostrar nueva línea de productos',
                'descripcion' => 'Sesión de fotos de producto sobre fondo blanco. 15 productos, 3 ángulos cada uno.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Página web", "Redes sociales"]',
                'publico_objetivo' => 'Compradores online',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'JPG 2000x2000px fondo blanco',
                'fechacreacion' => '2026-05-04 10:00:00',
                'fecharequerida' => '2026-05-04 19:00:00',
                'prioridad' => 'Alta'
            ],

            // ============ MARTES 6 DE MAYO ============
            // Requerimiento 4
            [
                'idusuarioempresa' => 14,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Testimonial Estudiante Destacado',
                'objetivo_comunicacion' => 'Generar confianza y credibilidad institucional',
                'descripcion' => 'Edición de entrevista a estudiante destacado para campaña de admisión. Duración 45 segundos.',
                'tipo_requerimiento' => 'Creación de Vídeos (vídeos institucionales, reels, historias, etc)',
                'canales_difusion' => '["Redes sociales", "Página web"]',
                'publico_objetivo' => 'Postulantes 2026',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Reel/Historia Instagram"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-05 08:00:00',
                'fecharequerida' => '2026-05-05 17:00:00',
                'prioridad' => 'Media'
            ],

            // Requerimiento 5
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Rediseño Logo Corporativo',
                'objetivo_comunicacion' => 'Modernizar identidad de marca',
                'descripcion' => 'Actualización de logo manteniendo colores institucionales. 3 propuestas diferentes.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Página web", "Redes sociales", "Impresos"]',
                'publico_objetivo' => 'Público general',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'AI, PNG, SVG',
                'fechacreacion' => '2026-05-05 09:00:00',
                'fecharequerida' => '2026-05-05 18:00:00',
                'prioridad' => 'Alta'
            ],

            // ============ MIÉRCOLES 7 DE MAYO ============
            // Requerimiento 6
            [
                'idusuarioempresa' => 13,
                'idservicio' => 3,
                'servicio_personalizado' => null,
                'titulo' => 'Carrusel Tips de Productividad',
                'objetivo_comunicacion' => 'Educar y fidelizar a la audiencia',
                'descripcion' => 'Carrusel de 5 slides con tips de productividad para estudiantes universitarios. Diseño minimalista.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Estudiantes universitarios 18-25 años',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Post Instagram"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-06 08:30:00',
                'fecharequerida' => '2026-05-06 19:00:00',
                'prioridad' => 'Baja'
            ],

            // Requerimiento 7
            [
                'idusuarioempresa' => 14,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Reel Recorrido Virtual Campus',
                'objetivo_comunicacion' => 'Mostrar instalaciones a distancia',
                'descripcion' => 'Video dinámico de 30 segundos mostrando laboratorios, biblioteca y áreas recreativas.',
                'tipo_requerimiento' => 'Creación de Vídeos (vídeos institucionales, reels, historias, etc)',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Postulantes de provincia',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Reel/Historia Instagram", "Reel/Historia TikTok"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-06 09:00:00',
                'fecharequerida' => '2026-05-06 18:00:00',
                'prioridad' => 'Alta'
            ],

            // Requerimiento 8
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Flyer Digital Evento Mayo',
                'objetivo_comunicacion' => 'Convocar a evento presencial',
                'descripcion' => 'Flyer para difusión de evento empresarial. Incluir fecha, hora, lugar y código QR.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales", "Email"]',
                'publico_objetivo' => 'Empresarios locales',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Historia Instagram"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-06 11:00:00',
                'fecharequerida' => '2026-05-06 17:00:00',
                'prioridad' => 'Media'
            ],

            // Requerimiento 9
            [
                'idusuarioempresa' => 14,
                'idservicio' => null,
                'servicio_personalizado' => 'Calendario de contenidos semanal',
                'titulo' => 'Planificación Contenido Semana 19',
                'objetivo_comunicacion' => 'Mantener presencia activa en RRSS',
                'descripcion' => 'Planificación de 7 posts para la semana con copy, hashtags y horarios sugeridos.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Seguidores actuales',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Post Instagram", "Post Facebook"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-06 14:00:00',
                'fecharequerida' => '2026-05-06 20:00:00',
                'prioridad' => 'Baja'
            ],

            // ============ JUEVES 8 DE MAYO ============
            // Requerimiento 10
            [
                'idusuarioempresa' => 14,
                'idservicio' => 4,
                'servicio_personalizado' => null,
                'titulo' => 'Cobertura Feria de Ciencias',
                'objetivo_comunicacion' => 'Documentar evento académico',
                'descripcion' => 'Cobertura fotográfica de feria de ciencias. Entrega de 30 fotos editadas en alta resolución.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales", "Página web"]',
                'publico_objetivo' => 'Comunidad académica',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'JPG alta resolución',
                'fechacreacion' => '2026-05-07 07:00:00',
                'fecharequerida' => '2026-05-07 20:00:00',
                'prioridad' => 'Alta'
            ],

            // Requerimiento 11
            [
                'idusuarioempresa' => 13,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Promocional Flash Sale',
                'objetivo_comunicacion' => 'Generar urgencia de compra',
                'descripcion' => 'Video corto 15 segundos anunciando venta flash de 24 horas. Estilo dinámico.',
                'tipo_requerimiento' => 'Creación de Vídeos (vídeos institucionales, reels, historias, etc)',
                'canales_difusion' => '["Redes sociales"]',
                'publico_objetivo' => 'Clientes activos',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Historia Instagram", "Historia Facebook"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-07 09:00:00',
                'fecharequerida' => '2026-05-07 16:00:00',
                'prioridad' => 'Alta'
            ],

            // Requerimiento 12
            [
                'idusuarioempresa' => 14,
                'idservicio' => null,
                'servicio_personalizado' => 'Campaña completa admisión',
                'titulo' => 'Campaña Admisión 2026-II Completa',
                'objetivo_comunicacion' => 'Incrementar postulaciones en 30%',
                'descripcion' => 'Estrategia integral: 10 posts, 5 historias, 3 reels y 2 videos para YouTube.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales", "Página web", "YouTube"]',
                'publico_objetivo' => 'Jóvenes 16-20 años',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Post Instagram", "Reel/Historia Instagram", "Video YouTube"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-07 08:00:00',
                'fecharequerida' => '2026-05-07 19:00:00',
                'prioridad' => 'Alta'
            ],

            // Requerimiento 13
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Infografía Estadísticas Mensuales',
                'objetivo_comunicacion' => 'Transparentar resultados',
                'descripcion' => 'Infografía con datos de ventas, alcance y engagement del mes abril.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Redes sociales", "Email"]',
                'publico_objetivo' => 'Stakeholders y clientes VIP',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Post Instagram"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-07 10:30:00',
                'fecharequerida' => '2026-05-07 18:00:00',
                'prioridad' => 'Media'
            ]
        ];

        $this->db->table('requerimiento')->insertBatch($data);
    }
}