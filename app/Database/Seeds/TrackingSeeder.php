<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TrackingSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ATENCIÓN 1: Logo Aniversario UAI (Estado: finalizado)
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación ',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-02-25 10:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Asignado al diseñador Juan Carlos del área de Diseño Gráfico.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-02-26 09:30:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 3,
                'accion' => 'Inicio del diseño del logo. Se solicitaron referencias de la marca.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-02 09:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 3,
                'accion' => 'Primera versión enviada a revisión. Cliente solicita ajustar tono dorado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-08 16:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 3,
                'accion' => 'Correcciones realizadas. Ajustado tono dorado según feedback.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-10 11:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 3,
                'accion' => 'Versión final entregada para aprobación del cliente.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-12 14:30:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Cliente aprobó el diseño final. Proyecto marcado como completado.',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-03-13 18:30:00'
            ],

            // ATENCIÓN 2: Banner Laboratorio (Estado: finalizado)
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-03-04 15:20:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Asignado a María del área de Diseño Gráfico.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-03-05 10:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 4,
                'accion' => 'Inicio de diseño de banner 2x1 metros.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-09 14:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 4,
                'accion' => 'Diseño completado y enviado a imprenta.',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-03-20 17:15:00'
            ],

            // ATENCIÓN 3: Video Reel Bienvenida (Estado: en_revision)
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-03-10 09:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Asignado al editor de video Carlos (área Audiovisual).',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-03-12 11:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 6,
                'accion' => 'Inicio de edición. Recopilación de material de archivo.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-16 08:30:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 6,
                'accion' => 'Primera versión enviada. Cliente solicita cambiar música de fondo.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-03-25 15:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 6,
                'accion' => 'Ajustes realizados. Cambiada música y sincronización.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-03-28 10:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 6,
                'accion' => 'Segunda versión entregada. Esperando aprobación final.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-04-05 14:00:00'
            ],

            // ATENCIÓN 4: Campaña Intriga Verano (Estado: en_proceso)
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento registrado. Pendiente de asignación',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-04-08 10:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Asignado al equipo creativo liderado por Ana.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-04-09 09:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 3,
                'accion' => 'Inicio de fase creativa. Desarrollo de concepto "Puzzle".',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-04-10 09:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 3,
                'accion' => 'Bocetos iniciales presentados al director de arte.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-04-15 16:00:00'
            ],
        ];

        $this->db->table('tracking')->insertBatch($data);
    }
}