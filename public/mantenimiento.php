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
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="page-title">Gestión de Mantenimiento</h2>
                </div>
            </div>

            <!-- Resumen de Incidencias -->
            <div class="row g-4 mb-4">
                <!-- Pendientes -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-clock stat-icon text-warning"></i>
                            <div id="pendientes" class="stat-value">0</div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                </div>

                <!-- En Proceso -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-tools stat-icon text-info"></i>
                            <div id="en-proceso" class="stat-value">0</div>
                            <div class="stat-label">En Proceso</div>
                        </div>
                    </div>
                </div>

                <!-- Resueltas -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle stat-icon text-success"></i>
                            <div id="resueltas" class="stat-value">0</div>
                            <div class="stat-label">Resueltas</div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-list stat-icon text-primary"></i>
                            <div id="total" class="stat-value">0</div>
                            <div class="stat-label">Total Incidencias</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros de búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">Buscar Incidencias</h3>
                    <form onsubmit="event.preventDefault(); listarIncidenciasPaginado(1);" class="row g-3">
                        <div class="col-md-4">
                            <label for="searchMant" class="form-label">Búsqueda:</label>
                            <input type="text" id="searchMant" class="form-control" placeholder="Buscar por descripción...">
                        </div>
                        <div class="col-md-4">
                            <label for="estadoMant" class="form-label">Estado:</label>
                            <select id="estadoMant" class="form-select">
                                <option value="">Todos</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Resuelto">Resuelto</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de incidencias -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="card-title mb-0">Listado de Incidencias</h3>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaIncidencia">
                            <i class="fas fa-plus me-2"></i>Nueva Incidencia
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
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
                    <div id="paginacionMant" class="mt-3"></div>
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