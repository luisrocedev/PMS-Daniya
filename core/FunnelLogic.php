<?php
// core/FunnelLogic.php

require_once __DIR__ . '/../vendor/autoload.php'; // Ajustar si tu vendor está en otra ruta

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

        // Aquí seleccionamos asunto y mensaje según el estado del funnel
        // Ajusta según tu lógica y naming interno
        switch ($estado) {
            case 'Nuevo':
                $asunto = '¡Gracias por tu interés en Hotel Daniya Denia!';
                // Mensaje HTML de ejemplo para "Nuevo"
                $mensajeHTML = <<<HTML
                <h2>¡Hola, {$nombre}!</h2>
                <p>Gracias por registrarte en nuestro hotel de 4 estrellas, Daniya Denia. 
                   Queremos brindarte la mejor experiencia desde el primer momento.</p>
                <p>Te invitamos a conocer nuestras habitaciones exclusivas y ofertas especiales.
                   Para más información, no dudes en responder este correo o llamar a recepción.</p>
                <p>¡Te esperamos pronto!</p>
                HTML;
                break;

            case 'Interesado':
                $asunto = '¿Listos para una experiencia inolvidable en Daniya Denia?';
                // Mensaje HTML de ejemplo para "Interesado"
                $mensajeHTML = <<<HTML
                <h2>¡Hola, {$nombre}!</h2>
                <p>Nos encanta saber que estás interesado en tu próxima estancia con nosotros. 
                   En Daniya Denia cuidamos hasta el último detalle para que disfrutes de unas vacaciones perfectas.</p>
                <p>¿Sabías que tenemos un <strong>spa y restaurante gourmet</strong> disponibles 
                   para que tu estadía sea aún más placentera? ¡Contáctanos para contarte más!</p>
                <p>Un saludo afectuoso,<br>El Equipo de Daniya Denia</p>
                HTML;
                break;

            case 'En Negociacion':
                $asunto = 'Seguimos preparando tu mejor experiencia en Daniya Denia';
                // Mensaje HTML de ejemplo para "En Negociacion"
                $mensajeHTML = <<<HTML
                <h2>¡Hola, {$nombre}!</h2>
                <p>Estamos ultimando detalles para que tu estancia sea inolvidable. 
                   Queremos ofrecerte la mejor tarifa y los servicios adecuados a tus necesidades.</p>
                <p>Si tienes alguna duda o requerimiento especial (¡tenemos un servicio de transporte, 
                   desayuno buffet y muchas sorpresas!), respóndenos y te atenderemos de inmediato.</p>
                <p>Te esperamos,<br>Departamento de Reservas Daniya Denia</p>
                HTML;
                break;

            case 'Cerrado':
                $asunto = '¡Bienvenido a la familia Daniya Denia!';
                // Mensaje HTML de ejemplo para "Cerrado"
                $mensajeHTML = <<<HTML
                <h2>¡Enhorabuena, {$nombre}!</h2>
                <p>Tu reserva ya está confirmada. Estamos felices de poder recibirte en Daniya Denia. 
                   Nuestro equipo te espera para ofrecerte una experiencia de lujo junto al Mediterráneo.</p>
                <p>Recuerda que nuestro <strong>servicio de atención al cliente</strong> está disponible 
                   las 24 horas para cualquier duda. ¡Nos vemos muy pronto!</p>
                <p>Un saludo cordial,<br>Hotel Daniya Denia</p>
                HTML;
                break;

            default:
                $asunto = 'Actualización de tu información en Daniya Denia';
                $mensajeHTML = <<<HTML
                <h2>Hola, {$nombre}!</h2>
                <p>Hemos actualizado tus datos en nuestro sistema. Si necesitas cualquier aclaración, 
                   no dudes en contactarnos.</p>
                <p>Saludos,<br>Equipo de Daniya Denia</p>
                HTML;
        }

        // Aquí construimos un pequeño template con un "banner" y algo de CSS inline
        // para que se vea un poco más vistoso:
        $plantillaHTML = <<<HTML
        <html>
        <head>
            <meta charset="UTF-8" />
            <style>
                /* Estilos básicos dentro del <style> */
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    background-color: #f4f6fc;
                    padding: 20px;
                }
                .banner {
                    width: 100%;
                    max-height: 200px;
                    object-fit: cover;
                }
                .content {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .footer {
                    margin-top: 20px;
                    font-size: 0.9em;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <img src="https://via.placeholder.com/1200x200/5f9ea0/ffffff?text=Hotel+Daniya+Denia" alt="Banner Daniya Denia" class="banner" />
                <div class="content">
                    {$mensajeHTML}
                </div>
                <div class="footer">
                    <hr>
                    <p><small>© 2025 Hotel Daniya Denia. Todos los derechos reservados.</small></p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        // Configuramos PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configuración SMTP (IONOS, según tu ejemplo)
            $mail->isSMTP();
            $mail->Host       = 'smtp.ionos.es';
            $mail->SMTPAuth   = true;
            $mail->Username   = '@.es';
            $mail->Password   = '.';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom('@.es', 'CRM-PMS Daniya');
            $mail->addAddress($email, $nombre);

            // Ajustamos para que sea HTML
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $plantillaHTML;

            // Enviamos
            $mail->send();

            // En caso de querer loguear el envío, podríamos guardar en BD algo como:
            // self::registrarLogEmail($email, $asunto, $mensajeHTML);

        } catch (Exception $e) {
            // Manejo de errores
            // Podrías loguear con $mail->ErrorInfo o similar
        }
    }

    // (Opcional) Función para registrar logs en tabla "crm_logs"
    // Ajusta según tu base de datos. Solo a modo de ejemplo:
    /*
    private static function registrarLogEmail($destino, $asunto, $contenido) {
        $pdo = Database::getInstance()->getConnection();
        $sql = "INSERT INTO crm_logs (destinatario, asunto, contenido, fecha_envio) 
                VALUES (:d, :a, :c, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':d', $destino);
        $stmt->bindValue(':a', $asunto);
        $stmt->bindValue(':c', $contenido);
        $stmt->execute();
    }
    */
}
