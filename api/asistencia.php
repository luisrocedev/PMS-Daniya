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
    $where = " WHERE 1=1 ";
    $p = [];
    if (!empty($_GET['emp'])) {
        $where .= " AND id_empleado=:e";
        $p[':e'] = $_GET['emp'];
    }
    if (!empty($_GET['desde'])) {
        $where .= " AND fecha>=:d";
        $p[':d'] = $_GET['desde'];
    }
    if (!empty($_GET['hasta'])) {
        $where .= " AND fecha<=:h";
        $p[':h'] = $_GET['hasta'];
    }
    if ($id) {
        echo json_encode($super->getById('asistencia', $id));
        exit;
    }
    $sql = "SELECT * FROM asistencia $where ORDER BY fecha DESC";
    $st = $pdo->prepare($sql);
    foreach ($p as $k => $v) $st->bindValue($k, $v);
    $st->execute();
    echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
    exit;
} elseif ($m === 'POST') {
    // fichaje rápido
    $id_emp = $_POST['id_empleado'] ?? 0;
    $tipo   = $_POST['tipo'] ?? 'entrada';           // entrada|salida
    if (!$id_emp) {
        echo json_encode(['error' => 'Falta id_empleado']);
        exit;
    }
    $campo = $tipo === 'entrada' ? 'hora_entrada' : 'hora_salida';
    $sql = "INSERT INTO asistencia (id_empleado,fecha,$campo) 
          VALUES (:e,CURDATE(),NOW())
          ON DUPLICATE KEY UPDATE $campo=NOW()";
    $st = $pdo->prepare($sql);
    $st->execute([':e' => $id_emp]);
    echo json_encode(['success' => true]);
    exit;
} elseif ($m === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $data);
    echo json_encode($super->update('asistencia', $id, $data) ? ['success' => true] : ['error' => 'No se pudo actualizar']);
} elseif ($m === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    echo json_encode($super->delete('asistencia', $id) ? ['success' => true] : ['error' => 'No se pudo eliminar']);
} else echo json_encode(['error' => 'Método no permitido']);
