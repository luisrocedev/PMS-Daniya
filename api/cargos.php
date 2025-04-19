<?php
// api/cargos.php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$pdo    = Database::getInstance()->getConnection();
$sm     = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($method === 'GET') {
    if ($id) {
        echo json_encode($sm->getById('cargos', $id));
    } else {
        $reserva = isset($_GET['reserva']) ? intval($_GET['reserva']) : 0;
        $stmt = $pdo->prepare("SELECT * FROM cargos WHERE id_reserva=:r ORDER BY fecha DESC");
        $stmt->execute([':r' => $reserva]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} elseif ($method === 'POST') {
    $data = [
        'id_reserva' => $_POST['id_reserva'],
        'descripcion' => $_POST['descripcion'],
        'importe'    => $_POST['importe'],
    ];
    $ok = $sm->create('cargos', $data);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo crear']);
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $input);
    $ok = $sm->update('cargos', $id, $input);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo actualizar']);
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $sm->delete('cargos', $id);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo eliminar']);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
