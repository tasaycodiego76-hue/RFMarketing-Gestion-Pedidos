<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('TRUNCATE TABLE requerimiento RESTART IDENTITY CASCADE');

        $data = [
            [
                'idusuarioempresa' => 13, // ANA FLORES (cliente UAI, idarea=4)
                'idservicio' => 3,
                'titulo' => 'Campaña Intriga Verano',
                'objetivo_comunicacion' => 'Generar expectativa sobre la nueva colección de ropa de verano.',
                'descripcion' => 'Se requiere una serie de 5 posts para Instagram y Facebook con estilo minimalista.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Instagram", "Facebook"]',
                'publico_objetivo' => 'Jóvenes de 18 a 30 años interesados en moda.',
                'tiene_materiales' => true,
                'url_subida' => 'https://dropbox.com/s/materiales_verano',
                'formatos_solicitados' => '["Cuadrado (1:1)", "Vertical (9:16)"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-03-01 09:00:00',
                'fecharequerida' => '2026-03-10 00:00:00',
                'prioridad' => 'Baja',
                'servicio_personalizado' => null
            ],
            [
                'idusuarioempresa' => 16, // LUIS MENDOZA (cliente BYRON, idarea=52)
                'idservicio' => 2,
                'titulo' => 'Video Reel Bienvenida',
                'objetivo_comunicacion' => 'Dar la bienvenida a los nuevos estudiantes del ciclo 2026-I.',
                'descripcion' => 'Video dinámico de 30 segundos con tomas del campus y clips de eventos anteriores.',
                'tipo_requerimiento' => 'Creación de Videos',
                'canales_difusion' => '["TikTok", "Instagram Reels"]',
                'publico_objetivo' => 'Nuevos ingresantes y comunidad universitaria.',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Vertical (9:16)"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-03-15 11:00:00',
                'fecharequerida' => '2026-03-22 00:00:00',
                'prioridad' => 'Alta',
                'servicio_personalizado' => null
            ],
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'titulo' => 'Logo Aniversario UAI',
                'objetivo_comunicacion' => 'Identidad visual conmemorativa por los 25 años de la universidad.',
                'descripcion' => 'Rediseño temporal del logo institucional incorporando el número 25 y elementos festivos.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["Papelería", "Web", "Redes sociales"]',
                'publico_objetivo' => 'Toda la comunidad universitaria y público externo.',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'Vectorial (AI, PDF) y PNG transparete',
                'fechacreacion' => '2026-03-21 09:00:00',
                'fecharequerida' => '2026-03-28 00:00:00',
                'prioridad' => 'Media',
                'servicio_personalizado' => null
            ],
            [
                'idusuarioempresa' => 16,
                'idservicio' => 1,
                'titulo' => 'Infografía Académica',
                'objetivo_comunicacion' => 'Explicar el proceso de matrícula de forma visual y sencilla.',
                'descripcion' => 'Infografía paso a paso para ser distribuida por WhatsApp y correo electrónico.',
                'tipo_requerimiento' => 'Creación de Arte',
                'canales_difusion' => '["WhatsApp", "Correo electrónico"]',
                'publico_objetivo' => 'Estudiantes regulares.',
                'tiene_materiales' => true,
                'url_subida' => null,
                'formatos_solicitados' => '["Otros"]',
                'formato_otros' => 'JPEG 1200x1200px',
                'fechacreacion' => '2026-03-25 10:00:00',
                'fecharequerida' => '2026-03-30 00:00:00',
                'prioridad' => 'Media',
                'servicio_personalizado' => null
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
                'idusuarioempresa' => 16,
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
                'idusuarioempresa' => 16,
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
                'idusuarioempresa' => 16,
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
                'idusuarioempresa' => 16,
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
                'idusuarioempresa' => 16,
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
                'idusuarioempresa' => 16,
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