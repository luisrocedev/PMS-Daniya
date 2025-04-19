<?php
// public/checkins_hoy.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Check‑in de Hoy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2>Check‑in realizados hoy (<?= date('Y-m-d') ?>)</h2>
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>ID Reserva</th>
                                <th>Cliente</th>
                                <th>Habitación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-checkins-hoy">
                            <!-- se carga vía JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="js/checkins_hoy.js"></script>
</body>

</html>