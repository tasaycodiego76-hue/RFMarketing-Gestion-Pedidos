<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Flujo coherente con el sistema:
 * - pendiente_sin_asignar: sin empleado; aún no aprobado/enviado al flujo del área (idarea puede estar fijada en demo para verla en el tablero).
 * - pendiente_asignado: ya en área; sin empleado O con empleado pero sin "iniciar trabajo".
 * - en_proceso: empleado asignado y trabajo en curso (fechainicio).
 * - en_revision / finalizado: etapas siguientes.
 *
 * IDs empleados (UsuariosSeeder): 2 Rodrigo(4), 3 Noemi(3), 4 Maria(3), 5 Jheninfer(3), 6 Nicol(3),
 * 7 Sonia(2), 8 Jose(2), 9 Jonathan(2), 10 Fabrizio(1), 11 Nayru(1), 12 Jesus(1). idadmin = 1.
 */
class AtencionSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('TRUNCATE TABLE atencion RESTART IDENTITY CASCADE');

        $now = date('Y-m-d H:i:s');

        $data = [
            // --- ÁREA 1: DISEÑO GRÁFICO (idarea_agencia = 1) ---
            [
                'idrequerimiento'        => 1,
                'idadmin'                => 1,
                'idempleado'             => 10, // Fabrizio — desarrollando
                'idservicio'             => 1,
                'idarea_agencia'         => 1,
                'servicio_personalizado' => null,
                'titulo'                 => 'Logo Aniversario UAI',
                'prioridad'              => 'Media',
                'estado'                 => 'en_proceso',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-10',
                'fechacompletado'        => null,
            ],
            [
                'idrequerimiento'        => 2,
                'idadmin'                => 1,
                'idempleado'             => 11, // Nayru — en revisión con entrega
                'idservicio'             => 1,
                'idarea_agencia'         => 1,
                'servicio_personalizado' => null,
                'titulo'                 => 'Banner Laboratorio de Ingeniería',
                'prioridad'              => 'Media',
                'estado'                 => 'en_revision',
                'num_modificaciones'     => 1,
                'observacion_revision'   => 'Revisar proporciones del banner.',
                'url_entrega'            => 'https://ejemplo.com/entregas/banner_lab.pdf',
                'fechainicio'            => '2026-03-09 14:00:00',
                'fechafin'               => '2026-04-15',
                'fechacompletado'        => null,
            ],
            [
                'idrequerimiento'        => 7,
                'idadmin'                => 1,
                'idempleado'             => null,
                'idservicio'             => 1,
                'idarea_agencia'         => 1, // visible en tab Diseño; aún "por aprobar" (demo)
                'servicio_personalizado' => null,
                'titulo'                 => 'Infografía Académica',
                'prioridad'              => 'Media',
                'estado'                 => 'pendiente_sin_asignar',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-30',
                'fechacompletado'        => null,
            ],

            // --- ÁREA 2: EDICIÓN Y VIDEO (idarea_agencia = 2) ---
            [
                'idrequerimiento'        => 3,
                'idadmin'                => 1,
                'idempleado'             => 7, // Sonia — en proceso
                'idservicio'             => 2,
                'idarea_agencia'         => 2,
                'servicio_personalizado' => null,
                'titulo'                 => 'Video Reel Bienvenida',
                'prioridad'              => 'Alta',
                'estado'                 => 'en_proceso',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-05',
                'fechacompletado'        => null,
            ],
            [
                'idrequerimiento'        => 6,
                'idadmin'                => 1,
                'idempleado'             => 8, // Jose — finalizado
                'idservicio'             => 2,
                'idarea_agencia'         => 2,
                'servicio_personalizado' => null,
                'titulo'                 => 'Video Tutorial Campus',
                'prioridad'              => 'Media',
                'estado'                 => 'finalizado',
                'num_modificaciones'     => 2,
                'observacion_revision'   => null,
                'url_entrega'            => 'https://youtube.com/watch?v=example',
                'fechainicio'            => '2026-04-01 09:00:00',
                'fechafin'               => '2026-04-10',
                'fechacompletado'        => '2026-04-10 17:00:00',
            ],
            [
                'idrequerimiento'        => 6,
                'idadmin'                => 1,
                'idempleado'             => 9, // Jonathan — revisión
                'idservicio'             => 2,
                'idarea_agencia'         => 2,
                'servicio_personalizado' => null,
                'titulo'                 => 'Post-producción Video Tutorial',
                'prioridad'              => 'Media',
                'estado'                 => 'en_revision',
                'num_modificaciones'     => 0,
                'observacion_revision'   => 'Ajustar corte final min 1:45.',
                'url_entrega'            => 'https://ejemplo.com/entregas/postprod_v1.mp4',
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-12',
                'fechacompletado'        => null,
            ],

            // --- ÁREA 3: CREACIÓN DE CONTENIDO (idarea_agencia = 3) ---
            [
                'idrequerimiento'        => 4,
                'idadmin'                => 1,
                'idempleado'             => null, // en área, falta asignar empleado
                'idservicio'             => 3,
                'idarea_agencia'         => 3,
                'servicio_personalizado' => null,
                'titulo'                 => 'Planificación Lanzamiento 2026',
                'prioridad'              => 'Alta',
                'estado'                 => 'pendiente_asignado',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-20',
                'fechacompletado'        => null,
            ],
            [
                'idrequerimiento'        => 4,
                'idadmin'                => 1,
                'idempleado'             => 4, // Maria Pia — asignada, aún no en_proceso real
                'idservicio'             => 3,
                'idarea_agencia'         => 3,
                'servicio_personalizado' => null,
                'titulo'                 => 'Redacción Posts Puzzle',
                'prioridad'              => 'Media',
                'estado'                 => 'pendiente_asignado',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-22',
                'fechacompletado'        => null,
            ],
            [
                'idrequerimiento'        => 4,
                'idadmin'                => 1,
                'idempleado'             => null,
                'idservicio'             => 3,
                'idarea_agencia'         => 3,
                'servicio_personalizado' => null,
                'titulo'                 => 'Gestión de Comunidad Lanzamiento',
                'prioridad'              => 'Baja',
                'estado'                 => 'pendiente_asignado',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-04-25',
                'fechacompletado'        => null,
            ],
            [
                'idrequerimiento'        => 4,
                'idadmin'                => 1,
                'idempleado'             => 6, // Nicol — finalizado
                'idservicio'             => 3,
                'idarea_agencia'         => 3,
                'servicio_personalizado' => null,
                'titulo'                 => 'Review Lanzamiento 2026',
                'prioridad'              => 'Media',
                'estado'                 => 'finalizado',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => 'https://drive.google.com/example',
                'fechainicio'            => '2026-04-05 10:00:00',
                'fechafin'               => '2026-04-10',
                'fechacompletado'        => '2026-04-10 12:00:00',
            ],

            // --- ÁREA 4: FOTOGRAFÍA (idarea_agencia = 4) ---
            [
                'idrequerimiento'        => 5,
                'idadmin'                => 1,
                'idempleado'             => 2, // Rodrigo
                'idservicio'             => 4,
                'idarea_agencia'         => 4,
                'servicio_personalizado' => null,
                'titulo'                 => 'Sesión de Fotos Corporativas',
                'prioridad'              => 'Media',
                'estado'                 => 'en_proceso',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechainicio'            => $now,
                'fechafin'               => '2026-05-10',
                'fechacompletado'        => null,
            ],
        ];

        $this->db->table('atencion')->insertBatch($data);
    }
}
