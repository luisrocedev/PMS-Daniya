<?php
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
    <title>RRHH - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container">
            <h2 class="page-title">Recursos Humanos</h2>

            <div class="row g-4">
                <!-- Tarjeta Turnos -->
                <div class="col-md-4">
                    <div class="card h-100 text-center shadow">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="card-title">Turnos</h3>
                            <p class="card-text">Catálogo de turnos estándar (mañana, tarde, etc.).</p>
                            <a href="turnos.php" class="btn btn-primary mt-auto">Gestionar Turnos</a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Horarios -->
                <div class="col-md-4">
                    <div class="card h-100 text-center shadow">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="card-title">Horarios</h3>
                            <p class="card-text">Planificación de turnos por empleado y fechas.</p>
                            <a href="horarios.php" class="btn btn-primary mt-auto">Ver Horarios</a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Asistencia -->
                <div class="col-md-4">
                    <div class="card h-100 text-center shadow">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="card-title">Asistencia</h3>
                            <p class="card-text">Fichajes diarios, control de ausencias y bajas.</p>
                            <a href="asistencia.php" class="btn btn-primary mt-auto">Registro de Asistencia</a>
                        </div>
                    </div>
                </div>
            </div><!-- /.row -->
        </div>
    </div>
</body>

</html>