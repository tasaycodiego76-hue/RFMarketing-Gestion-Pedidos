<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaRetroalimentacion extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'auto_increment' => true,
            ],
            'idatencion' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'idevaluador' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'contenido' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'fecha' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idatencion',  'atencion', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idevaluador', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('retroalimentacion');
    }

    public function down()
    {
        $this->forge->dropTable('retroalimentacion');
    }
}