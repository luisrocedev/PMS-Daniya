<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['error' => 'No autenticado']);
  exit;
}

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

// Obtener check-ins del día actual
$sqlCheckins = "SELECT 
    r.id_reserva,
    TIME(r.fecha_entrada) as hora,
    r.fecha_entrada as fecha_checkin,
    CONCAT(c.nombre, ' ', c.apellidos) as cliente,
    c.nombre,
    c.apellidos,
    c.email,
    h.numero_habitacion,
    r.estado_reserva as estado
FROM reservas r
INNER JOIN clientes c ON r.id_cliente = c.id_cliente
INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
WHERE DATE(r.fecha_entrada) = CURRENT_DATE
ORDER BY r.fecha_entrada ASC";

$checkins = $pdo->query($sqlCheckins)->fetchAll(PDO::FETCH_ASSOC);

// Obtener check-ins pendientes
$sqlPendientes = "SELECT r.id_reserva
FROM reservas r
WHERE DATE(r.fecha_entrada) = CURRENT_DATE 
AND r.estado_reserva = 'Confirmada'";

$pendientes = $pdo->query($sqlPendientes)->fetchAll(PDO::FETCH_ASSOC);

// Obtener check-ins completados hoy
$sqlCompletados = "SELECT r.id_reserva
FROM reservas r
WHERE DATE(r.fecha_entrada) = CURRENT_DATE 
AND r.estado_reserva = 'CheckIn'";

$completados = $pdo->query($sqlCompletados)->fetchAll(PDO::FETCH_ASSOC);

// Obtener próximas llegadas (próxima hora)
$sqlProximaHora = "SELECT 
    r.id_reserva,
    r.fecha_entrada,
    c.nombre,
    c.apellidos,
    h.numero_habitacion
FROM reservas r
INNER JOIN clientes c ON r.id_cliente = c.id_cliente
INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
WHERE DATE(r.fecha_entrada) = CURRENT_DATE 
AND TIME(r.fecha_entrada) BETWEEN CURRENT_TIME AND ADDTIME(CURRENT_TIME, '1:00:00')
AND r.estado_reserva = 'Confirmada'
ORDER BY r.fecha_entrada ASC";

$proximaHora = $pdo->query($sqlProximaHora)->fetchAll(PDO::FETCH_ASSOC);

// Obtener próximas llegadas del día
$sqlProximasLlegadas = "SELECT 
    r.id_reserva,
    r.fecha_entrada,
    c.nombre,
    c.apellidos,
    h.numero_habitacion
FROM reservas r
INNER JOIN clientes c ON r.id_cliente = c.id_cliente
INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
WHERE DATE(r.fecha_entrada) = CURRENT_DATE 
AND TIME(r.fecha_entrada) > CURRENT_TIME
AND r.estado_reserva = 'Confirmada'
ORDER BY r.fecha_entrada ASC
LIMIT 5";

$proximasLlegadas = $pdo->query($sqlProximasLlegadas)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'checkins' => $checkins,
  'pendientes' => $pendientes,
  'completados' => $completados,
  'proximaHora' => $proximaHora,
  'proximasLlegadas' => $proximasLlegadas
]);
