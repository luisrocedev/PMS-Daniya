<?php
// api/checkinout.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

// ...
$pdo = Database::getInstance()->getConnection();
$superModel = new SuperModel();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // como antes
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_reserva = $_POST['id_reserva'] ?? 0;
    if (!$id_reserva) {
        echo json_encode(['error' => 'Falta id_reserva']);
        exit;
    }

    // Antes de cambiar, vemos estado
    $resAct = $superModel->getById('reservas', $id_reserva);
    if (!$resAct) {
        echo json_encode(['error' => 'Reserva no encontrada']);
        exit;
    }
    $actual = $resAct['estado_reserva'];

    if ($action === 'checkin') {
        // Solo permitido si $actual === 'Confirmada'
        if ($actual !== 'Confirmada') {
            echo json_encode(['error' => "No se puede hacer CheckIn si la reserva no está Confirmada. Estado actual: $actual"]);
            exit;
        }
        // Actualizamos
        $superModel->update('reservas', $id_reserva, ['estado_reserva' => 'CheckIn']);
        echo json_encode(['success' => true, 'msg' => 'Check-in realizado']);
        exit;
    } elseif ($action === 'checkout') {
        // Solo permitido si $actual === 'CheckIn'
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
