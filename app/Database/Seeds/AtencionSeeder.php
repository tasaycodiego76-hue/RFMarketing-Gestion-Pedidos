<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AtencionSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('TRUNCATE TABLE atencion RESTART IDENTITY CASCADE');

        $data = [
            // Requerimiento 1: Campaña Verano - Más antiguo (Estado: finalizado)
            [
                'idrequerimiento'        => 1,
                'idadmin'                => 1,
                'idempleado'             => 6, // Nicol - completado y entregado al cliente
                'idservicio'             => 3,
                'idarea_agencia'         => 3, // Creación de Contenido
                'servicio_personalizado' => 'Campaña Intriga Verano',
                'titulo'                 => 'Lanzamiento Colección 2026',
                'prioridad'              => 'Baja',
                'estado'                 => 'finalizado',
                'num_modificaciones'     => 2,
                'observacion_revision'   => null,
                'url_entrega'            => 'https://drive.google.com/campaña_verano_final.zip',
                'fechacreacion'          => '2026-03-01 09:00:00',
                'fechainicio'            => '2026-03-02 09:00:00',
                'fechafin'               => '2026-03-08 17:00:00', // Terminó y envió a revisión
                'fechacompletado'        => '2026-03-09 11:30:00', // Cliente aprobó y se completó
                'cancelacionmotivo'      => null,
                'fechacancelacion'       => null,
            ],
            
            // Requerimiento 2: Video Reel Bienvenida - Intermedio (Estado: en_revision)
            [
                'idrequerimiento'        => 2,
                'idadmin'                => 1,
                'idempleado'             => 7, // Sonia - terminó y envió a revisión
                'idservicio'             => 2,
                'idarea_agencia'         => 2, // Edición y Video
                'servicio_personalizado' => null,
                'titulo'                 => 'Video Reel Bienvenida',
                'prioridad'              => 'Alta',
                'estado'                 => 'en_revision',
                'num_modificaciones'     => 1,
                'observacion_revision'   => 'Cliente solicita cambiar música de fondo',
                'url_entrega'            => 'https://drive.google.com/entrega/reel_bienvenida_v1.mp4',
                'fechacreacion'          => '2026-03-15 11:00:00',
                'fechainicio'            => '2026-03-16 10:00:00',
                'fechafin'               => '2026-03-20 16:30:00', // Terminó y envió a revisión
                'fechacompletado'        => null,
                'cancelacionmotivo'      => null,
                'fechacancelacion'       => null,
            ],
            
            // Requerimiento 3: Logo Aniversario UAI - Más nuevo (Estado: en_proceso)
            [
                'idrequerimiento'        => 3,
                'idadmin'                => 1,
                'idempleado'             => 10, // Fabrizio - trabajando actualmente
                'idservicio'             => 1,
                'idarea_agencia'         => 1, // Diseño Gráfico
                'servicio_personalizado' => null,
                'titulo'                 => 'Logo Aniversario UAI',
                'prioridad'              => 'Media',
                'estado'                 => 'en_proceso',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechacreacion'          => '2026-03-21 09:00:00',
                'fechainicio'            => '2026-03-22 14:00:00',
                'fechafin'               => null, // Aún no termina
                'fechacompletado'        => null,
                'cancelacionmotivo'      => null,
                'fechacancelacion'       => null,
            ],
            
            // Requerimiento 4: Infografía Académica - Usuario 14 (Estado: pendiente_sin_asignar)
            [
                'idrequerimiento'        => 4,
                'idadmin'                => 1,
                'idempleado'             => null, // Aún no asignado
                'idservicio'             => 1,
                'idarea_agencia'         => 1, // Diseño Gráfico
                'servicio_personalizado' => null,
                'titulo'                 => 'Infografía Académica',
                'prioridad'              => 'Media',
                'estado'                 => 'pendiente_sin_asignar',
                'num_modificaciones'     => 0,
                'observacion_revision'   => null,
                'url_entrega'            => null,
                'fechacreacion'          => '2026-03-25 10:00:00',
                'fechainicio'            => null,
                'fechafin'               => null,
                'fechacompletado'        => null,
                'cancelacionmotivo'      => null,
                'fechacancelacion'       => null,
            ],
        ];

        $this->db->table('atencion')->insertBatch($data);
    }
}
