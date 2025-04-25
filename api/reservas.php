<?php
// api/reservas.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

// Obtener parámetros
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 10;
$estado = $_GET['estado'] ?? '';
$search = $_GET['search'] ?? '';

// Si se solicitan solo las reservas activas para el dashboard
if ($estado === 'activas') {
    // Obtener total de reservas activas actuales
    $sqlActivas = "SELECT COUNT(*) as total 
                   FROM reservas 
                   WHERE estado_reserva IN ('Confirmada', 'CheckIn') 
                   AND fecha_salida >= CURRENT_DATE";

    $stmt = $pdo->query($sqlActivas);
    $resultadoActivas = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener total de reservas activas de ayer
    $sqlAyer = "SELECT COUNT(*) as total 
                FROM reservas 
                WHERE estado_reserva IN ('Confirmada', 'CheckIn')
                AND fecha_salida >= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)
                AND fecha_entrada <= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)";

    $stmt = $pdo->query($sqlAyer);
    $resultadoAyer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calcular tendencia
    $tendencia = $resultadoActivas['total'] - $resultadoAyer['total'];

    echo json_encode([
        'total' => (int)$resultadoActivas['total'],
        'tendencia' => $tendencia
    ]);
    exit;
}

// Lógica existente para listar reservas
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search) {
    $where[] = "(id_reserva LIKE :search OR id_cliente LIKE :search OR id_habitacion LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($estado) {
    $where[] = "estado_reserva = :estado";
    $params[':estado'] = $estado;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM reservas $whereClause";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener registros paginados
$sql = "SELECT * FROM reservas $whereClause ORDER BY fecha_entrada DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
}
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'data' => $reservas,
    'total' => $total,
    'page' => (int)$page,
    'limit' => (int)$limit
]);
