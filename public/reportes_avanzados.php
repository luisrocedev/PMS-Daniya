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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reportes Avanzados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .content-page {
            display: none;
        }

        .content-page.active {
            display: block;
        }

        .chart-container {
            height: 440px;
            position: relative;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        .dashboard-card {
            transition: all 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2 class="page-title mb-4">Reportes Avanzados</h2>

            <div id="reportes-avanzados-pages">
                <div class="content-page active" data-page="1">
                    <!-- Panel principal con tarjetas de reportes disponibles -->
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="card dashboard-card h-100">
                                <div class="card-body text-center">
                                    <div class="feature-icon mb-3">
                                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                                    </div>
                                    <h4 class="card-title">Ingresos Mensuales</h4>
                                    <p class="card-text">Visualiza los ingresos totales por mes</p>
                                    <button class="btn btn-primary" onclick="cargarReporte('ingresos'); showReportPage(2);">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="card dashboard-card h-100">
                                <div class="card-body text-center">
                                    <div class="feature-icon mb-3">
                                        <i class="fas fa-bed fa-3x text-success"></i>
                                    </div>
                                    <h4 class="card-title">Ocupación</h4>
                                    <p class="card-text">Análisis de ocupación por tipo de habitación</p>
                                    <button class="btn btn-success" onclick="cargarReporte('ocupacion'); showReportPage(2);">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="card dashboard-card h-100">
                                <div class="card-body text-center">
                                    <div class="feature-icon mb-3">
                                        <i class="fas fa-users fa-3x text-info"></i>
                                    </div>
                                    <h4 class="card-title">Perfil de Clientes</h4>
                                    <p class="card-text">Análisis demográfico de tus huéspedes</p>
                                    <button class="btn btn-info" onclick="cargarReporte('clientes'); showReportPage(2);">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-page" data-page="2">
                    <!-- Filtros del reporte -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h3 class="card-title mb-0" id="reporteTitulo">Parámetros de reporte</h3>
                                <button class="btn btn-outline-secondary btn-sm" onclick="showReportPage(1)">
                                    <i class="fas fa-arrow-left"></i> Volver a reportes
                                </button>
                            </div>

                            <form id="reporteForm" onsubmit="event.preventDefault(); aplicarFiltros();" class="row g-3">
                                <div class="col-md-4">
                                    <label for="yearInput" class="form-label">Año:</label>
                                    <input type="number" id="yearInput" class="form-control" value="2025" min="2000" max="2100">
                                </div>

                                <div class="col-md-4">
                                    <label for="periodoSelect" class="form-label">Periodo:</label>
                                    <select id="periodoSelect" class="form-select">
                                        <option value="mensual">Mensual</option>
                                        <option value="trimestral">Trimestral</option>
                                        <option value="anual">Anual</option>
                                    </select>
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" type="submit">
                                        <i class="fas fa-search"></i> Aplicar filtros
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Botones de exportación -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="btn-group">
                                <button class="btn btn-outline-primary" onclick="exportarPDF()">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </button>
                                <button class="btn btn-outline-success" onclick="exportarXLSX()">
                                    <i class="fas fa-file-excel"></i> Exportar Excel
                                </button>
                                <button class="btn btn-outline-secondary" onclick="exportarCSV()">
                                    <i class="fas fa-file-csv"></i> Exportar CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Visualización del reporte: Tabs para gráfico y tabla -->
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="reporteTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="grafico-tab" data-bs-toggle="tab" data-bs-target="#grafico-tab-pane" type="button" role="tab">
                                        <i class="fas fa-chart-bar me-2"></i>Gráfico
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla-tab-pane" type="button" role="tab">
                                        <i class="fas fa-table me-2"></i>Tabla de datos
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content p-3" id="reporteTabsContent">
                                <!-- Tab Gráfico -->
                                <div class="tab-pane fade show active" id="grafico-tab-pane" role="tabpanel" aria-labelledby="grafico-tab" tabindex="0">
                                    <div class="chart-container">
                                        <canvas id="chartIngresos"></canvas>
                                    </div>
                                </div>

                                <!-- Tab Tabla -->
                                <div class="tab-pane fade" id="tabla-tab-pane" role="tabpanel" aria-labelledby="tabla-tab" tabindex="0">
                                    <div class="table-container">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Periodo</th>
                                                    <th class="text-end">Total (EUR)</th>
                                                    <th class="text-end">% Variación</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaIngresos"></tbody>
                                        </table>
                                    </div>
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
    <script src="js/reportes_avanzados.js"></script>
</body>

</html>