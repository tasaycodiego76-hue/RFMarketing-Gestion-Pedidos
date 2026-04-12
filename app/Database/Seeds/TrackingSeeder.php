<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TrackingSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ATENCIÓN 1: Video Reel Bienvenida (Estado: en_revision)
            [
                'idatencion' => 1,
                'idusuario' => 1, // Admin (Rodrigo)
                'accion' => 'Requerimiento recibido y validado por el administrador.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-04-01 09:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1, // Admin asigna
                'accion' => 'Rodrigo asignó el pedido al diseñador Juan Carlos.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-04-01 10:30:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 6, // Diseñador (Juan Carlos)
                'accion' => 'Juan Carlos inició el proceso de edición del video.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-04-02 08:30:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 6, // Diseñador sube propuesta
                'accion' => 'Material subido para revisión (Versión 2 con ajustes de audio).',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-04-05 14:00:00'
            ],

            // ATENCIÓN 2: Logo Aniversario UAI (Estado: en_proceso)
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Pedido aprobado para producción.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-04-08 11:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 3, // Diseñador asignado
                'accion' => 'Diseñador aceptó la tarea y comenzó la fase de bocetaje.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-04-09 09:15:00'
            ],

            // ATENCIÓN 3: Banner Laboratorio Ingeniería (Estado: pendiente_sin_asignar)
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Nuevo requerimiento creado por el cliente. Esperando asignación de diseñador.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => date('Y-m-d H:i:s')
            ],

            // ATENCIÓN 4: Banner Laboratorio Sistemas (Estado: pendiente_asignado)
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Requerimiento validado.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-04-10 15:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Se asignó la tarea prioritariamente debido a fecha de evento.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-04-10 16:30:00'
            ],
        ];

        $this->db->table('tracking')->insertBatch($data);
    }
}