<?php
// public/reservas.php
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
    <title>Gestión de Reservas - PMS Daniya Denia</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Incluir navbar -->
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div style="display:flex; margin-top:1rem;">
        <!-- Incluir sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">Gestión de Reservas</h2>

            <!-- Filtros de búsqueda -->
            <div class="card">
                <h3>Buscar Reservas</h3>
                <form onsubmit="event.preventDefault(); listarReservasPaginado(1);">
                    <label for="searchRes">Buscar (ID Reserva / ID Cliente / ID Habitación):</label>
                    <input type="text" id="searchRes" placeholder="Ej: 3, o 12...">

                    <label for="estadoRes">Estado:</label>
                    <select id="estadoRes">
                        <option value="">Todos</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Confirmada">Confirmada</option>
                        <option value="Cancelada">Cancelada</option>
                        <option value="CheckIn">CheckIn</option>
                        <option value="CheckOut">CheckOut</option>
                    </select>

                    <button class="btn" type="submit">Filtrar</button>
                </form>
            </div>

            <!-- Listado de reservas (tabla) -->
            <div class="card">
                <h3>Listado de Reservas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Reserva</th>
                            <th>ID Cliente</th>
                            <th>ID Hab</th>
                            <th>F. Entrada</th>
                            <th>F. Salida</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-reservas">
                        <!-- Se llenará con JS -->
                    </tbody>
                </table>
                <div id="paginacionReservas" style="margin-top:1rem;"></div>
            </div>

            <!-- Formulario para crear nueva reserva -->
            <div class="card">
                <h3>Crear Nueva Reserva</h3>
                <form onsubmit="event.preventDefault(); crearReserva();">
                    <label for="id_cliente_nueva">ID Cliente:</label>
                    <input type="number" id="id_cliente_nueva" required>

                    <label for="id_habitacion_nueva">ID Habitación:</label>
                    <input type="number" id="id_habitacion_nueva" required>

                    <label for="fecha_entrada_nueva">Fecha Entrada:</label>
                    <input type="date" id="fecha_entrada_nueva" required>

                    <label for="fecha_salida_nueva">Fecha Salida:</label>
                    <input type="date" id="fecha_salida_nueva" required>

                    <!-- Estado inicial (puede ser Pendiente/Confirmada, según tu lógica) -->
                    <label for="estado_nueva">Estado:</label>
                    <select id="estado_nueva">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Confirmada">Confirmada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>

                    <button class="btn" type="submit">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR (opcional) -->
    <div id="modalEditarReserva" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Editar Reserva</h3>
            <form onsubmit="event.preventDefault(); actualizarReserva();">
                <!-- Campo oculto para guardar el id_reserva que estamos editando -->
                <input type="hidden" id="id_reserva_editar">

                <label for="id_cliente_editar">ID Cliente:</label>
                <input type="number" id="id_cliente_editar" required>

                <label for="id_habitacion_editar">ID Habitación:</label>
                <input type="number" id="id_habitacion_editar" required>

                <label for="fecha_entrada_editar">Fecha Entrada:</label>
                <input type="date" id="fecha_entrada_editar" required>

                <label for="fecha_salida_editar">Fecha Salida:</label>
                <input type="date" id="fecha_salida_editar" required>

                <label for="estado_editar">Estado:</label>
                <select id="estado_editar">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Cancelada">Cancelada</option>
                    <option value="CheckIn">CheckIn</option>
                    <option value="CheckOut">CheckOut</option>
                </select>

                <button class="btn" type="submit">Guardar Cambios</button>
                <button class="btn" type="button" onclick="cerrarModalEditar()">Cerrar</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        /*********************************************************
         * 1. Variables globales para paginación
         *********************************************************/
        let limitReservas = 5;

        /*********************************************************
         * 2. Función para LISTAR con filtros/paginación
         *********************************************************/
        function listarReservasPaginado(page = 1) {
            const searchVal = document.getElementById('searchRes').value || '';
            const estadoVal = document.getElementById('estadoRes').value || '';

            let url = `../api/reservas.php?&page=${page}&limit=${limitReservas}`;

            // Puedes implementar la lógica de filtros en tu endpoint (ej: ?search=... &estado=...)
            // Para simplificar, o si ya tienes un 'search' genérico:
            if (searchVal) url += `&search=${encodeURIComponent(searchVal)}`;
            if (estadoVal) url += `&estado=${encodeURIComponent(estadoVal)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    // supongamos que devuelves { data, total, page, limit }, 
                    // similar a como hiciste con empleados/clientes.
                    // Pero si no has implementado paginación en el endpoint, devuelves un array plano 
                    // y no podrás saber el total. Ajusta según tu API.
                    if (Array.isArray(obj)) {
                        // Caso 1: sin paginación, 'obj' es un array con todas las reservas
                        renderTablaReservas(obj);
                    } else {
                        // Caso 2: con paginación
                        const data = obj.data || [];
                        const total = obj.total || 0;
                        const pag = obj.page || 1;
                        const lim = obj.limit || limitReservas;

                        renderTablaReservas(data);
                        renderPaginacionReservas(pag, lim, total);
                    }
                })
                .catch(e => console.error(e));
        }

        function renderTablaReservas(reservas) {
            const tbody = document.getElementById('tabla-reservas');
            tbody.innerHTML = '';
            reservas.forEach(res => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td>${res.id_reserva}</td>
      <td>${res.id_cliente}</td>
      <td>${res.id_habitacion}</td>
      <td>${res.fecha_entrada}</td>
      <td>${res.fecha_salida}</td>
      <td>${res.estado_reserva}</td>
      <td>
        <button class="btn" onclick="abrirModalEditar(${res.id_reserva})">Editar</button>
        <button class="btn" onclick="eliminarReserva(${res.id_reserva})">Eliminar</button>
      </td>
    `;
                tbody.appendChild(tr);
            });
        }

        function renderPaginacionReservas(page, limit, total) {
            const divPag = document.getElementById('paginacionReservas');
            divPag.innerHTML = '';
            const totalPages = Math.ceil(total / limit);

            if (page > 1) {
                const btnPrev = document.createElement('button');
                btnPrev.classList.add('btn');
                btnPrev.textContent = 'Anterior';
                btnPrev.onclick = () => listarReservasPaginado(page - 1);
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
                btnNext.onclick = () => listarReservasPaginado(page + 1);
                divPag.appendChild(btnNext);
            }
        }

        /*********************************************************
         * 3. Crear Reserva (POST)
         *********************************************************/
        function crearReserva() {
            const id_cliente = document.getElementById('id_cliente_nueva').value;
            const id_habitacion = document.getElementById('id_habitacion_nueva').value;
            const fecha_entrada = document.getElementById('fecha_entrada_nueva').value;
            const fecha_salida = document.getElementById('fecha_salida_nueva').value;
            const estado = document.getElementById('estado_nueva').value;

            fetch('../api/reservas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id_cliente,
                        id_habitacion,
                        fecha_entrada,
                        fecha_salida,
                        estado_reserva: estado
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        listarReservasPaginado(1);
                        // Limpiar formulario
                        document.getElementById('id_cliente_nueva').value = '';
                        document.getElementById('id_habitacion_nueva').value = '';
                        document.getElementById('fecha_entrada_nueva').value = '';
                        document.getElementById('fecha_salida_nueva').value = '';
                        document.getElementById('estado_nueva').value = 'Pendiente';
                    } else {
                        alert(data.error || 'No se pudo crear la reserva');
                    }
                })
                .catch(e => console.error(e));
        }

        /*********************************************************
         * 4. Editar Reserva (PUT)
         *********************************************************/

        // a) Abrir modal y cargar datos
        function abrirModalEditar(idReserva) {
            // Llamamos a GET /reservas.php?id=XX para obtener la reserva
            fetch(`../api/reservas.php?id=${idReserva}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.id_reserva) {
                        alert('No se encontró la reserva');
                        return;
                    }
                    // Rellenar los campos del modal
                    document.getElementById('id_reserva_editar').value = res.id_reserva;
                    document.getElementById('id_cliente_editar').value = res.id_cliente;
                    document.getElementById('id_habitacion_editar').value = res.id_habitacion;
                    document.getElementById('fecha_entrada_editar').value = res.fecha_entrada;
                    document.getElementById('fecha_salida_editar').value = res.fecha_salida;
                    document.getElementById('estado_editar').value = res.estado_reserva;

                    document.getElementById('modalEditarReserva').style.display = 'block';
                })
                .catch(e => console.error(e));
        }

        // b) Guardar cambios (PUT)
        function actualizarReserva() {
            const id_reserva_editar = document.getElementById('id_reserva_editar').value;
            const id_cliente_editar = document.getElementById('id_cliente_editar').value;
            const id_habitacion_edit = document.getElementById('id_habitacion_editar').value;
            const fecha_entrada_edit = document.getElementById('fecha_entrada_editar').value;
            const fecha_salida_edit = document.getElementById('fecha_salida_editar').value;
            const estado_edit = document.getElementById('estado_editar').value;

            // Enviamos PUT a /reservas.php?id=XX
            fetch(`../api/reservas.php?id=${id_reserva_editar}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id_cliente: id_cliente_editar,
                        id_habitacion: id_habitacion_edit,
                        fecha_entrada: fecha_entrada_edit,
                        fecha_salida: fecha_salida_edit,
                        estado_reserva: estado_edit
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        cerrarModalEditar();
                        listarReservasPaginado();
                    } else {
                        alert(data.error || 'No se pudo actualizar la reserva');
                    }
                })
                .catch(e => console.error(e));
        }

        // c) Cerrar modal
        function cerrarModalEditar() {
            document.getElementById('modalEditarReserva').style.display = 'none';
        }

        /*********************************************************
         * 5. Eliminar Reserva (DELETE)
         *********************************************************/
        function eliminarReserva(idReserva) {
            if (!confirm('¿Seguro que deseas eliminar esta reserva?')) return;

            fetch(`../api/reservas.php?id=${idReserva}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.msg);
                        listarReservasPaginado();
                    } else {
                        alert(data.error || 'No se pudo eliminar');
                    }
                })
                .catch(e => console.error(e));
        }

        /*********************************************************
         * 6. Carga Inicial
         *********************************************************/
        document.addEventListener('DOMContentLoaded', () => {
            listarReservasPaginado(1);
        });
    </script>

    <!-- Ejemplo de estilos para el modal (puedes ajustarlo a tu style.css) -->
    <style>
        .modal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: #fff;
            padding: 1rem;
            margin: 10% auto;
            width: 400px;
            border-radius: 8px;
        }
    </style>

</body>

</html>