<?php

namespace App\Models;

use CodeIgniter\Model;

class ResponsablesEmpresaModel extends Model
{
    protected $table      = 'responsables_empresa';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['idusuario', 'idempresa', 'fecha_inicio', 'fecha_fin', 'estado'];

    /**
     * Asigna un usuario como responsable de una empresa.
     */
    public function asignarResponsable(int $idUsuario, int $idEmpresa): void
    {
        $this->insert([
            'idusuario'    => $idUsuario,
            'idempresa'    => $idEmpresa,
            'fecha_inicio' => date('Y-m-d H:i:s'),
            'estado'       => 'activo',
        ]);
    }

    /**
     * Obtiene el registro activo de responsable para un usuario-empresa.
     */
    public function obtenerActivoPorUsuario(int $idUsuario): ?array
    {
        return $this->db->table('responsables_empresa re')
            ->select('re.*, emp.nombreempresa, emp.ruc')
            ->join('empresas emp', 'emp.id = re.idempresa')
            ->where('re.idusuario', $idUsuario)
            ->where('re.estado', 'activo')
            ->get()->getRowArray();
    }

    /**
     * Obtiene el historial de responsables de una empresa.
     */
    public function historialPorEmpresa(int $idEmpresa): array
    {
        return $this->db->table('responsables_empresa re')
            ->select('re.*, u.nombre, u.apellidos, u.correo')
            ->join('usuarios u', 'u.id = re.idusuario')
            ->where('re.idempresa', $idEmpresa)
            ->orderBy('re.fecha_inicio', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Finaliza la responsabilidad actual (o crea el primer registro de historial) y prepara la transición.
     */
    public function reasignar(?int $idRegistroActual, int $idNuevoUsuario, int $idEmpresa, ?int $idUsuarioAnterior = null): bool
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Manejar el responsable anterior
            if (!empty($idRegistroActual)) {
                // Si ya tenía registro en esta tabla, lo cerramos
                $this->update($idRegistroActual, [
                    'fecha_fin' => date('Y-m-d H:i:s'),
                    'estado'    => 'inactivo',
                ]);
            } else if (!empty($idUsuarioAnterior)) {
                // Si NO tenía registro en esta tabla (era virtual por idarea), 
                // creamos su entrada histórica para que aparezca en la línea de tiempo
                $this->insert([
                    'idusuario'    => $idUsuarioAnterior,
                    'idempresa'    => $idEmpresa,
                    'fecha_inicio' => date('Y-m-d H:i:s'), // O podrías usar created_at del usuario
                    'fecha_fin'    => date('Y-m-d H:i:s'),
                    'estado'       => 'inactivo',
                ]);
            }

            // 2. Insertar nuevo responsable activo
            $this->insert([
                'idusuario'    => $idNuevoUsuario,
                'idempresa'    => $idEmpresa,
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'estado'       => 'activo',
            ]);

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
}