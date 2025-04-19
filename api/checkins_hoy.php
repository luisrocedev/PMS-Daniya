<?php
// api/checkins_hoy.php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

// Selecciona las reservas que hicieron check‑in hoy
$sql = "
  SELECT ci.id_reserva, ci.fecha_checkin,
         r.id_cliente, c.nombre, c.apellidos, h.numero_habitacion
    FROM checkin_info ci
    JOIN reservas r ON ci.id_reserva = r.id_reserva
    JOIN clientes c ON r.id_cliente = c.id_cliente
    JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
   WHERE DATE(ci.fecha_checkin) = CURDATE()
   ORDER BY ci.fecha_checkin DESC
";
$stmt = $pdo->query($sql);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($datos);
