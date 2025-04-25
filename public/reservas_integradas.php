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
    <title>Gestión de Reservas - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="page-title">Gestión de Reservas</h2>
                </div>
            </div>

            <!-- Pestañas para Lista/Creación y Calendario -->
            <ul class="nav nav-tabs" id="reservasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="lista-tab" data-bs-toggle="tab" data-bs-target="#lista">
                        <i class="fas fa-list me-2"></i>Lista y Creación
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calendario-tab" data-bs-toggle="tab" data-bs-target="#calendario">
                        <i class="fas fa-calendar-alt me-2"></i>Calendario
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="reservasTabContent">
                <!-- Pestaña Lista y Creación -->
                <div class="tab-pane fade show active" id="lista" role="tabpanel">
                    <!-- Filtros de búsqueda -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Buscar Reservas</h3>
                            <form onsubmit="event.preventDefault(); listarReservasPaginado(1);" class="row g-3">
                                <div class="col-md-3">
                                    <label for="searchRes" class="form-label">Búsqueda:</label>
                                    <input type="text" id="searchRes" class="form-control" placeholder="ID o Cliente">
                                </div>
                                <div class="col-md-3">
                                    <label for="estadoRes" class="form-label">Estado:</label>
                                    <select id="estadoRes" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Confirmada">Confirmada</option>
                                        <option value="Cancelada">Cancelada</option>
                                        <option value="CheckIn">Check-in</option>
                                        <option value="CheckOut">Check-out</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="fechaInicioFiltro" class="form-label">Fecha Desde:</label>
                                    <input type="date" id="fechaInicioFiltro" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label for="fechaFinFiltro" class="form-label">Fecha Hasta:</label>
                                    <input type="date" id="fechaFinFiltro" class="form-control">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de reservas -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="card-title mb-0">Listado de Reservas</h3>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaReserva">
                                    <i class="fas fa-plus me-2"></i>Nueva Reserva
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Habitación</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-reservas">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="paginacionReservas" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Pestaña Calendario -->
                <div class="tab-pane fade" id="calendario" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="filtroTipoHab" class="form-label">Filtrar por Tipo:</label>
                                    <select id="filtroTipoHab" class="form-select" onchange="actualizarCalendario()">
                                        <option value="">Todas las habitaciones</option>
                                        <option value="Individual">Individual</option>
                                        <option value="Doble">Doble</option>
                                        <option value="Suite">Suite</option>
                                    </select>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Reserva -->
    <div class="modal fade" id="modalNuevaReserva" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearReserva" onsubmit="event.preventDefault(); crearReserva();">
                        <div class="mb-3">
                            <label for="id_cliente_nueva" class="form-label">ID Cliente:</label>
                            <input type="number" id="id_cliente_nueva" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_habitacion_nueva" class="form-label">ID Habitación:</label>
                            <input type="number" id="id_habitacion_nueva" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_entrada_nueva" class="form-label">Fecha Entrada:</label>
                            <input type="date" id="fecha_entrada_nueva" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_salida_nueva" class="form-label">Fecha Salida:</label>
                            <input type="date" id="fecha_salida_nueva" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado_nueva" class="form-label">Estado:</label>
                            <select id="estado_nueva" class="form-select">
                                <option value="Pendiente">Pendiente</option>
                                <option value="Confirmada">Confirmada</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formCrearReserva" class="btn btn-primary">Crear Reserva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Reserva -->
    <div class="modal fade" id="modalEditarReserva" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarReserva" onsubmit="event.preventDefault(); editarReserva();">
                        <input type="hidden" id="id_reserva_editar">
                        <div class="mb-3">
                            <label for="id_cliente_editar" class="form-label">ID Cliente:</label>
                            <input type="number" id="id_cliente_editar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_habitacion_editar" class="form-label">ID Habitación:</label>
                            <input type="number" id="id_habitacion_editar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_entrada_editar" class="form-label">Fecha Entrada:</label>
                            <input type="date" id="fecha_entrada_editar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_salida_editar" class="form-label">Fecha Salida:</label>
                            <input type="date" id="fecha_salida_editar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado_editar" class="form-label">Estado:</label>
                            <select id="estado_editar" class="form-select">
                                <option value="Pendiente">Pendiente</option>
                                <option value="Confirmada">Confirmada</option>
                                <option value="Cancelada">Cancelada</option>
                                <option value="CheckIn">Check-in</option>
                                <option value="CheckOut">Check-out</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarReserva" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Reserva desde Calendario -->
    <div class="modal fade" id="modalNuevaReservaCalendario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaReservaCalendario" onsubmit="event.preventDefault(); crearReserva(true);">
                        <div class="mb-3">
                            <label for="fecha_entrada_cal" class="form-label">Fecha Entrada:</label>
                            <input type="date" id="fecha_entrada_cal" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_salida_cal" class="form-label">Fecha Salida:</label>
                            <input type="date" id="fecha_salida_cal" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-info w-100" onclick="buscarDisponibles()">
                                <i class="fas fa-search me-2"></i>Buscar Habitaciones Disponibles
                            </button>
                        </div>
                        <div class="mb-3">
                            <label for="habitacion_disp" class="form-label">Habitación Disponible:</label>
                            <select id="habitacion_disp" class="form-select" required>
                                <option value="">Seleccione una habitación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_cliente_cal" class="form-label">ID Cliente:</label>
                            <input type="number" id="id_cliente_cal" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNuevaReservaCalendario" class="btn btn-primary">Crear Reserva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="js/reservas.js"></script>
</body>

</html>