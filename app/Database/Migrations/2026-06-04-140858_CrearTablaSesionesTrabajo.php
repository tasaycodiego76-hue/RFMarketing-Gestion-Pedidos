<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaSesionesTrabajo extends Migration
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
                'null' => true,
            ],
            'idusuario' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'hora_inicio' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
            ],
            'hora_fin' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
            ],
            'motivo_pausa' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idatencion', 'atencion', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('idusuario', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('sesiones_trabajo');
    }

    public function down()
    {
        $this->forge->dropTable('sesiones_trabajo');
    }
}
