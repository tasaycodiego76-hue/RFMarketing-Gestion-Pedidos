<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class AtencionSeeder extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE atencion RESTART IDENTITY CASCADE");
        $data = [
            // --- AREA 1: DISEÑO GRÁFICO ---
            [
                'idrequerimiento' => 1,
                'idadmin' => 1,
                'idempleado' => 10,
                'idservicio' => 1,
                'idarea_agencia' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Logo Aniversario UAI',
                'prioridad' => 'Media',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-10'
            ],
            [
                'idrequerimiento' => 2,
                'idadmin' => 1,
                'idempleado' => 11,
                'idservicio' => 1,
                'idarea_agencia' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio de Ingeniería',
                'prioridad' => 'Media',
                'estado' => 'en_revision',
                'num_modificaciones' => 1,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-15'
            ],
            [
                'idrequerimiento' => 7,
                'idadmin' => 1,
                'idempleado' => null,
                'idservicio' => 1,
                'idarea_agencia' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Infografía Académica',
                'prioridad' => 'Media',
                'estado' => 'pendiente_sin_asignar',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-30'
            ],

            // --- AREA 2: EDICIÓN Y VIDEO ---
            [
                'idrequerimiento' => 3,
                'idadmin' => 1,
                'idempleado' => 7, // Sonia
                'idservicio' => 2,
                'idarea_agencia' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Reel Bienvenida',
                'prioridad' => 'Alta',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-05'
            ],
            [
                'idrequerimiento' => 6,
                'idadmin' => 1,
                'idempleado' => 8, // Jose
                'idservicio' => 2,
                'idarea_agencia' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Tutorial Campus',
                'prioridad' => 'Media',
                'estado' => 'finalizado',
                'num_modificaciones' => 2,
                'url_entrega' => 'https://youtube.com/watch?v=example',
                'fechainicio' => '2026-04-01 09:00:00',
                'fechafin' => '2026-04-10'
            ],
            [
                'idrequerimiento' => 6,
                'idadmin' => 1,
                'idempleado' => 9, // Jonathan
                'idservicio' => 2,
                'idarea_agencia' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Post-producción Video Tutorial',
                'prioridad' => 'Media',
                'estado' => 'en_revision',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-12'
            ],

            // --- AREA 3: CREACION DE CONTENIDO ---
            [
                'idrequerimiento' => 4,
                'idadmin' => 1,
                'idempleado' => 3, // Noemi
                'idservicio' => 3,
                'idarea_agencia' => 3,
                'servicio_personalizado' => null,
                'titulo' => 'Planificación Lanzamiento 2026',
                'prioridad' => 'Alta',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-20'
            ],
            [
                'idrequerimiento' => 4,
                'idadmin' => 1,
                'idempleado' => 4, // Maria Pia
                'idservicio' => 3,
                'idarea_agencia' => 3,
                'servicio_personalizado' => null,
                'titulo' => 'Redacción Posts Puzzle',
                'prioridad' => 'Media',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-22'
            ],
            [
                'idrequerimiento' => 4,
                'idadmin' => 1,
                'idempleado' => 5, // Jheninfer
                'idservicio' => 3,
                'idarea_agencia' => 3,
                'servicio_personalizado' => null,
                'titulo' => 'Gestión de Comunidad Lanzamiento',
                'prioridad' => 'Baja',
                'estado' => 'pendiente_sin_asignar',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-25'
            ],
            [
                'idrequerimiento' => 4,
                'idadmin' => 1,
                'idempleado' => 6, // Nicol
                'idservicio' => 3,
                'idarea_agencia' => 3,
                'servicio_personalizado' => null,
                'titulo' => 'Review Lanzamiento 2026',
                'prioridad' => 'Media',
                'estado' => 'finalizado',
                'num_modificaciones' => 0,
                'url_entrega' => 'https://drive.google.com/example',
                'fechainicio' => '2026-04-05 10:00:00',
                'fechafin' => '2026-04-10'
            ],

            // --- AREA 4: FOTOGRAFIA ---
            [
                'idrequerimiento' => 5,
                'idadmin' => 1,
                'idempleado' => 2, // Rodrigo
                'idservicio' => 4,
                'idarea_agencia' => 4,
                'servicio_personalizado' => null,
                'titulo' => 'Sesión de Fotos Corporativas',
                'prioridad' => 'Media',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'url_entrega' => null,
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-05-10'
            ],
        ];

        $this->db->table('atencion')->insertBatch($data);
    }
}