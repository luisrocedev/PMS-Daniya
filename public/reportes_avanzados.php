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

    <!-- Cargar Chart.js desde CDN -->
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
                <h3>Parámetros de reporte</h3>
                <form onsubmit="event.preventDefault(); cargarReporte();">
                    <label for="yearInput">Año:</label>
                    <input type="number" id="yearInput" value="2025" min="2000" max="2100">
                    <button class="btn" type="submit">Consultar</button>
                </form>
            </div>

            <!-- Div para botones de exportación -->
            <div class="card">
                <button class="btn" onclick="exportarCSV()">Exportar CSV</button>
                <button class="btn" onclick="exportarPDF()">Exportar PDF</button>
                <button class="btn" onclick="exportarXLSX()">Exportar Excel</button>
            </div>

            <!-- Canvas para el gráfico Chart.js -->
            <div class="card">
                <canvas id="chartIngresos" width="400" height="200"></canvas>
            </div>

            <!-- Tabla con resultados -->
            <div class="card">
                <h3>Detalle de Ingresos</h3>
                <table>
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

    <script>
        let chartIngresos = null;

        // 1) Cargar datos desde el endpoint (JSON)
        function cargarReporte() {
            const year = document.getElementById('yearInput').value;
            const url = `../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    // data => [ { mes: 1, total_mes: 1234 }, { mes: 2, total_mes: 1500 }, ...]

                    // Llenar la tabla
                    const tbody = document.getElementById('tablaIngresos');
                    tbody.innerHTML = '';
                    data.forEach(obj => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${obj.mes}</td>
                        <td>${obj.total_mes}</td>
                    `;
                        tbody.appendChild(tr);
                    });

                    // Crear arrays para el gráfico
                    const labels = data.map(d => 'Mes ' + d.mes);
                    const valores = data.map(d => d.total_mes);

                    // Llamamos a la función que actualiza Chart.js
                    actualizarChart(labels, valores, year);
                })
                .catch(err => console.error(err));
        }

        // 2) Actualizar gráfico con Chart.js
        function actualizarChart(labels, valores, year) {
            const ctx = document.getElementById('chartIngresos').getContext('2d');
            // Si ya había un gráfico anterior, lo destruimos
            if (chartIngresos) {
                chartIngresos.destroy();
            }
            chartIngresos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Ingresos (Año ${year})`,
                        data: valores
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // 3) Funciones para exportar
        function exportarCSV() {
            const year = document.getElementById('yearInput').value;
            // Abrimos la URL en otra pestaña (o forzamos descarga)
            window.open(`../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}&export=csv`, '_blank');
        }

        function exportarPDF() {
            const year = document.getElementById('yearInput').value;
            window.open(`../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}&export=pdf`, '_blank');
        }

        function exportarXLSX() {
            const year = document.getElementById('yearInput').value;
            window.open(`../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}&export=xlsx`, '_blank');
        }

        // 4) Cargar reporte por defecto al abrir la página
        document.addEventListener('DOMContentLoaded', () => {
            cargarReporte();
        });
    </script>
</body>

</html>