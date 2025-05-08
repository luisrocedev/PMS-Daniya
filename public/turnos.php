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
    <title>Gestión de Turnos — PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Gestión de Turnos</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" type="button" onclick="limpiarFormulario()">
                        <i class="fas fa-plus me-2"></i>Nuevo Turno
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen de turnos -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="totalTurnos">0</div>
                            <div class="stat-label">Turnos Activos</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-sun fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="turnosDiurnos">0</div>
                            <div class="stat-label">Turnos Diurnos</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-moon fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="turnosNocturnos">0</div>
                            <div class="stat-label">Turnos Nocturnos</div>
                        </div>
                    </div>
                </div>

                <!-- Formulario y Tabla -->
                <div class="row mt-4">
                    <!-- Formulario de Turno -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title mb-4" id="form-title">Nuevo Turno</h3>
                                <form id="formTurno" onsubmit="event.preventDefault(); guardarTurno();">
                                    <input type="hidden" id="id_turno">

                                    <div class="mb-3">
                                        <label class="form-label">Nombre del Turno</label>
                                        <input class="form-control" id="nombre_turno" required
                                            placeholder="Ej: Mañana, Tarde, Noche">
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="form-label">Hora inicio</label>
                                            <input type="time" class="form-control" id="hora_inicio" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Hora fin</label>
                                            <input type="time" class="form-control" id="hora_fin" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Tiempo de Descanso (minutos)</label>
                                        <input type="number" min="0" class="form-control" id="descanso_min"
                                            value="0" placeholder="Ej: 30">
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Guardar Turno
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Turnos -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title mb-4">Listado de Turnos</h3>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tabla-turnos">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Inicio</th>
                                                <th>Fin</th>
                                                <th>Descanso</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = '../api/turnos.php';

        // Función para actualizar estadísticas
        function actualizarEstadisticas(turnos) {
            const totalTurnos = turnos.length;
            const turnosDiurnos = turnos.filter(t => {
                const hora = parseInt(t.hora_inicio.split(':')[0]);
                return hora >= 6 && hora < 22;
            }).length;
            const turnosNocturnos = totalTurnos - turnosDiurnos;

            document.getElementById('totalTurnos').textContent = totalTurnos;
            document.getElementById('turnosDiurnos').textContent = turnosDiurnos;
            document.getElementById('turnosNocturnos').textContent = turnosNocturnos;
        }

        // Función para listar turnos
        function listar() {
            fetch(API)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.querySelector('#tabla-turnos tbody');
                    tbody.innerHTML = '';
                    data.forEach(t => {
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td>${t.id_turno}</td>
                                <td>${t.nombre_turno}</td>
                                <td>${t.hora_inicio}</td>
                                <td>${t.hora_fin}</td>
                                <td>${t.descanso_min} min</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="cargar(${t.id_turno})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="borrar(${t.id_turno})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`);
                    });
                    actualizarEstadisticas(data);
                });
        }

        // Guardar turno
        function guardarTurno() {
            const datos = new URLSearchParams({
                nombre_turno: nombre_turno.value,
                hora_inicio: hora_inicio.value,
                hora_fin: hora_fin.value,
                descanso_min: descanso_min.value || 0
            });

            const id = id_turno.value;
            const url = id ? `${API}?id=${id}` : API;
            const metodo = id ? 'PUT' : 'POST';

            fetch(url, {
                    method: metodo,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: datos
                })
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        mostrarAlerta(res.error, 'danger');
                        return;
                    }
                    mostrarAlerta('Turno guardado correctamente', 'success');
                    limpiarFormulario();
                    listar();
                });
        }

        // Cargar turno en formulario
        function cargar(id) {
            fetch(`${API}?id=${id}`)
                .then(r => r.json())
                .then(t => {
                    id_turno.value = t.id_turno;
                    nombre_turno.value = t.nombre_turno;
                    hora_inicio.value = t.hora_inicio;
                    hora_fin.value = t.hora_fin;
                    descanso_min.value = t.descanso_min;
                    document.getElementById('form-title').textContent = 'Editar Turno';
                    nombre_turno.focus();
                });
        }

        // Borrar turno
        function borrar(id) {
            if (!confirm('¿Estás seguro de eliminar este turno?')) return;

            fetch(`${API}?id=${id}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        mostrarAlerta(res.error, 'danger');
                        return;
                    }
                    mostrarAlerta('Turno eliminado correctamente', 'success');
                    listar();
                });
        }

        // Limpiar formulario
        function limpiarFormulario() {
            formTurno.reset();
            id_turno.value = '';
            document.getElementById('form-title').textContent = 'Nuevo Turno';
            nombre_turno.focus();
        }

        // Mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            listar();
        });
    </script>
</body>

</html>