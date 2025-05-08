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

// Recoger parámetros
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;

try {
  // Construir consulta base
  $sql = "SELECT r.*, h.numero_habitacion, h.tipo_habitacion, c.nombre, c.apellidos 
            FROM reservas r 
            JOIN habitaciones h ON r.id_habitacion = h.id_habitacion 
            LEFT JOIN clientes c ON r.id_cliente = c.id_cliente 
            WHERE 1=1";

  $params = [];

  // Añadir filtros si existen
  if ($tipo) {
    $sql .= " AND h.tipo_habitacion = :tipo";
    $params[':tipo'] = $tipo;
  }

  if ($estado) {
    $sql .= " AND r.estado_reserva = :estado";
    $params[':estado'] = $estado;
  }

  if ($start && $end) {
    $sql .= " AND r.fecha_entrada < :end AND r.fecha_salida > :start";
    $params[':start'] = $start;
    $params[':end'] = $end;
  }

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $eventos = [];
  foreach ($reservas as $res) {
    // Crear título descriptivo
    $titulo = sprintf(
      "Habitación %s - %s %s",
      $res['numero_habitacion'],
      $res['nombre'],
      $res['apellidos']
    );

    // Asignar color según estado
    $color = match ($res['estado_reserva']) {
      'Pendiente' => '#ffc107', // Amarillo
      'Confirmada' => '#0d6efd', // Azul
      'CheckIn' => '#198754', // Verde
      'CheckOut' => '#dc3545', // Rojo
      'Cancelada' => '#6c757d', // Gris
      default => '#0dcaf0' // Cyan para otros casos
    };

    // Para que el día de salida se muestre completo
    $fechaSalidaInclusiva = date('Y-m-d', strtotime($res['fecha_salida'] . ' +1 day'));

    $eventos[] = [
      'id' => $res['id_reserva'],
      'title' => $titulo,
      'start' => $res['fecha_entrada'],
      'end' => $fechaSalidaInclusiva,
      'backgroundColor' => $color,
      'borderColor' => $color,
      'textColor' => '#ffffff',
      'extendedProps' => [
        'descripcion' => "Estado: {$res['estado_reserva']}"
      ]
    ];
  }

  echo json_encode($eventos);
} catch (PDOException $e) {
  error_log("Error en reservas_calendar.php: " . $e->getMessage());
  echo json_encode(['error' => 'Error al cargar las reservas']);
}
