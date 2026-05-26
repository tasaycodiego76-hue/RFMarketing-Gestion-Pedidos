<?php
namespace App\Controllers;

class SetupController extends \CodeIgniter\Controller
{
    public function index()
    {
        if ($this->request->getGet('key') !== 'rf2026secret') {
            die('No autorizado');
        }

        $migrate = \Config\Services::migrations();
        $seeder = \Config\Database::seeder();

        try {
            $migrate->latest();
            echo "✅ Migraciones OK<br>";

            $seeder->call('DatabaseSeeder');
            echo "✅ Seeders OK<br>";
        } catch (\Throwable $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}