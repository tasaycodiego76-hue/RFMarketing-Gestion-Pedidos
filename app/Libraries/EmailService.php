<?php

namespace App\Libraries;

class EmailService
{
    /**
     * Envia un correo con formato HTML basado en una plantilla
     * @param mixed $para
     * @param mixed $asunto
     * @param mixed $cuerpo
     * @return bool
     */
    private function enviar($para, $asunto, $cuerpo): bool
    {
        $email = \Config\Services::email();

        // Limpiar configuración previa para evitar solapamientos
        $email->clear();

        $email->setTo($para);
        $email->setSubject($asunto);
        $email->setMessage($cuerpo);

        if ($email->send()) {
            return true;
        } else {
            // Guardar log del error en desarrollo
            log_message('error', '[EmailService::enviar] Falló el envío a: ' . $para . '. Detalle: ' . $email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Plantilla base para envolver el diseño estético de todos los correos
     * @param mixed $tituloHeader
     * @param mixed $contenidoHtml
     * @return string
     */
    private function obtenerPlantillaBase($tituloHeader, $contenidoHtml): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$tituloHeader}</title>
            <style>
                body {
                    margin: 0; padding: 0; background-color: #f7f9fc;
                    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .wrapper {
                    width: 100%; table-layout: fixed; background-color: #f7f9fc; padding: 40px 0;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .main-card {
                    max-width: 600px; margin: 0 auto; background-color: #ffffff;
                    border-radius: 16px; overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                    border: 1px solid #eef2f6;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .header {
                    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                    padding: 40px 30px; text-align: center; color: #ffffff;
                }
                .header h1 {
                    margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 0.5px;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .header p {
                    margin: 10px 0 0 0; font-size: 14px; color: #e0e6ed; font-weight: 300;
                }
                .content {
                    padding: 40px 30px; color: #334155; line-height: 1.6; font-size: 15px;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .greeting {
                    font-size: 20px; font-weight: 700; color: #1e293b; margin-top: 0; margin-bottom: 20px;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .badge {
                    display: inline-block; padding: 6px 16px; border-radius: 50px;
                    font-size: 12px; font-weight: 600; text-transform: uppercase;
                    letter-spacing: 0.5px; margin: 15px 0;
                }
                .badge-process { background-color: #e0f2f1; color: #00796b; }
                .badge-review { background-color: #fff9c4; color: #f57f17; }
                .badge-success { background-color: #e8f5e9; color: #2e7d32; }
                .badge-cancel { background-color: #ffe0b2; color: #d84315; }
                
                .info-box {
                    background-color: #f8fafc; border-left: 4px solid #3b82f6;
                    padding: 20px; border-radius: 8px; margin: 25px 0;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .info-row {
                    display: flex; margin-bottom: 10px; font-size: 14px;
                    word-wrap: break-word; overflow-wrap: break-word;
                }
                .info-row:last-child { margin-bottom: 0; }
                .info-label {
                    font-weight: 600; color: #64748b; width: 140px; flex-shrink: 0;
                }
                .info-value {
                    color: #1e293b;
                    word-wrap: break-word; overflow-wrap: break-word; word-break: break-all;
                }
                
                .btn {
                    display: inline-block; padding: 12px 30px; background-color: #2a5298;
                    color: #ffffff !important; text-decoration: none; border-radius: 8px;
                    font-weight: 600; font-size: 14px; text-align: center;
                    margin: 20px 0; box-shadow: 0 4px 12px rgba(42, 82, 152, 0.2);
                    transition: background-color 0.3s ease;
                }
                .footer {
                    background-color: #f8fafc; padding: 30px; text-align: center;
                    font-size: 12px; color: #94a3b8; border-top: 1px solid #eef2f6;
                }
                .footer p { margin: 5px 0; }
                .social-links { margin-top: 15px; }
                .social-links a { color: #64748b; text-decoration: none; margin: 0 10px; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='main-card'>
                    <div class='header'>
                        <h1>RFMarketing</h1>
                        <p>Gestión de Requerimientos</p>
                    </div>
                    <div class='content'>
                        {$contenidoHtml}
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " RFMarketing Agency. Todos los derechos reservados.</p>
                        <p>Este es un correo automático de seguimiento, por favor no respondas a este mensaje.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Notificación: Requerimiento delegado a un Empleado
     * @param mixed $correoCliente
     * @param mixed $nombreCliente
     * @param mixed $tituloPedido
     * @param mixed $nombreTecnico
     * @return bool
     */
    public function notificarAsignacionTecnico($correoCliente, $nombreCliente, $tituloPedido, $nombreTecnico): bool
    {
        $asunto = "Trabajador Asignado - " . $tituloPedido;

        $cuerpo = "
            <h2 class='greeting'>¡Hola, {$nombreCliente}!</h2>
            <p>Queremos informarte que tu requerimiento ha sido revisado y ya cuenta con un Empleado asignado para su ejecución.</p>
            
            <div class='badge badge-process'>Asignado / En Cola</div>

            <div class='info-box'>
                <div class='info-row'>
                    <div class='info-label'>Requerimiento:</div>
                    <div class='info-value'><strong>{$tituloPedido}</strong></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Especialista:</div>
                    <div class='info-value'>{$nombreTecnico}</div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Estado:</div>
                    <div class='info-value'>En cola de desarrollo</div>
                </div>
            </div>

            <p>El técnico asignado comenzará el análisis inicial de los requerimientos y el brief suministrado. Te mantendremos informado tan pronto inicie el trabajo operativo.</p>
        ";

        $htmlFinal = $this->obtenerPlantillaBase($asunto, $cuerpo);
        return $this->enviar($correoCliente, $asunto, $htmlFinal);
    }

    /**
     * Notificacion: Trabaja Empezado por el Empleado
     * @param mixed $correoCliente
     * @param mixed $nombreCliente
     * @param mixed $tituloPedido
     * @return bool
     */
    public function notificarInicioTrabajo($correoCliente, $nombreCliente, $tituloPedido): bool
    {
        $asunto = "¡Trabajo en Curso! - " . $tituloPedido;

        $cuerpo = "
            <h2 class='greeting'>¡Buenas noticias, {$nombreCliente}!</h2>
            <p>El Empleado ha comenzado a trabajar activamente en el desarrollo de tu requerimiento.</p>
            
            <div class='badge badge-process'>En Proceso</div>

            <div class='info-box'>
                <div class='info-row'>
                    <div class='info-label'>Requerimiento:</div>
                    <div class='info-value'><strong>{$tituloPedido}</strong></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Estado:</div>
                    <div class='info-value'>Trabajo operativo iniciado</div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Hora de Inicio:</div>
                    <div class='info-value'>" . date('d/m/Y H:i:s') . "</div>
                </div>
            </div>

            <p>El trabajo está activo. Una vez que las piezas o entregables estén terminados, se subirán a revisión para su revision</p>
        ";

        $htmlFinal = $this->obtenerPlantillaBase($asunto, $cuerpo);
        return $this->enviar($correoCliente, $asunto, $htmlFinal);
    }

    /**
     *  Notificación: Requerimiento Finalizado y Aprobado
     * @param mixed $correoCliente
     * @param mixed $nombreCliente
     * @param mixed $tituloPedido
     * @param mixed $urlEntrega
     * @param array $archivos
     * @return bool
     */
    public function notificarFinalizado($correoCliente, $nombreCliente, $tituloPedido, $urlEntrega = null, $archivos = []): bool
    {
        $asunto = "¡Requerimiento Completado! - " . $tituloPedido;

        $urlTexto = !empty($urlEntrega) ? "<a href='{$urlEntrega}' target='_blank' style='color: #2a5298; font-weight: 600;'>Ver Enlace de Entrega</a>" : "Disponible en tu panel de cliente";

        // Renderizar los archivos entregados si existen
        $seccionArchivos = '';
        if (!empty($archivos)) {
            $seccionArchivos .= "
            <div style='margin-top: 25px; border-top: 2px solid #eef2f6; padding-top: 20px;'>
                <h3 style='font-size: 16px; font-weight: 700; color: #1e293b; margin-top: 0; margin-bottom: 12px;'> Archivos Finales Entregados:</h3>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
            ";
            foreach ($archivos as $archivo) {
                $urlDescarga = base_url($archivo['ruta']);
                $tamanoKB = round($archivo['tamano'] / 1024, 2);
                $seccionArchivos .= "
                <tr style='border-bottom: 1px solid #f1f5f9;'>
                    <td style='padding: 10px 0; color: #475569;'>
                        <span style='margin-right: 8px;'>📄</span>
                        <a href='{$urlDescarga}' target='_blank' style='color: #2563eb; text-decoration: none; font-weight: 600;'>
                            " . htmlspecialchars($archivo['nombre']) . "
                        </a>
                    </td>
                    <td style='padding: 10px 0; text-align: right; color: #94a3b8; font-size: 12px;'>
                        {$tamanoKB} KB
                    </td>
                </tr>
                ";
            }
            $seccionArchivos .= "
                </table>
            </div>
            ";
        }

        // Renderizar enlace externo si existe
        $seccionEnlaces = '';
        if (!empty($urlEntrega)) {
            $seccionEnlaces .= "
            <div style='margin-top: 20px; background-color: #f0f9ff; border: 1px dashed #bae6fd; padding: 15px; border-radius: 8px;'>
                <h4 style='font-size: 13px; font-weight: 700; color: #0369a1; margin: 0 0 6px 0;'>Enlace de Entrega (Recurso Externo):</h4>
                <a href='{$urlEntrega}' target='_blank' style='color: #0284c7; text-decoration: underline; font-size: 13px; word-break: break-all; font-weight: 500;'>
                    " . htmlspecialchars($urlEntrega) . "
                </a>
            </div>
            ";
        }

        $cuerpo = "
            <h2 class='greeting'>¡Hola, {$nombreCliente}!</h2>
            <p>Tu requerimiento ha sido completado y aprobado oficialmente. Las piezas finales están listas para ser descargadas.</p>
            
            <div class='badge badge-success'>Completado</div>

            <div class='info-box'>
                <div class='info-row'>
                    <div class='info-label'>Requerimiento:</div>
                    <div class='info-value'><strong>{$tituloPedido}</strong></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Fecha de Cierre:</div>
                    <div class='info-value'>" . date('d/m/Y') . "</div>
                </div>
            </div>

            {$seccionEnlaces}
            {$seccionArchivos}

            <p style='margin-top: 25px;'>Agradecemos tu confianza. Si tienes nuevos requerimientos, puedes registrarlos en cualquier momento desde tu panel.</p>
        ";

        $htmlFinal = $this->obtenerPlantillaBase($asunto, $cuerpo);
        return $this->enviar($correoCliente, $asunto, $htmlFinal);
    }

    /**
     * Notificacion: Requerimiento Cancelado
     * @param mixed $correoCliente
     * @param mixed $nombreCliente
     * @param mixed $tituloPedido
     * @param mixed $motivo
     * @return bool
     */
    public function notificarCancelado($correoCliente, $nombreCliente, $tituloPedido, $motivo = 'Sin especificar'): bool
    {
        $asunto = "Requerimiento Cancelado - " . $tituloPedido;

        $cuerpo = "
            <h2 class='greeting'>Estimado(a) {$nombreCliente},</h2>
            <p>Te informamos que tu solicitud ha sido cancelada en el sistema.</p>
            
            <div class='badge badge-cancel'>Cancelado</div>

            <div class='info-box' style='border-left-color: #ea580c;'>
                <div class='info-row'>
                    <div class='info-label'>Requerimiento:</div>
                    <div class='info-value'><strong>{$tituloPedido}</strong></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Motivo:</div>
                    <div class='info-value' style='color: #ea580c;'>{$motivo}</div>
                </div>
            </div>

            <p>Si consideras que se trata de un error o deseas volver a reactivar el servicio bajo otros términos, no dudes en ponerte en contacto con tu Administrador de cuenta o registrar un nuevo requerimiento.</p>
        ";

        $htmlFinal = $this->obtenerPlantillaBase($asunto, $cuerpo);
        return $this->enviar($correoCliente, $asunto, $htmlFinal);
    }
}