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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Mantenimiento - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Gestión de Mantenimiento</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaIncidencia">
                        <i class="fas fa-plus me-2"></i>Nueva Incidencia
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen estadístico -->
                <div class="grid-container">
                    <!-- Pendientes -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="pendientes">0</div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>

                    <!-- En Proceso -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-tools fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="en-proceso">0</div>
                            <div class="stat-label">En Proceso</div>
                        </div>
                    </div>

                    <!-- Resueltas -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="resueltas">0</div>
                            <div class="stat-label">Resueltas</div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-clipboard-list fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="total">0</div>
                            <div class="stat-label">Total Incidencias</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda -->
                <div class="card mb-4 mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Buscar Incidencias</h3>
                        <form onsubmit="event.preventDefault(); listarIncidencias();" class="row g-3">
                            <div class="col-md-5">
                                <label for="searchMant" class="form-label">Búsqueda:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="searchMant" class="form-control" placeholder="Buscar por descripción...">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="estadoMant" class="form-label">Estado:</label>
                                <select id="estadoMant" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Resuelto">Resuelto</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de incidencias -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Listado de Incidencias</h3>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover check-table">
                                <thead>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Incidencia -->
    <div class="modal fade" id="modalNuevaIncidencia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearIncidencia" onsubmit="crearIncidencia(event)" class="row g-3">
                        <div class="col-md-6">
                            <label for="idHab" class="form-label">Habitación:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                                <select id="idHab" class="form-select" required>
                                    <option value="">Seleccione una habitación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="idEmp" class="form-label">Empleado:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-hard-hat"></i></span>
                                <select id="idEmp" class="form-select" required>
                                    <option value="">Seleccione un empleado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="descMant" class="form-label">Descripción:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                <textarea id="descMant" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fRep" class="form-label">Fecha Reporte:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" id="fRep" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fRes" class="form-label">Fecha Resolución:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                <input type="date" id="fRes" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="estMant" class="form-label">Estado:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <select id="estMant" class="form-select" required>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Resuelto">Resuelto</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formCrearIncidencia" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Incidencia -->
    <div class="modal fade" id="modalEditarIncidencia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarIncidencia" onsubmit="editarIncidencia(event)" class="row g-3">
                        <input type="hidden" id="id_incidencia_editar">
                        <div class="col-md-6">
                            <label for="id_habitacion_editar" class="form-label">Habitación:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                                <select id="id_habitacion_editar" class="form-select" required>
                                    <option value="">Seleccione una habitación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="id_empleado_editar" class="form-label">Empleado:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-hard-hat"></i></span>
                                <select id="id_empleado_editar" class="form-select" required>
                                    <option value="">Seleccione un empleado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="descripcion_editar" class="form-label">Descripción:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                <textarea id="descripcion_editar" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_reporte_editar" class="form-label">Fecha Reporte:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" id="fecha_reporte_editar" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_resolucion_editar" class="form-label">Fecha Resolución:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                <input type="date" id="fecha_resolucion_editar" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="estado_editar" class="form-label">Estado:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <select id="estado_editar" class="form-select" required>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Resuelto">Resuelto</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarIncidencia" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/mantenimientos.js"></script>
</body>

</html>