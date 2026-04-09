<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        $this->call('EmpresasSeeder');
        $this->call('AreaSeeder');
        $this->call('ServiciosSeeder');
        $this->call('AreasAgenciaSeeder');
        $this->call('UsuariosSeeder');
        $this->call('ResponsablesEmpresaSeeder');
        $this->call('RequerimientoSeeder');
        $this->call('AtencionSeeder');
        $this->call('ArchivosSeeder');
        $this->call('TrackingSeeder');
        $this->call('HistorialAsignacionesSeeder');
        $this->call('RetroalimentacionSeeder');

    }
}