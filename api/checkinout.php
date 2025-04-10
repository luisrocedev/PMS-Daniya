<?php
// api/checkinout.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

// Verificamos que el usuario esté logueado (opcional, según tu lógica)
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$pdo = Database::getInstance()->getConnection();
$superModel = new SuperModel();

$method = $_SERVER['REQUEST_METHOD'];

/**
 * GET => Listar reservas pendientes de check-in y check-out
 * POST => Realizar check-in o check-out
 */

// 1. Listado (GET)
if ($method === 'GET') {
    date_default_timezone_set('Europe/Madrid');
    $hoy = date('Y-m-d');

    // Pendientes de check-in: reservas con fecha_entrada = hoy y estado "Confirmada"
    $sqlCheckIn = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
                   FROM reservas r
                   JOIN clientes c ON r.id_cliente = c.id_cliente
                   WHERE r.fecha_entrada = :hoy AND r.estado_reserva = 'Confirmada'";
    $stmt = $pdo->prepare($sqlCheckIn);
    $stmt->bindValue(':hoy', $hoy);
    $stmt->execute();
    $pendientesCheckIn = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pendientes de check-out: reservas con fecha_salida = hoy y estado "CheckIn"
    $sqlCheckOut = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
                    FROM reservas r
                    JOIN clientes c ON r.id_cliente = c.id_cliente
                    WHERE r.fecha_salida = :hoy AND r.estado_reserva = 'CheckIn'";
    $stmt2 = $pdo->prepare($sqlCheckOut);
    $stmt2->bindValue(':hoy', $hoy);
    $stmt2->execute();
    $pendientesCheckOut = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'pendientesCheckIn' => $pendientesCheckIn,
        'pendientesCheckOut' => $pendientesCheckOut
    ]);
    exit;
}

// 2. Acciones (POST)
elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_reserva = $_POST['id_reserva'] ?? 0;

    if (!$id_reserva) {
        echo json_encode(['error' => 'Falta id_reserva']);
        exit;
    }

    if ($action === 'checkin') {
        // Cambiamos estado_reserva a "CheckIn"
        $superModel->update('reservas', $id_reserva, ['estado_reserva' => 'CheckIn']);
        echo json_encode(['success' => true, 'msg' => 'Check-in realizado']);
        exit;
    } elseif ($action === 'checkout') {
        // Cambiamos estado_reserva a "CheckOut"
        $superModel->update('reservas', $id_reserva, ['estado_reserva' => 'CheckOut']);
        echo json_encode(['success' => true, 'msg' => 'Check-out realizado']);
        exit;
    } else {
        echo json_encode(['error' => 'Acción desconocida']);
        exit;
    }
}

// Si no es GET/POST
else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
