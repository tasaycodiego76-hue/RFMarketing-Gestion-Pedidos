<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaHistorialAsignaciones extends Migration
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
            'idempleado_anterior' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'idempleado' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'idadmin' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'fecha_asignacion' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'fecha_fin' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'motivo_cambio' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idatencion',          'atencion', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idempleado',          'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idempleado_anterior', 'usuarios', 'id', 'SET NULL',  'CASCADE');
        $this->forge->addForeignKey('idadmin',             'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('historial_asignaciones');
    }

    public function down()
    {
        $this->forge->dropTable('historial_asignaciones');
    }
}