<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaArchivos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
            ],
            'idatencion' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'idrequerimiento' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'ruta' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'tamano' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'fechasubida' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idatencion', 'atencion', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('idrequerimiento', 'requerimiento', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('archivos');
    }

    public function down()
    {
        $this->forge->dropTable('archivos');
        helper('filesystem');
        delete_files(WRITEPATH . 'uploads/requerimientos/');
    }
}