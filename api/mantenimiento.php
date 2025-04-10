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
 * GET => /mantenimiento.php
 *   - search (en "descripcion")
 *   - estado (Pendiente, En proceso, Resuelto)
 *   - page, limit
 * POST => Crear incidencia
 * PUT => Actualizar ?id=XX
 * DELETE => Eliminar ?id=XX
 */

if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    $estado = $_GET['estado'] ?? '';
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

    if ($page < 1)  $page  = 1;
    if ($limit < 1) $limit = 5;
    $offset = ($page - 1) * $limit;

    if ($id) {
        $inc = $superModel->getById('mantenimiento', $id);
        echo json_encode($inc);
        exit;
    } else {
        $where = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $where .= " AND descripcion LIKE :s ";
            $params[':s'] = "%$search%";
        }
        if ($estado) {
            $where .= " AND estado = :est ";
            $params[':est'] = $estado;
        }

        $pdo = Database::getInstance()->getConnection();

        // total
        $sqlCount = "SELECT COUNT(*) as total FROM mantenimiento $where";
        $stmtC = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
        $stmtC->execute();
        $total = $stmtC->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // data
        $sql = "SELECT * FROM mantenimiento $where LIMIT :lim OFFSET :off";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
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
    $id_hab   = $_POST['id_habitacion']  ?? 0;
    $id_emp   = $_POST['id_empleado']    ?? 0;
    $desc     = $_POST['descripcion']    ?? '';
    $fRep     = $_POST['fecha_reporte']  ?? '';
    $fRes     = $_POST['fecha_resolucion'] ?? null;
    $estado   = $_POST['estado']         ?? 'Pendiente';

    $ok = $superModel->create('mantenimiento', [
        'id_habitacion'   => $id_hab,
        'id_empleado'     => $id_emp,
        'descripcion'     => $desc,
        'fecha_reporte'   => $fRep,
        'fecha_resolucion' => $fRes,
        'estado'          => $estado
    ]);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Incidencia creada'] : ['error' => 'No se pudo crear']);
    exit;
} elseif ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $input);

    $ok = $superModel->update('mantenimiento', $id, $input);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Actualizado'] : ['error' => 'No se pudo actualizar']);
    exit;
} elseif ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'Falta id']);
        exit;
    }
    $ok = $superModel->delete('mantenimiento', $id);
    echo json_encode($ok ? ['success' => true, 'msg' => 'Incidencia eliminada'] : ['error' => 'No se pudo eliminar']);
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
