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
    <title>Gestión de Facturas</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Facturas</h2>

            <!-- FILTRO POR RESERVA (ejemplo) -->
            <div class="card">
                <h3>Buscar Facturas</h3>
                <form onsubmit="event.preventDefault(); listarFacturasPaginado(1);">
                    <label for="reservaF">ID Reserva:</label>
                    <input type="number" id="reservaF">
                    <button class="btn" type="submit">Filtrar</button>
                </form>
            </div>

            <!-- LISTADO -->
            <div class="card">
                <h3>Listado de Facturas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Reserva</th>
                            <th>Fecha Emisión</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-fact"></tbody>
                </table>
                <div id="paginacionFact" style="margin-top:1rem;"></div>
            </div>

            <!-- CREAR -->
            <div class="card">
                <h3>Crear Factura</h3>
                <form onsubmit="event.preventDefault(); crearFactura();">
                    <label for="idResF">ID Reserva:</label>
                    <input type="number" id="idResF" required>

                    <label for="fechaF">Fecha Emisión:</label>
                    <input type="date" id="fechaF" required>

                    <label for="totalF">Total:</label>
                    <input type="number" step="0.01" id="totalF" required>

                    <label for="metodoF">Método Pago:</label>
                    <select id="metodoF">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>

                    <button class="btn" type="submit">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let limitFact = 5;

        function listarFacturasPaginado(page = 1) {
            const reserva = document.getElementById('reservaF').value || '';
            let url = `../api/facturas.php?page=${page}&limit=${limitFact}`;
            if (reserva) url += `&reserva=${encodeURIComponent(reserva)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const pag = obj.page || 1;
                    const lim = obj.limit || limitFact;

                    const tbody = document.getElementById('tabla-fact');
                    tbody.innerHTML = '';
                    data.forEach(f => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
          <td>${f.id_factura}</td>
          <td>${f.id_reserva}</td>
          <td>${f.fecha_emision}</td>
          <td>${f.total}</td>
          <td>${f.metodo_pago}</td>
          <td>
            <button class="btn" onclick="eliminarFactura(${f.id_factura})">Eliminar</button>
          </td>
        `;
                        tbody.appendChild(tr);
                    });

                    renderPagFact(pag, lim, total);
                })
                .catch(e => console.error(e));
        }

        function renderPagFact(page, limit, total) {
            const divP = document.getElementById('paginacionFact');
            divP.innerHTML = '';
            const totalPages = Math.ceil(total / limit);

            if (page > 1) {
                const bp = document.createElement('button');
                bp.classList.add('btn');
                bp.textContent = 'Anterior';
                bp.onclick = () => listarFacturasPaginado(page - 1);
                divP.appendChild(bp);
            }

            const sp = document.createElement('span');
            sp.style.margin = '0 10px';
            sp.textContent = `Página ${page} de ${totalPages} (Total: ${total})`;
            divP.appendChild(sp);

            if (page < totalPages) {
                const bn = document.createElement('button');
                bn.classList.add('btn');
                bn.textContent = 'Siguiente';
                bn.onclick = () => listarFacturasPaginado(page + 1);
                divP.appendChild(bn);
            }
        }

        function crearFactura() {
            const id_reserva = document.getElementById('idResF').value;
            const fecha_emision = document.getElementById('fechaF').value;
            const total = document.getElementById('totalF').value;
            const metodo_pago = document.getElementById('metodoF').value;

            fetch('../api/facturas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id_reserva,
                        fecha_emision,
                        total,
                        metodo_pago
                    })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(d.msg);
                        listarFacturasPaginado(1);
                        document.getElementById('idResF').value = '';
                        document.getElementById('fechaF').value = '';
                        document.getElementById('totalF').value = '';
                        document.getElementById('metodoF').value = 'Efectivo';
                    } else {
                        alert(d.error || 'No se pudo crear');
                    }
                })
                .catch(e => console.error(e));
        }

        function eliminarFactura(idF) {
            if (!confirm('¿Eliminar esta factura?')) return;
            fetch(`../api/facturas.php?id=${idF}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(d.msg);
                        listarFacturasPaginado();
                    } else {
                        alert(d.error || 'No se pudo eliminar');
                    }
                })
                .catch(e => console.error(e));
        }

        document.addEventListener('DOMContentLoaded', () => {
            listarFacturasPaginado(1);
        });
    </script>
</body>

</html>