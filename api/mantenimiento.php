<?php
// api/mantenimiento.php
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
 * GET    => /mantenimiento.php         => Listar todas las incidencias
 * GET    => /mantenimiento.php?id=X    => Obtener una incidencia concreta
 * POST   => /mantenimiento.php         => Crear una incidencia
 * PUT    => /mantenimiento.php?id=X    => Actualizar
 * DELETE => /mantenimiento.php?id=X    => Eliminar
 */

if ($method === 'GET') {
    if ($id) {
        $incidencia = $superModel->getById('mantenimiento', $id);
        echo json_encode($incidencia);
    } else {
        $all = $superModel->getAll('mantenimiento');
        echo json_encode($all);
    }
    exit;
} elseif ($method === 'POST') {
    $id_habitacion  = $_POST['id_habitacion']  ?? '';
    $id_empleado    = $_POST['id_empleado']    ?? '';
    $descripcion    = $_POST['descripcion']    ?? '';
    $fecha_reporte  = $_POST['fecha_reporte']  ?? '';
    $fecha_resol    = $_POST['fecha_resolucion'] ?? null;
    $estado         = $_POST['estado']         ?? 'Pendiente';

    $data = [
        'id_habitacion'  => $id_habitacion,
        'id_empleado'    => $id_empleado,
        'descripcion'    => $descripcion,
        'fecha_reporte'  => $fecha_reporte,
        'fecha_resolucion' => $fecha_resol,
        'estado'         => $estado
    ];

    $ok = $superModel->create('mantenimiento', $data);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Incidencia de mantenimiento creada']);
    } else {
        echo json_encode(['error' => 'No se pudo crear la incidencia']);
    }
    exit;
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents("php://input"), $input);

    $ok = $superModel->update('mantenimiento', $id, $input);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Incidencia actualizada']);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar']);
    }
    exit;
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('mantenimiento', $id);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Incidencia eliminada']);
    } else {
        echo json_encode(['error' => 'No se pudo eliminar']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
