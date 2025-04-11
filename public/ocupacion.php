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
    <title>Ocupación - Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Ocupación</h2>

            <!-- Ejemplo: Panel o widget para mostrar ocupación -->
            <div class="card">
                <h3>Estado de Habitaciones</h3>
                <p>Total de habitaciones: <span id="total_habs">...</span></p>
                <p>Ocupadas: <span id="ocupadas_habs">...</span></p>
                <p>En mantenimiento: <span id="mantenimiento_habs">...</span></p>
                <p>Disponibles: <span id="disponibles_habs">...</span></p>
                <button class="btn" onclick="cargarOcupacionDetalle()">Actualizar</button>
            </div>

            <!-- Opcional: Aquí podrías poner un gráfico, listado de habitaciones, etc. -->

        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            cargarOcupacionDetalle();
        });

        // Ejemplo de función para cargar la info de ocupación (puedes adaptarla)
        function cargarOcupacionDetalle() {
            fetch('../api/ocupacion.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total_habs').textContent = data.total;
                    document.getElementById('ocupadas_habs').textContent = data.ocupadas;
                    document.getElementById('mantenimiento_habs').textContent = data.mantenimiento;
                    document.getElementById('disponibles_habs').textContent = data.disponibles;
                })
                .catch(error => console.error('Error al cargar ocupación:', error));
        }
    </script>
</body>

</html>