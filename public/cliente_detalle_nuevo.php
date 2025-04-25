<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$id_cliente = intval($_GET['id'] ?? 0);
if (!$id_cliente) {
    header('Location: clientes.php');
    exit;
}

$pdo = Database::getInstance()->getConnection();
$sm = new SuperModel();

// Obtener datos del cliente
$cliente = $sm->getById('clientes', $id_cliente);
if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

// Obtener historial de reservas
$stmt = $pdo->prepare("
    SELECT r.*, h.numero_habitacion, h.tipo
    FROM reservas r
    JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
    WHERE r.id_cliente = :id
    ORDER BY r.fecha_entrada DESC
");
$stmt->execute([':id' => $id_cliente]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener facturas asociadas
$stmt = $pdo->prepare("
    SELECT f.*, r.fecha_entrada, r.fecha_salida
    FROM facturas f
    JOIN reservas r ON f.id_reserva = r.id_reserva
    WHERE r.id_cliente = :id
    ORDER BY f.fecha_emision DESC
");
$stmt->execute([':id' => $id_cliente]);
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalles del Cliente - <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                    <li class="breadcrumb-item active">Detalles del Cliente</li>
                </ol>
            </nav>

            <!-- Información del Cliente -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="card-title">
                            <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?>
                        </h2>
                        <button class="btn btn-primary" onclick="abrirModalEditar(<?= $cliente['id_cliente'] ?>)" data-bs-toggle="modal" data-bs-target="#modalEditarCliente">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>DNI:</strong> <?= htmlspecialchars($cliente['dni']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($cliente['email'] ?? 'No especificado') ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono'] ?? 'No especificado') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Dirección:</strong> <?= htmlspecialchars($cliente['direccion'] ?? 'No especificada') ?></p>
                            <p><strong>Estado Funnel:</strong>
                                <span class="badge funnel-<?= strtolower(str_replace(' ', '-', $cliente['estado_funnel'] ?? 'sin-estado')) ?>">
                                    <?= htmlspecialchars($cliente['estado_funnel'] ?? 'Sin estado') ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestañas para Reservas y Facturas -->
            <ul class="nav nav-tabs" id="clienteTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="reservas-tab" data-bs-toggle="tab" href="#reservas" role="tab">
                        <i class="fas fa-calendar-alt"></i> Historial de Reservas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="facturas-tab" data-bs-toggle="tab" href="#facturas" role="tab">
                        <i class="fas fa-file-invoice-dollar"></i> Facturas
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="clienteTabsContent">
                <!-- Pestaña de Reservas -->
                <div class="tab-pane fade show active" id="reservas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Habitación</th>
                                    <th>Tipo</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td><?= $reserva['id_reserva'] ?></td>
                                        <td><?= htmlspecialchars($reserva['numero_habitacion']) ?></td>
                                        <td><?= htmlspecialchars($reserva['tipo']) ?></td>
                                        <td><?= $reserva['fecha_entrada'] ?></td>
                                        <td><?= $reserva['fecha_salida'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= strtolower($reserva['estado_reserva']) === 'confirmada' ? 'success' : (strtolower($reserva['estado_reserva']) === 'pendiente' ? 'warning' : 'secondary') ?>">
                                                <?= htmlspecialchars($reserva['estado_reserva']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="reservas_integradas.php?id=<?= $reserva['id_reserva'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pestaña de Facturas -->
                <div class="tab-pane fade" id="facturas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Factura</th>
                                    <th>Fecha Emisión</th>
                                    <th>Total</th>
                                    <th>Método Pago</th>
                                    <th>Estancia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facturas as $factura): ?>
                                    <tr>
                                        <td><?= $factura['id_factura'] ?></td>
                                        <td><?= $factura['fecha_emision'] ?></td>
                                        <td><?= number_format($factura['total'], 2) ?> €</td>
                                        <td><?= htmlspecialchars($factura['metodo_pago']) ?></td>
                                        <td>
                                            <?= $factura['fecha_entrada'] ?> - <?= $factura['fecha_salida'] ?>
                                        </td>
                                        <td>
                                            <a href="generar_factura.php?id=<?= $factura['id_factura'] ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/clientes.js"></script>
</body>

</html>