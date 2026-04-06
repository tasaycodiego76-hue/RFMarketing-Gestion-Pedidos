<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class RequerimientoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idempresa' => 1,
                'idservicio' => 2,
                'servicio_personalizado' => null,
                'titulo' => 'Video Reel Bienvenida',
                'objetivo_comunicacion' => 'Saludo institucional',
                'descripcion' => 'Video de 15 segundos para historias',
                'tipo_requerimiento' => 'Audiovisual',
                'canales_difusion' => 'TikTok',
                'publico_objetivo' => 'Comunidad universitaria',
                'tiene_materiales' => true,
                'formatos_solicitados' => 'MP4 (Vertical)',
                'fecharequerida' => '2026-04-02',
                'prioridad' => 'Urgente'
            ],
            [
                'idempresa' => 1,
                'idservicio' => 1,
                'titulo' => 'Logo Aniversario UAI',
                'servicio_personalizado' => null,
                'objetivo_comunicacion' => 'Atraer nuevos postulantes',
                'descripcion' => 'Logo dorado conmemorativo',
                'tipo_requerimiento' => 'Diseño',
                'canales_difusion' => 'Web',
                'publico_objetivo' => 'Juvenil',
                'tiene_materiales' => false,
                'formatos_solicitados' => 'PNG, JPG',
                'fecharequerida' => '2026-04-15',
                'prioridad' => 'Media'
            ],
            [
                'idempresa' => 1,
                'idservicio' => 1,
                'titulo' => 'Banner Laboratorio de Ingeniería',
                'servicio_personalizado' => null,
                'objetivo_comunicacion' => 'Señalética interna',
                'descripcion' => 'Diseño de banner para la puerta del laboratorio de sistemas.',
                'tipo_requerimiento' => 'Impresión Gran Formato',
                'canales_difusion' => 'Físico',
                'publico_objetivo' => 'Estudiantes de Ingeniería',
                'tiene_materiales' => false,
                'formatos_solicitados' => 'PDF (Vectores)',
                'fecharequerida' => '2026-04-20',
                'prioridad' => 'Media'
            ],
            [
                'idempresa' => 1,
                'idservicio' => 1,
                'servicio_personalizado' => null,
                'titulo' => 'Banner Laboratorio Sistemas',
                'objetivo_comunicacion' => 'Identificación de ambiente',
                'descripcion' => 'Banner de lona para la entrada del lab 302.',
                'tipo_requerimiento' => 'Impresión',
                'canales_difusion' => 'Físico',
                'publico_objetivo' => 'Alumnos Ingeniería',
                'tiene_materiales' => true,
                'formatos_solicitados' => 'PDF Vectores',
                'fecharequerida' => '2026-04-20',
                'prioridad' => 'Alta'
            ],
            [
                'idempresa' => 1,
                'idservicio' => null,
                'servicio_personalizado' => 'Publicidad Online',
                'titulo' => 'Flyer Taller Autoestima',
                'objetivo_comunicacion' => 'Atraer alumnos de psicología',
                'descripcion' => 'Diseño para post de Instagram sobre salud mental.',
                'tipo_requerimiento' => 'Redes Sociales',
                'canales_difusion' => 'Instagram',
                'publico_objetivo' => 'Estudiantes',
                'tiene_materiales' => false,
                'formatos_solicitados' => 'JPG, PNG',
                'fecharequerida' => '2026-04-15',
                'prioridad' => 'Media'
            ]
        ];
        $this->db->table('requerimiento')->insertBatch($data);
    }
}