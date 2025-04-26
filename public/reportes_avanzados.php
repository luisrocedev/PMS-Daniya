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
    <title>Reportes Avanzados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Reportes Avanzados - Ingresos Mensuales</h2>

            <!-- Formulario: seleccionar año -->
            <div class="card">
                <div class="card-body">
                    <h3>Parámetros de reporte</h3>
                    <form onsubmit="event.preventDefault(); cargarReporte();" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="yearInput" class="form-label">Año:</label>
                                <input type="number" id="yearInput" class="form-control" value="2025" min="2000" max="2100">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Div para botones de exportación -->
            <div class="card mt-4">
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

            <!-- Canvas para el gráfico Chart.js -->
            <div class="card mt-4">
                <div class="card-body">
                    <canvas id="chartIngresos"></canvas>
                </div>
            </div>

            <!-- Tabla con resultados -->
            <div class="card mt-4">
                <div class="card-body">
                    <h3>Detalle de Ingresos</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Total (EUR)</th>
                                </tr>
                            </thead>
                            <tbody id="tablaIngresos"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/reportes_avanzados.js"></script>

    <script>
        // Inicializar la carga del reporte cuando la página esté lista
        document.addEventListener('DOMContentLoaded', () => {
            cargarReporte();
        });
    </script>
</body>

</html>