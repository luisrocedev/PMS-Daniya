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
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <!-- Incluir navbar -->
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="d-flex" style="margin-top:1rem;">
    <!-- Incluir sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content p-4 w-100">
      <h2 class="page-title">Check-in / Check-out</h2>

      <ul class="nav nav-tabs" id="checkTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="tab-checkin" data-bs-toggle="tab" href="#pane-checkin">Check-in</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="tab-checkout" data-bs-toggle="tab" href="#pane-checkout">Check-out</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="tab-cargos" data-bs-toggle="tab" href="#pane-cargos">Cargos</a>
        </li>
      </ul>

      <div class="tab-content p-3">
        <!-- Check-in -->
        <div class="tab-pane fade show active" id="pane-checkin">
          <div class="card mb-4">
            <h3 class="card-header">Reservas pendientes de Check-in</h3>
            <div class="card-body">
              <table class="table">
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

        <!-- Check-out -->
        <div class="tab-pane fade" id="pane-checkout">
          <div class="card mb-4">
            <h3 class="card-header">Reservas pendientes de Check-out</h3>
            <div class="card-body">
              <table class="table">
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

        <!-- Cargos -->
        <div class="tab-pane fade" id="pane-cargos">
          <div class="card">
            <h3 class="card-header">Cargos de la reserva</h3>
            <div class="card-body">
              <div id="lista-cargos" class="mb-3"></div>
              <hr>
              <h4>Añadir cargo</h4>
              <form id="form-nuevo-cargo" class="row g-3">
                <div class="col-md-8">
                  <input type="text" id="desc-cargo" class="form-control" placeholder="Descripción" required>
                </div>
                <div class="col-md-3">
                  <input type="number" step="0.01" id="imp-cargo" class="form-control" placeholder="Importe" required>
                </div>
                <div class="col-md-1">
                  <button type="button" id="btn-add-cargo" class="btn btn-primary">+</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal para Check-in -->
  <div id="modalCheckin" class="modal" style="display:none;">
    <div class="modal-content">
      <h3>Check-in: Datos de Documento y Firma</h3>
      <form id="formCheckinModal">
        <input type="hidden" name="id_reserva" id="id_reserva_modal" value="">
        <label for="dni_modal">Nº Documento:</label>
        <input type="text" name="dni" id="dni_modal" required>
        <label for="firma_modal">Firma:</label>
        <input type="text" name="firma" id="firma_modal" required>
        <div class="mt-3">
          <button class="btn btn-success" type="submit">Realizar Check-in</button>
          <button class="btn btn-secondary" type="button" onclick="cerrarModalCheckin()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/checkin_checkout.js"></script>
  <style>
    .modal {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 9999;
    }

    .modal-content {
      background: #fff;
      width: 400px;
      margin: 10% auto;
      padding: 1.5rem;
      border-radius: 8px;
    }
  </style>

</body>

</html>