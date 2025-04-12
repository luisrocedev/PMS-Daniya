<?php
// api/reservas_calendar.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode([]);
  exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$pdo = Database::getInstance()->getConnection();

// Recoger parámetros opcionales
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$start = isset($_GET['start']) ? $_GET['start'] : null;
$end   = isset($_GET['end'])   ? $_GET['end']   : null;

if ($tipo) {
  // Si se solicita filtrar por tipo de habitación, realizamos join con la tabla habitaciones
  $sql = "SELECT r.* 
            FROM reservas r 
            JOIN habitaciones h ON r.id_habitacion = h.id_habitacion 
            WHERE h.tipo_habitacion = :tipo";
  // Si además se enviaron parámetros de fecha, agregamos el filtro
  if ($start && $end) {
    $sql .= " AND r.fecha_entrada BETWEEN :start AND :end";
  }
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':tipo', $tipo);
  if ($start && $end) {
    $stmt->bindValue(':start', $start);
    $stmt->bindValue(':end', $end);
  }
  $stmt->execute();
  $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Si no se filtra por tipo, se obtienen todas las reservas
  // También se puede agregar filtro de fecha aquí si lo deseas
  $superModel = new SuperModel();
  $reservas = $superModel->getAll('reservas');
}

$eventos = [];
foreach ($reservas as $res) {
  $idRes = $res['id_reserva'];
  $idCli = $res['id_cliente'];
  $idHab = $res['id_habitacion'];
  $fEnt = $res['fecha_entrada']; // Ej: "2025-04-15"
  $fSal = $res['fecha_salida'];  // Ej: "2025-04-17"
  $estado = $res['estado_reserva'];

  // Título del evento: incluye ID, habitación y estado (puedes mejorar el detalle)
  $titleEvento = "Reserva #$idRes (Hab $idHab) - $estado";

  // Para que el día de salida se muestre completo en FullCalendar (sumar 1 día)
  $fechaSalidaInclusiva = date('Y-m-d', strtotime($fSal . ' +1 day'));

  $eventos[] = [
    'id'    => $idRes,
    'title' => $titleEvento,
    'start' => $fEnt,
    'end'   => $fechaSalidaInclusiva
  ];
}

echo json_encode($eventos);
