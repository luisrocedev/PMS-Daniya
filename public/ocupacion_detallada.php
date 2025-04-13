<?php
// public/ocupacion_detallada.php
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
    <title>Detalle de Ocupación - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container">
            <h2 class="page-title">Detalle de Ocupación</h2>

            <!-- Filtros avanzados -->
            <div class="card mb-3">
                <h3>Filtrar Habitaciones</h3>
                <form onsubmit="event.preventDefault(); listarHabitaciones();">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label for="filtroTipo" class="form-label">Tipo de Habitación:</label>
                            <select id="filtroTipo" class="form-select">
                                <option value="">Todos</option>
                                <option value="Doble">Doble</option>
                                <option value="Individual">Individual</option>
                                <option value="Suite">Suite</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroEstado" class="form-label">Estado:</label>
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todos</option>
                                <option value="Disponible">Disponible</option>
                                <option value="Ocupada">Ocupada</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Listado de habitaciones -->
            <div class="card">
                <h3>Listado de Habitaciones</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Piso</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaHabitaciones">
                        <!-- Se llenará dinámicamente mediante JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función para listar habitaciones aplicando filtros mediante la API de habitaciones
        function listarHabitaciones() {
            let url = '../api/habitaciones.php';
            const filtroTipo = document.getElementById('filtroTipo').value;
            const filtroEstado = document.getElementById('filtroEstado').value;
            let params = [];

            if (filtroTipo) {
                // Usamos el parámetro "search" para filtrar por el tipo de habitación
                params.push('search=' + encodeURIComponent(filtroTipo));
            }
            if (filtroEstado) {
                params.push('estado=' + encodeURIComponent(filtroEstado));
            }
            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let habitaciones = data.data ? data.data : data;
                    const tbody = document.getElementById('tablaHabitaciones');
                    tbody.innerHTML = '';
                    habitaciones.forEach(hab => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
              <td>${hab.id_habitacion}</td>
              <td>${hab.numero_habitacion}</td>
              <td>${hab.tipo_habitacion}</td>
              <td>${hab.piso}</td>
              <td>${hab.estado}</td>
            `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error('Error al listar habitaciones:', err));
        }

        document.addEventListener('DOMContentLoaded', () => {
            listarHabitaciones();
        });
    </script>
</body>

</html>