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
    <title>Gestión de Clientes - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Gestión de Clientes</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" id="crear-tab" data-bs-toggle="tab" data-bs-target="#crear">
                        <i class="fas fa-plus me-2"></i>Nuevo Cliente
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen estadístico -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="totalClientes">0</div>
                            <div class="stat-label">Clientes Totales</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-user-plus fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="clientesNuevos">0</div>
                            <div class="stat-label">Clientes Nuevos</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-handshake fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="clientesInteresados">0</div>
                            <div class="stat-label">Clientes Interesados</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="clientesCerrados">0</div>
                            <div class="stat-label">Clientes Cerrados</div>
                        </div>
                    </div>
                </div>

                <!-- Pestañas de navegación -->
                <ul class="nav nav-tabs mb-4" id="clientesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="buscar-tab" data-bs-toggle="tab" data-bs-target="#buscar" type="button" role="tab">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado" type="button" role="tab">
                            <i class="fas fa-list me-2"></i>Listado
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="crear-tab-nav" data-bs-toggle="tab" data-bs-target="#crear" type="button" role="tab">
                            <i class="fas fa-plus me-2"></i>Crear
                        </button>
                    </li>
                </ul>

                <!-- Contenido de las pestañas -->
                <div class="tab-content" id="clientesTabsContent">
                    <!-- Tab Búsqueda -->
                    <div class="tab-pane fade show active" id="buscar" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-3">Buscar Clientes</h3>
                                <form id="formBuscarCliente" class="row g-3">
                                    <div class="col-md-6">
                                        <label for="searchCli" class="form-label">Buscar por Nombre/Apellidos/DNI:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" id="searchCli" class="form-control" placeholder="Ingrese término de búsqueda...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="filtroEstado" class="form-label">Estado Funnel:</label>
                                        <select id="filtroEstado" class="form-select">
                                            <option value="">Todos</option>
                                            <option value="Nuevo">Nuevo</option>
                                            <option value="Interesado">Interesado</option>
                                            <option value="En Negociacion">En Negociación</option>
                                            <option value="Cerrado">Cerrado</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Buscar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Listado -->
                    <div class="tab-pane fade" id="listado" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="card-title h5 mb-0">Listado de Clientes</h3>
                                    <div class="export-buttons">
                                        <button class="btn btn-outline-success" onclick="exportarClientes('excel')">
                                            <i class="fas fa-file-excel me-2"></i>Excel
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="exportarClientes('pdf')">
                                            <i class="fas fa-file-pdf me-2"></i>PDF
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover check-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Apellidos</th>
                                                <th>DNI</th>
                                                <th>Email</th>
                                                <th>Teléfono</th>
                                                <th>Estado Funnel</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla-clientes">
                                            <!-- Se llena con JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="paginacionClientes" class="d-flex justify-content-between align-items-center mt-3">
                                    <!-- Se llena con JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Crear -->
                    <div class="tab-pane fade" id="crear" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-3">Crear Nuevo Cliente</h3>
                                <form id="formCrearCliente" class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombreCli" class="form-label">Nombre:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" id="nombreCli" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellidosCli" class="form-label">Apellidos:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" id="apellidosCli" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dniCli" class="form-label">DNI:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            <input type="text" id="dniCli" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="emailCli" class="form-label">Email:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" id="emailCli" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telCli" class="form-label">Teléfono:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="text" id="telCli" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dirCli" class="form-label">Dirección:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-home"></i></span>
                                            <input type="text" id="dirCli" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="estado_funnel" class="form-label">Estado Funnel:</label>
                                        <select id="estado_funnel" class="form-select">
                                            <option value="">Selecciona un estado</option>
                                            <option value="Nuevo">Nuevo</option>
                                            <option value="Interesado">Interesado</option>
                                            <option value="En Negociacion">En Negociación</option>
                                            <option value="Cerrado">Cerrado</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Guardar Cliente
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
        </div>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCliente" class="row g-3">
                        <input type="hidden" id="editId">
                        <div class="col-md-6">
                            <label for="editNombre" class="form-label">Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="editNombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editApellidos" class="form-label">Apellidos:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="editApellidos" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editDni" class="form-label">DNI:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" id="editDni" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editEmail" class="form-label">Email:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="editEmail" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editTelefono" class="form-label">Teléfono:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" id="editTelefono" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editDireccion" class="form-label">Dirección:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-home"></i></span>
                                <input type="text" id="editDireccion" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editEstadoFunnel" class="form-label">Estado Funnel:</label>
                            <select id="editEstadoFunnel" class="form-select">
                                <option value="">Sin estado</option>
                                <option value="Nuevo">Nuevo</option>
                                <option value="Interesado">Interesado</option>
                                <option value="En Negociacion">En Negociación</option>
                                <option value="Cerrado">Cerrado</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarCliente()">
                        <i class="fas fa-save me-2"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/clientes.js"></script>
</body>

</html>