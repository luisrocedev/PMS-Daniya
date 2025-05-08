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
  <title>Check-in/Check-out - PMS Daniya Denia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="d-flex" style="margin-top:1rem;">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content p-4 w-100">
      <!-- Header de la página -->
      <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="page-title mb-0">Check-in / Check-out</h2>
          <div class="page-actions">
            <div class="form-check form-switch d-flex align-items-center me-3">
              <input class="form-check-input me-2" type="checkbox" id="auto-refresh">
              <label class="form-check-label" for="auto-refresh">Auto-actualizar</label>
            </div>
            <button class="btn btn-outline-primary" onclick="filtrarHoy()">
              <i class="fas fa-calendar-day me-2"></i>Ver solo HOY
            </button>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="content-wrapper">
        <!-- Estadísticas -->
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card stat-card h-100">
              <div class="card-body text-center">
                <i class="fas fa-sign-in-alt fa-2x text-primary mb-3"></i>
                <div class="stat-value" id="pending-checkins">0</div>
                <div class="stat-label">Check-ins Pendientes</div>
                <div class="stat-trend">
                  <i class="fas fa-clock"></i> Hoy
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card stat-card h-100">
              <div class="card-body text-center">
                <i class="fas fa-sign-out-alt fa-2x text-warning mb-3"></i>
                <div class="stat-value" id="pending-checkouts">0</div>
                <div class="stat-label">Check-outs Pendientes</div>
                <div class="stat-trend">
                  <i class="fas fa-clock"></i> Hoy
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card stat-card h-100">
              <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                <div class="stat-value" id="completed-today">0</div>
                <div class="stat-label">Completados</div>
                <div class="stat-trend">
                  <i class="fas fa-calendar-day"></i> Hoy
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card stat-card h-100">
              <div class="card-body text-center">
                <i class="fas fa-percentage fa-2x text-info mb-3"></i>
                <div class="stat-value" id="occupancy-rate">0%</div>
                <div class="stat-label">Ocupación</div>
                <div class="stat-trend">
                  <i class="fas fa-hotel"></i> Actual
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline de próximas llegadas -->
        <div class="card mb-4">
          <div class="card-header">
            <h3 class="h5 mb-0">Próximas Llegadas</h3>
          </div>
          <div class="card-body">
            <div class="timeline" id="upcoming-arrivals">
              <!-- Se llena dinámicamente -->
            </div>
          </div>
        </div>

        <!-- Check-ins y Check-outs -->
        <div class="row g-4">
          <!-- Check-ins Pendientes -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0">Check-ins Pendientes</h3>
                <span class="badge bg-primary" id="checkin-count">0</span>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover check-table">
                    <thead>
                      <tr>
                        <th>ID Reserva</th>
                        <th>Cliente</th>
                        <th>Habitación</th>
                        <th>Hora Estimada</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="tabla-checkin"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Check-outs Pendientes -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0">Check-outs Pendientes</h3>
                <span class="badge bg-warning" id="checkout-count">0</span>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover check-table">
                    <thead>
                      <tr>
                        <th>ID Reserva</th>
                        <th>Cliente</th>
                        <th>Habitación</th>
                        <th>Hora Límite</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="tabla-checkout"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Gestión de Cargos -->
        <div class="card mt-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">Gestión de Cargos</h3>
          </div>
          <div class="card-body">
            <div id="lista-cargos" class="mb-4">
              <!-- Lista de cargos -->
            </div>
            <div class="cargo-form">
              <form id="form-nuevo-cargo" class="row g-3">
                <div class="col-md-6">
                  <input type="text" id="desc-cargo" class="form-control" placeholder="Descripción del cargo" required>
                </div>
                <div class="col-md-4">
                  <div class="input-group">
                    <span class="input-group-text">€</span>
                    <input type="number" step="0.01" id="imp-cargo" class="form-control" placeholder="Importe" required>
                  </div>
                </div>
                <div class="col-md-2">
                  <button type="button" id="btn-add-cargo" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Añadir
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Check-in -->
  <div class="modal fade" id="modalCheckin" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Realizar Check-in</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formCheckinModal" class="animate-fade-in">
            <input type="hidden" name="id_reserva" id="id_reserva_modal">

            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-id-card me-2"></i>Documento de Identidad
              </label>
              <input type="text" name="dni" id="dni_modal" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">
                <i class="fas fa-signature me-2"></i>Firma Digital
              </label>
              <input type="text" name="firma" id="firma_modal" class="form-control" required>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="terminos" required>
              <label class="form-check-label" for="terminos">
                Acepto los términos y condiciones
              </label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancelar
          </button>
          <button type="submit" form="formCheckinModal" class="btn btn-success">
            <i class="fas fa-check me-2"></i>Completar Check-in
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/checkin_checkout.js"></script>
  <script>
    function filtrarHoy() {
      // Suponiendo que ya tienes funciones de filtrado en checkin_checkout.js
      // Aquí solo llamamos a la función de filtrado por fecha actual
      if (typeof filtrarPorFechaHoy === 'function') {
        filtrarPorFechaHoy();
      } else {
        // Si no existe, recarga la página con un parámetro de filtro
        window.location.href = window.location.pathname + '?fecha=' + new Date().toISOString().split('T')[0];
      }
    }
  </script>
</body>

</html>