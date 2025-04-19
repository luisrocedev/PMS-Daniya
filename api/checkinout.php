<?php
// api/checkinout.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$pdo   = Database::getInstance()->getConnection();
$sm    = new SuperModel();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Consultar reservas confirmadas (para Check-in)
    $sql1 = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
             FROM reservas r
             JOIN clientes c ON r.id_cliente = c.id_cliente
             WHERE r.estado_reserva = 'Confirmada'";
    $pendCheckIn  = $pdo->query($sql1)->fetchAll(PDO::FETCH_ASSOC);

    // Consultar reservas en CheckIn (para Check-out)
    $sql2 = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
             FROM reservas r
             JOIN clientes c ON r.id_cliente = c.id_cliente
             WHERE r.estado_reserva = 'CheckIn'";
    $pendCheckOut = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'pendientesCheckIn'  => $pendCheckIn,
        'pendientesCheckOut' => $pendCheckOut
    ]);
    exit;
} elseif ($method === 'POST') {
    $action     = $_POST['action'] ?? '';
    $id_reserva = intval($_POST['id_reserva'] ?? 0);
    if (!$id_reserva) {
        echo json_encode(['error' => 'Falta id_reserva']);
        exit;
    }

    // Estado actual de la reserva
    $res = $sm->getById('reservas', $id_reserva);
    if (!$res) {
        echo json_encode(['error' => 'Reserva no encontrada']);
        exit;
    }
    $actual = $res['estado_reserva'];

    if ($action === 'checkin') {
        if ($actual !== 'Confirmada') {
            echo json_encode(['error' => "No se puede hacer CheckIn si la reserva no está Confirmada. Estado actual: $actual"]);
            exit;
        }
        $sm->update('reservas', $id_reserva, ['estado_reserva' => 'CheckIn']);
        echo json_encode(['success' => true, 'msg' => 'Check-in realizado']);
        exit;
    } elseif ($action === 'checkout') {
        if ($actual !== 'CheckIn') {
            echo json_encode(['error' => "No se puede hacer CheckOut si la reserva no está en CheckIn. Estado actual: $actual"]);
            exit;
        }
        // 1) Total cargos no pagados
        $stmtC = $pdo->prepare("SELECT SUM(importe) AS total FROM cargos WHERE id_reserva=:id AND pagado=0");
        $stmtC->execute([':id' => $id_reserva]);
        $row    = $stmtC->fetch(PDO::FETCH_ASSOC);
        $totalCargos = $row['total'] ?? 0;

        // 2) Total base de habitación (columna total en reservas)
        $stmtR = $pdo->prepare("SELECT total FROM reservas WHERE id_reserva=:id");
        $stmtR->execute([':id' => $id_reserva]);
        $r2        = $stmtR->fetch(PDO::FETCH_ASSOC);
        $totalHab  = $r2['total'] ?? 0;

        // 3) Detalle de todos los cargos
        $stmtD = $pdo->prepare("SELECT id_cargo, descripcion, importe, fecha FROM cargos WHERE id_reserva=:id");
        $stmtD->execute([':id' => $id_reserva]);
        $detalle = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        // 4) Crear factura con detalle JSON
        $factTotal = $totalHab + $totalCargos;
        $sm->create('facturas', [
            'id_reserva'    => $id_reserva,
            'fecha_emision' => date('Y-m-d'),
            'total'         => $factTotal,
            'metodo_pago'   => $_POST['metodo_pago'] ?? 'Efectivo',
            'detalle'       => json_encode($detalle)
        ]);

        // 5) Marcar cargos como pagados
        $upd = $pdo->prepare("UPDATE cargos SET pagado=1 WHERE id_reserva=:id");
        $upd->execute([':id' => $id_reserva]);

        // 6) Actualizar estado reserva
        $sm->update('reservas', $id_reserva, ['estado_reserva' => 'CheckOut']);
        echo json_encode(['success' => true, 'msg' => 'Check-out realizado']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
