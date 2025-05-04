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
    <title>Gestión de Habitaciones - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2 class="page-title mb-4">Gestión de Habitaciones</h2>

            <div id="habitaciones-pages">
                <div class="content-page active" data-page="1">
                    <!-- Tarjetas de Estadísticas -->
                    <div class="row g-4">
                        <!-- Habitaciones Disponibles -->
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-door-open stat-icon text-success"></i>
                                    <div id="disponibles" class="stat-value">0</div>
                                    <div class="stat-label">Habitaciones Disponibles</div>
                                </div>
                            </div>
                        </div>

                        <!-- Habitaciones Ocupadas -->
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-bed stat-icon text-primary"></i>
                                    <div id="ocupadas" class="stat-value">0</div>
                                    <div class="stat-label">Habitaciones Ocupadas</div>
                                </div>
                            </div>
                        </div>

                        <!-- En Mantenimiento -->
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-tools stat-icon text-warning"></i>
                                    <div id="mantenimiento" class="stat-value">0</div>
                                    <div class="stat-label">En Mantenimiento</div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Habitaciones -->
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-hotel stat-icon text-info"></i>
                                    <div id="total" class="stat-value">0</div>
                                    <div class="stat-label">Total Habitaciones</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de búsqueda -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Filtrar Habitaciones</h3>
                            <form onsubmit="event.preventDefault(); listarHabitaciones();" class="row g-3">
                                <div class="col-md-4">
                                    <label for="searchHab" class="form-label">Búsqueda:</label>
                                    <input type="text" id="searchHab" class="form-control" placeholder="Número o tipo...">
                                </div>
                                <div class="col-md-4">
                                    <label for="estadoHab" class="form-label">Estado:</label>
                                    <select id="estadoHab" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="Disponible">Disponible</option>
                                        <option value="Ocupada">Ocupada</option>
                                        <option value="Mantenimiento">Mantenimiento</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="content-page" data-page="2">
                    <!-- Listado de habitaciones -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title mb-0">Listado de Habitaciones</h3>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaHabitacion">
                                    <i class="fas fa-plus me-2"></i> Nueva Habitación
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Número</th>
                                            <th>Tipo</th>
                                            <th>Capacidad</th>
                                            <th>Piso</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-habitaciones">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="paginacionHabs" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <div class="content-page" data-page="3">
                    <!-- Formulario de creación compacto -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3 text-center">Nueva Habitación</h3>
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <form id="formNuevaHabitacionInPage" onsubmit="event.preventDefault(); crearHabitacionEnPagina();">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="numHabInPage" class="form-label">Número de Habitación:</label>
                                                <input type="text" class="form-control" id="numHabInPage" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="tipoHabInPage" class="form-label">Tipo:</label>
                                                <select class="form-select" id="tipoHabInPage" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="Individual">Individual</option>
                                                    <option value="Doble">Doble</option>
                                                    <option value="Suite">Suite</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="capHabInPage" class="form-label">Capacidad:</label>
                                                <input type="number" class="form-control" id="capHabInPage" min="1" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="pisoHabInPage" class="form-label">Piso:</label>
                                                <input type="number" class="form-control" id="pisoHabInPage" min="1" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="estHabInPage" class="form-label">Estado:</label>
                                                <select class="form-select" id="estHabInPage" required>
                                                    <option value="Disponible">Disponible</option>
                                                    <option value="Ocupada">Ocupada</option>
                                                    <option value="Mantenimiento">Mantenimiento</option>
                                                </select>
                                            </div>

                                            <div class="col-12 mt-4">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-plus-circle me-2"></i>Crear Habitación
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles de navegación de páginas -->
                <div class="page-nav text-center mt-4">
                    <button id="prevHab" class="btn btn-secondary me-2">Anterior</button>
                    <span class="page-indicator">Página <span id="currentHabPage">1</span> de <span id="totalHabPages">3</span></span>
                    <button id="nextHab" class="btn btn-secondary">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Habitación (mantenemos el modal para compatibilidad) -->
    <div class="modal fade" id="modalNuevaHabitacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaHabitacion" onsubmit="event.preventDefault(); crearHabitacion();">
                        <div class="mb-3">
                            <label for="numHab" class="form-label">Número de Habitación:</label>
                            <input type="text" class="form-control" id="numHab" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipoHab" class="form-label">Tipo:</label>
                            <select class="form-select" id="tipoHab" required>
                                <option value="">Seleccione...</option>
                                <option value="Individual">Individual</option>
                                <option value="Doble">Doble</option>
                                <option value="Suite">Suite</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="capHab" class="form-label">Capacidad:</label>
                            <input type="number" class="form-control" id="capHab" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="pisoHab" class="form-label">Piso:</label>
                            <input type="number" class="form-control" id="pisoHab" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="estHab" class="form-label">Estado:</label>
                            <select class="form-select" id="estHab" required>
                                <option value="Disponible">Disponible</option>
                                <option value="Ocupada">Ocupada</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNuevaHabitacion" class="btn btn-primary">Crear Habitación</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts personalizados -->
    <script src="js/habitaciones.js"></script>
</body>

</html>