<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SesionesTrabajoSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('TRUNCATE TABLE sesiones_trabajo RESTART IDENTITY CASCADE');

        $atenciones = [
            ['idrequerimiento' => 1, 'idempleado' => 11, 'fechainicio' => '2026-01-27 09:00:00', 'fechacompletado' => '2026-01-29 16:00:00'],
            ['idrequerimiento' => 2, 'idempleado' => 12, 'fechainicio' => '2026-01-27 11:00:00', 'fechacompletado' => '2026-01-29 14:00:00'],
            ['idrequerimiento' => 3, 'idempleado' => 13, 'fechainicio' => '2026-04-27 14:00:00', 'fechacompletado' => '2026-04-29 17:00:00'],
            ['idrequerimiento' => 4, 'idempleado' => 11, 'fechainicio' => '2026-02-09 11:00:00', 'fechacompletado' => '2026-02-10 16:30:00'],
            ['idrequerimiento' => 5, 'idempleado' => 13, 'fechainicio' => '2026-03-10 11:00:00', 'fechacompletado' => '2026-03-12 16:00:00'],
            ['idrequerimiento' => 6, 'idempleado' => 12, 'fechainicio' => '2026-02-18 14:00:00', 'fechacompletado' => '2026-02-21 13:00:00'],
            ['idrequerimiento' => 7, 'idempleado' => 11, 'fechainicio' => '2026-05-18 10:30:00', 'fechacompletado' => '2026-05-20 17:00:00'],
            ['idrequerimiento' => 8, 'idempleado' => 13, 'fechainicio' => '2026-02-19 09:00:00', 'fechacompletado' => '2026-02-21 11:00:00'],
            ['idrequerimiento' => 9, 'idempleado' => 12, 'fechainicio' => '2026-06-07 10:30:00', 'fechacompletado' => '2026-06-09 16:30:00'],
            ['idrequerimiento' => 10, 'idempleado' => 13, 'fechainicio' => '2026-01-08 10:30:00', 'fechacompletado' => '2026-01-10 16:45:00'],
            ['idrequerimiento' => 11, 'idempleado' => 12, 'fechainicio' => '2026-01-28 11:00:00', 'fechacompletado' => '2026-01-30 15:00:00'],
            ['idrequerimiento' => 12, 'idempleado' => 11, 'fechainicio' => '2026-01-29 09:00:00', 'fechacompletado' => '2026-01-31 14:30:00'],
            ['idrequerimiento' => 13, 'idempleado' => 13, 'fechainicio' => '2026-01-29 10:00:00', 'fechacompletado' => '2026-01-31 17:00:00'],
            ['idrequerimiento' => 14, 'idempleado' => 12, 'fechainicio' => '2026-04-07 09:00:00', 'fechacompletado' => '2026-04-09 15:00:00'],
            ['idrequerimiento' => 15, 'idempleado' => 11, 'fechainicio' => '2026-05-08 11:30:00', 'fechacompletado' => '2026-05-08 17:30:00'],
            ['idrequerimiento' => 16, 'idempleado' => 11, 'fechainicio' => '2026-04-01 11:00:00', 'fechacompletado' => '2026-04-05 16:00:00'],
            ['idrequerimiento' => 17, 'idempleado' => 13, 'fechainicio' => '2026-05-25 11:00:00', 'fechacompletado' => '2026-05-28 16:30:00'],
            ['idrequerimiento' => 18, 'idempleado' => 12, 'fechainicio' => '2026-04-18 14:00:00', 'fechacompletado' => '2026-04-22 15:00:00'],
            ['idrequerimiento' => 19, 'idempleado' => 11, 'fechainicio' => '2026-02-09 11:30:00', 'fechacompletado' => '2026-02-11 15:00:00'],
            ['idrequerimiento' => 20, 'idempleado' => 13, 'fechainicio' => '2026-02-20 14:00:00', 'fechacompletado' => '2026-02-24 17:00:00'],
            ['idrequerimiento' => 21, 'idempleado' => 12, 'fechainicio' => '2026-06-01 10:30:00', 'fechacompletado' => '2026-06-02 15:00:00'],
            ['idrequerimiento' => 22, 'idempleado' => 13, 'fechainicio' => '2026-01-27 14:00:00', 'fechacompletado' => '2026-01-30 17:00:00'],
            ['idrequerimiento' => 23, 'idempleado' => 11, 'fechainicio' => '2026-04-14 11:00:00', 'fechacompletado' => '2026-04-17 14:30:00'],
            ['idrequerimiento' => 24, 'idempleado' => 13, 'fechainicio' => '2026-05-06 14:00:00', 'fechacompletado' => '2026-05-09 16:00:00'],
            ['idrequerimiento' => 25, 'idempleado' => 11, 'fechainicio' => '2026-03-14 11:00:00', 'fechacompletado' => '2026-03-18 13:00:00'],
            ['idrequerimiento' => 26, 'idempleado' => 11, 'fechainicio' => '2026-04-10 14:00:00', 'fechacompletado' => '2026-04-14 16:00:00'],
            ['idrequerimiento' => 27, 'idempleado' => 12, 'fechainicio' => '2026-05-15 09:00:00', 'fechacompletado' => '2026-05-18 14:00:00'],
            ['idrequerimiento' => 28, 'idempleado' => 13, 'fechainicio' => '2026-01-22 14:00:00', 'fechacompletado' => '2026-01-25 17:00:00'],
            ['idrequerimiento' => 29, 'idempleado' => 12, 'fechainicio' => '2026-01-23 09:00:00', 'fechacompletado' => '2026-01-25 15:30:00'],
            ['idrequerimiento' => 30, 'idempleado' => 13, 'fechainicio' => '2026-02-20 11:00:00', 'fechacompletado' => '2026-02-23 16:00:00'],
            ['idrequerimiento' => 31, 'idempleado' => 12, 'fechainicio' => '2026-04-01 11:00:00', 'fechacompletado' => '2026-04-05 14:00:00'],
            ['idrequerimiento' => 32, 'idempleado' => 11, 'fechainicio' => '2026-05-11 11:00:00', 'fechacompletado' => '2026-05-14 16:00:00'],
            ['idrequerimiento' => 33, 'idempleado' => 13, 'fechainicio' => '2026-04-10 14:00:00', 'fechacompletado' => '2026-04-14 17:30:00'],
            ['idrequerimiento' => 34, 'idempleado' => 12, 'fechainicio' => '2026-04-20 09:00:00', 'fechacompletado' => '2026-04-22 13:00:00'],
            ['idrequerimiento' => 35, 'idempleado' => 11, 'fechainicio' => '2026-05-29 11:00:00', 'fechacompletado' => '2026-06-02 15:00:00'],
            ['idrequerimiento' => 36, 'idempleado' => 11, 'fechainicio' => '2026-01-15 10:00:00', 'fechacompletado' => '2026-01-17 14:00:00'],
            ['idrequerimiento' => 37, 'idempleado' => 13, 'fechainicio' => '2026-03-05 09:00:00', 'fechacompletado' => '2026-03-08 12:00:00'],
            ['idrequerimiento' => 38, 'idempleado' => 12, 'fechainicio' => '2026-04-25 14:00:00', 'fechacompletado' => '2026-04-28 11:00:00'],
            ['idrequerimiento' => 39, 'idempleado' => 11, 'fechainicio' => '2026-05-13 11:00:00', 'fechacompletado' => '2026-05-17 15:00:00'],
            ['idrequerimiento' => 40, 'idempleado' => 13, 'fechainicio' => '2026-04-26 09:00:00', 'fechacompletado' => '2026-04-28 16:00:00'],
            ['idrequerimiento' => 41, 'idempleado' => 12, 'fechainicio' => '2026-04-14 11:30:00', 'fechacompletado' => '2026-04-17 15:00:00'],
            ['idrequerimiento' => 42, 'idempleado' => 11, 'fechainicio' => '2026-01-27 11:00:00', 'fechacompletado' => '2026-01-30 14:00:00'],
            ['idrequerimiento' => 43, 'idempleado' => 12, 'fechainicio' => '2026-02-18 14:00:00', 'fechacompletado' => '2026-02-22 12:00:00'],
            ['idrequerimiento' => 44, 'idempleado' => 13, 'fechainicio' => '2026-02-20 11:00:00', 'fechacompletado' => '2026-02-24 16:30:00'],
            ['idrequerimiento' => 45, 'idempleado' => 13, 'fechainicio' => '2026-03-14 11:00:00', 'fechacompletado' => '2026-03-18 16:00:00'],
            ['idrequerimiento' => 46, 'idempleado' => 12, 'fechainicio' => '2026-02-27 14:00:00', 'fechacompletado' => '2026-03-02 13:00:00'],
            ['idrequerimiento' => 47, 'idempleado' => 12, 'fechainicio' => '2026-05-14 11:00:00', 'fechacompletado' => '2026-05-18 15:30:00'],
            ['idrequerimiento' => 48, 'idempleado' => 13, 'fechainicio' => '2026-03-15 09:00:00', 'fechacompletado' => '2026-03-18 14:00:00'],
            ['idrequerimiento' => 49, 'idempleado' => 7, 'fechainicio' => '2026-04-28 11:00:00', 'fechacompletado' => '2026-05-02 14:00:00'],
            ['idrequerimiento' => 50, 'idempleado' => 11, 'fechainicio' => '2026-04-28 14:00:00', 'fechacompletado' => '2026-05-02 15:30:00'],
            ['idrequerimiento' => 51, 'idempleado' => 6, 'fechainicio' => '2026-04-29 09:00:00', 'fechacompletado' => '2026-05-03 16:00:00'],
            ['idrequerimiento' => 52, 'idempleado' => 12, 'fechainicio' => '2026-05-28 11:00:00', 'fechacompletado' => '2026-06-01 15:00:00'],
        ];

        $sesionesData = [];

        foreach ($atenciones as $at) {
            $completado = strtotime($at['fechacompletado']);

            $variacion = rand(20, 50);
            $horaFin = date('Y-m-d H:i:s', strtotime('-' . $variacion . ' minutes', $completado));

            $sesionesData[] = [
                'idatencion' => $at['idrequerimiento'],
                'idusuario' => $at['idempleado'],
                'hora_inicio' => $at['fechainicio'],
                'hora_fin' => $horaFin,
                'motivo_pausa' => null,
            ];
        }

        $this->db->table('sesiones_trabajo')->insertBatch($sesionesData);
    }
}