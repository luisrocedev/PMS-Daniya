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
        $stmt = $pdo->prepare("SELECT SUM(total) as total FROM facturas WHERE DATE_FORMAT(fecha_emision, '%Y-%m') = :mes");
        $stmt->execute([':mes' => $mes_actual]);
        $ingresos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Ocupación media
        $stmt = $pdo->query("SELECT ROUND(AVG(ocupacion), 2) as media FROM (
            SELECT COUNT(*) * 100.0 / (SELECT COUNT(*) FROM habitaciones) as ocupacion
            FROM reservas r
            WHERE r.estado_reserva IN ('CheckIn', 'Confirmada')
            AND MONTH(r.fecha_entrada) = MONTH(CURRENT_DATE)
            GROUP BY DATE(r.fecha_entrada)
        ) t");
        $ocupacion = $stmt->fetch(PDO::FETCH_ASSOC)['media'] ?? 0;

        // Total reservas del mes
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservas WHERE DATE_FORMAT(fecha_entrada, '%Y-%m') = :mes");
        $stmt->execute([':mes' => $mes_actual]);
        $reservas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        echo json_encode([
            'ingresos_mes' => number_format($ingresos, 2),
            'ocupacion_media' => number_format($ocupacion, 1),
            'reservas_mes' => $reservas
        ]);
        break;

    case 'ingresos_mensuales':
        try {
            $year = $_GET['year'] ?? date('Y');
            $export = $_GET['export'] ?? '';

            $sql = "SELECT 
                    MONTH(fecha_emision) as mes,
                    ROUND(SUM(total), 2) as total_mes
                    FROM facturas
                    WHERE YEAR(fecha_emision) = :year
                    GROUP BY MONTH(fecha_emision)
                    ORDER BY mes";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':year' => $year]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Asegurarnos de que tenemos los 12 meses con valores
            $data = [];
            for ($i = 1; $i <= 12; $i++) {
                $encontrado = false;
                foreach ($result as $row) {
                    if ((int)$row['mes'] === $i) {
                        $data[] = [
                            'mes' => $i,
                            'total_mes' => (float)$row['total_mes']
                        ];
                        $encontrado = true;
                        break;
                    }
                }
                if (!$encontrado) {
                    $data[] = ['mes' => $i, 'total_mes' => 0];
                }
            }

            if ($export) {
                // Manejar exportaciones si se implementan
                switch ($export) {
                    case 'csv':
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="ingresos_mensuales.csv"');
                        $output = fopen('php://output', 'w');
                        fputcsv($output, ['Mes', 'Total']);
                        foreach ($data as $row) {
                            $nombreMes = date('F', mktime(0, 0, 0, $row['mes'], 1));
                            fputcsv($output, [$nombreMes, $row['total_mes']]);
                        }
                        fclose($output);
                        exit;
                        // Implementar otros formatos según sea necesario
                }
            }

            echo json_encode($data);
        } catch (Exception $e) {
            error_log("Error en ingresos_mensuales: " . $e->getMessage());
            echo json_encode(['error' => 'Error al procesar los datos de ingresos mensuales']);
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
