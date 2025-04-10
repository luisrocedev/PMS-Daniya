<?php
// api/ocupacion.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$pdo = Database::getInstance()->getConnection();

// Contamos total de habitaciones
$sqlTotal = "SELECT COUNT(*) as total FROM habitaciones";
$stmt = $pdo->query($sqlTotal);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Contamos cuántas están ocupadas
$sqlOcupadas = "SELECT COUNT(*) as ocupadas FROM habitaciones WHERE estado = 'Ocupada'";
$stmt2 = $pdo->query($sqlOcupadas);
$ocupadas = $stmt2->fetch(PDO::FETCH_ASSOC)['ocupadas'];

// Contamos cuántas están en mantenimiento
$sqlMantenimiento = "SELECT COUNT(*) as mantenimiento FROM habitaciones WHERE estado = 'Mantenimiento'";
$stmt3 = $pdo->query($sqlMantenimiento);
$mantenimiento = $stmt3->fetch(PDO::FETCH_ASSOC)['mantenimiento'];

// Calculamos las disponibles
$disponibles = $total - $ocupadas - $mantenimiento;

echo json_encode([
    'total' => $total,
    'ocupadas' => (int) $ocupadas,
    'mantenimiento' => (int) $mantenimiento,
    'disponibles' => (int) $disponibles
]);
