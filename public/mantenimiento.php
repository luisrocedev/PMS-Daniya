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
    <title>Gestión de Mantenimiento - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos para evitar el scroll vertical en la página principal */
        body,
        html {
            height: 100%;
            overflow: hidden;
        }

        .main-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .content-wrapper {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .scrollable-content {
            overflow-y: auto;
            flex: 1;
            padding: 15px;
        }

        .cards-row {
            flex-shrink: 0;
            margin-bottom: 15px;
        }

        .card-body {
            padding: 1rem;
        }

        /* Ajustes específicos para la tabla */
        .table-container {
            max-height: calc(100% - 20px);
            overflow-y: auto;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php include __DIR__ . '/../partials/navbar.php'; ?>

        <div class="content-wrapper">
            <?php include __DIR__ . '/../partials/sidebar.php'; ?>

            <div class="main-content container-fluid p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="page-title mb-0">Gestión de Mantenimiento</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaIncidencia">
                        <i class="fas fa-plus me-2"></i>Nueva Incidencia
                    </button>
                </div>

                <!-- Resumen de Incidencias - Fixed Height -->
                <div class="row g-3 cards-row">
                    <!-- Pendientes -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-clock stat-icon text-warning"></i>
                                <div id="pendientes" class="stat-value">0</div>
                                <div class="stat-label">Pendientes</div>
                            </div>
                        </div>
                    </div>

                    <!-- En Proceso -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-tools stat-icon text-info"></i>
                                <div id="en-proceso" class="stat-value">0</div>
                                <div class="stat-label">En Proceso</div>
                            </div>
                        </div>
                    </div>

                    <!-- Resueltas -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-check-circle stat-icon text-success"></i>
                                <div id="resueltas" class="stat-value">0</div>
                                <div class="stat-label">Resueltas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-clipboard-list stat-icon text-primary"></i>
                                <div id="total" class="stat-value">0</div>
                                <div class="stat-label">Total Incidencias</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda - Fixed Height -->
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <h5 class="card-title mb-3">Buscar Incidencias</h5>
                        <form onsubmit="event.preventDefault(); listarIncidenciasPaginado(1);" class="row g-2">
                            <div class="col-md-4">
                                <label for="searchMant" class="form-label small mb-1">Búsqueda:</label>
                                <input type="text" id="searchMant" class="form-control form-control-sm" placeholder="Buscar por descripción...">
                            </div>
                            <div class="col-md-4">
                                <label for="estadoMant" class="form-label small mb-1">Estado:</label>
                                <select id="estadoMant" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Resuelto">Resuelto</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-search me-1"></i>Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de incidencias - Scrollable -->
                <div class="card mb-0 flex-grow-1">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0">Listado de Incidencias</h5>
                    </div>
                    <div class="card-body p-0 d-flex flex-column h-100">
                        <div class="table-container">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>ID</th>
                                        <th>Habitación</th>
                                        <th>Empleado</th>
                                        <th>Descripción</th>
                                        <th>F. Reporte</th>
                                        <th>F. Resolución</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-mant">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        <div id="paginacionMant" class="mt-2 p-2 border-top"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Incidencia -->
    <div class="modal fade" id="modalNuevaIncidencia" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearIncidencia" onsubmit="crearIncidencia(event)">
                        <div class="mb-3">
                            <label for="idHab" class="form-label">Habitación:</label>
                            <select id="idHab" class="form-select" required>
                                <option value="">Seleccione una habitación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="idEmp" class="form-label">Empleado:</label>
                            <select id="idEmp" class="form-select" required>
                                <option value="">Seleccione un empleado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descMant" class="form-label">Descripción:</label>
                            <textarea id="descMant" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="fRep" class="form-label">Fecha Reporte:</label>
                            <input type="date" id="fRep" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fRes" class="form-label">Fecha Resolución:</label>
                            <input type="date" id="fRes" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="estMant" class="form-label">Estado:</label>
                            <select id="estMant" class="form-select" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Resuelto">Resuelto</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formCrearIncidencia" class="btn btn-primary">Crear Incidencia</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Incidencia -->
    <div class="modal fade" id="modalEditarIncidencia" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarIncidencia" onsubmit="editarIncidencia(event)">
                        <input type="hidden" id="id_incidencia_editar">
                        <div class="mb-3">
                            <label for="id_habitacion_editar" class="form-label">Habitación:</label>
                            <select id="id_habitacion_editar" class="form-select" required>
                                <option value="">Seleccione una habitación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_empleado_editar" class="form-label">Empleado:</label>
                            <select id="id_empleado_editar" class="form-select" required>
                                <option value="">Seleccione un empleado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion_editar" class="form-label">Descripción:</label>
                            <textarea id="descripcion_editar" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_reporte_editar" class="form-label">Fecha Reporte:</label>
                            <input type="date" id="fecha_reporte_editar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_resolucion_editar" class="form-label">Fecha Resolución:</label>
                            <input type="date" id="fecha_resolucion_editar" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="estado_editar" class="form-label">Estado:</label>
                            <select id="estado_editar" class="form-select" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Resuelto">Resuelto</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarIncidencia" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/mantenimientos.js"></script>
</body>

</html>