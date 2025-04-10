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
    <title>Reportes - Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Reportes</h2>

            <div class="card">
                <h3>Sección de reportes del PMS</h3>
                <p>Aquí podrías listar o generar reportes de facturación, ocupación histórica, etc.</p>
                <p>Ejemplos:</p>
                <ul>
                    <li>Reporte de ingresos por período</li>
                    <li>Ocupación por mes</li>
                    <li>Reservas canceladas vs. confirmadas</li>
                    <!-- etc. -->
                </ul>
                <!-- Podrías poner un formulario para filtrar fechas, un botón "Generar reporte", etc. -->
            </div>

            <!-- Espacio para resultados / tablas / gráficos -->
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // document.addEventListener('DOMContentLoaded', () => {
        //   // Si tuvieras algo para cargar reportes en automático
        // });
    </script>
</body>

</html>