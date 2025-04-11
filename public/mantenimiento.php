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
    <title>Gestión de Mantenimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Incidencias de Mantenimiento</h2>

            <!-- FILTROS -->
            <div class="card">
                <h3>Buscar Incidencias</h3>
                <form onsubmit="event.preventDefault(); listarMantenimientoPaginado(1);">
                    <label for="searchMant">Descripción:</label>
                    <input type="text" id="searchMant">

                    <label for="estadoMant">Estado:</label>
                    <select id="estadoMant">
                        <option value="">Todos</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Resuelto">Resuelto</option>
                    </select>

                    <button type="submit" class="btn">Filtrar</button>
                </form>
            </div>

            <!-- TABLA -->
            <div class="card">
                <h3>Listado de Incidencias</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hab</th>
                            <th>Empleado</th>
                            <th>Descripción</th>
                            <th>F.Rep</th>
                            <th>F.Res</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-mant"></tbody>
                </table>
                <div id="paginacionMant" style="margin-top:1rem;"></div>
            </div>

            <!-- FORM CREACIÓN -->
            <div class="card">
                <h3>Nueva Incidencia</h3>
                <form onsubmit="event.preventDefault(); crearIncidencia();">
                    <label for="idHab">ID Habitación:</label>
                    <input type="number" id="idHab" required>

                    <label for="idEmp">ID Empleado:</label>
                    <input type="number" id="idEmp" required>

                    <label for="descMant">Descripción:</label>
                    <input type="text" id="descMant" required>

                    <label for="fRep">Fecha Reporte:</label>
                    <input type="date" id="fRep" required>

                    <label for="fRes">Fecha Resolución:</label>
                    <input type="date" id="fRes">

                    <label for="estMant">Estado:</label>
                    <select id="estMant">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Resuelto">Resuelto</option>
                    </select>

                    <button type="submit" class="btn">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let limitMant = 5;

        function listarMantenimientoPaginado(page = 1) {
            const search = document.getElementById('searchMant').value || '';
            const estado = document.getElementById('estadoMant').value || '';

            let url = `../api/mantenimiento.php?page=${page}&limit=${limitMant}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (estado) url += `&estado=${encodeURIComponent(estado)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const pag = obj.page || 1;
                    const lim = obj.limit || limitMant;

                    const tbody = document.getElementById('tabla-mant');
                    tbody.innerHTML = '';
                    data.forEach(m => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
          <td>${m.id_incidencia}</td>
          <td>${m.id_habitacion}</td>
          <td>${m.id_empleado}</td>
          <td>${m.descripcion}</td>
          <td>${m.fecha_reporte}</td>
          <td>${m.fecha_resolucion||''}</td>
          <td>${m.estado}</td>
          <td><button class="btn" onclick="eliminarIncidencia(${m.id_incidencia})">Eliminar</button></td>
        `;
                        tbody.appendChild(tr);
                    });

                    renderPagMant(pag, lim, total);
                })
                .catch(e => console.error(e));
        }

        function renderPagMant(page, limit, total) {
            const divPag = document.getElementById('paginacionMant');
            divPag.innerHTML = '';
            const totalPages = Math.ceil(total / limit);

            if (page > 1) {
                const bPrev = document.createElement('button');
                bPrev.classList.add('btn');
                bPrev.textContent = 'Anterior';
                bPrev.onclick = () => listarMantenimientoPaginado(page - 1);
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
                bNext.onclick = () => listarMantenimientoPaginado(page + 1);
                divPag.appendChild(bNext);
            }
        }

        function crearIncidencia() {
            const id_habitacion = document.getElementById('idHab').value;
            const id_empleado = document.getElementById('idEmp').value;
            const descripcion = document.getElementById('descMant').value;
            const fecha_reporte = document.getElementById('fRep').value;
            const fecha_resolucion = document.getElementById('fRes').value;
            const estado = document.getElementById('estMant').value;

            fetch('../api/mantenimiento.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id_habitacion,
                        id_empleado,
                        descripcion,
                        fecha_reporte,
                        fecha_resolucion,
                        estado
                    })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(d.msg);
                        listarMantenimientoPaginado(1);
                        // clean
                        document.getElementById('idHab').value = '';
                        document.getElementById('idEmp').value = '';
                        document.getElementById('descMant').value = '';
                        document.getElementById('fRep').value = '';
                        document.getElementById('fRes').value = '';
                        document.getElementById('estMant').value = 'Pendiente';
                    } else {
                        alert(d.error || 'No se pudo crear');
                    }
                })
                .catch(e => console.error(e));
        }

        function eliminarIncidencia(idI) {
            if (!confirm('¿Eliminar esta incidencia?')) return;
            fetch(`../api/mantenimiento.php?id=${idI}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(d.msg);
                        listarMantenimientoPaginado();
                    } else {
                        alert(d.error || 'No se pudo eliminar');
                    }
                })
                .catch(e => console.error(e));
        }

        document.addEventListener('DOMContentLoaded', () => {
            listarMantenimientoPaginado(1);
        });
    </script>
</body>

</html>