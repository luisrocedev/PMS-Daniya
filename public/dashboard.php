<?php
session_start();

// Verificar si NO hay sesión iniciada
if (!isset($_SESSION['usuario_id'])) {
  // Redirigir a login
  header('Location: ../login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>PMS Daniya Denia - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css" />

</head>

<body>

  <!-- Incluir navbar -->
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <!-- Contenido principal (layout con sidebar opcional) -->
  <div style="display:flex; margin-top:1rem;">
    <!-- Incluir sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Área de contenido principal -->
    <div class="main-content">
      <h2 class="page-title">Bienvenido(a) al PMS de Daniya Denia</h2>
      <div class="card">
        <p>Aquí podrás visualizar un panel general del estado del hotel.</p>
        <ul>
          <li>Reservas activas, check-ins pendientes, ocupación, etc.</li>
          <li>Puedes navegar a las diferentes secciones usando el menú superior o la barra lateral.</li>
        </ul>
      </div>

      <!-- Ejemplo de widget -->
      <div class="card">
        <h3>Ocupación actual</h3>
        <p>Habitaciones ocupadas: <span id="ocupadas">...</span></p>
        <p>Habitaciones disponibles: <span id="disponibles">...</span></p>
        <button class="btn" onclick="actualizarOcupacion()">Actualizar</button>
      </div>
    </div>
  </div>

  <script src="js/main.js"></script>
  <script>
    // Ejemplo: al cargar la página, podríamos cargar datos iniciales
    document.addEventListener('DOMContentLoaded', () => {
      actualizarOcupacion();
    });
  </script>
</body>

</html>