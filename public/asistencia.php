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
    <meta charset="utf-8">
    <title>Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="main-content container">
            <h2 class="page-title">Registro de Asistencia</h2>

            <!-- Fichar -->
            <div class="card mb-3 p-3">
                <h3>Fichar ahora</h3>
                <div class="row g-2">
                    <div class="col-md-3"><input type="number" id="empFicha" class="form-control" placeholder="ID Empleado"></div>
                    <div class="col-md-3">
                        <select id="tipoFicha" class="form-select">
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                    <div class="col-md-3"><button class="btn btn-success w-100" onclick="fichar()">Fichar</button></div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-3 p-3">
                <h3>Filtros</h3>
                <div class="row g-2">
                    <div class="col-md-2"><label>ID Emp</label><input type="number" id="empFil" class="form-control"></div>
                    <div class="col-md-2"><label>Desde</label><input type="date" id="desdeFil" class="form-control"></div>
                    <div class="col-md-2"><label>Hasta</label><input type="date" id="hastaFil" class="form-control"></div>
                    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-secondary w-100" onclick="listar()">Aplicar</button></div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card p-3">
                <h3>Asistencias</h3>
                <table class="table" id="tbAsis">
                    <thead>
                        <tr>
                            <th>Emp</th>
                            <th>Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Obs</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = '../api/asistencia.php';

        function fichar() {
            const id_empleado = empFicha.value;
            const tipo = tipoFicha.value;
            if (!id_empleado) return alert('Introduce ID empleado');
            const fd = new URLSearchParams({
                id_empleado,
                tipo
            });
            fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: fd
                })
                .then(r => r.json()).then(() => {
                    alert('OK');
                    listar();
                });
        }

        function listar() {
            const p = new URLSearchParams();
            if (empFil.value) p.append('emp', empFil.value);
            if (desdeFil.value) p.append('desde', desdeFil.value);
            if (hastaFil.value) p.append('hasta', hastaFil.value);
            fetch(`${API}?${p}`).then(r => r.json()).then(rows => {
                const tb = document.querySelector('#tbAsis tbody');
                tb.innerHTML = '';
                rows.forEach(a => {
                    tb.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${a.id_empleado}</td><td>${a.fecha}</td>
          <td>${a.hora_entrada??''}</td><td>${a.hora_salida??''}</td>
          <td>${a.estado}</td><td>${a.observaciones??''}</td>
        </tr>`);
                });
            });
        }
        document.addEventListener('DOMContentLoaded', listar);
    </script>
</body>

</html>