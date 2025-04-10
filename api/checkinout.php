<?php
// api/checkinout.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$pdo = Database::getInstance()->getConnection();
$superModel = new SuperModel();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Consultar las reservas pendientes de Check-in (estado "Confirmada") y Check-out (estado "CheckIn")

    // Consulta para reservas pendientes de Check-in
    $sqlCheckIn = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente 
                   FROM reservas r 
                   JOIN clientes c ON r.id_cliente = c.id_cliente 
                   WHERE r.estado_reserva = 'Confirmada'";
    $stmt1 = $pdo->query($sqlCheckIn);
    $pendientesCheckIn = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para reservas pendientes de Check-out
    $sqlCheckOut = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente 
                    FROM reservas r 
                    JOIN clientes c ON r.id_cliente = c.id_cliente 
                    WHERE r.estado_reserva = 'CheckIn'";
    $stmt2 = $pdo->query($sqlCheckOut);
    $pendientesCheckOut = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Devolvemos las dos listas en un objeto JSON
    echo json_encode([
        'pendientesCheckIn' => $pendientesCheckIn,
        'pendientesCheckOut' => $pendientesCheckOut
    ]);
    exit;
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_reserva = $_POST['id_reserva'] ?? 0;
    if (!$id_reserva) {
        echo json_encode(['error' => 'Falta id_reserva']);
        exit;
    }

    // Comprobar que la reserva exista y su estado actual
    $resAct = $superModel->getById('reservas', $id_reserva);
    if (!$resAct) {
        echo json_encode(['error' => 'Reserva no encontrada']);
        exit;
    }
    $actual = $resAct['estado_reserva'];

    if ($action === 'checkin') {
        if ($actual !== 'Confirmada') {
            echo json_encode(['error' => "No se puede hacer CheckIn si la reserva no está Confirmada. Estado actual: $actual"]);
            exit;
        }
        $superModel->update('reservas', $id_reserva, ['estado_reserva' => 'CheckIn']);
        echo json_encode(['success' => true, 'msg' => 'Check-in realizado']);
        exit;
    } elseif ($action === 'checkout') {
        if ($actual !== 'CheckIn') {
            echo json_encode(['error' => "No se puede hacer CheckOut si la reserva no está en CheckIn. Estado actual: $actual"]);
            exit;
        }
        $superModel->update('reservas', $id_reserva, ['estado_reserva' => 'CheckOut']);
        echo json_encode(['success' => true, 'msg' => 'Check-out realizado']);
        exit;
    } else {
        echo json_encode(['error' => 'Acción desconocida']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
