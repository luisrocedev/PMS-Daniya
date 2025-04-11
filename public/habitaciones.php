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
    <title>Gestión de Habitaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Habitaciones</h2>

            <!-- FILTROS -->
            <div class="card">
                <h3>Buscar Habitaciones</h3>
                <form onsubmit="event.preventDefault(); listarHabitacionesPaginado(1);">
                    <label for="searchHab">Número/Tipo:</label>
                    <input type="text" id="searchHab">

                    <label for="estadoHab">Estado:</label>
                    <select id="estadoHab">
                        <option value="">Todos</option>
                        <option value="Disponible">Disponible</option>
                        <option value="Ocupada">Ocupada</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>

                    <button type="submit" class="btn">Filtrar</button>
                </form>
            </div>

            <!-- TABLA -->
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

                <div id="paginacionHabs" style="margin-top:1rem;"></div>
            </div>

            <!-- FORM CREACIÓN -->
            <div class="card">
                <h3>Nueva Habitación</h3>
                <form onsubmit="event.preventDefault(); crearHabitacion();">
                    <label for="numHab">Número:</label>
                    <input type="text" id="numHab" required>

                    <label for="tipoHab">Tipo:</label>
                    <input type="text" id="tipoHab" required>

                    <label for="capHab">Capacidad:</label>
                    <input type="number" id="capHab" required>

                    <label for="pisoHab">Piso:</label>
                    <input type="number" id="pisoHab" required>

                    <label for="estHab">Estado:</label>
                    <select id="estHab">
                        <option value="Disponible">Disponible</option>
                        <option value="Ocupada">Ocupada</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let limitHab = 5;

        function listarHabitacionesPaginado(page = 1) {
            const search = document.getElementById('searchHab').value || '';
            const estado = document.getElementById('estadoHab').value || '';

            let url = `../api/habitaciones.php?page=${page}&limit=${limitHab}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (estado) url += `&estado=${encodeURIComponent(estado)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const pag = obj.page || 1;
                    const lim = obj.limit || limitHab;

                    const tbody = document.getElementById('tabla-habitaciones');
                    tbody.innerHTML = '';
                    data.forEach(h => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
          <td>${h.id_habitacion}</td>
          <td>${h.numero_habitacion}</td>
          <td>${h.tipo_habitacion}</td>
          <td>${h.capacidad}</td>
          <td>${h.piso}</td>
          <td>${h.estado}</td>
          <td>
            <button class="btn" onclick="eliminarHabitacion(${h.id_habitacion})">Eliminar</button>
          </td>
        `;
                        tbody.appendChild(tr);
                    });

                    renderPagHabs(pag, lim, total);
                })
                .catch(e => console.error(e));
        }

        function renderPagHabs(page, limit, total) {
            const divPag = document.getElementById('paginacionHabs');
            divPag.innerHTML = '';
            const totalPages = Math.ceil(total / limit);

            if (page > 1) {
                const bPrev = document.createElement('button');
                bPrev.classList.add('btn');
                bPrev.textContent = 'Anterior';
                bPrev.onclick = () => listarHabitacionesPaginado(page - 1);
                divPag.appendChild(bPrev);
            }

            const sp = document.createElement('span');
            sp.style.margin = '0 10px';
            sp.textContent = `Página ${page} de ${totalPages} (Total: ${total})`;
            divPag.appendChild(sp);

            if (page < totalPages) {
                const bNext = document.createElement('button');
                bNext.classList.add('btn');
                bNext.textContent = 'Siguiente';
                bNext.onclick = () => listarHabitacionesPaginado(page + 1);
                divPag.appendChild(bNext);
            }
        }

        function crearHabitacion() {
            const numero_habitacion = document.getElementById('numHab').value;
            const tipo_habitacion = document.getElementById('tipoHab').value;
            const capacidad = document.getElementById('capHab').value;
            const piso = document.getElementById('pisoHab').value;
            const estado = document.getElementById('estHab').value;

            fetch('../api/habitaciones.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        numero_habitacion,
                        tipo_habitacion,
                        capacidad,
                        piso,
                        estado
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        listarHabitacionesPaginado(1);
                        // Clean
                        document.getElementById('numHab').value = '';
                        document.getElementById('tipoHab').value = '';
                        document.getElementById('capHab').value = '';
                        document.getElementById('pisoHab').value = '';
                        document.getElementById('estHab').value = 'Disponible';
                    } else {
                        alert(data.error || 'No se pudo crear');
                    }
                })
                .catch(e => console.error(e));
        }

        function eliminarHabitacion(idH) {
            if (!confirm('¿Eliminar esta habitación?')) return;
            fetch(`../api/habitaciones.php?id=${idH}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(d.msg);
                        listarHabitacionesPaginado();
                    } else {
                        alert(d.error || 'No se pudo eliminar');
                    }
                })
                .catch(e => console.error(e));
        }

        document.addEventListener('DOMContentLoaded', () => {
            listarHabitacionesPaginado(1);
        });
    </script>
</body>

</html>