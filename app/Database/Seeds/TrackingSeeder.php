<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TrackingSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('TRUNCATE TABLE tracking RESTART IDENTITY CASCADE');

        $data = [
            // ATENCIÓN 1: Campaña Verano - Más antiguo (Estado: finalizado)
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-03-01 09:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Asignado a Nicol del área de Creación de Contenido.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-03-01 10:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 6,
                'accion' => 'Inicio de desarrollo de conceptos para campaña puzzle.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-02 09:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 6,
                'accion' => 'Bocetos iniciales presentados. Cliente solicita ajustes de colores.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-05 11:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 6,
                'accion' => 'Correcciones aplicadas. Segunda versión enviada.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-07 14:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 6,
                'accion' => 'Versión final completada y entregada para aprobación.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-08 17:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Cliente aprobó campaña. Proyecto marcado como completado.',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-03-09 11:30:00'
            ],

            // ATENCIÓN 2: Video Reel Bienvenida - Intermedio (Estado: en_revision)
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-03-15 11:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Asignado a Sonia del área de Edición y Video.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-03-15 14:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 7,
                'accion' => 'Inicio de edición del reel. Selección de material de archivo.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-16 10:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 7,
                'accion' => 'Primera versión completada. Enviada para revisión del cliente.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-20 16:30:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Cliente solicita cambiar música de fondo.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-21 09:15:00'
            ],

            // ATENCIÓN 3: Logo Aniversario UAI - Más nuevo (Estado: en_proceso)
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-03-21 09:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Asignado a Fabrizio del área de Diseño Gráfico.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-03-21 10:30:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 10,
                'accion' => 'Inicio del diseño del logo. Recopilando referencias doradas.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-22 14:00:00'
            ],

            // ATENCIÓN 4: Infografía Académica - Usuario 14 (Estado: pendiente_sin_asignar)
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-03-25 10:00:00'
            ],
        ];

        $this->db->table('tracking')->insertBatch($data);
    }
}