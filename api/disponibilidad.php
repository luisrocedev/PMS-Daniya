<?php
// api/disponibilidad.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';

$pdo = Database::getInstance()->getConnection();

// Validar parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? null;
$fechaFin = $_GET['fecha_fin'] ?? null;

if (!$fechaInicio || !$fechaFin) {
    echo json_encode([
        'error' => 'Se requieren fechas de inicio y fin'
    ]);
    exit;
}

try {
    // Buscar habitaciones disponibles
    $sql = "
        SELECT h.* 
        FROM habitaciones h
        WHERE h.estado = 'Disponible'
        AND h.id_habitacion NOT IN (
            SELECT r.id_habitacion
            FROM reservas r
            WHERE r.estado_reserva IN ('Confirmada', 'CheckIn')
            AND (
                (r.fecha_entrada <= :fecha_inicio AND r.fecha_salida > :fecha_inicio)
                OR (r.fecha_entrada < :fecha_fin AND r.fecha_salida >= :fecha_fin)
                OR (r.fecha_entrada >= :fecha_inicio AND r.fecha_salida <= :fecha_fin)
            )
        )
        ORDER BY h.numero_habitacion
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fecha_inicio' => $fechaInicio,
        ':fecha_fin' => $fechaFin
    ]);

    $habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $habitaciones
    ]);
} catch (PDOException $e) {
    error_log("Error en disponibilidad.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error al buscar habitaciones disponibles'
    ]);
}
