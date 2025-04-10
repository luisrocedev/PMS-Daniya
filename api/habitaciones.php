<?php
// api/habitaciones.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? 0;

/**
 * GET    => /habitaciones.php        => Listar todas las habitaciones
 * GET    => /habitaciones.php?id=X   => Obtener una habitación
 * POST   => /habitaciones.php        => Crear una nueva habitación
 * PUT    => /habitaciones.php?id=X   => Actualizar una habitación
 * DELETE => /habitaciones.php?id=X   => Eliminar una habitación
 */

if ($method === 'GET') {
    if ($id) {
        // Obtener una sola habitación
        $habitacion = $superModel->getById('habitaciones', $id);
        echo json_encode($habitacion);
    } else {
        // Listar todas
        $all = $superModel->getAll('habitaciones');
        echo json_encode($all);
    }
    exit;
} elseif ($method === 'POST') {
    // Crear
    $numero_habitacion = $_POST['numero_habitacion'] ?? '';
    $tipo_habitacion   = $_POST['tipo_habitacion']   ?? '';
    $capacidad         = $_POST['capacidad']         ?? 1;
    $piso              = $_POST['piso']              ?? 1;
    $estado            = $_POST['estado']            ?? 'Disponible';

    $data = [
        'numero_habitacion' => $numero_habitacion,
        'tipo_habitacion'   => $tipo_habitacion,
        'capacidad'         => $capacidad,
        'piso'              => $piso,
        'estado'            => $estado
    ];

    $ok = $superModel->create('habitaciones', $data);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Habitación creada con éxito']);
    } else {
        echo json_encode(['error' => 'No se pudo crear la habitación']);
    }
    exit;
} elseif ($method === 'PUT') {
    // Actualizar
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    // parse_str para leer datos de PUT
    parse_str(file_get_contents("php://input"), $input);

    $ok = $superModel->update('habitaciones', $id, $input);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Habitación actualizada']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar la habitación']);
    }
    exit;
} elseif ($method === 'DELETE') {
    // Eliminar
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('habitaciones', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Habitación eliminada']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar la habitación']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
