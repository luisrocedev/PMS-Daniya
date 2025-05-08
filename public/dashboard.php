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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="d-flex" style="margin-top:1rem;">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content">
      <!-- Header de la página -->
      <div class="page-header">
        <h2 class="page-title">Panel de Control</h2>
        <div class="page-actions">
          <button class="btn btn-primary" onclick="actualizarDashboard()">
            <i class="fas fa-sync-alt me-2"></i>Actualizar Datos
          </button>
        </div>
      </div>

      <!-- Contenido principal con scroll -->
      <div class="content-wrapper">
        <!-- Resumen estadístico -->
        <div class="grid-container animate-fadeInUp">
          <div class="card stat-card">
            <div class="card-body">
              <i class="fas fa-bed fa-2x text-primary mb-3"></i>
              <div class="stat-value" id="habitacionesDisponibles">0</div>
              <div class="stat-label">Habitaciones Disponibles</div>
              <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i> <span id="trendHabitaciones">--</span>
              </div>
            </div>
          </div>

          <div class="card stat-card">
            <div class="card-body">
              <i class="fas fa-calendar-check fa-2x text-success mb-3"></i>
              <div class="stat-value" id="reservasHoy">0</div>
              <div class="stat-label">Reservas de Hoy</div>
              <div class="stat-trend neutral">
                <i class="fas fa-equals"></i> Sin cambios
              </div>
            </div>
          </div>

          <div class="card stat-card">
            <div class="card-body">
              <i class="fas fa-percentage fa-2x text-info mb-3"></i>
              <div class="stat-value" id="ocupacion">0%</div>
              <div class="stat-label">Ocupación</div>
              <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i> vs. mes anterior
              </div>
            </div>
          </div>

          <div class="card stat-card">
            <div class="card-body">
              <i class="fas fa-euro-sign fa-2x text-warning mb-3"></i>
              <div class="stat-value" id="ingresosDiarios">0€</div>
              <div class="stat-label">Ingresos del Día</div>
              <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i> +15% hoy
              </div>
            </div>
          </div>
        </div>

        <!-- Gráficos y KPIs -->
        <div class="row mt-4">
          <!-- Gráfico de Ocupación -->
          <div class="col-md-8 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <h3 class="card-title h5 mb-3">Ocupación Mensual</h3>
                <canvas id="occupancyChart"></canvas>
              </div>
            </div>
          </div>

          <!-- KPIs y Métricas -->
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <h3 class="card-title h5 mb-3">KPIs del Hotel</h3>
                <div class="kpi-list">
                  <div class="kpi-item">
                    <span class="kpi-label">RevPAR</span>
                    <span class="kpi-value" id="revpar">--€</span>
                  </div>
                  <div class="kpi-item">
                    <span class="kpi-label">ADR</span>
                    <span class="kpi-value" id="adr">--€</span>
                  </div>
                  <div class="kpi-item">
                    <span class="kpi-label">Estancia Media</span>
                    <span class="kpi-value" id="avgStay">-- días</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actividad Reciente y Tareas -->
        <div class="row">
          <!-- Actividad Reciente -->
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title h5 mb-3">Actividad Reciente</h3>
                <div class="activity-list" id="activityList">
                  <!-- Se llena dinámicamente -->
                </div>
              </div>
            </div>
          </div>

          <!-- Tareas Pendientes -->
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title h5 mb-3">Tareas Pendientes</h3>
                <div class="task-list" id="taskList">
                  <!-- Se llena dinámicamente -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/countup.js@2.0.8/dist/countUp.umd.js"></script>
  <script src="js/dashboard.js"></script>
</body>

</html>