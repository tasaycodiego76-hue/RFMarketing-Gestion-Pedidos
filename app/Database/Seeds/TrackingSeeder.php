<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TrackingSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('TRUNCATE TABLE tracking RESTART IDENTITY CASCADE');

        $data = [
            // ATENCIÓN 1
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-01 09:05:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-01 10:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JESUS DE LA CRUZ GARCÍA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-01 11:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 12,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-02 09:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 12,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-04 10:00:00'
            ],
            [
                'idatencion' => 1,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-04 11:30:00'
            ],
            // ATENCIÓN 2
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-01 10:05:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-01 11:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: RODRIGO ALEXANDER FELIX HUAMAN. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-01 12:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 2,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-02 10:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 2,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-04 14:00:00'
            ],
            [
                'idatencion' => 2,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-04 15:00:00'
            ],
            // ATENCIÓN 3
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-01 11:05:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-01 12:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NICOL MICHELLE GUERRERO TORREALBA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-01 13:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 6,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-02 11:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 6,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-04 16:00:00'
            ],
            [
                'idatencion' => 3,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-04 17:00:00'
            ],
            // ATENCIÓN 4
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-02 09:05:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-02 10:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NAYRU GOMEZ MAGALLANES. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-02 11:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 11,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-03 09:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 11,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-05 10:00:00'
            ],
            [
                'idatencion' => 4,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-05 12:00:00'
            ],
            // ATENCIÓN 5
            [
                'idatencion' => 5,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-02 10:05:00'
            ],
            [
                'idatencion' => 5,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-02 11:00:00'
            ],
            [
                'idatencion' => 5,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JONATHAN MEDINA CAMPOS. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-02 12:00:00'
            ],
            [
                'idatencion' => 5,
                'idusuario' => 9,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-03 10:00:00'
            ],
            [
                'idatencion' => 5,
                'idusuario' => 9,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-05 15:00:00'
            ],
            [
                'idatencion' => 5,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-05 17:30:00'
            ],
            // ATENCIÓN 6
            [
                'idatencion' => 6,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-03 09:05:00'
            ],
            [
                'idatencion' => 6,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 10:00:00'
            ],
            [
                'idatencion' => 6,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: RODRIGO ALEXANDER FELIX HUAMAN. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 11:00:00'
            ],
            [
                'idatencion' => 6,
                'idusuario' => 2,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-04 09:00:00'
            ],
            [
                'idatencion' => 6,
                'idusuario' => 2,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-06 10:00:00'
            ],
            [
                'idatencion' => 6,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-06 11:00:00'
            ],
            // ATENCIÓN 7
            [
                'idatencion' => 7,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-03 10:05:00'
            ],
            [
                'idatencion' => 7,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 11:00:00'
            ],
            [
                'idatencion' => 7,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: FABRIZIO RAMOS TIPISMANA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 12:00:00'
            ],
            [
                'idatencion' => 7,
                'idusuario' => 10,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-04 10:00:00'
            ],
            [
                'idatencion' => 7,
                'idusuario' => 10,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-06 14:00:00'
            ],
            [
                'idatencion' => 7,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-06 15:00:00'
            ],
            // ATENCIÓN 8
            [
                'idatencion' => 8,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-03 11:05:00'
            ],
            [
                'idatencion' => 8,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 12:00:00'
            ],
            [
                'idatencion' => 8,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: MARIA PIA CASTILLA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 13:00:00'
            ],
            [
                'idatencion' => 8,
                'idusuario' => 4,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-04 11:00:00'
            ],
            [
                'idatencion' => 8,
                'idusuario' => 4,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-06 16:00:00'
            ],
            [
                'idatencion' => 8,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-06 17:00:00'
            ],
            // ATENCIÓN 9
            [
                'idatencion' => 9,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-03 14:05:00'
            ],
            [
                'idatencion' => 9,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 15:00:00'
            ],
            [
                'idatencion' => 9,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NOEMI TORRES TINEDO. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-03 16:00:00'
            ],
            [
                'idatencion' => 9,
                'idusuario' => 3,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-04 14:00:00'
            ],
            [
                'idatencion' => 9,
                'idusuario' => 3,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-06 18:00:00'
            ],
            [
                'idatencion' => 9,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-06 18:30:00'
            ],
            // ATENCIÓN 10
            [
                'idatencion' => 10,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-04 09:05:00'
            ],
            [
                'idatencion' => 10,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-04 10:00:00'
            ],
            [
                'idatencion' => 10,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JOSE GUERRA CHACÓN. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-04 11:00:00'
            ],
            [
                'idatencion' => 10,
                'idusuario' => 8,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-05 09:00:00'
            ],
            [
                'idatencion' => 10,
                'idusuario' => 8,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-07 10:00:00'
            ],
            [
                'idatencion' => 10,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-07 11:30:00'
            ],
            // ATENCIÓN 11
            [
                'idatencion' => 11,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-04 10:05:00'
            ],
            [
                'idatencion' => 11,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-04 11:00:00'
            ],
            [
                'idatencion' => 11,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JESUS DE LA CRUZ GARCÍA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-04 12:00:00'
            ],
            [
                'idatencion' => 11,
                'idusuario' => 12,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-05 10:00:00'
            ],
            [
                'idatencion' => 11,
                'idusuario' => 12,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-07 14:00:00'
            ],
            [
                'idatencion' => 11,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-07 15:00:00'
            ],
            // ATENCIÓN 12
            [
                'idatencion' => 12,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-04 11:05:00'
            ],
            [
                'idatencion' => 12,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-04 12:00:00'
            ],
            [
                'idatencion' => 12,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: RODRIGO ALEXANDER FELIX HUAMAN. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-04 13:00:00'
            ],
            [
                'idatencion' => 12,
                'idusuario' => 2,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-05 11:00:00'
            ],
            [
                'idatencion' => 12,
                'idusuario' => 2,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-07 16:00:00'
            ],
            [
                'idatencion' => 12,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-07 17:00:00'
            ],
            // ATENCIÓN 13
            [
                'idatencion' => 13,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-05 09:05:00'
            ],
            [
                'idatencion' => 13,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-05 10:00:00'
            ],
            [
                'idatencion' => 13,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JHENINFER MIRELLI CCOICCA ALVAREZ. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-05 11:00:00'
            ],
            [
                'idatencion' => 13,
                'idusuario' => 5,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-06 09:00:00'
            ],
            [
                'idatencion' => 13,
                'idusuario' => 5,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-08 10:00:00'
            ],
            [
                'idatencion' => 13,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-08 12:00:00'
            ],
            // ATENCIÓN 14
            [
                'idatencion' => 14,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-05 10:05:00'
            ],
            [
                'idatencion' => 14,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-05 11:00:00'
            ],
            [
                'idatencion' => 14,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: FABRIZIO RAMOS TIPISMANA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-05 12:00:00'
            ],
            [
                'idatencion' => 14,
                'idusuario' => 10,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-06 10:00:00'
            ],
            [
                'idatencion' => 14,
                'idusuario' => 10,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-08 15:00:00'
            ],
            [
                'idatencion' => 14,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-08 17:00:00'
            ],
            // ATENCIÓN 15
            [
                'idatencion' => 15,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-05 11:05:00'
            ],
            [
                'idatencion' => 15,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-05 12:00:00'
            ],
            [
                'idatencion' => 15,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: SONIA ALEJANDRA TELLO ROJAS. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-05 13:00:00'
            ],
            [
                'idatencion' => 15,
                'idusuario' => 7,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-06 11:00:00'
            ],
            [
                'idatencion' => 15,
                'idusuario' => 7,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-08 17:30:00'
            ],
            [
                'idatencion' => 15,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-08 18:30:00'
            ],
            // ATENCIÓN 16
            [
                'idatencion' => 16,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-08 09:05:00'
            ],
            [
                'idatencion' => 16,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 10:00:00'
            ],
            [
                'idatencion' => 16,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: RODRIGO ALEXANDER FELIX HUAMAN. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 11:00:00'
            ],
            [
                'idatencion' => 16,
                'idusuario' => 2,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-09 09:00:00'
            ],
            [
                'idatencion' => 16,
                'idusuario' => 2,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-11 10:00:00'
            ],
            [
                'idatencion' => 16,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-11 11:00:00'
            ],
            // ATENCIÓN 17
            [
                'idatencion' => 17,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-08 10:05:00'
            ],
            [
                'idatencion' => 17,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 11:00:00'
            ],
            [
                'idatencion' => 17,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NAYRU GOMEZ MAGALLANES. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 12:00:00'
            ],
            [
                'idatencion' => 17,
                'idusuario' => 11,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-09 10:00:00'
            ],
            [
                'idatencion' => 17,
                'idusuario' => 11,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-11 14:00:00'
            ],
            [
                'idatencion' => 17,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-11 15:00:00'
            ],
            // ATENCIÓN 18
            [
                'idatencion' => 18,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-08 11:05:00'
            ],
            [
                'idatencion' => 18,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 12:00:00'
            ],
            [
                'idatencion' => 18,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NICOL MICHELLE GUERRERO TORREALBA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 13:00:00'
            ],
            [
                'idatencion' => 18,
                'idusuario' => 6,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-09 11:00:00'
            ],
            [
                'idatencion' => 18,
                'idusuario' => 6,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-11 16:00:00'
            ],
            [
                'idatencion' => 18,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-11 17:00:00'
            ],
            // ATENCIÓN 19
            [
                'idatencion' => 19,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-08 14:05:00'
            ],
            [
                'idatencion' => 19,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 15:00:00'
            ],
            [
                'idatencion' => 19,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JESUS DE LA CRUZ GARCÍA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-08 16:00:00'
            ],
            [
                'idatencion' => 19,
                'idusuario' => 12,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-09 14:00:00'
            ],
            [
                'idatencion' => 19,
                'idusuario' => 12,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-11 18:00:00'
            ],
            [
                'idatencion' => 19,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-11 18:30:00'
            ],
            // ATENCIÓN 20
            [
                'idatencion' => 20,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-09 09:05:00'
            ],
            [
                'idatencion' => 20,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-09 10:00:00'
            ],
            [
                'idatencion' => 20,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: SONIA ALEJANDRA TELLO ROJAS. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-09 11:00:00'
            ],
            [
                'idatencion' => 20,
                'idusuario' => 7,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-10 09:00:00'
            ],
            [
                'idatencion' => 20,
                'idusuario' => 7,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-12 10:00:00'
            ],
            [
                'idatencion' => 20,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-12 12:00:00'
            ],
            // ATENCIÓN 21
            [
                'idatencion' => 21,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-09 10:05:00'
            ],
            [
                'idatencion' => 21,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-09 11:00:00'
            ],
            [
                'idatencion' => 21,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: FABRIZIO RAMOS TIPISMANA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-09 12:00:00'
            ],
            [
                'idatencion' => 21,
                'idusuario' => 10,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-10 10:00:00'
            ],
            [
                'idatencion' => 21,
                'idusuario' => 10,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-12 15:00:00'
            ],
            [
                'idatencion' => 21,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos. ',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-12 17:00:00'
            ],
            // ATENCIÓN 22
            [
                'idatencion' => 22,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-10 09:05:00'
            ],
            [
                'idatencion' => 22,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-10 10:00:00'
            ],
            [
                'idatencion' => 22,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: RODRIGO ALEXANDER FELIX HUAMAN. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-10 11:00:00'
            ],
            [
                'idatencion' => 22,
                'idusuario' => 2,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-11 09:00:00'
            ],
            [
                'idatencion' => 22,
                'idusuario' => 2,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-13 10:00:00'
            ],
            [
                'idatencion' => 22,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-13 11:30:00'
            ],
            // ATENCIÓN 23
            [
                'idatencion' => 23,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-10 10:05:00'
            ],
            [
                'idatencion' => 23,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-10 11:00:00'
            ],
            [
                'idatencion' => 23,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: MARIA PIA CASTILLA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-10 12:00:00'
            ],
            [
                'idatencion' => 23,
                'idusuario' => 4,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-11 10:00:00'
            ],
            [
                'idatencion' => 23,
                'idusuario' => 4,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-13 14:00:00'
            ],
            [
                'idatencion' => 23,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-13 15:30:00'
            ],
            // ATENCIÓN 24
            [
                'idatencion' => 24,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-10 11:05:00'
            ],
            [
                'idatencion' => 24,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-10 12:00:00'
            ],
            [
                'idatencion' => 24,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NAYRU GOMEZ MAGALLANES. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-10 13:00:00'
            ],
            [
                'idatencion' => 24,
                'idusuario' => 11,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-11 11:00:00'
            ],
            [
                'idatencion' => 24,
                'idusuario' => 11,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-13 17:00:00'
            ],
            [
                'idatencion' => 24,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-13 18:00:00'
            ],
            // ATENCIÓN 25
            [
                'idatencion' => 25,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-11 09:05:00'
            ],
            [
                'idatencion' => 25,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-11 10:00:00'
            ],
            [
                'idatencion' => 25,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: JONATHAN MEDINA CAMPOS. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-11 11:00:00'
            ],
            [
                'idatencion' => 25,
                'idusuario' => 9,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-12 09:00:00'
            ],
            [
                'idatencion' => 25,
                'idusuario' => 9,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-14 10:00:00'
            ],
            [
                'idatencion' => 25,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-14 12:00:00'
            ],
            // ATENCIÓN 26
            [
                'idatencion' => 26,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-11 10:05:00'
            ],
            [
                'idatencion' => 26,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-11 11:00:00'
            ],
            [
                'idatencion' => 26,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: FABRIZIO RAMOS TIPISMANA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-11 12:00:00'
            ],
            [
                'idatencion' => 26,
                'idusuario' => 10,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-12 10:00:00'
            ],
            [
                'idatencion' => 26,
                'idusuario' => 10,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-14 14:00:00'
            ],
            [
                'idatencion' => 26,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-14 15:00:00'
            ],
            // ATENCIÓN 27
            [
                'idatencion' => 27,
                'idusuario' => 1,
                'accion' => 'Solicitud registrada exitosamente. Su requerimiento ha sido recibido y se encuentra en cola de asignación.',
                'estado' => 'pendiente_sin_asignar',
                'fecha_registro' => '2026-05-11 11:05:00'
            ],
            [
                'idatencion' => 27,
                'idusuario' => 1,
                'accion' => 'Su solicitud ha sido aprobada por Administrador y enviada al área correspondiente para su gestión.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-11 12:00:00'
            ],
            [
                'idatencion' => 27,
                'idusuario' => 1,
                'accion' => 'Pedido delegado al Empleado: NICOL MICHELLE GUERRERO TORREALBA. Estado Pendiente de inicio por parte del Empleado.',
                'estado' => 'pendiente_asignado',
                'fecha_registro' => '2026-05-11 13:00:00'
            ],
            [
                'idatencion' => 27,
                'idusuario' => 6,
                'accion' => '¡Trabajo iniciado! El Empleado ha comenzado con la ejecución del Requerimiento.',
                'estado' => 'en_proceso',
                'fecha_registro' => '2026-05-12 11:00:00'
            ],
            [
                'idatencion' => 27,
                'idusuario' => 6,
                'accion' => '¡Entrega enviada para revisión! Se adjuntaron evidencias del trabajo terminado.',
                'estado' => 'en_revision',
                'fecha_registro' => '2026-05-14 16:00:00'
            ],
            [
                'idatencion' => 27,
                'idusuario' => 1,
                'accion' => '¡Proyecto aprobado y finalizado con éxito! La entrega cumple con todos los requisitos',
                'estado' => 'finalizado',
                'fecha_registro' => '2026-05-14 17:00:00'
            ],
        ];

        $this->db->table('tracking')->insertBatch($data);
    }
}