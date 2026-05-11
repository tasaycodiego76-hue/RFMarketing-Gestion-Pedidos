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
                'idusuarioempresa' => 14, // LUIS MENDOZA (cliente BYRON, idarea=52)
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
                'idusuarioempresa' => 14,
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
            // --- PEDIDOS PARA PRUEBA DE SLA (11/05/2026) ---
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'titulo' => 'TEST: PEDIDO ATRASADO',
                'objetivo_comunicacion' => 'Prueba Rojo',
                'descripcion' => 'Este pedido debe verse ROJO porque venció ayer.',
                'tipo_requerimiento' => 'Prueba',
                'canales_difusion' => '["Web"]',
                'publico_objetivo' => 'Interno',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Digital"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-01 10:00:00',
                'fecharequerida' => '2026-05-10 23:59:59', // Ayer
                'prioridad' => 'Alta',
                'servicio_personalizado' => null
            ],
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'titulo' => 'TEST: VENCE HOY',
                'objetivo_comunicacion' => 'Prueba Naranja',
                'descripcion' => 'Este pedido debe verse NARANJA porque vence hoy.',
                'tipo_requerimiento' => 'Prueba',
                'canales_difusion' => '["Web"]',
                'publico_objetivo' => 'Interno',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Digital"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-11 08:00:00',
                'fecharequerida' => '2026-05-11 23:59:59', // Hoy
                'prioridad' => 'Media',
                'servicio_personalizado' => null
            ],
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'titulo' => 'TEST: VENCE MAÑANA',
                'objetivo_comunicacion' => 'Prueba Amarillo',
                'descripcion' => 'Este pedido debe verse AMARILLO porque vence mañana.',
                'tipo_requerimiento' => 'Prueba',
                'canales_difusion' => '["Web"]',
                'publico_objetivo' => 'Interno',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Digital"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-11 08:00:00',
                'fecharequerida' => '2026-05-12 23:59:59', // Mañana
                'prioridad' => 'Media',
                'servicio_personalizado' => null
            ],
            [
                'idusuarioempresa' => 13,
                'idservicio' => 1,
                'titulo' => 'TEST: EN TIEMPO',
                'objetivo_comunicacion' => 'Prueba Verde',
                'descripcion' => 'Este pedido debe verse VERDE porque falta tiempo.',
                'tipo_requerimiento' => 'Prueba',
                'canales_difusion' => '["Web"]',
                'publico_objetivo' => 'Interno',
                'tiene_materiales' => false,
                'url_subida' => null,
                'formatos_solicitados' => '["Digital"]',
                'formato_otros' => '',
                'fechacreacion' => '2026-05-11 08:00:00',
                'fecharequerida' => '2026-05-15 23:59:59', // A tiempo
                'prioridad' => 'Baja',
                'servicio_personalizado' => null
            ]
        ];

        $this->db->table('requerimiento')->insertBatch($data);
    }
}