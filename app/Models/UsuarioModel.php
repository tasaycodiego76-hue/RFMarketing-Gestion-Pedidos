<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'usuarios';
    // Nombre de la columna clave primaria (ID único del usuario)
    protected $primaryKey = 'id';

    // Tipo de datos retornado: 'array' , 'object', o entidad personalizada
    protected $returnType = 'array';

    // Campos permitidos para operaciones de creación y actualización
    // Esto previene inyección de datos no autorizados
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
}