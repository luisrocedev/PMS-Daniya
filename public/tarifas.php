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
    <title>Gestión de Tarifas</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display: flex; margin-top: 1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Tarifas</h2>

            <!-- FILTROS -->
            <div class="card">
                <h3>Buscar Tarifas</h3>
                <form onsubmit="event.preventDefault(); listarTarifas(1);">
                    <label for="tipoHab">Tipo Hab.:</label>
                    <input type="text" id="tipoHab" placeholder="Doble, Suite, etc.">

                    <label for="tempTar">Temporada:</label>
                    <input type="text" id="tempTar" placeholder="Alta, Baja, ...">

                    <button class="btn" type="submit">Filtrar</button>
                </form>
            </div>

            <!-- LISTADO DE TARIFAS -->
            <div class="card">
                <h3>Listado de Tarifas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo Hab.</th>
                            <th>Precio</th>
                            <th>Temporada</th>
                            <th>F.Inicio</th>
                            <th>F.Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-tarifas">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>
                <div id="paginacionTarifas" style="margin-top:1rem;"></div>
            </div>

            <!-- FORMULARIO PARA CREAR TARIFA -->
            <div class="card">
                <h3>Crear Tarifa</h3>
                <form onsubmit="event.preventDefault(); crearTarifa();">
                    <label for="nomTarifa">Nombre Tarifa:</label>
                    <input type="text" id="nomTarifa" required>

                    <label for="tipoTarifa">Tipo Habitación:</label>
                    <input type="text" id="tipoTarifa" placeholder="Doble, Suite..." required>

                    <label for="precioTar">Precio:</label>
                    <input type="number" step="0.01" id="precioTar" required>

                    <label for="tempTar2">Temporada:</label>
                    <input type="text" id="tempTar2" placeholder="Baja, Alta..." required>

                    <label for="iniTar">Fecha Inicio:</label>
                    <input type="date" id="iniTar" required>

                    <label for="finTar">Fecha Fin:</label>
                    <input type="date" id="finTar" required>

                    <button class="btn" type="submit">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let limitTar = 5;

        function listarTarifas(page = 1) {
            const tipoHab = document.getElementById('tipoHab').value || '';
            const temporada = document.getElementById('tempTar').value || '';

            let url = `../api/tarifas.php?page=${page}&limit=${limitTar}`;
            if (tipoHab) url += `&tipo_hab=${encodeURIComponent(tipoHab)}`;
            if (temporada) url += `&temporada=${encodeURIComponent(temporada)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    // { data, total, page, limit }
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const pag = obj.page || 1;
                    const lim = obj.limit || limitTar;

                    // Rellenar tabla
                    const tbody = document.getElementById('tabla-tarifas');
                    tbody.innerHTML = '';
                    data.forEach(t => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                    <td>${t.id_tarifa}</td>
                    <td>${t.nombre_tarifa}</td>
                    <td>${t.tipo_habitacion}</td>
                    <td>${t.precio}</td>
                    <td>${t.temporada}</td>
                    <td>${t.fecha_inicio}</td>
                    <td>${t.fecha_fin}</td>
                    <td>
                        <button class="btn" onclick="eliminarTarifa(${t.id_tarifa})">Eliminar</button>
                    </td>
                `;
                        tbody.appendChild(tr);
                    });
                    renderPaginacionTarifas(pag, lim, total);
                })
                .catch(e => console.error(e));
        }

        function renderPaginacionTarifas(page, limit, total) {
            const divPag = document.getElementById('paginacionTarifas');
            divPag.innerHTML = '';
            const totalPages = Math.ceil(total / limit);

            if (page > 1) {
                const btnPrev = document.createElement('button');
                btnPrev.classList.add('btn');
                btnPrev.textContent = 'Anterior';
                btnPrev.onclick = () => listarTarifas(page - 1);
                divPag.appendChild(btnPrev);
            }

            const sp = document.createElement('span');
            sp.style.margin = '0 10px';
            sp.textContent = `Página ${page} de ${totalPages} (Total: ${total})`;
            divPag.appendChild(sp);

            if (page < totalPages) {
                const btnNext = document.createElement('button');
                btnNext.classList.add('btn');
                btnNext.textContent = 'Siguiente';
                btnNext.onclick = () => listarTarifas(page + 1);
                divPag.appendChild(btnNext);
            }
        }

        function crearTarifa() {
            const nombre_tarifa = document.getElementById('nomTarifa').value;
            const tipo_habitacion = document.getElementById('tipoTarifa').value;
            const precio = document.getElementById('precioTar').value;
            const temporada = document.getElementById('tempTar2').value;
            const fecha_inicio = document.getElementById('iniTar').value;
            const fecha_fin = document.getElementById('finTar').value;

            fetch('../api/tarifas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        nombre_tarifa,
                        tipo_habitacion,
                        precio,
                        temporada,
                        fecha_inicio,
                        fecha_fin
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Tarifa creada con éxito');
                        listarTarifas(1);
                        // Limpieza
                        document.getElementById('nomTarifa').value = '';
                        document.getElementById('tipoTarifa').value = '';
                        document.getElementById('precioTar').value = '';
                        document.getElementById('tempTar2').value = '';
                        document.getElementById('iniTar').value = '';
                        document.getElementById('finTar').value = '';
                    } else {
                        alert(data.error || 'No se pudo crear la tarifa');
                    }
                })
                .catch(e => console.error(e));
        }

        function eliminarTarifa(idT) {
            if (!confirm('¿Seguro que deseas eliminar esta tarifa?')) return;
            fetch(`../api/tarifas.php?id=${idT}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Tarifa eliminada');
                        listarTarifas();
                    } else {
                        alert(data.error || 'No se pudo eliminar');
                    }
                })
                .catch(e => console.error(e));
        }

        document.addEventListener('DOMContentLoaded', () => {
            listarTarifas(1);
        });
    </script>
</body>

</html>