<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SuperModel.php';

$id_reserva = intval($_GET['id_reserva'] ?? 0);
$pdo   = Database::getInstance()->getConnection();
$sm    = new SuperModel();

// Datos reserva y cliente
$stmt = $pdo->prepare("
  SELECT r.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente, h.numero_habitacion
    FROM reservas r
    JOIN clientes c ON r.id_cliente = c.id_cliente
    JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
   WHERE r.id_reserva = :id
");
$stmt->execute([':id' => $id_reserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reserva) {
    echo 'Reserva no encontrada';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha Reserva #<?= $reserva['id_reserva'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2>Ficha Reserva #<?= $reserva['id_reserva'] ?></h2>
            <p>
                Cliente: <strong><?= htmlspecialchars($reserva['nombre_cliente'] . ' ' . $reserva['apellidos_cliente']) ?></strong><br>
                Habitación: <strong><?= htmlspecialchars($reserva['numero_habitacion']) ?></strong><br>
                Entrada: <?= $reserva['fecha_entrada'] ?> — Salida: <?= $reserva['fecha_salida'] ?><br>
                Estado: <?= $reserva['estado_reserva'] ?>
            </p>

            <hr>
            <h3>Catálogo de cargos</h3>
            <div id="catalogo-cargos" class="mb-4"></div>

            <h3>Cargos de esta reserva</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Importe</th>
                        <th>Fecha</th>
                        <th>Pagado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-cargos"></tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Editar Cargo -->
    <div id="modalCargo" class="modal" style="display:none;">
        <div class="modal-content">
            <h4 id="modalTitle">Editar Cargo</h4>
            <form id="formCargo">
                <input type="hidden" id="cargo_id">
                <div class="mb-3">
                    <label>Descripción</label>
                    <input type="text" id="cargo_desc" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Importe</label>
                    <input type="number" step="0.01" id="cargo_imp" class="form-control" required>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Guardar</button>
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hacemos disponible la reserva en JS
        const idReserva = <?= json_encode($reserva['id_reserva']) ?>;
    </script>
    <script src="js/cliente_detalle.js"></script>
    <style>
        .modal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: #fff;
            width: 400px;
            margin: 10% auto;
            padding: 1.5rem;
            border-radius: 8px;
        }
    </style>
</body>

</html>