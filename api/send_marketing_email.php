<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
require_once __DIR__ . '/../core/EmailService.php';

$superModel = new SuperModel();

// Obtener datos del formulario
$emailType = $_POST['emailType'] ?? '';
$clientSelection = $_POST['clientSelection'] ?? '';
$clientIds = json_decode($_POST['clientIds'] ?? '[]', true);

if (empty($emailType)) {
    echo json_encode(['error' => 'Tipo de email no seleccionado']);
    exit;
}

// Obtener la lista de clientes
$clients = [];
if ($clientSelection === 'all') {
    $clients = $superModel->getAll('clientes');
} elseif ($clientSelection === 'selected' && !empty($clientIds)) {
    foreach ($clientIds as $id) {
        $client = $superModel->getById('clientes', $id);
        if ($client) {
            $clients[] = $client;
        }
    }
}

if (empty($clients)) {
    echo json_encode(['error' => 'No hay clientes seleccionados']);
    exit;
}

// Variables para rastrear el progreso
$totalClients = count($clients);
$successfulSends = 0;
$failedSends = [];

// Enviar emails según el tipo seleccionado
foreach ($clients as $client) {
    $emailContent = '';
    $subject = '';

    switch ($emailType) {
        case 'promocion_verano':
            $emailContent = file_get_contents(__DIR__ . '/../api/emails/promocion_verano.html');
            $emailContent = str_replace('[Nombre del Cliente]', $client['nombre'], $emailContent);
            $emailContent = str_replace('[Fecha Límite]', '31 de diciembre de 2023', $emailContent);
            $emailContent = str_replace('[Enlace a la Página de Reservas]', 'https://www.daniyadenia.com/reservas', $emailContent);
            $subject = 'Promoción de Verano - Hotel Daniya Denia';
            break;

            // Añade más casos según los tipos de emails que tengas
    }

    // Intentar enviar el email
    try {
        $result = EmailService::send($client['email'], $client['nombre'], $subject, $emailContent);
        if ($result) {
            $successfulSends++;
        } else {
            $failedSends[] = $client['email'];
        }
    } catch (Exception $e) {
        // Registrar el error en los logs pero continuar con el siguiente cliente
        error_log('Error al enviar el email a ' . $client['email'] . ': ' . $e->getMessage());
        $failedSends[] = $client['email'];
    }
}

// Devolver resultados
echo json_encode([
    'success' => true,
    'message' => "Emails enviados correctamente.",
    'details' => [
        'totalClients' => $totalClients,
        'successfulSends' => $successfulSends,
        'failedSends' => $failedSends
    ]
]);
