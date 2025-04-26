<?php
// public/turnos.php
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
    <title>Gestión de Turnos — PMS Daniya Denia</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tu hoja de estilos -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container">
            <h2 class="page-title">Gestión de Turnos</h2>

            <!-- LISTADO ------------------------------------------------------->
            <div class="card mb-4 p-3">
                <h3>Turnos existentes</h3>
                <table class="table table-striped" id="tabla-turnos">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Descanso&nbsp;(min)</th>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- FORMULARIO ---------------------------------------------------->
            <div class="card p-3">
                <h3 id="form-title">Nuevo turno</h3>
                <form id="formTurno" onsubmit="event.preventDefault(); guardarTurno();">
                    <input type="hidden" id="id_turno">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Nombre</label>
                            <input class="form-control" id="nombre_turno" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Hora inicio</label>
                            <input type="time" class="form-control" id="hora_inicio" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Hora fin</label>
                            <input type="time" class="form-control" id="hora_fin" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Descanso (min)</label>
                            <input type="number" min="0" class="form-control" id="descanso_min" value="0">
                        </div>

                        <div class="col-md-2 d-grid">
                            <button class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div> <!-- /.main-content -->
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /* ------------------------------ CONFIG ---------------------------- */
        const API = '../api/turnos.php';

        /* ------------------------------ LISTAR --------------------------- */
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
                                <td>${t.descanso_min}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary me-1"
                                            onclick="cargar(${t.id_turno})">✏️</button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="borrar(${t.id_turno})">🗑️</button>
                                </td>
                            </tr>`);
                    });
                });
        }

        /* ------------------------------ GUARDAR -------------------------- */
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
                    if (res.error) return alert(res.error);
                    formTurno.reset();
                    id_turno.value = '';
                    document.getElementById('form-title').textContent = 'Nuevo turno';
                    listar();
                });
        }

        /* ------------------------------ CARGAR EN FORM ------------------- */
        function cargar(id) {
            fetch(`${API}?id=${id}`)
                .then(r => r.json())
                .then(t => {
                    id_turno.value = t.id_turno;
                    nombre_turno.value = t.nombre_turno;
                    hora_inicio.value = t.hora_inicio;
                    hora_fin.value = t.hora_fin;
                    descanso_min.value = t.descanso_min;
                    document.getElementById('form-title').textContent = 'Editar turno';
                });
        }

        /* ------------------------------ BORRAR --------------------------- */
        function borrar(id) {
            if (!confirm('¿Eliminar este turno?')) return;
            fetch(`${API}?id=${id}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(res => {
                    if (res.error) alert(res.error);
                    listar();
                });

        }

        document.addEventListener('DOMContentLoaded', listar);
    </script>
</body>

</html>