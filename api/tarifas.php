<?php
// api/tarifas.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$superModel = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? 0;

/**
 * GET => /tarifas.php (o ?id=XX)
 *   - Filtrar por tipo_habitacion, temporada, rango de fechas...
 * POST => Crear
 * PUT => Actualizar ?id=XX
 * DELETE => Eliminar ?id=XX
 */

if ($method === 'GET') {
    // Posibles filtros
    $tipoHab = $_GET['tipo_hab'] ?? '';
    $temporada = $_GET['temporada'] ?? '';
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 5;

    if ($id) {
        $row = $superModel->getById('tarifas', $id);
        echo json_encode($row);
        exit;
    } else {
        $where = " WHERE 1=1 ";
        $params = [];

        if ($tipoHab) {
            $where .= " AND tipo_habitacion = :t ";
            $params[':t'] = $tipoHab;
        }
        if ($temporada) {
            $where .= " AND temporada = :temp ";
            $params[':temp'] = $temporada;
        }

        $pdo = Database::getInstance()->getConnection();

        // total
        $sqlCount = "SELECT COUNT(*) as total FROM tarifas $where";
        $stmtC = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtC->bindValue($k, $v);
        }
        $stmtC->execute();
        $total = $stmtC->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $offset = ($page - 1) * $limit;
        // datos
        $sql = "SELECT * FROM tarifas $where LIMIT :lim OFFSET :off";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'data' => $data,
            'total' => (int)$total,
            'page' => (int)$page,
            'limit' => (int)$limit
        ]);
        exit;
    }
} elseif ($method === 'POST') {
    // Crear
    $nombre_tarifa     = $_POST['nombre_tarifa']     ?? '';
    $tipo_habitacion   = $_POST['tipo_habitacion']   ?? '';
    $precio            = $_POST['precio']            ?? '';
    $temporada         = $_POST['temporada']         ?? '';
    $fecha_inicio      = $_POST['fecha_inicio']      ?? '';
    $fecha_fin         = $_POST['fecha_fin']         ?? '';

    $ok = $superModel->create('tarifas', [
        'nombre_tarifa'    => $nombre_tarifa,
        'tipo_habitacion'  => $tipo_habitacion,
        'precio'           => $precio,
        'temporada'        => $temporada,
        'fecha_inicio'     => $fecha_inicio,
        'fecha_fin'        => $fecha_fin
    ]);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo crear la tarifa']);
    exit;
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $input);

    $ok = $superModel->update('tarifas', $id, $input);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo actualizar']);
    exit;
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('tarifas', $id);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo eliminar']);
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
