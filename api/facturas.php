<?php
// api/facturas.php
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
$id = $_GET['id'] ?? 0;

/**
 * GET    => /facturas.php       => Listar todas
 * GET    => /facturas.php?id=X  => Obtener una factura
 * POST   => /facturas.php       => Crear
 * PUT    => /facturas.php?id=X  => Actualizar
 * DELETE => /facturas.php?id=X  => Eliminar
 */

if ($method === 'GET') {
    if ($id) {
        // Obtener una factura
        $factura = $superModel->getById('facturas', $id);
        echo json_encode($factura);
    } else {
        // Listar todas
        $all = $superModel->getAll('facturas');
        echo json_encode($all);
    }
    exit;
} elseif ($method === 'POST') {
    $id_reserva   = $_POST['id_reserva']   ?? '';
    $fecha_emision = $_POST['fecha_emision'] ?? '';
    $total        = $_POST['total']        ?? 0;
    $metodo_pago  = $_POST['metodo_pago']  ?? 'Efectivo';

    $data = [
        'id_reserva'    => $id_reserva,
        'fecha_emision' => $fecha_emision,
        'total'         => $total,
        'metodo_pago'   => $metodo_pago
    ];

    $ok = $superModel->create('facturas', $data);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Factura creada']);
    } else {
        echo json_encode(['error' => 'No se pudo crear la factura']);
    }
    exit;
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents("php://input"), $input);

    $ok = $superModel->update('facturas', $id, $input);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Factura actualizada']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar']);
    }
    exit;
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('facturas', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Factura eliminada']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar la factura']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
