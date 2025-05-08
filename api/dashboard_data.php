<?php
// api/dashboard_data.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$pdo = Database::getInstance()->getConnection();
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Total habitaciones
$totalRoomsStmt = $pdo->query("SELECT COUNT(*) as total FROM habitaciones");
$totalRooms = (int) ($totalRoomsStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Disponible hoy
$sqlDisp = "SELECT COUNT(*) as disponibles FROM habitaciones h
    WHERE h.estado = 'Disponible'
    AND h.id_habitacion NOT IN (
        SELECT r.id_habitacion FROM reservas r
        WHERE r.estado_reserva IN ('Confirmada','CheckIn')
        AND r.fecha_entrada <= :today
        AND r.fecha_salida > :today
    )";
$stmt = $pdo->prepare($sqlDisp);
$stmt->execute([':today' => $today]);
$disponibles = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['disponibles'] ?? 0);

// Disponible ayer para tendencia
$stmt = $pdo->prepare($sqlDisp);
$stmt->execute([':today' => $yesterday]);
$disponiblesAyer = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['disponibles'] ?? 0);
$trendHabitaciones = $disponibles - $disponiblesAyer;

// Reservas hoy
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservas WHERE fecha_entrada = :today");
$stmt->execute([':today' => $today]);
$reservasHoy = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Ingresos diarios
$stmt = $pdo->prepare("SELECT SUM(total) as ingresos FROM facturas WHERE fecha_emision = :today");
$stmt->execute([':today' => $today]);
$ingresos = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['ingresos'] ?? 0);

// Ocupación %
$ocupacion = $totalRooms > 0 ? (int) round((($totalRooms - $disponibles) / $totalRooms) * 100) : 0;

// Datos mensuales de ocupación
$occupancyData = [];
for ($m = 1; $m <= 12; $m++) {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT id_habitacion) as ocupadas
        FROM reservas
        WHERE estado_reserva IN ('Confirmada','CheckIn')
        AND MONTH(fecha_entrada) = :m");
    $stmt->execute([':m' => $m]);
    $ocupadas = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['ocupadas'] ?? 0);
    $occupancyData[] = $totalRooms > 0 ? (int) round(($ocupadas / $totalRooms) * 100) : 0;
}

// KPIs
$revpar = $totalRooms > 0 ? round($ingresos / $totalRooms, 2) : 0;
$adr = $reservasHoy > 0 ? round($ingresos / $reservasHoy, 2) : 0;

// Estancia media este mes
date_default_timezone_set(date_default_timezone_get());
$stmt = $pdo->prepare("SELECT AVG(DATEDIFF(fecha_salida, fecha_entrada)) as avg_stay
    FROM reservas
    WHERE estado_reserva IN ('Confirmada','CheckIn')
    AND MONTH(fecha_entrada) = MONTH(:today)
    AND YEAR(fecha_entrada) = YEAR(:today)");
$stmt->execute([':today' => $today]);
$avgStay = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['avg_stay'] ?? 0);

// Actividad reciente (últimas 5 reservas confirmadas)
$stmt = $pdo->query("SELECT r.id_reserva, CONCAT(c.nombre, ' ', c.apellidos) as cliente, r.fecha_entrada as time
    FROM reservas r
    LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
    WHERE r.estado_reserva IN ('Confirmada','CheckIn')
    ORDER BY r.fecha_entrada DESC LIMIT 5");
$activities = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $activities[] = [
        'icon' => 'fa-calendar-check',
        'title' => "Reserva #{$row['id_reserva']} - {$row['cliente']}",
        'time' => date('d/m H:i', strtotime($row['time']))
    ];
}

// Tareas vacías (implementar según estructura de BD si existe)
$tasks = [];

echo json_encode([
    'success' => true,
    'stats' => [
        'habitaciones_disponibles' => $disponibles,
        'reservas_hoy' => $reservasHoy,
        'ocupacion' => $ocupacion,
        'ingresos_diarios' => $ingresos,
        'trend_habitaciones' => $trendHabitaciones
    ],
    'occupancy' => $occupancyData,
    'kpis' => [
        'revpar' => $revpar,
        'adr' => $adr,
        'avg_stay' => $avgStay
    ],
    'activity' => $activities,
    'tasks' => $tasks
]);
