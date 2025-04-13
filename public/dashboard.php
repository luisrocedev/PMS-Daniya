<?php
// public/dashboard.php
session_start();

// Si no existe sesión, redirige a login
if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>PMS Daniya Denia - Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS personalizado -->
  <link rel="stylesheet" href="css/style.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Contenedor de gráfico con altura fija para evitar scroll infinito */
    #grafico-container {
      position: relative;
      height: 300px;
      /* Ajusta este valor según convenga */
      margin: auto;
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="d-flex" style="margin-top:1rem;">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content container">
      <h2 class="page-title">Bienvenido(a) al PMS de Daniya Denia</h2>

      <!-- Información general -->
      <div class="card mb-3">
        <p>Aquí encontrarás un resumen del estado actual del hotel.</p>
        <ul>
          <li>Indicadores globales: habitaciones ocupadas, en mantenimiento y disponibles.</li>
          <li>Accede a la vista detallada para más información o acciones puntuales.</li>
        </ul>
        <div class="mt-2">
          <!-- Botón para ver detalle de ocupación -->
          <a href="ocupacion_detallada.php" class="btn btn-info">Ver Detalle de Ocupación</a>
        </div>
      </div>

      <!-- Widget Visual: Gráfico de Estado del Hotel -->
      <div class="card">
        <h3>Estado del Hotel</h3>
        <div id="grafico-container">
          <canvas id="graficoOcupacion"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle con Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Función para cargar datos de ocupación y renderizar gráfico
    function cargarGraficoOcupacion() {
      fetch('../api/ocupacion.php')
        .then(response => response.json())
        .then(data => {
          const ctx = document.getElementById('graficoOcupacion').getContext('2d');
          new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Ocupadas', 'En Mantenimiento', 'Disponibles'],
              datasets: [{
                data: [data.ocupadas, data.mantenimiento, data.disponibles],
                backgroundColor: [
                  'rgba(220, 53, 69, 0.6)',
                  'rgba(255, 193, 7, 0.6)',
                  'rgba(40, 167, 69, 0.6)'
                ],
                borderColor: [
                  'rgba(220, 53, 69, 1)',
                  'rgba(255, 193, 7, 1)',
                  'rgba(40, 167, 69, 1)'
                ],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'bottom'
                }
              }
            }
          });
        })
        .catch(err => console.error('Error al cargar datos de ocupación:', err));
    }

    document.addEventListener('DOMContentLoaded', () => {
      cargarGraficoOcupacion();
    });
  </script>
</body>

</html>