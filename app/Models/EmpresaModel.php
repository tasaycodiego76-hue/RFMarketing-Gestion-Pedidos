<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table      = 'empresas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombreempresa',
        'ruc',
        'correo',
        'telefono',
        'estado',
    ];
    public function listarActivas(): array
  {
      return $this->where('estado', true)->findAll();
  }

    private array $colores = [
    '#FF6B6B', '#FFD93D', '#6BCB77',
    '#4D96FF', '#C77DFF', '#FF9F43',
];

public function obtenerConStats(): array
{
    $empresas     = $this->findAll();
    $atencionModel = new \App\Models\AtencionModel();

    foreach ($empresas as $i => &$empresa) {
        $color = $this->colores[$i % count($this->colores)];
        $empresa['color']       = $color;
        $empresa['inicial']     = strtoupper(substr($empresa['nombreempresa'], 0, 1));
        $empresa['por_aprobar'] = $atencionModel->contarPorEstadoEmpresa('pendiente_sin_asignar', $empresa['id']);
        $empresa['activos'] = $atencionModel->contarActivosEmpresa($empresa['id']);
        $empresa['completados'] = $atencionModel->contarPorEstadoEmpresa('finalizado',             $empresa['id']);
    }

    return $empresas;
}
public function obtenerConStatsActivas(): array
  {
      $empresas     = $this->where('estado', true)->findAll();
      $atencionModel = new \App\Models\AtencionModel();

      foreach ($empresas as $i => &$empresa) {
          $color = $this->colores[$i % count($this->colores)];
          $empresa['color']       = $color;
          $empresa['inicial']     =
  strtoupper(substr($empresa['nombreempresa'], 0, 1));
          $empresa['por_aprobar'] =
  $atencionModel->contarPorEstadoEmpresa('pendiente_sin_asignar',
  $empresa['id']);
          $empresa['activos'] =
  $atencionModel->contarActivosEmpresa($empresa['id']);
          $empresa['completados'] =
  $atencionModel->contarPorEstadoEmpresa('finalizado', $empresa['id']);
      }

      return $empresas;
  }
}