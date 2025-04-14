<?php
// core/FunnelLogic.php

require_once __DIR__ . '/../vendor/autoload.php'; // Ajusta si tu vendor está en otra ruta

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class FunnelLogic
{
    public static function enviarEmailFunnel($clienteData)
    {
        // Extraemos datos con valores por defecto
        $nombre = $clienteData['nombre'] ?? 'Sin Nombre';
        $email  = $clienteData['email'] ?? '';
        $estado = $clienteData['estado_funnel'] ?? 'Nuevo';

        // Seleccionamos asunto y mensaje según el estado
        switch ($estado) {
            case 'Nuevo':
                $asunto = '¡Gracias por tu interés!';
                $mensaje = "Hola $nombre, gracias por registrarte. Pronto nos pondremos en contacto.";
                break;
            case 'Interesado':
                $asunto = '¿Podemos ayudarte con algo más?';
                $mensaje = "Hola $nombre, vemos que estás interesado. ¡Estamos aquí para ayudarte!";
                break;
            case 'En Negociacion':
                $asunto = 'Seguimos atentos a tu solicitud';
                $mensaje = "Hola $nombre, seguimos negociando tu caso. Cualquier duda, estamos aquí.";
                break;
            case 'Cerrado':
                $asunto = '¡Bienvenido a nuestra familia!';
                $mensaje = "Hola $nombre, nos alegra tenerte con nosotros. ¡Gracias por unirte!";
                break;
            default:
                $asunto = 'Actualización de tu información';
                $mensaje = "Hola $nombre, hemos actualizado tus datos en nuestro sistema.";
        }

        // Configuramos PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP de IONOS
            $mail->isSMTP();
            $mail->Host       = 'smtp.ionos.es';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'luisrodriguez@luisrocedev.es';
            $mail->Password   = '9acGNx3ETSLhEZ.';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            $mail->Port       = 465;

            // Aseguramos la codificación UTF-8
            $mail->CharSet    = 'UTF-8';

            // Configuración del remitente y destinatario
            $mail->setFrom('luisrodriguez@luisrocedev.es', 'CRM-PMS');
            $mail->addAddress($email, $nombre);

            // Contenido del correo
            $mail->isHTML(false);
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;

            $mail->send();
            // Si deseas, puedes registrar un log indicando el envío correcto
        } catch (Exception $e) {
            // Manejo de errores (por ejemplo, puedes loguear el error con $mail->ErrorInfo)
            // echo "No se pudo enviar el email: {$mail->ErrorInfo}";
        }
    }
}
