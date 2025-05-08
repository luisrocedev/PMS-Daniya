<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;

$formato = $_GET['formato'] ?? '';
$search = $_GET['search'] ?? '';
$estadoFunnel = $_GET['estado_funnel'] ?? '';

$superModel = new SuperModel();
$pdo = Database::getInstance()->getConnection();

// Construir consulta
$where = " WHERE 1=1 ";
$params = [];

if ($search) {
    $where .= " AND (nombre LIKE :search OR apellidos LIKE :search OR dni LIKE :search) ";
    $params[':search'] = "%$search%";
}

if ($estadoFunnel) {
    $where .= " AND estado_funnel = :estado_funnel ";
    $params[':estado_funnel'] = $estadoFunnel;
}

$sql = "SELECT * FROM clientes $where ORDER BY id_cliente";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Exportar según formato
if ($formato === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeceras
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Apellidos');
    $sheet->setCellValue('D1', 'DNI');
    $sheet->setCellValue('E1', 'Email');
    $sheet->setCellValue('F1', 'Teléfono');
    $sheet->setCellValue('G1', 'Dirección');
    $sheet->setCellValue('H1', 'Estado Funnel');

    // Datos
    $row = 2;
    foreach ($clientes as $cliente) {
        $sheet->setCellValue('A' . $row, $cliente['id_cliente']);
        $sheet->setCellValue('B' . $row, $cliente['nombre']);
        $sheet->setCellValue('C' . $row, $cliente['apellidos']);
        $sheet->setCellValue('D' . $row, $cliente['dni']);
        $sheet->setCellValue('E' . $row, $cliente['email']);
        $sheet->setCellValue('F' . $row, $cliente['telefono']);
        $sheet->setCellValue('G' . $row, $cliente['direccion']);
        $sheet->setCellValue('H' . $row, $cliente['estado_funnel']);
        $row++;
    }

    // Auto-ajustar columnas
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Estilo para cabeceras
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E0E0E0']
        ]
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

    // Generar archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="clientes.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} elseif ($formato === 'pdf') {
    // Crear nuevo PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar documento
    $pdf->SetCreator('PMS Daniya Denia');
    $pdf->SetAuthor('PMS Daniya Denia');
    $pdf->SetTitle('Listado de Clientes');

    // Eliminar cabecera y pie de página predeterminados
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Añadir página
    $pdf->AddPage();

    // Título
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Listado de Clientes', 0, 1, 'C');
    $pdf->Ln(10);

    // Cabeceras de tabla
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(224, 224, 224);
    $header = ['ID', 'Nombre', 'Apellidos', 'DNI', 'Email', 'Teléfono', 'Estado'];
    $w = [15, 35, 35, 25, 45, 25, 25];

    foreach ($header as $i => $h) {
        $pdf->Cell($w[$i], 7, $h, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Datos
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetFillColor(255, 255, 255);

    foreach ($clientes as $cliente) {
        $pdf->Cell($w[0], 6, $cliente['id_cliente'], 'LR', 0, 'C');
        $pdf->Cell($w[1], 6, $cliente['nombre'], 'LR', 0, 'L');
        $pdf->Cell($w[2], 6, $cliente['apellidos'], 'LR', 0, 'L');
        $pdf->Cell($w[3], 6, $cliente['dni'], 'LR', 0, 'C');
        $pdf->Cell($w[4], 6, $cliente['email'], 'LR', 0, 'L');
        $pdf->Cell($w[5], 6, $cliente['telefono'], 'LR', 0, 'C');
        $pdf->Cell($w[6], 6, $cliente['estado_funnel'], 'LR', 0, 'C');
        $pdf->Ln();
    }

    // Línea final de la tabla
    $pdf->Cell(array_sum($w), 0, '', 'T');

    // Generar archivo
    $pdf->Output('clientes.pdf', 'D');
} else {
    echo json_encode(['error' => 'Formato no válido']);
}
