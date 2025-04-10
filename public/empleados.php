<?php
// public/empleados.php
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
    <title>Gestión de Empleados - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">

        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Empleados</h2>

            <div class="card">
                <h3>Listado de Empleados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Departamento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-empleados">
                        <!-- Se llenará con JS -->
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Crear Nuevo Empleado</h3>
                <form onsubmit="event.preventDefault(); crearEmpleado();">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" required>

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" required>

                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono">

                    <label for="email">Email:</label>
                    <input type="email" id="email">

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion">

                    <label for="fecha_contrat">Fecha de Contratación:</label>
                    <input type="date" id="fecha_contrat">

                    <!-- Ejemplo: selects para rol y departamento -->
                    <label for="id_rol">Rol:</label>
                    <select id="id_rol">
                        <!-- Podrías cargar dinámicamente los roles -->
                        <option value="1">Recepcionista</option>
                        <option value="2">Camarero</option>
                        <option value="3">Cocinero</option>
                        <option value="4">Jefe de Mantenimiento</option>
                        <option value="5">Mantenimiento</option>
                        <option value="6">Gobernanta</option>
                        <option value="7">Limpieza</option>
                        <option value="8">Gerente</option>
                    </select>

                    <label for="id_departamento">Departamento:</label>
                    <select id="id_departamento">
                        <option value="1">Recepción</option>
                        <option value="2">Restaurante</option>
                        <option value="3">Mantenimiento</option>
                        <option value="4">Pisos</option>
                        <option value="5">Administración</option>
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            listarEmpleados();
        });
    </script>
</body>

</html>