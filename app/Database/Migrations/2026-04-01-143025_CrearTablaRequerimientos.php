<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaRequerimiento extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TYPE prioridad_enum AS ENUM ('Baja', 'Media', 'Alta', 'Urgente')");

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'auto_increment' => true,
            ],
            'idempresa' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'idservicio' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'area' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'objetivo_comunicacion' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'tipo_requerimiento' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'canales_difusion' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'publico_objetivo' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'tiene_materiales' => [
                'type' => 'BOOLEAN',
                'null' => false,
            ],
            'formatos_solicitados' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'formato_otros' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
                'default'    => '',
            ],
            'fecharequerida' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'prioridad' => [
                'type'    => 'prioridad_enum',
                'null'    => false,
                'default' => 'Media',
            ],
            'fechacreacion' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idempresa',  'empresas',  'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idservicio', 'servicios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('requerimiento');
    }

    public function down()
    {
        $this->forge->dropTable('requerimiento');
        $this->db->query("DROP TYPE IF EXISTS prioridad_enum");
    }
}