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
     * y la empresa para responsables de áreas de clientes
     * @return array
     */
    public function listarConArea(?string $search = null): array
    {
        $builder = $this->db->table('usuarios u')
            ->select('u.*')
            ->select("COALESCE(aa.nombre, ae.nombre, '-') as area_nombre")
            ->select("emp.nombreempresa as empresa_nombre")
            ->join('areas_agencia aa', 'aa.id = u.idarea_agencia', 'left')
            ->join('areas ae', 'ae.id = u.idarea', 'left')
            ->join('empresas emp', 'emp.id = ae.idempresa', 'left');

        if (!empty($search)) {
            $s = $this->db->escapeLikeString($search);
            $builder->groupStart()
                ->where("u.nombre ILIKE '%$s%'", null, false)
                ->orWhere("u.apellidos ILIKE '%$s%'", null, false)
                ->orWhere("u.correo ILIKE '%$s%'", null, false)
                ->orWhere("u.usuario ILIKE '%$s%'", null, false)
                ->orWhere("u.rol::text ILIKE '%$s%'", null, false)
                ->orWhere("aa.nombre ILIKE '%$s%'", null, false)
                ->orWhere("ae.nombre ILIKE '%$s%'", null, false)
                ->orWhere("emp.nombreempresa ILIKE '%$s%'", null, false)
                ->groupEnd();
        }

        $result = $builder->orderBy('area_nombre', 'ASC')
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
     * Devuelve un usuario por ID junto con el nombre de su área de agencia
     * @param int $id
     * @return array|null
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
        return $this->select('usuarios.*, areas.nombre as nombre_area, areas_agencia.nombre as nombre_areaagencia, empresas.nombreempresa as nombre_empresa')
            ->join('areas', 'areas.id = usuarios.idarea', 'left')
            ->join('areas_agencia', 'areas_agencia.id = usuarios.idarea_agencia', 'left')
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

    /**
     * Devuelve usuarios asignables del área (empleados activos)
     * @param int $idAreaAgencia
     * @return array
     */
    public function obtenerAsignablesPorAreaAgencia(int $idAreaAgencia): array
    {
        // Definimos la consulta con placeholders para mayor seguridad
        $sql = "
            SELECT id, nombre, apellidos, esresponsable, rol, estado, idarea_agencia
            FROM usuarios
            WHERE idarea_agencia = ?
                AND rol = 'empleado'
                AND (estado = true OR estado = 't' OR estado = '1') 
            ORDER BY esresponsable DESC, nombre ASC";
        // Ejecutamos la consulta pasando el ID del área
        $query = $this->db->query($sql, [$idAreaAgencia]);
        // Retornamos el array con los resultados
        return $query->getResultArray();
    }

    /**
     * Busca un empleado específico activo que pertenezca a un área determinada
     * @param int $idUsuario
     * @param int $idAreaAgencia
     * @return array|null
     */
    public function obtenerEmpleadoAsignable(int $idUsuario, int $idAreaAgencia): ?array
    {
        $sql = "
            SELECT id, nombre, apellidos 
            FROM usuarios 
            WHERE id = ? 
                AND rol = 'empleado' 
                AND (estado = true OR estado = 't' OR estado = '1') 
                AND idarea_agencia = ? 
            LIMIT 1";
        $query = $this->db->query($sql, [$idUsuario, $idAreaAgencia]);
        return $query->getRowArray();
    }
}