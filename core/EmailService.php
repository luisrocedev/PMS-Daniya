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

            // Log de depuración
            if ($config['app']['debug']) {
                error_log("Intentando enviar email a: $email");
                error_log("Configuración SMTP: " . json_encode($config['smtp']));
            }

            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $config['smtp']['port'];
            $mail->CharSet = 'UTF-8';

            // Habilitar debug si está en modo desarrollo
            if ($config['app']['debug']) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function ($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };
            }

            // Remitente y destinatario
            $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
            $mail->addAddress($email, $nombre);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $contenidoHTML;

            // Enviar el correo
            $mail->send();
            if ($config['app']['debug']) {
                error_log("Email enviado exitosamente a: $email");
            }
            return true;
        } catch (Exception $e) {
            $errorMsg = "Error al enviar el correo a $email: " . $mail->ErrorInfo;
            error_log($errorMsg);
            if (isset($config['app']['debug']) && $config['app']['debug']) {
                throw new Exception($errorMsg);
            }
            return false;
        }
    }
}
