<?php
require_once __DIR__ . '/core/EmailService.php';

$email = 'jahirguaperas2@gmail.com'; // Dirección de correo del destinatario
$nombre = 'Luis Rodríguez'; // Nombre del destinatario
$asunto = 'Prueba de Email desde PMS Daniya Denia'; // Asunto del correo
$contenidoHTML = '<h1>¡Hola, Luis!</h1><p>Este es un correo de prueba enviado desde el sistema PMS Daniya Denia.</p>';

$resultado = EmailService::send($email, $nombre, $asunto, $contenidoHTML);

if ($resultado) {
    echo 'Correo enviado correctamente.';
} else {
    echo 'Error al enviar el correo.';
}
