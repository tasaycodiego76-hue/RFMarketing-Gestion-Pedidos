<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaUsuarios extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TYPE rol_enum     AS ENUM ('administrador', 'empleado', 'cliente')");
        $this->db->query("CREATE TYPE tipodoc_enum AS ENUM ('DNI', 'RUC', 'CE')");

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
            'apellidos' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => '',
            ],
            'tipodoc' => [
                'type' => 'tipodoc_enum',
                'null' => true,
            ],
            'numerodoc' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'rol' => [
                'type' => 'rol_enum',
                'null' => false,
            ],
            'idarea_agencia' => [
                'type' => 'BIGINT',
                'null' => true,  
            ],
            'idarea' => [
            'type' => 'BIGINT',
            'null' => true,
            ],
            'esresponsable' => [
                'type'    => 'BOOLEAN',
                'null'    => false,
                'default' => false,
            ],
            'estado' => [
                'type'    => 'BOOLEAN',
                'null'    => false,
                'default' => true,
            ],
            'fechacreacion' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('correo');
        $this->forge->addUniqueKey('usuario');
        $this->forge->addForeignKey('idarea_agencia', 'areas_agencia', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('idarea', 'areas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        $this->forge->dropTable('usuarios');
        $this->db->query("DROP TYPE IF EXISTS rol_enum");
        $this->db->query("DROP TYPE IF EXISTS tipodoc_enum");
    }
}