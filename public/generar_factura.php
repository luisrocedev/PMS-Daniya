<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$sm = new SuperModel();
$factura = $sm->getById('facturas', $id);
if (!$factura) {
    echo 'Factura no encontrada';
    exit;
}

// Obtener detalles de la reserva
$reserva = $sm->getById('reservas', $factura['id_reserva']);
// Obtener detalles del cliente
$cliente = $reserva ? $sm->getById('clientes', $reserva['id_cliente']) : null;
// Obtener cargos asociados
$detalle = json_decode($factura['detalle'] ?? '[]', true) ?: [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura #<?= $factura['id_factura'] ?> - Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                padding: 0;
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
        }

        .factura-header {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }

        .logo {
            max-height: 100px;
        }

        .datos-hotel {
            font-size: 0.9rem;
        }

        .datos-cliente {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 2rem;
        }

        .tabla-detalle {
            margin-bottom: 2rem;
        }

        .total-factura {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .footer {
            margin-top: 3rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container bg-white shadow my-4 p-4">
        <!-- Cabecera -->
        <div class="factura-header">
            <div class="row align-items-center">
                <div class="col-6">
                    <img src="/img/logo.png" alt="Hotel Daniya Denia" class="logo">
                </div>
                <div class="col-6 text-end datos-hotel">
                    <h4 class="mb-0">Hotel Daniya Denia</h4>
                    <p class="mb-0">Carretera Las Marinas, km 4</p>
                    <p class="mb-0">03700 - Denia (Alicante)</p>
                    <p class="mb-0">Tel: +34 965 784 180</p>
                    <p class="mb-0">CIF: B-12345678</p>
                </div>
            </div>
        </div>

        <!-- Información de Factura -->
        <div class="row mb-4">
            <div class="col-6">
                <h5>FACTURA</h5>
                <p class="mb-0"><strong>Nº Factura:</strong> <?= str_pad($factura['id_factura'], 6, '0', STR_PAD_LEFT) ?></p>
                <p class="mb-0"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></p>
                <p class="mb-0"><strong>Reserva:</strong> #<?= $factura['id_reserva'] ?></p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-0"><strong>Método de Pago:</strong></p>
                <h5><?= $factura['metodo_pago'] ?></h5>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <?php if ($cliente): ?>
            <div class="datos-cliente">
                <h5 class="mb-3">DATOS DEL CLIENTE</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?></p>
                        <p class="mb-1"><strong>DNI/NIF:</strong> <?= htmlspecialchars($cliente['dni']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($cliente['direccion']) ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($cliente['email']) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Detalle de Factura -->
        <div class="tabla-detalle">
            <h5 class="mb-3">DETALLE DE CARGOS</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detalle)): ?>
                        <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['descripcion']) ?></td>
                                <td class="text-end"><?= number_format($item['importe'], 2) ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td>Cargo por estancia</td>
                            <td class="text-end"><?= number_format($factura['total'], 2) ?> €</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-factura">
                        <td>TOTAL</td>
                        <td class="text-end"><?= number_format($factura['total'], 2) ?> €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notas y Condiciones -->
        <div class="footer">
            <div class="row">
                <div class="col-12">
                    <p class="mb-1 text-center">Esta factura sirve como justificante de pago.</p>
                    <p class="mb-1 text-center">IVA incluido en los precios según la legislación vigente.</p>
                    <p class="mb-0 text-center">Gracias por confiar en Hotel Daniya Denia</p>
                </div>
            </div>
        </div>

        <!-- Botones de Acción (no se imprimen) -->
        <div class="row mt-4 no-print">
            <div class="col-12 text-center">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
                <button onclick="window.close()" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</body>

</html>