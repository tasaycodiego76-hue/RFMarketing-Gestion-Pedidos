<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaAreas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'auto_increment' => true,
            ],
            'idempresa' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addForeignKey('idempresa', 'empresas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('areas');
    }

    public function down()
    {
        $this->forge->dropTable('areas');
    }
}