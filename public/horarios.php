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
    <title>Gestión de Horarios - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Gestión de Horarios</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoHorario">
                        <i class="fas fa-plus me-2"></i>Asignar Horario
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Filtros y Resumen -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-3">Filtros</h3>
                                <div class="mb-3">
                                    <label class="form-label">Empleado</label>
                                    <select id="empFiltro" class="form-select">
                                        <option value="">Todos los empleados</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Departamento</label>
                                    <select id="deptoFiltro" class="form-select">
                                        <option value="">Todos los departamentos</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100" onclick="refrescarCalendario()">
                                    <i class="fas fa-filter me-2"></i>Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <i class="fas fa-user-clock fa-2x text-primary mb-3"></i>
                                        <div class="stat-value" id="totalEmpleadosHoy">0</div>
                                        <div class="stat-label">Empleados Hoy</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <i class="fas fa-sun fa-2x text-warning mb-3"></i>
                                        <div class="stat-value" id="turnoManana">0</div>
                                        <div class="stat-label">Turno Mañana</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <i class="fas fa-moon fa-2x text-info mb-3"></i>
                                        <div class="stat-value" id="turnoTarde">0</div>
                                        <div class="stat-label">Turno Tarde</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Horario -->
    <div class="modal fade" id="modalNuevoHorario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Nuevo Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoHorario" onsubmit="event.preventDefault(); guardarHorario();">
                        <div class="mb-3">
                            <label class="form-label">Empleado</label>
                            <select id="empleadoSelect" class="form-select" required>
                                <option value="">Seleccione un empleado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Turno</label>
                            <select id="turnoSelect" class="form-select" required>
                                <option value="">Seleccione un turno</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" id="fechaInicio" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" id="fechaFin" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea id="observaciones" class="form-control" rows="3"
                                placeholder="Notas adicionales..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNuevoHorario" class="btn btn-primary">Guardar Horario</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        let calendar;

        // Inicialización
        document.addEventListener('DOMContentLoaded', () => {
            cargarEmpleados();
            cargarDepartamentos();
            cargarTurnos();
            inicializarCalendario();
            actualizarEstadisticas();
        });

        // Inicializar calendario
        function inicializarCalendario() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '06:00:00',
                slotMaxTime: '23:00:00',
                height: 'auto',
                allDaySlot: false,
                events: cargarEventos,
                eventClick: editarHorario,
                eventContent: function(arg) {
                    return {
                        html: `<div class="fc-content">
                                <div class="fc-title">${arg.event.title}</div>
                                <div class="fc-description small">${arg.event.extendedProps.descripcion || ''}</div>
                            </div>`
                    };
                }
            });
            calendar.render();
        }

        // Cargar eventos del calendario
        function cargarEventos(fetchInfo, successCallback, failureCallback) {
            const emp = document.getElementById('empFiltro').value;
            const depto = document.getElementById('deptoFiltro').value;

            const params = new URLSearchParams({
                desde: fetchInfo.startStr.substring(0, 10),
                hasta: fetchInfo.endStr.substring(0, 10)
            });

            if (emp) params.append('emp', emp);
            if (depto) params.append('depto', depto);

            fetch(`../api/horarios.php?${params}`)
                .then(r => r.json())
                .then(data => {
                    const eventos = data.map(h => ({
                        id: h.id_horario,
                        title: `${h.nombre_empleado} - ${h.nombre_turno}`,
                        start: `${h.fecha_inicio}T${h.hora_inicio}`,
                        end: `${h.fecha_fin}T${h.hora_fin}`,
                        color: obtenerColorTurno(h.nombre_turno),
                        descripcion: h.observaciones
                    }));
                    successCallback(eventos);
                    actualizarEstadisticas(data);
                })
                .catch(failureCallback);
        }

        // Obtener color según el turno
        function obtenerColorTurno(nombreTurno) {
            const turnos = {
                'Mañana': '#4CAF50',
                'Tarde': '#2196F3',
                'Noche': '#9C27B0',
                'default': '#757575'
            };
            return turnos[nombreTurno] || turnos.default;
        }

        // Actualizar estadísticas
        function actualizarEstadisticas(data = []) {
            const hoy = new Date().toISOString().split('T')[0];
            const turnosHoy = data.filter(h => h.fecha_inicio <= hoy && h.fecha_fin >= hoy);

            document.getElementById('totalEmpleadosHoy').textContent = turnosHoy.length;
            document.getElementById('turnoManana').textContent =
                turnosHoy.filter(h => h.nombre_turno === 'Mañana').length;
            document.getElementById('turnoTarde').textContent =
                turnosHoy.filter(h => h.nombre_turno === 'Tarde').length;
        }

        // Cargar empleados en select
        function cargarEmpleados() {
            fetch('../api/empleados.php')
                .then(r => r.json())
                .then(data => {
                    const options = data.map(emp =>
                        `<option value="${emp.id_empleado}">${emp.nombre} ${emp.apellidos}</option>`
                    ).join('');
                    document.getElementById('empleadoSelect').innerHTML =
                        '<option value="">Seleccione un empleado</option>' + options;
                    document.getElementById('empFiltro').innerHTML =
                        '<option value="">Todos los empleados</option>' + options;
                });
        }

        // Cargar departamentos en select
        function cargarDepartamentos() {
            fetch('../api/departamentos.php')
                .then(r => r.json())
                .then(data => {
                    const options = data.map(dep =>
                        `<option value="${dep.id_departamento}">${dep.nombre_departamento}</option>`
                    ).join('');
                    document.getElementById('deptoFiltro').innerHTML =
                        '<option value="">Todos los departamentos</option>' + options;
                });
        }

        // Cargar turnos en select
        function cargarTurnos() {
            fetch('../api/turnos.php')
                .then(r => r.json())
                .then(data => {
                    const options = data.map(turno =>
                        `<option value="${turno.id_turno}">${turno.nombre_turno}</option>`
                    ).join('');
                    document.getElementById('turnoSelect').innerHTML =
                        '<option value="">Seleccione un turno</option>' + options;
                });
        }

        // Guardar nuevo horario
        function guardarHorario() {
            const datos = new URLSearchParams({
                id_empleado: document.getElementById('empleadoSelect').value,
                id_turno: document.getElementById('turnoSelect').value,
                fecha_inicio: document.getElementById('fechaInicio').value,
                fecha_fin: document.getElementById('fechaFin').value,
                observaciones: document.getElementById('observaciones').value
            });

            fetch('../api/horarios.php', {
                    method: 'POST',
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
                    mostrarAlerta('Horario guardado correctamente', 'success');
                    document.getElementById('modalNuevoHorario').querySelector('button.btn-close').click();
                    document.getElementById('formNuevoHorario').reset();
                    refrescarCalendario();
                });
        }

        // Editar horario existente
        function editarHorario(info) {
            // Implementar edición de horario
            console.log('Editar horario:', info.event);
        }

        // Refrescar calendario
        function refrescarCalendario() {
            calendar.refetchEvents();
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
    </script>
</body>

</html>