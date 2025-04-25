<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';

$pdo = Database::getInstance()->getConnection();

// Obtener ocupación actual
$sql = "SELECT 
            SUM(CASE WHEN estado = 'Ocupada' THEN 1 ELSE 0 END) as ocupadas,
            SUM(CASE WHEN estado = 'Mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
            SUM(CASE WHEN estado = 'Disponible' THEN 1 ELSE 0 END) as disponibles
        FROM habitaciones";

$stmt = $pdo->query($sql);
$ocupacion = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener ocupación de ayer para calcular tendencia
$sqlAyer = "SELECT 
            COUNT(*) as total_ocupadas
            FROM reservas 
            WHERE fecha_entrada <= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)
            AND fecha_salida >= DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)
            AND estado_reserva = 'CheckIn'";

$stmtAyer = $pdo->query($sqlAyer);
$ocupacionAyer = $stmtAyer->fetch(PDO::FETCH_ASSOC);

// Calcular tendencia
$totalHoy = $ocupacion['ocupadas'] + $ocupacion['mantenimiento'] + $ocupacion['disponibles'];
$porcentajeOcupacionHoy = ($ocupacion['ocupadas'] / $totalHoy) * 100;
$porcentajeOcupacionAyer = ($ocupacionAyer['total_ocupadas'] / $totalHoy) * 100;

$tendencia = $porcentajeOcupacionHoy - $porcentajeOcupacionAyer;

// Añadir tendencia a la respuesta
$ocupacion['tendencia'] = round($tendencia, 1);

echo json_encode($ocupacion);
