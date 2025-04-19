<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$super  = new SuperModel();
$pdo    = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? 0;

/* ------------------------- GET -------------------------------- */
if ($method === 'GET') {
    echo json_encode($id ? $super->getById('turnos', $id)
        : $super->getAll('turnos'));
    exit;
}

/* ------------------------- POST (crear) ----------------------- */
if ($method === 'POST') {
    $ok = $super->create('turnos', $_POST);
    echo json_encode($ok ? ['success' => true]
        : ['error'   => 'No se pudo crear']);
    exit;
}

/* ------------------------- PUT  (actualizar) ------------------ */
if ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }

    parse_str(file_get_contents('php://input'), $data);
    $ok = $super->update('turnos', $id, $data);
    echo json_encode($ok ? ['success' => true]
        : ['error'   => 'No se pudo actualizar']);
    exit;
}

/* ------------------------- DELETE ----------------------------- */
if ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }

    try {
        $ok = $super->delete('turnos', $id);
        echo json_encode($ok ? ['success' => true]
            : ['error'   => 'No se pudo eliminar']);
    } catch (PDOException $e) {
        // 1451 = restricción FK (hay horarios que apuntan a este turno)
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
            echo json_encode([
                'error' => 'No se puede eliminar: existen horarios asociados a este turno.'
            ]);
        } else {
            echo json_encode([
                'error' => 'Excepción: ' . $e->getMessage()
            ]);
        }
    }
    exit;
}

/* ------------------------- Fallback --------------------------- */
echo json_encode(['error' => 'Método no permitido']);
