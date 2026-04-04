<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'activo' => [
                'type'    => 'BOOLEAN',
                'null'    => false,
                'default' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('servicios');
    }

    public function down()
    {
        $this->forge->dropTable('servicios');
    }
}