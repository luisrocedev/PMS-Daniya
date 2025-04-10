<?php
// public/mantenimiento.php
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
    <title>Gestión de Mantenimiento - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Incidencias de Mantenimiento</h2>

            <div class="card">
                <h3>Listado de Incidencias</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Habitación</th>
                            <th>Empleado Asignado</th>
                            <th>Descripción</th>
                            <th>F. Reporte</th>
                            <th>F. Resolución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-mantenimiento">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Crear Incidencia</h3>
                <form onsubmit="event.preventDefault(); crearIncidencia();">
                    <label for="id_habitacion">ID Habitación:</label>
                    <input type="number" id="id_habitacion" required>

                    <label for="id_empleado">ID Empleado (Mantenimiento):</label>
                    <input type="number" id="id_empleado" required>

                    <label for="descripcion">Descripción:</label>
                    <input type="text" id="descripcion" required>

                    <label for="fecha_reporte">Fecha de Reporte:</label>
                    <input type="date" id="fecha_reporte" required>

                    <label for="fecha_resolucion">Fecha de Resolución:</label>
                    <input type="date" id="fecha_resolucion">

                    <label for="estado_mant">Estado:</label>
                    <select id="estado_mant">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Resuelto">Resuelto</option>
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            listarIncidencias();
        });
    </script>
</body>

</html>