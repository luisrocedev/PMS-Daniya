<?php
// public/clientes.php
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
    <title>Gestión de Clientes - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Clientes</h2>

            <!-- Tarjeta para Listar Clientes -->
            <div class="card">
                <h3>Listado de Clientes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes">
                        <!-- Se llenará via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Tarjeta para Crear Cliente -->
            <div class="card">
                <h3>Crear Nuevo Cliente</h3>
                <form onsubmit="event.preventDefault(); crearCliente();">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" required>

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono">

                    <label for="email">Email:</label>
                    <input type="email" id="email">

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            listarClientes();
        });
    </script>
</body>

</html>