<?php
// api/disponibilidad.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';

if (!$fechaInicio || !$fechaFin) {
    echo json_encode(['error' => 'Faltan parámetros de fecha']);
    exit;
}

$pdo = Database::getInstance()->getConnection();

// Se seleccionan habitaciones que NO tengan reservas que se solapen con el intervalo deseado
$sql = "SELECT * FROM habitaciones WHERE id_habitacion NOT IN (
            SELECT id_habitacion FROM reservas 
            WHERE (fecha_entrada < :fechaFin) AND (fecha_salida > :fechaInicio)
        )";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':fechaFin', $fechaFin);
$stmt->bindValue(':fechaInicio', $fechaInicio);
$stmt->execute();
$disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $disponibles]);
exit;
