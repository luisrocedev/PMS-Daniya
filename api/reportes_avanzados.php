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
$year = $_GET['year'] ?? date('Y');
$periodo = $_GET['periodo'] ?? 'mensual';
$export = $_GET['export'] ?? '';

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
    case 'ingresos':
        try {
            $sql = "";

            // Seleccionar query según el periodo
            if ($periodo == 'trimestral') {
                $sql = "SELECT 
                        QUARTER(fecha_emision) as periodo,
                        ROUND(SUM(total), 2) as total
                        FROM facturas
                        WHERE YEAR(fecha_emision) = :year
                        GROUP BY QUARTER(fecha_emision)
                        ORDER BY periodo";
            } else if ($periodo == 'anual') {
                $sql = "SELECT 
                        YEAR(fecha_emision) as periodo,
                        ROUND(SUM(total), 2) as total
                        FROM facturas
                        WHERE YEAR(fecha_emision) BETWEEN :year-5 AND :year
                        GROUP BY YEAR(fecha_emision)
                        ORDER BY periodo";
            } else { // mensual (default)
                $sql = "SELECT 
                        MONTH(fecha_emision) as mes,
                        ROUND(SUM(total), 2) as total_mes
                        FROM facturas
                        WHERE YEAR(fecha_emision) = :year
                        GROUP BY MONTH(fecha_emision)
                        ORDER BY mes";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':year' => $year]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Procesar resultados según el periodo
            $data = [];

            if ($periodo == 'mensual') {
                // Asegurarnos de que tenemos los 12 meses con valores
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
            } else if ($periodo == 'trimestral') {
                // Asegurarnos de que tenemos los 4 trimestres
                for ($i = 1; $i <= 4; $i++) {
                    $encontrado = false;
                    foreach ($result as $row) {
                        if ((int)$row['periodo'] === $i) {
                            $data[] = [
                                'periodo' => $i,
                                'total' => (float)$row['total']
                            ];
                            $encontrado = true;
                            break;
                        }
                    }
                    if (!$encontrado) {
                        $data[] = ['periodo' => $i, 'total' => 0];
                    }
                }
            } else { // anual
                // Devolver directamente los datos anuales
                $data = $result;
            }

            if ($export) {
                // Manejar exportaciones si se implementan
                switch ($export) {
                    case 'csv':
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="ingresos_' . $periodo . '.csv"');
                        $output = fopen('php://output', 'w');

                        if ($periodo == 'mensual') {
                            fputcsv($output, ['Mes', 'Total']);
                            foreach ($data as $row) {
                                $nombreMes = date('F', mktime(0, 0, 0, $row['mes'], 1));
                                fputcsv($output, [$nombreMes, $row['total_mes']]);
                            }
                        } else if ($periodo == 'trimestral') {
                            fputcsv($output, ['Trimestre', 'Total']);
                            foreach ($data as $row) {
                                fputcsv($output, ['Q' . $row['periodo'], $row['total']]);
                            }
                        } else { // anual
                            fputcsv($output, ['Año', 'Total']);
                            foreach ($data as $row) {
                                fputcsv($output, [$row['periodo'], $row['total']]);
                            }
                        }

                        fclose($output);
                        exit;

                    case 'pdf':
                        // Placeholder para implementación de PDF
                        echo json_encode(['error' => 'Exportación a PDF no implementada aún']);
                        exit;

                    case 'xlsx':
                        // Placeholder para implementación de Excel
                        echo json_encode(['error' => 'Exportación a Excel no implementada aún']);
                        exit;
                }
            }

            echo json_encode($data);
        } catch (Exception $e) {
            error_log("Error en reporte de ingresos: " . $e->getMessage());
            echo json_encode(['error' => 'Error al procesar los datos de ingresos: ' . $e->getMessage()]);
        }
        break;

    case 'ocupacion':
        try {
            $sql = "";

            if ($periodo == 'mensual') {
                $sql = "SELECT 
                        MONTH(fecha_entrada) as mes,
                        ROUND(COUNT(*) * 100.0 / 
                            (SELECT COUNT(*) FROM habitaciones) / 
                            DAY(LAST_DAY(CONCAT(:year, '-', MONTH(fecha_entrada), '-01')))
                        , 2) as total_mes
                        FROM reservas
                        WHERE YEAR(fecha_entrada) = :year
                        AND estado_reserva IN ('CheckIn', 'Confirmada')
                        GROUP BY MONTH(fecha_entrada)
                        ORDER BY mes";
            } else if ($periodo == 'trimestral') {
                $sql = "SELECT 
                        QUARTER(fecha_entrada) as periodo,
                        ROUND(COUNT(*) * 100.0 / 
                            ((SELECT COUNT(*) FROM habitaciones) * 
                             (CASE 
                                WHEN QUARTER(fecha_entrada) = 1 THEN 90
                                WHEN QUARTER(fecha_entrada) = 2 THEN 91
                                WHEN QUARTER(fecha_entrada) = 3 THEN 92
                                ELSE 92
                             END))
                        , 2) as total
                        FROM reservas
                        WHERE YEAR(fecha_entrada) = :year
                        AND estado_reserva IN ('CheckIn', 'Confirmada')
                        GROUP BY QUARTER(fecha_entrada)
                        ORDER BY periodo";
            } else { // anual
                $sql = "SELECT 
                        YEAR(fecha_entrada) as periodo,
                        ROUND(COUNT(*) * 100.0 / 
                            ((SELECT COUNT(*) FROM habitaciones) * 
                             CASE 
                                WHEN (YEAR(fecha_entrada) % 4 = 0 AND YEAR(fecha_entrada) % 100 <> 0) OR YEAR(fecha_entrada) % 400 = 0 THEN 366
                                ELSE 365
                             END)
                        , 2) as total
                        FROM reservas
                        WHERE YEAR(fecha_entrada) BETWEEN :year-5 AND :year
                        AND estado_reserva IN ('CheckIn', 'Confirmada')
                        GROUP BY YEAR(fecha_entrada)
                        ORDER BY periodo";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':year' => $year]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Procesar datos según el periodo
            $data = [];

            if ($periodo == 'mensual') {
                // Asegurar que tenemos los 12 meses
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
            } else if ($periodo == 'trimestral') {
                // Asegurar que tenemos los 4 trimestres
                for ($i = 1; $i <= 4; $i++) {
                    $encontrado = false;
                    foreach ($result as $row) {
                        if ((int)$row['periodo'] === $i) {
                            $data[] = [
                                'periodo' => $i,
                                'total' => (float)$row['total']
                            ];
                            $encontrado = true;
                            break;
                        }
                    }
                    if (!$encontrado) {
                        $data[] = ['periodo' => $i, 'total' => 0];
                    }
                }
            } else {
                $data = $result;
            }

            echo json_encode($data);
        } catch (Exception $e) {
            error_log("Error en reporte de ocupación: " . $e->getMessage());
            echo json_encode(['error' => 'Error al procesar los datos de ocupación: ' . $e->getMessage()]);
        }
        break;

    case 'clientes':
        try {
            // Para el análisis de clientes, podemos mostrar información diferente
            // Por ejemplo, distribución por tipo de cliente, frecuencia, etc.

            // Ejemplo: distribución por nacionalidad
            $sql = "SELECT 
                    IFNULL(nacionalidad, 'No especificada') as nacionalidad, 
                    COUNT(*) as total 
                    FROM clientes 
                    GROUP BY nacionalidad 
                    ORDER BY total DESC 
                    LIMIT 10";

            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Error en reporte de clientes: " . $e->getMessage());
            echo json_encode(['error' => 'Error al procesar los datos de clientes: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no válida: ' . $action]);
        break;
}
