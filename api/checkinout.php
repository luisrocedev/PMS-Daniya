<?php
header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(403);
        throw new Exception('No autorizado');
    }

    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/SuperModel.php';

    $pdo = Database::getInstance()->getConnection();

    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }

    $sm = new SuperModel();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        try {
            // Consultar reservas confirmadas (para Check-in)
            $sql1 = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente,
                            h.numero_habitacion as id_habitacion
                     FROM reservas r
                     JOIN clientes c ON r.id_cliente = c.id_cliente
                     JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
                     WHERE r.estado_reserva = 'Confirmada'
                     AND DATE(r.fecha_entrada) <= CURRENT_DATE";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute();
            $pendCheckIn = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            // Consultar reservas en CheckIn (para Check-out)
            $sql2 = "SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente,
                            h.numero_habitacion as id_habitacion
                     FROM reservas r
                     JOIN clientes c ON r.id_cliente = c.id_cliente
                     JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
                     WHERE r.estado_reserva = 'CheckIn'
                     AND DATE(r.fecha_salida) <= CURRENT_DATE";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute();
            $pendCheckOut = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Obtener estadísticas de completados hoy
            $sqlStats = "SELECT 
                COUNT(CASE WHEN estado_reserva = 'CheckIn' AND DATE(fecha_entrada) = CURRENT_DATE THEN 1 END) as checkins,
                COUNT(CASE WHEN estado_reserva = 'CheckOut' AND DATE(fecha_salida) = CURRENT_DATE THEN 1 END) as checkouts
                FROM reservas";
            $stmt3 = $pdo->prepare($sqlStats);
            $stmt3->execute();
            $stats = $stmt3->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'pendientesCheckIn' => $pendCheckIn ?: [],
                'pendientesCheckOut' => $pendCheckOut ?: [],
                'completadosHoy' => $stats ?: ['checkins' => 0, 'checkouts' => 0]
            ]);
        } catch (PDOException $e) {
            throw new Exception('Error al obtener datos de reservas: ' . $e->getMessage());
        }
    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        $id_reserva = intval($_POST['id_reserva'] ?? 0);

        if (!$id_reserva) {
            throw new Exception('Falta id_reserva');
        }

        // Estado actual de la reserva
        $res = $sm->getById('reservas', $id_reserva);
        if (!$res) {
            throw new Exception('Reserva no encontrada');
        }
        $actual = $res['estado_reserva'];

        if ($action === 'checkin') {
            if ($actual !== 'Confirmada') {
                throw new Exception("No se puede hacer CheckIn si la reserva no está Confirmada. Estado actual: $actual");
            }
            $sm->update('reservas', $id_reserva, [
                'estado_reserva' => 'CheckIn'
            ]);
            echo json_encode(['success' => true, 'msg' => 'Check-in realizado']);
        } elseif ($action === 'checkout') {
            if ($actual !== 'CheckIn') {
                throw new Exception("No se puede hacer CheckOut si la reserva no está en CheckIn. Estado actual: $actual");
            }

            // Iniciar transacción
            $pdo->beginTransaction();
            try {
                // 1) Total cargos no pagados
                $stmtC = $pdo->prepare("SELECT COALESCE(SUM(importe), 0) AS total FROM cargos WHERE id_reserva=:id AND pagado=0");
                $stmtC->execute([':id' => $id_reserva]);
                $row = $stmtC->fetch(PDO::FETCH_ASSOC);
                $totalCargos = $row['total'];

                // 2) Total base de habitación
                $stmtR = $pdo->prepare("SELECT total FROM reservas WHERE id_reserva=:id");
                $stmtR->execute([':id' => $id_reserva]);
                $r2 = $stmtR->fetch(PDO::FETCH_ASSOC);
                $totalHab = $r2['total'] ?? 0;

                // 3) Detalle de todos los cargos
                $stmtD = $pdo->prepare("SELECT id_cargo, descripcion, importe, fecha FROM cargos WHERE id_reserva=:id");
                $stmtD->execute([':id' => $id_reserva]);
                $detalle = $stmtD->fetchAll(PDO::FETCH_ASSOC);

                // 4) Crear factura
                $factTotal = $totalHab + $totalCargos;
                $sm->create('facturas', [
                    'id_reserva' => $id_reserva,
                    'fecha_emision' => date('Y-m-d'),
                    'total' => $factTotal,
                    'metodo_pago' => $_POST['metodo_pago'] ?? 'Efectivo',
                    'detalle' => json_encode($detalle)
                ]);

                // 5) Marcar cargos como pagados
                $upd = $pdo->prepare("UPDATE cargos SET pagado=1 WHERE id_reserva=:id");
                $upd->execute([':id' => $id_reserva]);

                // 6) Actualizar estado reserva
                $sm->update('reservas', $id_reserva, [
                    'estado_reserva' => 'CheckOut'
                ]);

                $pdo->commit();
                echo json_encode(['success' => true, 'msg' => 'Check-out realizado correctamente']);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw new Exception('Error al procesar el check-out: ' . $e->getMessage());
            }
        } else {
            throw new Exception('Acción no válida');
        }
    } else {
        throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
