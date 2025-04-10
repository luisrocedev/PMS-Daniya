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
  <link rel="stylesheet" href="css/style.css">
</head>

<!-- Incluir navbar -->
<?php include __DIR__ . '/../partials/navbar.php'; ?>


<div style="display:flex; margin-top:1rem;">
  <!-- Incluir sidebar -->
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2 class="page-title">Check-in / Check-out</h2>

    <div class="card">
      <h3>Reservas pendientes de Check-in</h3>
      <table>
        <thead>
          <tr>
            <th>ID Reserva</th>
            <th>Cliente</th>
            <th>Habitación</th>
            <th>Check-in</th>
          </tr>
        </thead>
        <tbody id="tabla-checkin">
          <!-- Se llenará mediante JS con los datos de la BD -->
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Reservas pendientes de Check-out</h3>
      <table>
        <thead>
          <tr>
            <th>ID Reserva</th>
            <th>Cliente</th>
            <th>Habitación</th>
            <th>Check-out</th>
          </tr>
        </thead>
        <tbody id="tabla-checkout">
          <!-- Se llenará mediante JS con los datos de la BD -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="js/main.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    cargarCheckInOut();
  });
</script>
</body>

</html>