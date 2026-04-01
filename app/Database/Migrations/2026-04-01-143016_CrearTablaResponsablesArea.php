<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaResponsablesArea extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TYPE estado_resp_area_enum AS ENUM ('activo', 'inactivo')");

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'auto_increment' => true,
            ],
            'idarea' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'idusuario' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'fecha_inicio' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'fecha_fin' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'estado' => [
                'type'    => 'estado_resp_area_enum',
                'null'    => true,
                'default' => 'activo',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idarea',    'areas',    'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idusuario', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('responsables_area');
    }

    public function down()
    {
        $this->forge->dropTable('responsables_area');
        $this->db->query("DROP TYPE IF EXISTS estado_resp_area_enum");
    }
}