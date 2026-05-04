<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\AtencionModel;

class EmpresaModel extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombreempresa',
        'ruc',
        'correo',
        'telefono',
        'estado',
    ];

    /* ADMINISTRADOR */

    /**
     * Obtiene todas las empresas marcadas como activas
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarActivas(): array
    {
        return $this->where('estado', true)->findAll();
    }

    /**
     * Paleta de colores predefinida para identificar visualmente a las empresas
     * @var array
     */
    private array $colores = [
        '#FF6B6B', // Rojo suave
        '#FFD93D', // Amarillo
        '#6BCB77', // Verde
        '#4D96FF', // Azul
        '#C77DFF', // Púrpura
        '#FF9F43', // Naranja
    ];

    /**
     * Obtiene empresas activas junto con sus métricas de pedidos en tiempo real
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function obtenerConStatsActivas(): array
    {
        $empresas = $this->where('estado', true)->findAll();
        $atencionModel = new AtencionModel();

        foreach ($empresas as $i => &$empresa) {
            // Asignación de color estético
            $color = $this->colores[$i % count($this->colores)];
            $empresa['color'] = $color;
            
            // Inicial para el logo/avatar
            $empresa['inicial'] = strtoupper(substr($empresa['nombreempresa'], 0, 1));
            
            // Conteos de estados (Lógica cruzada con AtencionModel)
            $empresa['por_aprobar'] = $atencionModel->contarPorEstadoEmpresa('pendiente_sin_asignar', $empresa['id']);
            $empresa['activos'] = $atencionModel->contarActivosEmpresa($empresa['id']);
            $empresa['en_revision'] = $atencionModel->contarPorEstadoEmpresa('en_revision', $empresa['id']);
            $empresa['completados'] = $atencionModel->contarPorEstadoEmpresa('finalizado', $empresa['id']);
        }

        return $empresas;
    }
}