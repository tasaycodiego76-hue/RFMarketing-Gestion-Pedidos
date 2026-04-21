<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaAtencion extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TYPE estado_atencion_enum AS ENUM (
            'pendiente_sin_asignar',
            'pendiente_asignado',
            'en_proceso',
            'en_revision',
            'finalizado',
            'cancelado'
        )");

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
            ],
            'idrequerimiento' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'idadmin' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'idempleado' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'idarea_agencia' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'idservicio' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'servicio_personalizado' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'titulo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'prioridad' => [
                'type' => 'prioridad_enum',
                'null' => false,
                'default' => 'Media',
            ],
            'estado' => [
                'type' => 'estado_atencion_enum',
                'null' => false,
                'default' => 'pendiente_sin_asignar',
            ],
            'num_modificaciones' => [
                'type' => 'INTEGER',
                'null' => false,
                'default' => 0,
            ],
            'observacion_revision' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'url_entrega' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => '',
            ],
            'fechainicio' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'fechafin' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'fechacreacion' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'fechacompletado' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'cancelacionmotivo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fechacancelacion' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idrequerimiento', 'requerimiento', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idadmin', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idempleado', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('idservicio', 'servicios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idarea_agencia', 'areas_agencia', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('atencion');
    }

    public function down()
    {
        $this->forge->dropTable('atencion');
        $this->db->query("DROP TYPE IF EXISTS estado_atencion_enum");
    }
}