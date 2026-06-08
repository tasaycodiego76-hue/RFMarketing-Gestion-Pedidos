<?php

namespace App\Services;

use Pusher\Pusher;

class PusherService
{
    private Pusher $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('PUSHER_KEY'),
            env('PUSHER_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_CLUSTER'), 'useTLS' => true]
        );
    }

    public function emitir(string $canal, string $evento, array $data): void
    {
        $this->pusher->trigger($canal, $evento, $data);
    }

    /**
     * Notifica a TODOS los roles cuando un pedido cambia de estado.
     */
    public function notificarCambioEstado(int $idAtencion, string $estadoNuevo, array $extra = []): void
    {
        try {
            $db = \Config\Database::connect();
            $atencion = $db->query("
                SELECT r.idusuarioempresa, a.idarea_agencia 
                FROM atencion a 
                JOIN requerimiento r ON r.id = a.idrequerimiento 
                WHERE a.id = ?
            ", [$idAtencion])->getRowArray();

$data = array_merge([
    'id'             => $idAtencion,
    'estado_nuevo'   => $estadoNuevo,
    'idarea_agencia' => $atencion['idarea_agencia'] ?? null,
], $extra);

            // Broadcast a los 3 canales de roles internos
            $this->pusher->trigger(
                ['kanban-admin', 'kanban-empleados', 'kanban-responsables'],
                'solicitud.actualizada',
                $data
            );

            // Canal personal del cliente
            $clienteId = $atencion['idusuarioempresa'] ?? null;
            if ($clienteId) {
                $this->emitir("cliente-{$clienteId}", 'solicitud.actualizada', $data);
            }
        } catch (\Exception $e) {
            log_message('error', 'Pusher notificarCambioEstado: ' . $e->getMessage());
        }
    }

    /**
     * Notifica que se creó un nuevo pedido (evento 'solicitud.nueva').
     */
    public function notificarNuevaSolicitud(int $idAtencion, array $datosExtra): void
    {
        try {
            $data = array_merge(['id' => $idAtencion], $datosExtra);
            
            $this->pusher->trigger(
                ['kanban-admin', 'kanban-responsables', 'kanban-empleados'],
                'solicitud.nueva',
                $data
            );
        } catch (\Exception $e) {
            log_message('error', 'Pusher notificarNuevaSolicitud: ' . $e->getMessage());
        }
    }
}