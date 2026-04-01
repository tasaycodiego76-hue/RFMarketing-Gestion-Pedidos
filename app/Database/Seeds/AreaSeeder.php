<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run()
    {
        // Obtener la empresa principal buscando por el nombre de columna correcto: nombreempresa
        $empresa = $this->db->table('empresas')
                            ->where('nombreempresa', 'UNIVERSIDAD AUTÓNOMA DE ICA')
                            ->get()->getRow();
        
        // Si no la encuentra, toma la primera que exista en la tabla
        if (!$empresa) {
            $empresa = $this->db->table('empresas')->get()->getRow();
        }

        $idEmpresa = $empresa ? $empresa->id : 1;

        $nombres = [
            'Diseño Gráfico', 'Edicion y Video', 'ADMINISTRACION FINANCIERA Y TALENTO HUMANO',
            'ÁREA COMERCIAL', 'ATENCIÓN AL CLIENTE', 'BIBLIOTECA', 'BIENESTAR UNIVERSITARIO',
            'COBRANZAS Y CAJA', 'COMITÉ SEGURIDAD BIOLOGICA Y QUIMICA', 
            'COMITÉ DE SEGURIDAD Y SALUD EN EL TRABAJO', 'CONTABILIDAD', 'CONTROL INTERNO',
            'COORDINACIÓN ADMINISTRATIVA Y ACADEMICA', 'DEFENSORIA UNIVERSITARIA',
            'DEPARTAMENTO ACADEMICO DE EEGG', 'DIRECCIÓN ADMINISTRATIVA', 'DIRECIÓN DE CALIDAD',
            'DOCENTE', 'GERENCIA GENERAL', 'GRADOS Y TITULOS', 'INFRAESTRUCTURA',
            'INVESTIGACION Y PRODUCCION INTELECTUAL', 'LABORATORIO', 
            'LOGÍSTICA - MANTENIMIENTO - SS.GG Y VIGILANCIA', 'MARKETING Y DISEÑO',
            'OFICINA CENTRAL DE TRÁMITE DOCUMENTARIO Y ARCHIVO (OCTDA)',
            'PLANIFICACION ESTRATEGICA, PRESUPUESTO Y CONTROL', 'RESPONSABILIDAD SOCIAL',
            'SISTEMAS', 'SOPORTE TECNICO', 'SUB GERENCIA GENERAL', 'TÓPICO',
            'REGISTROS ACADÉMICOS', 'VICERRECTORADO', 
            'VINCULO UNIVERSITARIO - EMPRESA Y BOLSA DE TRABAJO',
            'DIRECCIÓN DE ADMINISTRACIÓN Y FINANZAS', 'DIRECCIÓN DE CONTABILIDAD',
            'DIRECCIÓN DE DERECHO', 'DIRECCIÓN DE ING. DE SISTEMAS', 'DIRECCIÓN DE ING. INDUSTRIAL',
            'DIRECCIÓN DE INGENIERÍA CIVIL', 'DIRECCIÓN DE ARQUITECTURA', 'DIRECCIÓN DE ENFERMERÍA',
            'DIRECCIÓN DE OBSTETRICIA', 'DIRECCIÓN DE PSICOLOGÍA', 'DIRECCIÓN DE MEDICINA HUMANA',
            'DIRECCIÓN DE TECNOLOGÍA MÉDICA', 'TECNOLOGÍA DE LA INFORMACIÓN', 'SECRETARÍA DE FICA',
            'SECRETARÍA DE FCS', 'ESCUELA DE POSGRADO', 'SERVICIO PSICOPEDAGÓGICO'
        ];

        $data = [];
        foreach ($nombres as $nombre) {
            $data[] = [
                'nombre'    => $nombre,
                'idempresa' => $idEmpresa,
                'activo'    => true
            ];
        }

        $this->db->table('areas')->insertBatch($data);
    }
}