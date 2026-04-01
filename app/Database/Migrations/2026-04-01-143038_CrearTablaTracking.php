<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaTracking extends Migration
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
            'idusuario' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'accion' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'estado' => [
                'type' => 'estado_atencion_enum',
                'null' => false,
            ],
            'fecha_registro' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idatencion', 'atencion', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idusuario',  'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('tracking');
    }

    public function down()
    {
        $this->forge->dropTable('tracking');
    }
}