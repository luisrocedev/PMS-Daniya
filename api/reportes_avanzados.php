<?php
// api/reportes_avanzados.php
header('Content-Type: application/json');
session_start();

// Asegurar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';

// Si vas a usar el "Excel" (PhpSpreadsheet) y "PDF" (FPDF), necesitarás los require correspondientes. Por ejemplo:
// require __DIR__ . '/../lib/fpdf/fpdf.php';         // Ajusta la ruta donde tengas FPDF
// require __DIR__ . '/../../vendor/autoload.php';    // Si instalaste PhpSpreadsheet con Composer

$pdo = Database::getInstance()->getConnection();

// Parámetros recibidos
$action = $_GET['action'] ?? '';   // p.ej: "ingresos_mensuales"
$year   = $_GET['year']   ?? date('Y');  // si no mandan año, usamos el actual
$export = $_GET['export'] ?? '';   // p.ej: "csv", "pdf", "xlsx" ...

if ($action === 'ingresos_mensuales') {
    // 1) Consulta SQL para agrupar facturas por mes
    $sql = "SELECT 
                MONTH(fecha_emision) AS mes,
                SUM(total) AS total_mes
            FROM facturas
            WHERE YEAR(fecha_emision) = :year
            GROUP BY mes
            ORDER BY mes";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':year', (int)$year, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // Ej: [ [ 'mes' => 1, 'total_mes' => '1500.00'], ... ]

    // Segun $export, respondemos de distintas maneras:
    if ($export === 'csv') {
        // --------------------------
        // Exportar en CSV
        // --------------------------
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=ingresos_{$year}.csv");

        // Encabezado de columnas
        echo "Mes,Total\n";
        // Filas
        foreach ($rows as $r) {
            echo $r['mes'] . "," . $r['total_mes'] . "\n";
        }
        exit;

    } elseif ($export === 'pdf') {
        // ---------------------------------------------------
        // Exportar en PDF (usando FPDF como ejemplo)
        // ---------------------------------------------------
        // IMPORTANTE: necesitas tener FPDF en tu proyecto.
        // require_once __DIR__ . '/../lib/fpdf/fpdf.php'; // Ajusta si no lo has puesto antes

        // Generamos un mini informe
        // Crea tu clase PDF o usa la de FPDF directamente:
        class PDF extends FPDF {
            function Header() {
                $this->SetFont('Arial', 'B', 14);
                $this->Cell(0, 10, 'Ingresos Mensuales - Año ' . $_GET['year'], 0, 1, 'C');
                $this->Ln(5);
                $this->SetFont('Arial', 'B', 10);
                $this->Cell(30, 8, 'Mes', 1, 0, 'C');
                $this->Cell(50, 8, 'Total (EUR)', 1, 1, 'C');
            }
        }

        // Crear PDF
        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);

        foreach ($rows as $r) {
            $pdf->Cell(30, 8, $r['mes'], 1, 0, 'C');
            $pdf->Cell(50, 8, $r['total_mes'], 1, 1, 'C');
        }

        // Salida
        $pdf->Output("I", "ingresos_{$year}.pdf"); // I => inline, o D => force download
        exit;

    } elseif ($export === 'xlsx') {
        // ---------------------------------------------------
        // Exportar en EXCEL (XLSX) usando PhpSpreadsheet
        // ---------------------------------------------------
        // require_once __DIR__ . '/../../vendor/autoload.php'; // Ajusta la ruta
        use PhpOffice\PhpSpreadsheet\Spreadsheet;
        use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'Mes');
        $sheet->setCellValue('B1', 'Total (EUR)');

        // Rellenar datos
        $rowIndex = 2;
        foreach ($rows as $r) {
            $sheet->setCellValue("A{$rowIndex}", $r['mes']);
            $sheet->setCellValue("B{$rowIndex}", $r['total_mes']);
            $rowIndex++;
        }

        // Ajustar ancho de columnas, estilos, etc. (opcional)
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(15);

        // Generar archivo
        $writer = new Xlsx($spreadsheet);

        // Encabezados de descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=ingresos_{$year}.xlsx");

        $writer->save("php://output");
        exit;

    } else {
        // --------------------------------------
        // Respuesta por defecto en JSON (para JS)
        // --------------------------------------
        header('Content-Type: application/json');
        echo json_encode($rows);
        exit;
    }

} else {
    // Si no hay un "action" reconocido
    echo json_encode(['error' => 'Accion desconocida.']);
    exit;
}
