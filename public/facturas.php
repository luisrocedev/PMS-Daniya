<?php
// public/facturas.php
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
    <title>Gestión de Facturas - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Facturación</h2>

            <div class="card">
                <h3>Listado de Facturas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Reserva</th>
                            <th>Fecha Emisión</th>
                            <th>Total</th>
                            <th>Método de Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-facturas">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Crear Factura</h3>
                <form onsubmit="event.preventDefault(); crearFactura();">
                    <label for="id_reserva">ID Reserva:</label>
                    <input type="number" id="id_reserva" required>

                    <label for="fecha_emision">Fecha Emisión:</label>
                    <input type="date" id="fecha_emision" required>

                    <label for="total_factura">Total:</label>
                    <input type="number" step="0.01" id="total_factura" required>

                    <label for="metodo_pago">Método de Pago:</label>
                    <select id="metodo_pago">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            listarFacturas();
        });
    </script>
</body>

</html>