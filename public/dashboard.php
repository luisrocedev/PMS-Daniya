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
  <title>PMS Daniya Denia - Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS personalizado -->
  <link rel="stylesheet" href="css/style.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- ApexCharts para gráficos más avanzados -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <!-- CountUp.js para animaciones numéricas -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.0.7/countUp.umd.min.js"></script>
  <style>
    .stat-card {
      transition: transform 0.3s ease;
      cursor: pointer;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-icon {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .chart-container {
      position: relative;
      height: 300px;
      margin: 1rem 0;
    }

    .trend-indicator {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.5rem;
      border-radius: 1rem;
      font-size: 0.8rem;
      margin-left: 0.5rem;
    }

    .trend-up {
      background-color: var(--success-color);
      color: white;
    }

    .trend-down {
      background-color: var(--danger-color);
      color: white;
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="d-flex" style="margin-top:1rem;">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content container">
      <?php
      include __DIR__ . '/../partials/breadcrumbs.php';
      echo getBreadcrumbs();
      ?>

      <h2 class="page-title mb-4">Panel de Control - Daniya Denia</h2>

      <div id="dashboard-pages">
        <div class="dashboard-page active" data-page="1">
          <!-- Página 1: Tarjetas de Estadísticas -->
          <div class="row g-4 mb-4">
            <!-- Ocupación -->
            <div class="col-md-3">
              <div class="card stat-card">
                <div class="card-body text-center">
                  <i class="fas fa-bed stat-icon text-primary"></i>
                  <div id="occupancyRate" class="stat-value">0%</div>
                  <div class="stat-label">Ocupación Actual</div>
                  <div id="occupancyTrend" class="trend-indicator"></div>
                </div>
              </div>
            </div>

            <!-- Ingresos -->
            <div class="col-md-3">
              <div class="card stat-card">
                <div class="card-body text-center">
                  <i class="fas fa-euro-sign stat-icon text-success"></i>
                  <div id="revenue" class="stat-value">0€</div>
                  <div class="stat-label">Ingresos del Mes</div>
                  <div id="revenueTrend" class="trend-indicator"></div>
                </div>
              </div>
            </div>

            <!-- Reservas -->
            <div class="col-md-3">
              <div class="card stat-card">
                <div class="card-body text-center">
                  <i class="fas fa-calendar-check stat-icon text-info"></i>
                  <div id="bookings" class="stat-value">0</div>
                  <div class="stat-label">Reservas Activas</div>
                  <div id="bookingsTrend" class="trend-indicator"></div>
                </div>
              </div>
            </div>

            <!-- Check-ins -->
            <div class="col-md-3">
              <div class="card stat-card">
                <div class="card-body text-center">
                  <i class="fas fa-key stat-icon text-warning"></i>
                  <div id="checkins" class="stat-value">0</div>
                  <div class="stat-label">Check-ins Hoy</div>
                  <div id="checkinsTrend" class="trend-indicator"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="dashboard-page" data-page="2">
          <!-- Página 2: Gráficos -->
          <div class="row g-4 mb-4">
            <!-- Estado del Hotel -->
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Estado del Hotel</h3>
                  <div class="chart-container">
                    <canvas id="occupancyChart"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ingresos por Día -->
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h3 class="card-title">Ingresos Diarios</h3>
                  <div class="chart-container">
                    <div id="revenueChart"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="dashboard-page" data-page="3">
          <!-- Página 3: Próximos Check-ins -->
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Próximos Check-ins</h3>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Hora</th>
                      <th>Cliente</th>
                      <th>Habitación</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody id="upcomingCheckins">
                    <!-- Se llena dinámicamente -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Controles de navegación de páginas -->
        <div class="page-nav text-center mt-4">
          <button id="prevPage" class="btn btn-secondary me-2">Anterior</button>
          <span class="page-indicator">Página <span id="currentPage">1</span> de <span id="totalPages">3</span></span>
          <button id="nextPage" class="btn btn-secondary">Siguiente</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Scripts personalizados -->
  <script src="js/dashboard.js"></script>
</body>

</html>