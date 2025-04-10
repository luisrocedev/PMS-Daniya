<?php
// api/habitaciones.php
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
 * GET => /habitaciones.php
 *   - search (busca en numero_habitacion, tipo_habitacion)
 *   - estado (filtra por "Disponible", "Ocupada", "Mantenimiento")
 *   - page, limit
 * POST => Crear
 * PUT => Actualizar ?id=XX
 * DELETE => Eliminar ?id=XX
 */

if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    $estado = $_GET['estado'] ?? '';
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 5;
    $offset = ($page - 1) * $limit;

    if ($id) {
        $hab = $superModel->getById('habitaciones', $id);
        echo json_encode($hab);
        exit;
    } else {
        $where = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $where .= " AND (numero_habitacion LIKE :s OR tipo_habitacion LIKE :s) ";
            $params[':s'] = "%$search%";
        }
        if ($estado) {
            $where .= " AND estado = :estado ";
            $params[':estado'] = $estado;
        }

        $pdo = Database::getInstance()->getConnection();

        // Conteo total
        $sqlCount = "SELECT COUNT(*) AS total FROM habitaciones $where";
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Datos con LIMIT
        $sql = "SELECT * FROM habitaciones $where LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'data'  => $data,
            'total' => (int)$total,
            'page'  => $page,
            'limit' => $limit
        ]);
        exit;
    }
} elseif ($method === 'POST') {
    $num      = $_POST['numero_habitacion'] ?? '';
    $tipo     = $_POST['tipo_habitacion']   ?? '';
    $cap      = $_POST['capacidad']         ?? 1;
    $piso     = $_POST['piso']             ?? 1;
    $estado   = $_POST['estado']           ?? 'Disponible';

    $ok = $superModel->create('habitaciones', [
        'numero_habitacion' => $num,
        'tipo_habitacion'   => $tipo,
        'capacidad'         => $cap,
        'piso'              => $piso,
        'estado'            => $estado
    ]);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Habitación creada'] : ['error' => 'No se pudo crear']);
    exit;
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $input);

    $ok = $superModel->update('habitaciones', $id, $input);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Habitación actualizada'] : ['error' => 'No se pudo actualizar']);
    exit;
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('habitaciones', $id);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Habitación eliminada'] : ['error' => 'No se pudo eliminar']);
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
