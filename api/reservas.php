<?php
// api/reservas.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Posible convención:
 * GET /reservas.php       => Listar todas las reservas
 * GET /reservas.php?id=1  => Obtener reserva con id=1
 * POST /reservas.php      => Crear una reserva (action=create)
 * PUT /reservas.php?id=1  => Actualizar reserva con id=1
 * DELETE /reservas.php?id=1 => Eliminar reserva con id=1
 *
 * Para simplificar, usaremos 'action' en POST. O directamente varios endpoints.
 */

// Leer ID si viene en la query
$id = $_GET['id'] ?? 0;

// 1. GET => Listar o una sola
if ($method === 'GET') {
    if ($id) {
        // Obtener una sola reserva
        $reserva = $superModel->getById('reservas', $id);
        echo json_encode($reserva);
        exit;
    } else {
        // Obtener todas
        $all = $superModel->getAll('reservas');
        echo json_encode($all);
        exit;
    }
}

// 2. POST => Crear una nueva reserva
elseif ($method === 'POST') {
    // Para crear, esperamos datos en $_POST (o JSON en php://input).
    // Asumiendo un form normal con method=POST.
    $id_cliente = $_POST['id_cliente'] ?? '';
    $id_habitacion = $_POST['id_habitacion'] ?? '';
    $fecha_entrada = $_POST['fecha_entrada'] ?? '';
    $fecha_salida = $_POST['fecha_salida'] ?? '';
    $estado_reserva = $_POST['estado_reserva'] ?? 'Pendiente';

    // Creamos
    $data = [
        'id_cliente' => $id_cliente,
        'id_habitacion' => $id_habitacion,
        'fecha_entrada' => $fecha_entrada,
        'fecha_salida' => $fecha_salida,
        'estado_reserva' => $estado_reserva
    ];
    $ok = $superModel->create('reservas', $data);

    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Reserva creada']);
    } else {
        echo json_encode(['error' => 'No se pudo crear la reserva']);
    }
    exit;
}

// 3. PUT => Actualizar
elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id para actualizar']);
        exit;
    }
    // Obtenemos datos del body (PUT no usa $_POST)
    parse_str(file_get_contents("php://input"), $input);
    /*
      $input contendrá algo como:
      [
        'id_cliente' => 'X',
        'id_habitacion' => 'Y',
        ...
      ]
    */

    $ok = $superModel->update('reservas', $id, $input);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Reserva actualizada']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar la reserva']);
    }
    exit;
}

// 4. DELETE => Eliminar
elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id para eliminar']);
        exit;
    }
    $ok = $superModel->delete('reservas', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Reserva eliminada']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar la reserva']);
    }
    exit;
}

// Si no coincide con nada:
else {
    echo json_encode(['error' => 'Método no permitido']);
}
