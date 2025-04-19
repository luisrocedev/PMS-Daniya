<?php
// public/generar_factura.php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id      = intval($_GET['id'] ?? 0);
$sm      = new SuperModel();
$factura = $sm->getById('facturas', $id);
if (!$factura) {
    echo 'Factura no encontrada';
    exit;
}
$detalle = json_decode($factura['detalle'], true);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura #<?= $factura['id_factura'] ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Factura #<?= $factura['id_factura'] ?></h1>
    <p>Reserva: <?= $factura['id_reserva'] ?> | Fecha: <?= $factura['fecha_emision'] ?></p>
    <table class="table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['descripcion']) ?></td>
                    <td><?= number_format($item['importe'], 2) ?> €</td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th>Total</th>
                <th><?= number_format($factura['total'], 2) ?> €</th>
            </tr>
        </tbody>
    </table>
    <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
</body>

</html>