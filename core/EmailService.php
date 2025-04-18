<?php
// core/EmailService.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    public static function send($email, $nombre, $asunto, $contenidoHTML)
    {
        $mail = new PHPMailer(true);

        try {
            // Cargar configuración SMTP
            $config = require __DIR__ . '/../config/config.php';

            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $config['smtp']['port'];
            $mail->CharSet = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
            $mail->addAddress($email, $nombre);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $contenidoHTML;

            // Enviar el correo
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Error al enviar el correo: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
