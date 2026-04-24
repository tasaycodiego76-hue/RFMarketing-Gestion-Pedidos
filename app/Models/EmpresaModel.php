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

    /**
     * Funcion que devuelve todas las empresas activas
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function listarActivas(): array
    {
        return $this->where('estado', true)->findAll();
    }

    /**
     * Funcion que devuelve los colores personalizables que se utilizan para mostrar cada empresa (KANBAN)
     * @var array
     */
    private array $colores = [
        '#FF6B6B',
        '#FFD93D',
        '#6BCB77',
        '#4D96FF',
        '#C77DFF',
        '#FF9F43',
    ];


    /**
     * Funcion que Obtiene las Empresas Activas y con Atencion Model, calcula las Estadisticas de cada empresa (Requerimientos)
     * @return array<array<bool|float|int|object|string|null>|object>
     */
    public function obtenerConStatsActivas(): array
    {
        $empresas = $this->where('estado', true)->findAll();
        $atencionModel = new AtencionModel();

        foreach ($empresas as $i => &$empresa) {
            $color = $this->colores[$i % count($this->colores)];
            $empresa['color'] = $color;
            $empresa['inicial'] =
                strtoupper(substr($empresa['nombreempresa'], 0, 1));
            $empresa['por_aprobar'] =
                $atencionModel->contarPorEstadoEmpresa(
                    'pendiente_sin_asignar',
                    $empresa['id']
                );
            $empresa['activos'] =
                $atencionModel->contarActivosEmpresa($empresa['id']);
            $empresa['en_revision'] = $atencionModel->contarPorEstadoEmpresa('en_revision', $empresa['id']);
            $empresa['completados'] =
                $atencionModel->contarPorEstadoEmpresa('finalizado', $empresa['id']);
        }

        return $empresas;
    }
}