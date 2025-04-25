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
    <title>Reportes - Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2 class="page-title mb-4">Panel de Reportes</h2>

            <!-- Tarjetas de estadísticas generales -->
            <div class="report-stats animate-fade-in">
                <div class="stat-card">
                    <div class="stat-label">Total Facturación</div>
                    <div class="stat-value" id="total-revenue">0€</div>
                    <div class="stat-trend">Este mes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Ocupación Media</div>
                    <div class="stat-value" id="avg-occupancy">0%</div>
                    <div class="stat-trend">Últimos 30 días</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Reservas Totales</div>
                    <div class="stat-value" id="total-bookings">0</div>
                    <div class="stat-trend">Este mes</div>
                </div>
            </div>

            <!-- Tabs de tipos de reportes -->
            <ul class="nav nav-tabs report-tabs mb-4 animate-fade-in" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="financial-tab" data-bs-toggle="tab" href="#financial">
                        <i class="fas fa-chart-line me-2"></i>Financiero
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="occupancy-tab" data-bs-toggle="tab" href="#occupancy">
                        <i class="fas fa-bed me-2"></i>Ocupación
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="reservations-tab" data-bs-toggle="tab" href="#reservations">
                        <i class="fas fa-calendar-check me-2"></i>Reservas
                    </a>
                </li>
            </ul>

            <div class="tab-content animate-fade-in" id="reportsContent">
                <!-- Panel Financiero -->
                <div class="tab-pane fade show active" id="financial">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Ingresos por Período</h3>
                            <div class="report-controls">
                                <select id="financial-period" class="form-select">
                                    <option value="daily">Diario</option>
                                    <option value="weekly">Semanal</option>
                                    <option value="monthly">Mensual</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="financialChart"></canvas>
                        </div>
                    </div>

                    <div class="export-buttons mb-4">
                        <button class="btn btn-outline-primary" onclick="exportReport('financial', 'pdf')">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                        <button class="btn btn-outline-success" onclick="exportReport('financial', 'excel')">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportReport('financial', 'csv')">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </button>
                    </div>
                </div>

                <!-- Panel Ocupación -->
                <div class="tab-pane fade" id="occupancy">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Tasa de Ocupación</h3>
                            <div class="report-controls">
                                <input type="month" id="occupancy-month" class="form-control">
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="occupancyChart"></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">Desglose por Tipo de Habitación</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Disponibles</th>
                                            <th>Ocupadas</th>
                                            <th>% Ocupación</th>
                                            <th>Ingreso Medio</th>
                                        </tr>
                                    </thead>
                                    <tbody id="room-type-stats"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Reservas -->
                <div class="tab-pane fade" id="reservations">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Análisis de Reservas</h3>
                            <div class="report-controls">
                                <select id="booking-metric" class="form-select">
                                    <option value="status">Por Estado</option>
                                    <option value="channel">Por Canal</option>
                                    <option value="cancelled">Cancelaciones</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="reservationsChart"></div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="mb-0">Tendencias de Reserva</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="bookingTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="mb-0">Estancia Media</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="avgStayChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
    <script src="js/reports.js"></script>
</body>

</html>