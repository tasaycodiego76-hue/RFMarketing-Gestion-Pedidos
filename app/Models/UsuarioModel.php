<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombre',
        'apellidos',
        'correo',
        'telefono',
        'tipodoc',
        'numerodoc',
        'usuario',
        'clave',
        'rol',
        'idarea',
        'idarea_agencia',
        'esresponsable',
        'estado',
        'fechacreacion'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'fechacreacion';
    protected $updatedField = '';

    /**
     * Devuelve todos los usuarios con el nombre de su área resuelta
     * (area_agencia tiene prioridad sobre area).
     */
   
  /**
   * Devuelve todos los usuarios con el nombre de su área resuelta
   * y la empresa para responsables de áreas de clientes.
   */
  public function listarConArea(): array
  {
      $result = $this->db->table('usuarios u')
          ->select('u.*')
          ->select("COALESCE(aa.nombre, ae.nombre, '-') as area_nombre")
          ->select("emp.nombreempresa as empresa_nombre")
          ->join('areas_agencia aa', 'aa.id = u.idarea_agencia', 'left')
          ->join('areas ae', 'ae.id = u.idarea', 'left')
          ->join('empresas emp', 'emp.id = ae.idempresa', 'left')
          ->orderBy('u.rol', 'ASC')
          ->orderBy('u.nombre', 'ASC')
          ->get()->getResultArray();

     
      foreach ($result as &$u) {
          if ($u['rol'] === 'cliente' && !empty($u['idarea'])) {
              $u['rol_visual'] = 'Responsable';
          } else {
              $u['rol_visual'] = ucfirst($u['rol']);
          }

          // Formatear área/empresa para mostrar
          if (!empty($u['area_nombre']) && $u['area_nombre'] !== '-') {
              if (!empty($u['empresa_nombre'])) {
                  // Es área de cliente - mostrar: Área (Empresa)
                  $u['area_completa'] = $u['area_nombre'] . ' (' . $u['empresa_nombre'] . ')';
              } else {
                  // Es área de agencia
                  $u['area_completa'] = $u['area_nombre'];
              }
          } else {
              $u['area_completa'] = '-';
          }
      }

      return $result;
  }

    /**
     * Devuelve un usuario por ID junto con el nombre de su área de agencia.
     */
    public function obtenerConArea(int $id): ?array
    {
        return $this->db->table('usuarios u')
            ->select('u.*, a.nombre as area_nombre')
            ->join('areas_agencia a', 'a.id = u.idarea_agencia', 'left')
            ->where('u.id', $id)
            ->get()->getRowArray() ?: null;
    }

    /**
     * Funcion que Busca los Detalles de un Usuario para Mostrar su Info en la PLantilla
     * @param mixed $idUsuario ID Filtra Datos
     * @return array
     */
    public function getDetalleUsuario($idUsuario)
    {
        return $this->select('usuarios.*, areas.nombre as nombre_area, empresas.nombreempresa as nombre_empresa')
            ->join('areas', 'areas.id = usuarios.idarea', 'left')
            ->join('empresas', 'empresas.id = areas.idempresa', 'left')
            ->where('usuarios.id', $idUsuario)
            ->first(); // Retorna una sola fila como array
    }

    /**
     * Obtiene la empresa vinculada al usuario a través de su área
     * Puede ser Usada para Procesos de Guardado (Insert / POST)
     * @param mixed $usuarioId
     * @return array|null
     */
    public function getDatosEmpresaUsuario($usuarioId)
    {
        $sql = "
            SELECT 
                u.id, 
                a.idempresa
            FROM usuarios u
            INNER JOIN areas a ON a.id = u.idarea
            WHERE u.id = ?
        ";

        return $this->db->query($sql, [$usuarioId])->getRowArray();
    }
}