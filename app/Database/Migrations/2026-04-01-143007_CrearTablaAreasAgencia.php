<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaAreasAgencia extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'null' => false,
                'default' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('areas_agencia');
    }

    public function down()
    {
        $this->forge->dropTable('areas_agencia');
    }
}