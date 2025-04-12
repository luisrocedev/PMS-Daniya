<?php
// public/reservas_integradas.php
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
    <title>Gestión de Reservas Integradas - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Propio -->
    <link rel="stylesheet" href="css/style.css">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container-fluid">
            <h2 class="page-title">Gestión de Reservas Integradas</h2>

            <!-- Pestañas para Lista/Creación y Calendario -->
            <ul class="nav nav-tabs" id="reservasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="lista-tab" data-bs-toggle="tab" data-bs-target="#lista" type="button" role="tab" aria-controls="lista" aria-selected="true">Lista y Creación</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calendario-tab" data-bs-toggle="tab" data-bs-target="#calendario" type="button" role="tab" aria-controls="calendario" aria-selected="false">Calendario</button>
                </li>
            </ul>

            <div class="tab-content" id="reservasTabContent">
                <!-- Pestaña Lista y Creación -->
                <div class="tab-pane fade show active" id="lista" role="tabpanel" aria-labelledby="lista-tab">
                    <!-- Filtros de búsqueda con rango de fechas -->
                    <div class="card mt-3">
                        <h3>Buscar Reservas</h3>
                        <form onsubmit="event.preventDefault(); listarReservasPaginado(1);">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <label for="searchRes" class="form-label mb-0">Buscar:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="searchRes" class="form-control" placeholder="ID o Cliente">
                                </div>
                                <div class="col-auto">
                                    <label for="estadoRes" class="form-label mb-0">Estado:</label>
                                </div>
                                <div class="col-auto">
                                    <select id="estadoRes" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Confirmada">Confirmada</option>
                                        <option value="Cancelada">Cancelada</option>
                                        <option value="CheckIn">CheckIn</option>
                                        <option value="CheckOut">CheckOut</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Filtros por rango de fechas -->
                            <div class="row g-2 align-items-center mt-2">
                                <div class="col-auto">
                                    <label for="fechaInicioFiltro" class="form-label mb-0">Fecha Entrada Desde:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="date" id="fechaInicioFiltro" class="form-control">
                                </div>
                                <div class="col-auto">
                                    <label for="fechaFinFiltro" class="form-label mb-0">Hasta:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="date" id="fechaFinFiltro" class="form-control">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" type="submit">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Listado de reservas -->
                    <div class="card mt-3">
                        <h3>Listado de Reservas</h3>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID Reserva</th>
                                    <th>ID Cliente</th>
                                    <th>ID Habitación</th>
                                    <th>Fecha Entrada</th>
                                    <th>Fecha Salida</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-reservas">
                                <!-- Se llenará mediante JS -->
                            </tbody>
                        </table>
                        <div id="paginacionReservas" class="mt-2"></div>
                    </div>

                    <!-- Formulario para Crear Nueva Reserva -->
                    <div class="card mt-4">
                        <h3>Crear Nueva Reserva</h3>
                        <form id="formCrearReserva" onsubmit="event.preventDefault(); crearReserva();">
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label for="id_cliente_nueva" class="form-label">ID Cliente:</label>
                                    <input type="number" id="id_cliente_nueva" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="id_habitacion_nueva" class="form-label">ID Habitación:</label>
                                    <input type="number" id="id_habitacion_nueva" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_entrada_nueva" class="form-label">Fecha Entrada:</label>
                                    <input type="date" id="fecha_entrada_nueva" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_salida_nueva" class="form-label">Fecha Salida:</label>
                                    <input type="date" id="fecha_salida_nueva" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="estado_nueva" class="form-label">Estado:</label>
                                    <select id="estado_nueva" class="form-select">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Confirmada">Confirmada</option>
                                        <option value="Cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success w-100">Crear Reserva</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Pestaña Calendario -->
                <div class="tab-pane fade" id="calendario" role="tabpanel" aria-labelledby="calendario-tab">
                    <div class="card mt-3">
                        <h3>Calendario de Reservas</h3>
                        <!-- Filtro avanzado para Calendario: Tipo de Habitación -->
                        <div class="mb-3">
                            <label for="filtroTipoHab" class="form-label">Tipo de Habitación:</label>
                            <select id="filtroTipoHab" class="form-select" onchange="actualizarCalendario()">
                                <option value="">Todos</option>
                                <option value="Doble">Doble</option>
                                <option value="Individual">Individual</option>
                                <option value="Suite">Suite</option>
                            </select>
                        </div>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Reserva desde el Calendario -->
    <div id="modalNuevaReservaCalendario" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Crear Reserva</h3>
            <form id="formNuevaReservaCalendario" onsubmit="event.preventDefault(); crearReservaCalendario();">
                <div class="mb-2">
                    <label for="fecha_entrada_cal" class="form-label">Fecha Entrada:</label>
                    <input type="date" id="fecha_entrada_cal" class="form-control" readonly>
                </div>
                <div class="mb-2">
                    <label for="fecha_salida_cal" class="form-label">Fecha Salida:</label>
                    <input type="date" id="fecha_salida_cal" class="form-control" required>
                </div>
                <div class="mb-2">
                    <button type="button" class="btn btn-info" onclick="buscarDisponibles()">Buscar Habitaciones Disponibles</button>
                </div>
                <div class="mb-2">
                    <label for="habitacion_disp" class="form-label">Habitación Disponible:</label>
                    <select id="habitacion_disp" class="form-select" required>
                        <option value="">Seleccione</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label for="id_cliente_cal" class="form-label">ID Cliente:</label>
                    <input type="number" id="id_cliente_cal" class="form-control" required>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success me-2">Crear Reserva</button>
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalNuevaReservaCalendario()">Cerrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Edición (Ejemplo básico) -->
    <div id="modalEditarReserva" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Editar Reserva</h3>
            <form onsubmit="event.preventDefault(); actualizarReserva();">
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
                <div class="mt-2">
                    <button class="btn btn-primary" type="submit">Guardar Cambios</button>
                    <button class="btn btn-secondary" type="button" onclick="cerrarModalEditar()">Cerrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <!-- Script JS Personalizado -->
    <script>
        // Función para listar reservas con filtros avanzados
        function listarReservasPaginado(page = 1) {
            const searchVal = document.getElementById('searchRes').value || '';
            const estadoVal = document.getElementById('estadoRes').value || '';
            const fechaInicio = document.getElementById('fechaInicioFiltro').value || '';
            const fechaFin = document.getElementById('fechaFinFiltro').value || '';

            let url = `../api/reservas.php?page=${page}&limit=5`;
            if (searchVal) url += `&search=${encodeURIComponent(searchVal)}`;
            if (estadoVal) url += `&estado=${encodeURIComponent(estadoVal)}`;
            if (fechaInicio) url += `&fecha_inicio_filtro=${encodeURIComponent(fechaInicio)}`;
            if (fechaFin) url += `&fecha_fin_filtro=${encodeURIComponent(fechaFin)}`;

            fetch(url)
                .then(r => r.json())
                .then(obj => {
                    const data = obj.data || [];
                    const total = obj.total || 0;
                    const pageNum = obj.page || 1;
                    const tbody = document.getElementById('tabla-reservas');
                    tbody.innerHTML = '';
                    data.forEach(res => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
              <td>${res.id_reserva}</td>
              <td>${res.id_cliente}</td>
              <td>${res.id_habitacion}</td>
              <td>${res.fecha_entrada}</td>
              <td>${res.fecha_salida}</td>
              <td>${res.estado_reserva}</td>
              <td>
                <button class="btn btn-sm btn-primary" onclick="abrirModalEditar(${res.id_reserva})">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarReserva(${res.id_reserva})">Eliminar</button>
              </td>
            `;
                        tbody.appendChild(tr);
                    });
                    document.getElementById('paginacionReservas').innerHTML = `Página ${pageNum} - Total reservas: ${total}`;
                })
                .catch(e => console.error('Error al listar reservas:', e));
        }

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
                        alert(data.error);
                    }
                })
                .catch(e => console.error('Error al eliminar reserva:', e));
        }

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
                        document.getElementById('formCrearReserva').reset();
                    } else {
                        alert(data.error || 'No se pudo crear la reserva');
                    }
                })
                .catch(e => console.error('Error al crear reserva:', e));
        }

        // Funciones para editar reservas (modal)
        function abrirModalEditar(idReserva) {
            fetch(`../api/reservas.php?id=${idReserva}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.id_reserva) {
                        alert('No se encontró la reserva');
                        return;
                    }
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

        function actualizarReserva() {
            const id_reserva_editar = document.getElementById('id_reserva_editar').value;
            const id_cliente_editar = document.getElementById('id_cliente_editar').value;
            const id_habitacion_edit = document.getElementById('id_habitacion_editar').value;
            const fecha_entrada_edit = document.getElementById('fecha_entrada_editar').value;
            const fecha_salida_edit = document.getElementById('fecha_salida_editar').value;
            const estado_edit = document.getElementById('estado_editar').value;

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

        function cerrarModalEditar() {
            document.getElementById('modalEditarReserva').style.display = 'none';
        }

        // --- Integración de FullCalendar ---
        let calendar;

        function renderCalendar(filterTipo = '') {
            const calendarEl = document.getElementById('calendar');
            if (calendar) {
                calendar.destroy();
            }
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                // Al hacer clic en una fecha se abre el modal para crear reserva
                dateClick: function(info) {
                    abrirModalNuevaReservaCalendario(info.dateStr);
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    let url = `../api/reservas_calendar.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`;
                    if (filterTipo) {
                        url += `&tipo=${encodeURIComponent(filterTipo)}`;
                    }
                    fetch(url)
                        .then(response => response.json())
                        .then(events => successCallback(events))
                        .catch(error => failureCallback(error));
                },
                eventClick: function(info) {
                    alert(`Reserva: ${info.event.title}\nID Reserva: ${info.event.id}`);
                }
            });
            calendar.render();
        }

        function actualizarCalendario() {
            const filtroTipo = document.getElementById('filtroTipoHab').value || '';
            renderCalendar(filtroTipo);
        }

        // Función para abrir el modal de nueva reserva desde el calendario
        function abrirModalNuevaReservaCalendario(fechaEntrada) {
            document.getElementById('fecha_entrada_cal').value = fechaEntrada;
            document.getElementById('fecha_salida_cal').value = '';
            document.getElementById('habitacion_disp').innerHTML = '<option value="">Seleccione</option>';
            document.getElementById('id_cliente_cal').value = '';
            document.getElementById('modalNuevaReservaCalendario').style.display = 'block';
        }

        function cerrarModalNuevaReservaCalendario() {
            document.getElementById('modalNuevaReservaCalendario').style.display = 'none';
        }

        // Buscar habitaciones disponibles llamando a disponibilidad.php
        function buscarDisponibles() {
            const fechaEntrada = document.getElementById('fecha_entrada_cal').value;
            const fechaSalida = document.getElementById('fecha_salida_cal').value;
            if (!fechaEntrada || !fechaSalida) {
                alert('Debe indicar fecha de entrada y salida');
                return;
            }
            const url = `../api/disponibilidad.php?fecha_inicio=${encodeURIComponent(fechaEntrada)}&fecha_fin=${encodeURIComponent(fechaSalida)}`;
            fetch(url)
                .then(response => response.json())
                .then(obj => {
                    const select = document.getElementById('habitacion_disp');
                    select.innerHTML = '<option value="">Seleccione</option>';
                    if (obj.data && obj.data.length > 0) {
                        obj.data.forEach(hab => {
                            const option = document.createElement('option');
                            option.value = hab.id_habitacion;
                            option.textContent = `${hab.numero_habitacion} - ${hab.tipo_habitacion}`;
                            select.appendChild(option);
                        });
                    } else {
                        alert('No hay habitaciones disponibles para el rango seleccionado');
                    }
                })
                .catch(e => console.error('Error al buscar disponibles:', e));
        }

        // Crear reserva desde el modal del calendario
        function crearReservaCalendario() {
            const id_cliente = document.getElementById('id_cliente_cal').value;
            const id_habitacion = document.getElementById('habitacion_disp').value;
            const fecha_entrada = document.getElementById('fecha_entrada_cal').value;
            const fecha_salida = document.getElementById('fecha_salida_cal').value;
            const estado = "Pendiente";

            if (!id_cliente || !id_habitacion || !fecha_entrada || !fecha_salida) {
                alert('Complete todos los campos');
                return;
            }

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
                        cerrarModalNuevaReservaCalendario();
                        if (calendar) {
                            calendar.refetchEvents();
                        }
                    } else {
                        alert(data.error || 'No se pudo crear la reserva');
                    }
                })
                .catch(e => console.error('Error al crear reserva desde calendario:', e));
        }

        // Sincronización en tiempo real con setInterval para refrescar la lista y el calendario cada 10 segundos
        document.addEventListener('DOMContentLoaded', () => {
            listarReservasPaginado(1);
            renderCalendar();
            setInterval(() => {
                listarReservasPaginado(1);
                if (calendar) {
                    calendar.refetchEvents();
                }
            }, 10000);
        });
    </script>

    <!-- Estilos rápidos para el modal -->
    <style>
        .modal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
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