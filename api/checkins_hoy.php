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
$sql = "SELECT 
            r.id_reserva,
            TIME(r.fecha_entrada) as hora,
            CONCAT(c.nombre, ' ', c.apellidos) as cliente,
            h.numero_habitacion as habitacion,
            r.estado_reserva as estado
        FROM reservas r
        INNER JOIN clientes c ON r.id_cliente = c.id_cliente
        INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
        WHERE DATE(r.fecha_entrada) = CURRENT_DATE
        AND r.estado_reserva IN ('Confirmada', 'CheckIn')
        ORDER BY r.fecha_entrada ASC";

$stmt = $pdo->query($sql);
$checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($checkins);
