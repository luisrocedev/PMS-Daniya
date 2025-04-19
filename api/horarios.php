<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
$pdo   = Database::getInstance()->getConnection();
$super = new SuperModel();
$m     = $_SERVER['REQUEST_METHOD'];
$id    = $_GET['id'] ?? 0;

if ($m === 'GET') {
    // filtros opcionales
    $where = " WHERE 1=1 ";
    $p = [];
    if (!empty($_GET['emp'])) {
        $where .= " AND id_empleado=:e";
        $p[':e'] = $_GET['emp'];
    }
    if (!empty($_GET['desde']) && !empty($_GET['hasta'])) {
        $where .= " AND (fecha_inicio<=:h AND fecha_fin>=:d)";
        $p[':d'] = $_GET['desde'];
        $p[':h'] = $_GET['hasta'];
    }
    if ($id) {
        echo json_encode($super->getById('horarios_empleado', $id));
        exit;
    }
    $sql = "SELECT * FROM horarios_empleado $where";
    $st = $pdo->prepare($sql);
    foreach ($p as $k => $v) $st->bindValue($k, $v);
    $st->execute();
    echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
    exit;
} elseif ($m === 'POST') {
    echo json_encode($super->create('horarios_empleado', $_POST) ? ['success' => true] : ['error' => 'No se pudo crear']);
} elseif ($m === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $data);
    echo json_encode($super->update('horarios_empleado', $id, $data) ? ['success' => true] : ['error' => 'No se pudo actualizar']);
} elseif ($m === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    echo json_encode($super->delete('horarios_empleado', $id) ? ['success' => true] : ['error' => 'No se pudo eliminar']);
} else echo json_encode(['error' => 'Método no permitido']);
