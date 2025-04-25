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
 * GET => /tarifas.php
 *   - Sin parámetros: lista todas las tarifas
 *   - ?id=XX: obtiene una tarifa específica
 *   - ?stats=true: obtiene estadísticas
 *   Filtros opcionales:
 *   - tipo_hab: filtrar por tipo de habitación
 *   - temporada: filtrar por temporada
 *   - fecha_inicio: filtrar desde fecha
 *   - fecha_fin: filtrar hasta fecha
 *   - page: página actual (default: 1)
 *   - limit: resultados por página (default: 10)
 */

if ($method === 'GET') {
    $stats = isset($_GET['stats']) && $_GET['stats'] === 'true';

    if ($stats) {
        $pdo = Database::getInstance()->getConnection();

        // Total de tarifas
        $total = $pdo->query("SELECT COUNT(*) as total FROM tarifas")->fetch()['total'];

        // Tarifa promedio
        $avgPrice = $pdo->query("SELECT AVG(precio) as promedio FROM tarifas")->fetch()['promedio'];

        // Tarifas activas (vigentes hoy)
        $hoy = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) as activas FROM tarifas WHERE fecha_inicio <= :hoy AND fecha_fin >= :hoy");
        $stmt->execute([':hoy' => $hoy]);
        $activas = $stmt->fetch()['activas'];

        // Próximas a vencer (en los próximos 30 días)
        $treintaDias = date('Y-m-d', strtotime('+30 days'));
        $stmt = $pdo->prepare("SELECT COUNT(*) as vencer FROM tarifas WHERE fecha_fin BETWEEN :hoy AND :treinta");
        $stmt->execute([':hoy' => $hoy, ':treinta' => $treintaDias]);
        $porVencer = $stmt->fetch()['vencer'];

        echo json_encode([
            'total' => (int)$total,
            'promedio' => round((float)$avgPrice, 2),
            'activas' => (int)$activas,
            'por_vencer' => (int)$porVencer
        ]);
        exit;
    }

    if ($id) {
        $row = $superModel->getById('tarifas', $id);
        echo json_encode($row);
        exit;
    }

    // Listado con filtros
    $where = " WHERE 1=1 ";
    $params = [];

    // Filtros
    $tipoHab = $_GET['tipo_hab'] ?? '';
    $temporada = $_GET['temporada'] ?? '';
    $fechaInicio = $_GET['fecha_inicio'] ?? '';
    $fechaFin = $_GET['fecha_fin'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);

    if ($tipoHab) {
        $where .= " AND tipo_habitacion = :tipo ";
        $params[':tipo'] = $tipoHab;
    }
    if ($temporada) {
        $where .= " AND temporada = :temp ";
        $params[':temp'] = $temporada;
    }
    if ($fechaInicio) {
        $where .= " AND fecha_inicio >= :fini ";
        $params[':fini'] = $fechaInicio;
    }
    if ($fechaFin) {
        $where .= " AND fecha_fin <= :ffin ";
        $params[':ffin'] = $fechaFin;
    }

    $pdo = Database::getInstance()->getConnection();

    // Contar total de resultados
    $sqlCount = "SELECT COUNT(*) as total FROM tarifas $where";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtener resultados paginados
    $offset = ($page - 1) * $limit;
    $sql = "SELECT * FROM tarifas $where ORDER BY id_tarifa DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

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

if ($method === 'POST') {
    $input = $_POST;

    // Validaciones
    if (
        empty($input['nombre_tarifa']) || empty($input['tipo_habitacion']) ||
        empty($input['precio']) || empty($input['temporada']) ||
        empty($input['fecha_inicio']) || empty($input['fecha_fin'])
    ) {
        echo json_encode(['error' => 'Todos los campos son requeridos']);
        exit;
    }

    if (!is_numeric($input['precio']) || $input['precio'] <= 0) {
        echo json_encode(['error' => 'El precio debe ser un número positivo']);
        exit;
    }

    if ($input['fecha_inicio'] > $input['fecha_fin']) {
        echo json_encode(['error' => 'La fecha de inicio debe ser anterior a la fecha fin']);
        exit;
    }

    $ok = $superModel->create('tarifas', $input);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo crear la tarifa']);
    exit;
}

if ($method === 'PUT') {
    if (!$id) {
        echo json_encode(['error' => 'ID de tarifa requerido']);
        exit;
    }

    parse_str(file_get_contents('php://input'), $input);

    // Validaciones
    if (
        !empty($input['fecha_inicio']) && !empty($input['fecha_fin']) &&
        $input['fecha_inicio'] > $input['fecha_fin']
    ) {
        echo json_encode(['error' => 'La fecha de inicio debe ser anterior a la fecha fin']);
        exit;
    }

    if (!empty($input['precio']) && (!is_numeric($input['precio']) || $input['precio'] <= 0)) {
        echo json_encode(['error' => 'El precio debe ser un número positivo']);
        exit;
    }

    $ok = $superModel->update('tarifas', $id, $input);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo actualizar la tarifa']);
    exit;
}

if ($method === 'DELETE') {
    if (!$id) {
        echo json_encode(['error' => 'ID de tarifa requerido']);
        exit;
    }

    // Verificar si hay reservas asociadas
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservas WHERE id_tarifa = :id");
    $stmt->execute([':id' => $id]);
    $count = $stmt->fetch()['total'];

    if ($count > 0) {
        echo json_encode(['error' => 'No se puede eliminar la tarifa porque tiene reservas asociadas']);
        exit;
    }

    $ok = $superModel->delete('tarifas', $id);
    echo json_encode($ok ? ['success' => true] : ['error' => 'No se pudo eliminar la tarifa']);
    exit;
}

echo json_encode(['error' => 'Método no permitido']);
exit;
