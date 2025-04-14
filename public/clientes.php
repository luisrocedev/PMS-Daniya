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
    <title>Gestión de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Clientes</h2>

            <!-- Filtros -->
            <div class="card mb-3">
                <h3>Buscar Clientes</h3>
                <form onsubmit="event.preventDefault(); listarClientesPaginado(1);">
                    <label for="searchCli">Buscar (Nombre/Apellidos/DNI):</label>
                    <input type="text" id="searchCli" class="form-control">
                    <button type="submit" class="btn btn-primary mt-2">Aplicar Filtro</button>
                </form>
            </div>

            <!-- Tabla y paginación -->
            <div class="card mb-3">
                <h3>Listado de Clientes</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>
                <div id="paginacionClientes" style="margin-top:1rem;">
                    <!-- Botones Anterior/Siguiente -->
                </div>
            </div>

            <!-- Formulario de creación -->
            <div class="card">
                <h3>Crear Cliente</h3>
                <form onsubmit="event.preventDefault(); crearCliente();">
                    <label for="nombreCli">Nombre:</label>
                    <input type="text" id="nombreCli" class="form-control" required>

                    <label for="apellidosCli">Apellidos:</label>
                    <input type="text" id="apellidosCli" class="form-control" required>

                    <label for="dniCli">DNI:</label>
                    <input type="text" id="dniCli" class="form-control" required>

                    <label for="emailCli">Email:</label>
                    <input type="email" id="emailCli" class="form-control">

                    <label for="telCli">Teléfono:</label>
                    <input type="text" id="telCli" class="form-control">

                    <label for="dirCli">Dirección:</label>
                    <input type="text" id="dirCli" class="form-control">

                    <!-- NUEVO: Selector para Estado Funnel -->
                    <label for="estado_funnel">Estado Funnel:</label>
                    <select id="estado_funnel" class="form-select">
                        <option value="">Selecciona un estado (opcional)</option>
                        <option value="Nuevo">Nuevo</option>
                        <option value="Interesado">Interesado</option>
                        <option value="En Negociacion">En Negociacion</option>
                        <option value="Cerrado">Cerrado</option>
                    </select>

                    <button type="submit" class="btn btn-success mt-2">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ====================== PAGINACIÓN Y FILTRO ======================
        let limitCliente = 5;

        function listarClientesPaginado(page = 1) {
            const search = document.getElementById('searchCli').value || '';
            let url = `../api/clientes.php?page=${page}&limit=${limitCliente}`;
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    // obj: { data, total, page, limit }
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const pag = obj.page || 1;
                    const lim = obj.limit || limitCliente;

                    // Llenar tabla
                    const tbody = document.getElementById('tabla-clientes');
                    tbody.innerHTML = '';
                    data.forEach(cli => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${cli.id_cliente}</td>
                            <td>${cli.nombre}</td>
                            <td>${cli.apellidos}</td>
                            <td>${cli.dni}</td>
                            <td>${cli.email || ''}</td>
                            <td>${cli.telefono || ''}</td>
                            <td>${cli.direccion || ''}</td>
                            <td>
                                <button class="btn btn-danger" onclick="eliminarCliente(${cli.id_cliente})">Eliminar</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    renderPaginacionClientes(pag, lim, total);
                })
                .catch(err => console.error(err));
        }

        function renderPaginacionClientes(page, limit, total) {
            const divPag = document.getElementById('paginacionClientes');
            divPag.innerHTML = '';

            const totalPages = Math.ceil(total / limit);

            if (page > 1) {
                const btnPrev = document.createElement('button');
                btnPrev.classList.add('btn');
                btnPrev.textContent = 'Anterior';
                btnPrev.onclick = () => listarClientesPaginado(page - 1);
                divPag.appendChild(btnPrev);
            }

            const spanInfo = document.createElement('span');
            spanInfo.style.margin = '0 10px';
            spanInfo.textContent = `Página ${page} de ${totalPages} (Total: ${total})`;
            divPag.appendChild(spanInfo);

            if (page < totalPages) {
                const btnNext = document.createElement('button');
                btnNext.classList.add('btn');
                btnNext.textContent = 'Siguiente';
                btnNext.onclick = () => listarClientesPaginado(page + 1);
                divPag.appendChild(btnNext);
            }
        }

        // ====================== CREAR CLIENTE ======================
        function crearCliente() {
            const nombre = document.getElementById('nombreCli').value;
            const apellidos = document.getElementById('apellidosCli').value;
            const dni = document.getElementById('dniCli').value;
            const email = document.getElementById('emailCli').value;
            const telefono = document.getElementById('telCli').value;
            const direccion = document.getElementById('dirCli').value;
            const estadoFunnel = document.getElementById('estado_funnel').value; // Nuevo campo

            // Preparamos los datos a enviar, incluyendo el campo 'estado_funnel' si se seleccionó
            const params = new URLSearchParams({
                nombre,
                apellidos,
                dni,
                email,
                telefono,
                direccion
            });
            if (estadoFunnel) {
                params.append('estado_funnel', estadoFunnel);
            }

            fetch('../api/clientes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: params
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        listarClientesPaginado(1);
                        // Limpieza de campos
                        document.getElementById('nombreCli').value = '';
                        document.getElementById('apellidosCli').value = '';
                        document.getElementById('dniCli').value = '';
                        document.getElementById('emailCli').value = '';
                        document.getElementById('telCli').value = '';
                        document.getElementById('dirCli').value = '';
                        document.getElementById('estado_funnel').value = '';
                    } else {
                        alert(data.error || 'No se pudo crear el cliente');
                    }
                })
                .catch(err => console.error(err));
        }

        // ====================== ELIMINAR CLIENTE ======================
        function eliminarCliente(idCli) {
            if (!confirm('¿Seguro que deseas eliminar este cliente?')) return;
            fetch(`../api/clientes.php?id=${idCli}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        listarClientesPaginado();
                    } else {
                        alert(data.error || 'No se pudo eliminar');
                    }
                })
                .catch(err => console.error(err));
        }

        // ====================== CARGA INICIAL ======================
        document.addEventListener('DOMContentLoaded', () => {
            listarClientesPaginado(1);
        });
    </script>
</body>

</html>