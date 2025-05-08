<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';

$pdo = Database::getInstance()->getConnection();

try {
    // Obtener total de habitaciones
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM habitaciones");
    $totalHabitaciones = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtener habitaciones disponibles
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as disponibles 
        FROM habitaciones h
        WHERE h.id_habitacion NOT IN (
            SELECT id_habitacion 
            FROM reservas 
            WHERE estado_reserva IN ('Confirmada', 'CheckIn')
            AND fecha_entrada <= CURRENT_DATE 
            AND fecha_salida > CURRENT_DATE
        )
        AND h.estado = 'Disponible'
    ");
    $stmt->execute();
    $disponibles = $stmt->fetch(PDO::FETCH_ASSOC)['disponibles'];

    // Obtener check-ins para hoy
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as checkin 
        FROM reservas 
        WHERE fecha_entrada = CURRENT_DATE 
        AND estado_reserva IN ('Confirmada', 'Pendiente')
    ");
    $stmt->execute();
    $checkinHoy = $stmt->fetch(PDO::FETCH_ASSOC)['checkin'];

    // Obtener check-outs para hoy
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as checkout 
        FROM reservas 
        WHERE fecha_salida = CURRENT_DATE 
        AND estado_reserva = 'CheckIn'
    ");
    $stmt->execute();
    $checkoutHoy = $stmt->fetch(PDO::FETCH_ASSOC)['checkout'];

    // Calcular porcentaje de ocupación
    $ocupadas = $totalHabitaciones - $disponibles;
    $porcentajeOcupacion = $totalHabitaciones > 0 ? round(($ocupadas / $totalHabitaciones) * 100) : 0;

    echo json_encode([
        'disponibles' => $disponibles,
        'checkin_hoy' => $checkinHoy,
        'checkout_hoy' => $checkoutHoy,
        'ocupacion' => $porcentajeOcupacion
    ]);
} catch (PDOException $e) {
    error_log("Error en estadisticas_calendario.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener estadísticas']);
}
