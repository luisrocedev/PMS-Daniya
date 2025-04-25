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
  <title>Check-in/Check-out - Daniya Denia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="d-flex" style="margin-top:1rem;">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content p-4 w-100">
      <h2 class="page-title mb-4">Check-in / Check-out</h2>

      <!-- Tarjetas de estadísticas -->
      <div class="checkin-stats animate-fade-in">
        <div class="stat-card">
          <div class="stat-label">Check-ins Pendientes</div>
          <div class="stat-value" id="pending-checkins">0</div>
          <div class="stat-trend">Hoy</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Check-outs Pendientes</div>
          <div class="stat-value" id="pending-checkouts">0</div>
          <div class="stat-trend">Hoy</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Completados Hoy</div>
          <div class="stat-value" id="completed-today">0</div>
          <div class="stat-trend">Total</div>
        </div>
      </div>

      <!-- Tabs mejorados -->
      <ul class="nav nav-tabs check-tabs mb-4" id="checkTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="tab-checkin" data-bs-toggle="tab" href="#pane-checkin">
            <i class="fas fa-sign-in-alt me-2"></i>Check-in
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="tab-checkout" data-bs-toggle="tab" href="#pane-checkout">
            <i class="fas fa-sign-out-alt me-2"></i>Check-out
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="tab-cargos" data-bs-toggle="tab" href="#pane-cargos">
            <i class="fas fa-receipt me-2"></i>Cargos
          </a>
        </li>
      </ul>

      <div class="tab-content animate-fade-in">
        <!-- Check-in Panel -->
        <div class="tab-pane fade show active" id="pane-checkin">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="mb-0">Reservas pendientes de Check-in</h3>
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
                      <th>ID Reserva</th>
                      <th>Cliente</th>
                      <th>Habitación</th>
                      <th>Acción</th>
                    </tr>
                  </thead>
                  <tbody id="tabla-checkin"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Check-out Panel -->
        <div class="tab-pane fade" id="pane-checkout">
          <div class="card">
            <div class="card-header">
              <h3 class="mb-0">Reservas pendientes de Check-out</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table check-table">
                  <thead>
                    <tr>
                      <th>ID Reserva</th>
                      <th>Cliente</th>
                      <th>Habitación</th>
                      <th>Acción</th>
                    </tr>
                  </thead>
                  <tbody id="tabla-checkout"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Cargos Panel -->
        <div class="tab-pane fade" id="pane-cargos">
          <div class="card">
            <div class="card-header">
              <h3 class="mb-0">Cargos de la reserva</h3>
            </div>
            <div class="card-body">
              <div id="lista-cargos" class="cargo-list mb-4"></div>

              <div class="cargo-form">
                <h4 class="mb-3">Añadir nuevo cargo</h4>
                <form id="form-nuevo-cargo" class="row g-3">
                  <div class="col-md-8">
                    <input type="text" id="desc-cargo" class="form-control" placeholder="Descripción" required>
                  </div>
                  <div class="col-md-3">
                    <input type="number" step="0.01" id="imp-cargo" class="form-control" placeholder="Importe" required>
                  </div>
                  <div class="col-md-1">
                    <button type="button" id="btn-add-cargo" class="btn btn-primary">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Check-in Mejorado -->
  <div id="modalCheckin" class="modal" style="display:none;">
    <div class="modal-checkin">
      <h3>Check-in: Datos de Documento y Firma</h3>
      <form id="formCheckinModal" class="animate-fade-in">
        <input type="hidden" name="id_reserva" id="id_reserva_modal" value="">

        <div class="mb-3">
          <label for="dni_modal" class="form-label">Nº Documento:</label>
          <input type="text" name="dni" id="dni_modal" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="firma_modal" class="form-label">Firma:</label>
          <input type="text" name="firma" id="firma_modal" class="form-control" required>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" onclick="cerrarModalCheckin()">Cancelar</button>
          <button type="submit" class="btn btn-success">Realizar Check-in</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
  <script src="js/checkin_checkout.js"></script>
</body>

</html>