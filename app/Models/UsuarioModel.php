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

    /* TODAS LAS VISTAS (LGOIN) */

    /**
     * Valida si las credenciales ingresadas son correctas
     * @param string $usuario
     * @param string $clave
     * @return object|null
     */
    public function verificarCredenciales(string $usuario, string $clave): ?object
    {
        //Verificar si existe el Usuario
        $row = $this->where('usuario', $usuario)->first();

        //Asumimos que el usuario existe... entonces validamos la clave
        if ($row && password_verify($clave, $row['clave'])) {
            return (object) $row;
        }
        //El usuario NO existe, retornamos null
        return null;
    }

    /* ADMINISTRADOR */

    /**
     * Devuelve todos los usuarios con el nombre de su área resuelta
     * y la empresa para responsables de áreas de clientes
     * @param mixed $search
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
            // Lógica para etiquetas visuales de rol
            if ($u['rol'] === 'cliente' && !empty($u['idarea'])) {
                $u['rol_visual'] = 'Responsable';
            } else {
                $u['rol_visual'] = ucfirst($u['rol']);
            }

            // Formatear área/empresa para mostrar en la tabla
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
     * Funcion que obtiene un usuario específico resolviendo su área de la agencia
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
     * Busca los detalles completos de un usuario para mostrarlos en la plantilla (Header/Sidebar)
     * @param mixed $idUsuario
     * @return array<bool|float|int|object|string|null>|object|null
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
     * Funcion que Obtiene solo el ID de la empresa a la que pertenece un usuario cliente.
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

    /* RESPONSABLE_AREA */

    /**
     * Funcion que Filtra empleados activos que pertenecen a un área específica de la agencia.
     * @param int $idAreaAgencia
     * @return array
     */
    public function obtenerAsignablesPorAreaAgencia(int $idAreaAgencia): array
    {
        // Definimos la consulta con placeholders para mayor seguridad
        $sql = "
            SELECT id, nombre, apellidos, correo, esresponsable, rol, estado, idarea_agencia
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
     * Funcion que Busca un empleado específico validando que esté activo y en su área.
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
