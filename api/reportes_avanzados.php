<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'stats_generales':
        // Estadísticas del mes actual
        $mes_actual = date('Y-m');

        // Ingresos del mes
        $sql_ingresos = "SELECT SUM(total) as total FROM facturas 
                        WHERE DATE_FORMAT(fecha_emision, '%Y-%m') = :mes";
        $stmt = $pdo->prepare($sql_ingresos);
        $stmt->execute([':mes' => $mes_actual]);
        $ingresos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Ocupación media
        $sql_ocupacion = "SELECT 
            ROUND(AVG(
                (SELECT COUNT(*) FROM reservas r2 
                WHERE r2.estado_reserva IN ('CheckIn', 'Confirmada')
                AND DATE(r2.fecha_entrada) <= DATE(r1.fecha)
                AND DATE(r2.fecha_salida) > DATE(r1.fecha))
            / (SELECT COUNT(*) FROM habitaciones) * 100
            ), 2) as ocupacion_media
            FROM (
                SELECT CURDATE() - INTERVAL n DAY as fecha
                FROM (
                    SELECT @row := @row + 1 as n
                    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) t1,
                        (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) t2,
                        (SELECT @row := -1) t0
                    LIMIT 30
                ) numbers
            ) dates";
        $ocupacion = $pdo->query($sql_ocupacion)->fetch(PDO::FETCH_ASSOC)['ocupacion_media'] ?? 0;

        // Reservas del mes
        $sql_reservas = "SELECT COUNT(*) as total FROM reservas 
                        WHERE DATE_FORMAT(fecha_entrada, '%Y-%m') = :mes";
        $stmt = $pdo->prepare($sql_reservas);
        $stmt->execute([':mes' => $mes_actual]);
        $reservas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        echo json_encode([
            'ingresos_mes' => $ingresos,
            'ocupacion_media' => $ocupacion,
            'reservas_mes' => $reservas
        ]);
        break;

    case 'ingresos_daily':
        // Ingresos diarios últimos 30 días
        $sql = "SELECT 
            DATE(fecha_emision) as fecha,
            SUM(total) as total
            FROM facturas
            WHERE fecha_emision >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY DATE(fecha_emision)
            ORDER BY fecha";

        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $labels = array_map(function ($row) {
            return date('d/m', strtotime($row['fecha']));
        }, $result);

        $valores = array_column($result, 'total');

        echo json_encode(['labels' => $labels, 'valores' => $valores]);
        break;

    case 'ingresos_weekly':
        // Ingresos semanales últimas 12 semanas
        $sql = "SELECT 
            YEARWEEK(fecha_emision, 1) as semana,
            SUM(total) as total
            FROM facturas
            WHERE fecha_emision >= DATE_SUB(CURRENT_DATE, INTERVAL 12 WEEK)
            GROUP BY YEARWEEK(fecha_emision, 1)
            ORDER BY semana";

        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $labels = array_map(function ($row) {
            return 'Semana ' . substr($row['semana'], -2);
        }, $result);

        $valores = array_column($result, 'total');

        echo json_encode(['labels' => $labels, 'valores' => $valores]);
        break;

    case 'ingresos_monthly':
        $year = $_GET['year'] ?? date('Y');
        $sql = "SELECT 
            MONTH(fecha_emision) as mes,
            SUM(total) as total
            FROM facturas
            WHERE YEAR(fecha_emision) = :year
            GROUP BY MONTH(fecha_emision)
            ORDER BY mes";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':year' => $year]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = array_map(function ($row) {
            return date('F', mktime(0, 0, 0, $row['mes'], 1));
        }, $result);

        $valores = array_column($result, 'total');

        echo json_encode(['labels' => $labels, 'valores' => $valores]);
        break;

    case 'ocupacion':
        $month = $_GET['month'] ?? date('Y-m');

        // Datos de ocupación diaria
        $sql_ocupacion = "SELECT 
            DATE_FORMAT(fecha, '%d/%m') as fecha,
            ROUND(
                (SELECT COUNT(*) FROM reservas r 
                WHERE r.estado_reserva IN ('CheckIn', 'Confirmada')
                AND DATE(r.fecha_entrada) <= fecha
                AND DATE(r.fecha_salida) > fecha)
            / (SELECT COUNT(*) FROM habitaciones) * 100
            , 2) as porcentaje
            FROM (
                SELECT DATE(:month_start + INTERVAL n DAY) as fecha
                FROM (
                    SELECT @row := @row + 1 as n
                    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) t1,
                    (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) t2,
                    (SELECT @row := -1) t0
                    LIMIT 31
                ) numbers
                WHERE DATE(:month_start + INTERVAL n DAY) <= LAST_DAY(:month_start)
            ) dates";

        $stmt = $pdo->prepare($sql_ocupacion);
        $stmt->execute([':month_start' => $month . '-01']);
        $ocupacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Estadísticas por tipo de habitación
        $sql_tipos = "SELECT 
            th.nombre,
            COUNT(h.id_habitacion) as disponibles,
            (SELECT COUNT(*) FROM reservas r 
             WHERE r.id_habitacion = h.id_habitacion 
             AND r.estado_reserva IN ('CheckIn', 'Confirmada')
             AND DATE_FORMAT(r.fecha_entrada, '%Y-%m') = :month) as ocupadas,
            ROUND(
                (SELECT COUNT(*) FROM reservas r 
                 WHERE r.id_habitacion = h.id_habitacion 
                 AND r.estado_reserva IN ('CheckIn', 'Confirmada')
                 AND DATE_FORMAT(r.fecha_entrada, '%Y-%m') = :month)
                / COUNT(h.id_habitacion) * 100
            , 2) as porcentaje,
            ROUND(AVG(r2.total), 2) as ingreso_medio
            FROM habitaciones h
            JOIN tipos_habitacion th ON h.id_tipo = th.id_tipo
            LEFT JOIN reservas r2 ON h.id_habitacion = r2.id_habitacion 
                AND DATE_FORMAT(r2.fecha_entrada, '%Y-%m') = :month
            GROUP BY th.id_tipo";

        $stmt = $pdo->prepare($sql_tipos);
        $stmt->execute([':month' => $month]);
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'dates' => array_column($ocupacion, 'fecha'),
            'ocupacion' => array_column($ocupacion, 'porcentaje'),
            'roomTypes' => $tipos
        ]);
        break;

    case 'reservas':
        $metric = $_GET['metric'] ?? 'status';
        $data = [];

        switch ($metric) {
            case 'status':
                $sql = "SELECT 
                    estado_reserva as label,
                    COUNT(*) as valor
                    FROM reservas
                    WHERE fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    GROUP BY estado_reserva";
                break;

            case 'channel':
                $sql = "SELECT 
                    canal_reserva as label,
                    COUNT(*) as valor
                    FROM reservas
                    WHERE fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    GROUP BY canal_reserva";
                break;

            case 'cancelled':
                $sql = "SELECT 
                    DATE_FORMAT(fecha_cancelacion, '%d/%m') as label,
                    COUNT(*) as valor
                    FROM reservas
                    WHERE estado_reserva = 'Cancelada'
                    AND fecha_cancelacion >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    GROUP BY DATE(fecha_cancelacion)";
                break;
        }

        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Tendencias de reserva
        $sql_trends = "SELECT 
            DATE_FORMAT(fecha_entrada, '%d/%m') as fecha,
            COUNT(*) as total
            FROM reservas
            WHERE fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY DATE(fecha_entrada)
            ORDER BY fecha_entrada";

        $trends = $pdo->query($sql_trends)->fetchAll(PDO::FETCH_ASSOC);

        // Estancia media por tipo de habitación
        $sql_stay = "SELECT 
            th.nombre as label,
            ROUND(AVG(DATEDIFF(r.fecha_salida, r.fecha_entrada)), 1) as valor
            FROM reservas r
            JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
            JOIN tipos_habitacion th ON h.id_tipo = th.id_tipo
            WHERE r.fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY th.id_tipo";

        $avgStay = $pdo->query($sql_stay)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'labels' => array_column($result, 'label'),
            'valores' => array_column($result, 'valor'),
            'trends' => [
                'labels' => array_column($trends, 'fecha'),
                'valores' => array_column($trends, 'total')
            ],
            'avgStay' => [
                'labels' => array_column($avgStay, 'label'),
                'valores' => array_column($avgStay, 'valor')
            ]
        ]);
        break;

    case 'export_financial':
    case 'export_occupancy':
    case 'export_reservations':
        $format = $_GET['format'] ?? 'pdf';

        // Implementar exportación según el formato
        switch ($format) {
            case 'pdf':
                require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
                // Implementar generación de PDF
                break;

            case 'excel':
                require_once __DIR__ . '/../vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';
                // Implementar generación de Excel
                break;

            case 'csv':
                // Implementar exportación CSV
                break;
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
}
