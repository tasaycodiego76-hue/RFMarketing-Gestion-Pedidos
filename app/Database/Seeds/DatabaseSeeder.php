<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Tablas Base (Sin dependencias)
        $this->call('EmpresasSeeder');
        $this->call('AreaSeeder');
        $this->call('ServiciosSeeder');
        
        // 2. Tablas con FK simples
        $this->call('UsuariosSeeder');
        $this->call('RequerimientoSeeder');

        // 3. Tablas con dependencias múltiples
        $this->call('ResponsableAreaSeeder');
        $this->call('AtencionSeeder');

        // 4. Tablas de historial y archivos
        $this->call('ArchivosSeeder');
        $this->call('TrackingSeeder');
        $this->call('HistorialAsignacionesSeeder');
        $this->call('RetroalimentacionSeeder');
    }
}