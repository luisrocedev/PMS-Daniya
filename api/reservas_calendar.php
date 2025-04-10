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

// Obtenemos las reservas
$superModel = new SuperModel();
$reservas = $superModel->getAll('reservas');  // Devuelve todas

/* Formato FullCalendar:
[
  {
    "title": "Reserva #3 - Cliente 1",
    "start": "2025-04-15",
    "end":   "2025-04-17"
  },
  ...
]
*/

// Convertimos cada reserva a un evento del calendario
$eventos = [];

foreach ($reservas as $res) {
    $idRes = $res['id_reserva'];
    $idCli = $res['id_cliente'];
    $idHab = $res['id_habitacion'];
    $fEnt = $res['fecha_entrada']; // "2025-04-15"
    $fSal = $res['fecha_salida'];  // "2025-04-17"
    $estado = $res['estado_reserva'];

    // Podrías buscar el nombre del cliente para el 'title'
    // pero con "Reserva #$idRes" ya nos sirve.
    $titleEvento = "Reserva #$idRes (Hab $idHab) - $estado";

    // FullCalendar espera que 'end' sea exclusivo: si la reserva termina el 17,
    // el evento se dibuja hasta el 16 inclusive (dependiendo de la config).
    // A veces conviene sumar un día a 'end' para ver el día de salida completo.
    // Depende de tu preferencia. EJEMPLO:
    $fechaSalidaInclusiva = date('Y-m-d', strtotime($fSal . ' +1 day'));

    $eventos[] = [
        'id'    => $idRes,
        'title' => $titleEvento,
        'start' => $fEnt,               // '2025-04-15'
        'end'   => $fechaSalidaInclusiva // '2025-04-18' si sumaste 1 día
    ];
}

// Devolvemos en JSON
echo json_encode($eventos);
