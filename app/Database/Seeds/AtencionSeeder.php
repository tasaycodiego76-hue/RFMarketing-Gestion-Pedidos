<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class AtencionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idrequerimiento' => 1,
                'idadmin' => 1,
                'idempleado' => 6,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Reel Bienvenida',
                'prioridad' => 'Alta',
                'estado' => 'en_revision',
                'num_modificaciones' => 2,
                'respuestatexto' => 'Primera versión enviada, esperando validación de colores.',
                'fechainicio' => '2026-04-02 08:30:00',
                'fechafin' => '2026-04-02'
            ],
            [
                'idrequerimiento' => 2,
                'idadmin' => 1,
                'idempleado' => 3,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Logo Aniversario UAI',
                'prioridad' => 'Media',
                'estado' => 'en_proceso',
                'num_modificaciones' => 0,
                'respuestatexto' => 'Se inicia el diseño base.',
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-10'
            ],
            [
                'idrequerimiento' => 3,
                'idadmin' => 1,
                'idempleado' => null,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio de Ingeniería',
                'prioridad' => 'Media',
                'estado' => 'pendiente_sin_asignar',
                'num_modificaciones' => 0,
                'respuestatexto' => 'Requerimiento recibido, pendiente de revisión técnica.',
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-18'
            ],
            [
                'idrequerimiento' => 4,
                'idadmin' => 1,
                'idempleado' => 3,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio Sistemas',
                'prioridad' => 'Alta',
                'estado' => 'pendiente_asignado',
                'num_modificaciones' => 0,
                'respuestatexto' => 'Asignado a Jose Guerra, pendiente de confirmación.',
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-18'
            ],
            [
                'idrequerimiento' => 5,
                'idadmin' => 1,
                'idempleado' => null,
                'idservicio' => null,
                'servicio_personalizado' => 'Publicidad Online',
                'titulo' => 'Flyer Taller Autoestima',
                'prioridad' => 'Media',
                'estado' => 'pendiente_sin_asignar',
                'num_modificaciones' => 0,
                'respuestatexto' => 'Recibido en bandeja de entrada.',
                'fechainicio' => date('Y-m-d H:i:s'),
                'fechafin' => '2026-04-10'
            ]
        ];
        $this->db->table('atencion')->insertBatch($data);
    }
}