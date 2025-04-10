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
 * GET => /facturas.php
 *   - busqueda "reserva" (ej: filtrar por id_reserva)
 *   - busqueda "fecha"   (fecha_emision >=, etc. en un ejemplo simple: si usas un BETWEEN)
 *   - page, limit
 * POST => Crear
 * PUT => ?id=XX => Actualizar
 * DELETE => ?id=XX => Eliminar
 */

if ($method === 'GET') {
    $reserva  = $_GET['reserva'] ?? '';
    $page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 5;
    $offset = ($page - 1) * $limit;

    if ($id) {
        $fact = $superModel->getById('facturas', $id);
        echo json_encode($fact);
        exit;
    } else {
        $where = " WHERE 1=1 ";
        $params = [];

        if ($reserva) {
            $where .= " AND id_reserva = :r ";
            $params[':r'] = $reserva;
        }

        $pdo = Database::getInstance()->getConnection();
        // Count
        $sqlC = "SELECT COUNT(*) as total FROM facturas $where";
        $stmtC = $pdo->prepare($sqlC);
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = $stmtC->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Data
        $sql = "SELECT * FROM facturas $where LIMIT :lim OFFSET :off";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'data' => $data,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    }
} elseif ($method === 'POST') {
    $id_reserva   = $_POST['id_reserva']   ?? 0;
    $fecha_emision = $_POST['fecha_emision'] ?? '';
    $total        = $_POST['total']        ?? 0;
    $metodo_pago  = $_POST['metodo_pago']  ?? 'Efectivo';

    $ok = $superModel->create('facturas', [
        'id_reserva'   => $id_reserva,
        'fecha_emision' => $fecha_emision,
        'total'        => $total,
        'metodo_pago'  => $metodo_pago
    ]);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Factura creada'] : ['error' => 'No se pudo crear']);
    exit;
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $input);
    $ok = $superModel->update('facturas', $id, $input);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Actualizada'] : ['error' => 'No se pudo actualizar']);
    exit;
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('facturas', $id);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Eliminada'] : ['error' => 'No se pudo eliminar']);
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
