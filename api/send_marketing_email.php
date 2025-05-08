<?php
session_start();
header('Content-Type: application/json');

// Capturar todos los errores PHP
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }

    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/SuperModel.php';
    require_once __DIR__ . '/../core/EmailService.php';

    $superModel = new SuperModel();
    $pdo = Database::getInstance()->getConnection();

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

    // Enviar emails según la campaña seleccionada
    foreach ($clients as $client) {
        $emailContent = '';
        $subject = '';

        // Si el emailType es numérico, buscar campaña dinámica
        if (is_numeric($emailType)) {
            $stmt = $pdo->prepare("SELECT * FROM campanas_marketing WHERE id_campana = ?");
            $stmt->execute([$emailType]);
            $campana = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($campana) {
                $emailContent = $campana['contenido_html'];
                $subject = $campana['asunto'];
            } else {
                $failedSends[] = $client['email'];
                continue;
            }
        } else {
            // Compatibilidad con campañas hardcodeadas
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
} catch (Exception $e) {
    // En caso de error, devolver un mensaje de error en formato JSON
    echo json_encode([
        'error' => 'Se produjo un error al procesar la solicitud.',
        'details' => $e->getMessage()
    ]);
}
