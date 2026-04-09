<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run()
    {
        // Obtener la empresa principal buscando por el nombre de columna correcto: nombreempresa
        $empresaU = $this->db->table('empresas')
            ->where('nombreempresa', 'UNIVERSIDAD AUTÓNOMA DE ICA')
            ->get()->getRow();

        // Si no la encuentra, toma la primera que exista en la tabla
        if (!$empresaU) {
            $empresaU = $this->db->table('empresas')->get()->getRow();
        }

        $idEmpresa = $empresaU ? $empresaU->id : 1;

        $nombres = [
            'ADMINISTRACION FINANCIERA Y TALENTO HUMANO',
            'ÁREA COMERCIAL',
            'ATENCIÓN AL CLIENTE',
            'BIBLIOTECA',
            'BIENESTAR UNIVERSITARIO',
            'COBRANZAS Y CAJA',
            'COMITÉ SEGURIDAD BIOLOGICA Y QUIMICA',
            'COMITÉ DE SEGURIDAD Y SALUD EN EL TRABAJO',
            'CONTABILIDAD',
            'CONTROL INTERNO',
            'COORDINACIÓN ADMINISTRATIVA Y ACADEMICA',
            'DEFENSORIA UNIVERSITARIA',
            'DEPARTAMENTO ACADEMICO DE EEGG',
            'DIRECCIÓN ADMINISTRATIVA',
            'DIRECIÓN DE CALIDAD',
            'DOCENTE',
            'GERENCIA GENERAL',
            'GRADOS Y TITULOS',
            'INFRAESTRUCTURA',
            'INVESTIGACION Y PRODUCCION INTELECTUAL',
            'LABORATORIO',
            'LOGÍSTICA - MANTENIMIENTO - SS.GG Y VIGILANCIA',
            'MARKETING Y DISEÑO',
            'OFICINA CENTRAL DE TRÁMITE DOCUMENTARIO Y ARCHIVO (OCTDA)',
            'PLANIFICACION ESTRATEGICA, PRESUPUESTO Y CONTROL',
            'RESPONSABILIDAD SOCIAL',
            'SISTEMAS',
            'SOPORTE TECNICO',
            'SUB GERENCIA GENERAL',
            'TÓPICO',
            'REGISTROS ACADÉMICOS',
            'VICERRECTORADO',
            'VINCULO UNIVERSITARIO - EMPRESA Y BOLSA DE TRABAJO',
            'DIRECCIÓN DE ADMINISTRACIÓN Y FINANZAS',
            'DIRECCIÓN DE CONTABILIDAD',
            'DIRECCIÓN DE DERECHO',
            'DIRECCIÓN DE ING. DE SISTEMAS',
            'DIRECCIÓN DE ING. INDUSTRIAL',
            'DIRECCIÓN DE INGENIERÍA CIVIL',
            'DIRECCIÓN DE ARQUITECTURA',
            'DIRECCIÓN DE ENFERMERÍA',
            'DIRECCIÓN DE OBSTETRICIA',
            'DIRECCIÓN DE PSICOLOGÍA',
            'DIRECCIÓN DE MEDICINA HUMANA',
            'DIRECCIÓN DE TECNOLOGÍA MÉDICA',
            'TECNOLOGÍA DE LA INFORMACIÓN',
            'SECRETARÍA DE FICA',
            'SECRETARÍA DE FCS',
            'ESCUELA DE POSGRADO',
            'SERVICIO PSICOPEDAGÓGICO'
        ];

        $data = [];
        foreach ($nombres as $nombre) {
            $data[] = [
                'nombre' => $nombre,
                'idempresa' => $idEmpresa,
                'activo' => true
            ];
        }

        $this->db->table('areas')->insertBatch($data);
        // 1. Buscamos la empresa de Byron específicamente
        $empresaB = $this->db->table('empresas')
            ->where('nombreempresa', 'COLEGIO ADA BYRON')
            ->get()->getRow();

        // Si no existe Byron, usamos el ID 2 (o el que sepas que tiene en tu BD)
        $idByron = $empresaB ? $empresaB->id : 2;

        // 2. Solo unas cuantas áreas de ejemplo
        $ejemplos = [
            ['nombre' => 'ATENCIÓN AL CLIENTE', 'idempresa' => $idByron, 'activo' => true],
            ['nombre' => 'BIBLIOTECA', 'idempresa' => $idByron, 'activo' => true],
            ['nombre' => 'GERENCIA GENERAL', 'idempresa' => $idByron, 'activo' => true],
            ['nombre' => 'SOPORTE TECNICO', 'idempresa' => $idByron, 'activo' => true],
        ];

        // 3. Insertar solo los ejemplos
        $this->db->table('areas')->insertBatch($ejemplos);
    }
}