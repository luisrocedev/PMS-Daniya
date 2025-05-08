<?php
// public/empleados.php

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
    <title>Gestión de Empleados - PMS Daniya Denia</title>
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
                <h2 class="page-title">Gestión de Empleados</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" id="btnNuevoEmpleado">
                        <i class="fas fa-plus me-2"></i>Nuevo Empleado
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Filtros de búsqueda -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Buscar Empleados</h3>
                        <form onsubmit="event.preventDefault(); listarEmpleados();" class="row g-3">
                            <div class="col-md-4">
                                <label for="buscarTxt" class="form-label">Texto (Nombre, Apellidos, DNI):</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="buscarTxt" class="form-control" placeholder="Ej: 'López'">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="buscarRol" class="form-label">Rol:</label>
                                <select id="buscarRol" class="form-select">
                                    <option value="">Todos</option>
                                    <!-- Se llenará dinámicamente en cargarRolesYDeps() -->
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="buscarDep" class="form-label">Departamento:</label>
                                <select id="buscarDep" class="form-select">
                                    <option value="">Todos</option>
                                    <!-- Se llenará dinámicamente -->
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Aplicar Filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Resumen estadístico -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="totalEmpleados">0</div>
                            <div class="stat-label">Total Empleados</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-user-tie fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="totalDirectivos">0</div>
                            <div class="stat-label">Directivos</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-calendar-check fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="empleadosActivos">0</div>
                            <div class="stat-label">Activos Hoy</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="departamentos">0</div>
                            <div class="stat-label">Departamentos</div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de empleados -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Listado de Empleados</h3>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover check-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Apellidos</th>
                                        <th>DNI</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Departamento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-empleados">
                                    <!-- Se llena con JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Empleado -->
    <div class="modal fade" id="modalEmpleado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmpleadoTitulo">Nuevo Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEmpleado" class="row g-3">
                        <input type="hidden" id="id_empleado" value="">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="nombre" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="apellidos" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="dni" class="form-label">DNI:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" id="dni" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" id="telefono" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="email" class="form-label">Email:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-home"></i></span>
                                <input type="text" id="direccion" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="fecha_contrat" class="form-label">F. Contratación:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" id="fecha_contrat" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="id_rol" class="form-label">Rol:</label>
                            <select id="id_rol" class="form-select" required>
                                <option value="" disabled selected>Seleccione rol</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="id_departamento" class="form-label">Departamento:</label>
                            <select id="id_departamento" class="form-select" required>
                                <option value="" disabled selected>Seleccione departamento</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEmpleado">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/empleados.js"></script>
</body>

</html>