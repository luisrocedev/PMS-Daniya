<?php
// public/habitaciones.php
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
    <title>Gestión de Habitaciones - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">

        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Habitaciones</h2>

            <!-- Listado de habitaciones -->
            <div class="card">
                <h3>Listado de Habitaciones</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Capacidad</th>
                            <th>Piso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-habitaciones">
                        <!-- Llenado con JS -->
                    </tbody>
                </table>
            </div>

            <!-- Formulario para crear nueva habitación -->
            <div class="card">
                <h3>Crear Nueva Habitación</h3>
                <form onsubmit="event.preventDefault(); crearHabitacion();">
                    <label for="numero_habitacion">Número de Habitación:</label>
                    <input type="text" id="numero_habitacion" required>

                    <label for="tipo_habitacion">Tipo de Habitación:</label>
                    <input type="text" id="tipo_habitacion" required>

                    <label for="capacidad">Capacidad:</label>
                    <input type="number" id="capacidad" required>

                    <label for="piso">Piso:</label>
                    <input type="number" id="piso" required>

                    <label for="estado">Estado:</label>
                    <select id="estado">
                        <option value="Disponible">Disponible</option>
                        <option value="Ocupada">Ocupada</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            listarHabitaciones();
        });
    </script>
</body>

</html>