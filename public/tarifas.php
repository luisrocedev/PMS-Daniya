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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tarifas - PMS Daniya Denia</title>
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

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Gestión de Tarifas</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="prepararNuevaTarifa()" data-bs-toggle="modal" data-bs-target="#modalTarifa">
                        <i class="fas fa-plus me-2"></i>Nueva Tarifa
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen de Tarifas -->
                <div class="grid-container">
                    <!-- Total Tarifas -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-tags fa-2x text-primary mb-3"></i>
                            <div id="totalTarifas" class="stat-value">0</div>
                            <div class="stat-label">Total Tarifas</div>
                        </div>
                    </div>

                    <!-- Tarifa Media -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-euro-sign fa-2x text-success mb-3"></i>
                            <div id="tarifaPromedio" class="stat-value">0€</div>
                            <div class="stat-label">Tarifa Media</div>
                        </div>
                    </div>

                    <!-- Tarifas Activas -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-info mb-3"></i>
                            <div id="tarifasActivas" class="stat-value">0</div>
                            <div class="stat-label">Tarifas Activas</div>
                        </div>
                    </div>

                    <!-- Próximas a Vencer -->
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                            <div id="tarifasVencer" class="stat-value">0</div>
                            <div class="stat-label">Próximas a Vencer</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda -->
                <div class="card mb-4 mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Buscar Tarifas</h3>
                        <form onsubmit="event.preventDefault(); listarTarifasPaginado(1);" class="row g-3">
                            <div class="col-md-3">
                                <label for="filtroTipoHab" class="form-label">Tipo Habitación:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-bed"></i></span>
                                    <select id="filtroTipoHab" class="form-select">
                                        <option value="">Todos los tipos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroTemporada" class="form-label">Temporada:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <select id="filtroTemporada" class="form-select">
                                        <option value="">Todas las temporadas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroFechaInicio" class="form-label">Fecha Inicio:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                    <input type="date" id="filtroFechaInicio" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroFechaFin" class="form-label">Fecha Fin:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-minus"></i></span>
                                    <input type="date" id="filtroFechaFin" class="form-control">
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo me-2"></i>Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Tarifas -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Listado de Tarifas</h3>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover check-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Tipo Hab.</th>
                                        <th class="text-end">Precio</th>
                                        <th>Temporada</th>
                                        <th>F. Inicio</th>
                                        <th>F. Fin</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-tarifas">
                                    <!-- Se llena con JS -->
                                </tbody>
                            </table>
                        </div>
                        <div id="paginacionTarifas" class="mt-3 text-center">
                            <!-- Se llena con JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva/Editar Tarifa -->
    <div class="modal fade" id="modalTarifa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTarifaLabel">Nueva Tarifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTarifa" onsubmit="guardarTarifa(event)">
                        <div class="mb-3">
                            <label for="nombreTarifa" class="form-label">Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="nombreTarifa" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tipoHabitacion" class="form-label">Tipo Habitación:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-bed"></i></span>
                                <select class="form-select" id="tipoHabitacion" required>
                                    <option value="">Seleccione tipo</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-euro-sign"></i></span>
                                <input type="number" step="0.01" class="form-control" id="precio" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="temporada" class="form-label">Temporada:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <select class="form-select" id="temporada" required>
                                    <option value="">Seleccione temporada</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                <input type="date" class="form-control" id="fecha_inicio" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-minus"></i></span>
                                <input type="date" class="form-control" id="fecha_fin" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formTarifa" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Tarifa -->
    <div class="modal fade" id="modalDetalleTarifa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Tarifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID Tarifa:</dt>
                        <dd class="col-sm-8" id="detalleTarifaId"></dd>

                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8" id="detalleTarifaNombre"></dd>

                        <dt class="col-sm-4">Tipo Hab.:</dt>
                        <dd class="col-sm-8" id="detalleTarifaTipo"></dd>

                        <dt class="col-sm-4">Precio:</dt>
                        <dd class="col-sm-8" id="detalleTarifaPrecio"></dd>

                        <dt class="col-sm-4">Temporada:</dt>
                        <dd class="col-sm-8" id="detalleTarifaTemporada"></dd>

                        <dt class="col-sm-4">Fecha Inicio:</dt>
                        <dd class="col-sm-8" id="detalleTarifaInicio"></dd>

                        <dt class="col-sm-4">Fecha Fin:</dt>
                        <dd class="col-sm-8" id="detalleTarifaFin"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/tarifas.js"></script>
</body>

</html>