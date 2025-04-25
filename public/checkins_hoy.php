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
    <title>Check-ins de Hoy - Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2 class="page-title mb-4">Check-ins de Hoy</h2>

            <!-- Tarjetas de estadísticas -->
            <div class="checkin-stats animate-fade-in">
                <div class="stat-card">
                    <div class="stat-label">Check-ins Pendientes</div>
                    <div class="stat-value" id="pending-today">0</div>
                    <div class="stat-trend">Para hoy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Check-ins Completados</div>
                    <div class="stat-value" id="completed-today">0</div>
                    <div class="stat-trend">Hoy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Próxima Hora</div>
                    <div class="stat-value" id="next-hour">0</div>
                    <div class="stat-trend" id="next-hour-time">Llegadas</div>
                </div>
            </div>

            <!-- Panel principal -->
            <div class="card mt-4 animate-fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Registro de Check-ins (<?= date('d/m/Y') ?>)</h3>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto-refresh">
                        <label class="form-check-label" for="auto-refresh">Auto-actualizar</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table check-table">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>ID Reserva</th>
                                    <th>Cliente</th>
                                    <th>Habitación</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-checkins-hoy"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de próximas llegadas -->
            <div class="card mt-4 animate-fade-in">
                <div class="card-header">
                    <h3 class="mb-0">Próximas Llegadas</h3>
                </div>
                <div class="card-body">
                    <div class="timeline" id="upcoming-arrivals">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
    <script src="js/checkins_hoy.js"></script>
</body>

</html>