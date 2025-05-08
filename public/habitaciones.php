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
                <h2 class="page-title">Gestión de Habitaciones</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaHabitacion">
                        <i class="fas fa-plus me-2"></i>Nueva Habitación
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen estadístico -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-door-open fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="disponibles">0</div>
                            <div class="stat-label">Habitaciones Disponibles</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-bed fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="ocupadas">0</div>
                            <div class="stat-label">Habitaciones Ocupadas</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-tools fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="mantenimiento">0</div>
                            <div class="stat-label">En Mantenimiento</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-hotel fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="total">0</div>
                            <div class="stat-label">Total Habitaciones</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda -->
                <div class="card mb-4 mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Buscar Habitaciones</h3>
                        <form onsubmit="event.preventDefault(); listarHabitaciones();" class="row g-3">
                            <div class="col-md-5">
                                <label for="searchHab" class="form-label">Búsqueda:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="searchHab" class="form-control" placeholder="Número o tipo...">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="estadoHab" class="form-label">Estado:</label>
                                <select id="estadoHab" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Disponible">Disponible</option>
                                    <option value="Ocupada">Ocupada</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
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

                <!-- Tabla de habitaciones -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Listado de Habitaciones</h3>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover check-table">
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
                    </div>
                </div>

                <!-- Formulario de creación alternativo (opcional) -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Crear Nueva Habitación</h3>
                        <form id="formNuevaHabitacionInPage" onsubmit="event.preventDefault(); crearHabitacionEnPagina();" class="row g-3">
                            <div class="col-md-4">
                                <label for="numHabInPage" class="form-label">Número de Habitación:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" id="numHabInPage" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tipoHabInPage" class="form-label">Tipo:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                                    <select id="tipoHabInPage" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Individual">Individual</option>
                                        <option value="Doble">Doble</option>
                                        <option value="Suite">Suite</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="capHabInPage" class="form-label">Capacidad:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    <input type="number" id="capHabInPage" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="pisoHabInPage" class="form-label">Piso:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-level-up-alt"></i></span>
                                    <input type="number" id="pisoHabInPage" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="estHabInPage" class="form-label">Estado:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                    <select id="estHabInPage" class="form-select" required>
                                        <option value="Disponible">Disponible</option>
                                        <option value="Ocupada">Ocupada</option>
                                        <option value="Mantenimiento">Mantenimiento</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus-circle me-2"></i>Crear Habitación
                                </button>
                                <button type="reset" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-undo me-2"></i>Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Habitación -->
    <div class="modal fade" id="modalNuevaHabitacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaHabitacion" onsubmit="event.preventDefault(); crearHabitacion();" class="row g-3">
                        <div class="col-md-6">
                            <label for="numHab" class="form-label">Número de Habitación:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" id="numHab" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tipoHab" class="form-label">Tipo:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                                <select id="tipoHab" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Doble">Doble</option>
                                    <option value="Suite">Suite</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="capHab" class="form-label">Capacidad:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                                <input type="number" id="capHab" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="pisoHab" class="form-label">Piso:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-level-up-alt"></i></span>
                                <input type="number" id="pisoHab" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="estHab" class="form-label">Estado:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <select id="estHab" class="form-select" required>
                                    <option value="Disponible">Disponible</option>
                                    <option value="Ocupada">Ocupada</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNuevaHabitacion" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Habitación -->
    <div class="modal fade" id="modalEditarHabitacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarHabitacion" class="row g-3">
                        <input type="hidden" id="idHabEdit">
                        <div class="col-md-6">
                            <label for="numHabEdit" class="form-label">Número de Habitación:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" id="numHabEdit" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tipoHabEdit" class="form-label">Tipo:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                                <select id="tipoHabEdit" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Doble">Doble</option>
                                    <option value="Suite">Suite</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="capHabEdit" class="form-label">Capacidad:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                                <input type="number" id="capHabEdit" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="pisoHabEdit" class="form-label">Piso:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-level-up-alt"></i></span>
                                <input type="number" id="pisoHabEdit" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="estHabEdit" class="form-label">Estado:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <select id="estHabEdit" class="form-select" required>
                                    <option value="Disponible">Disponible</option>
                                    <option value="Ocupada">Ocupada</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarHabitacion()">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
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