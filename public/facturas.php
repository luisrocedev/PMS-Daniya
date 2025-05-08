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
    <title>Gestión de Facturas - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header de la página -->
            <div class="page-header">
                <h2 class="page-title">Gestión de Facturas</h2>
                <div class="page-actions">
                    <a href="generar_factura.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nueva Factura
                    </a>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen estadístico -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-file-invoice-dollar fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="total-facturas">0</div>
                            <div class="stat-label">Facturas Emitidas</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-money-bill-wave fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="total-ingresos">0€</div>
                            <div class="stat-label">Ingresos Totales</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="facturas-pendientes">0</div>
                            <div class="stat-label">Pendientes de Pago</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="promedio-factura">0€</div>
                            <div class="stat-label">Valor Promedio</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda -->
                <div class="card mb-4 mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Filtrar Facturas</h3>
                        <form onsubmit="event.preventDefault(); filtrarFacturas(1);" class="row g-3">
                            <div class="col-md-3">
                                <label for="busqueda" class="form-label">Buscar:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="busqueda" class="form-control" placeholder="Cliente, número...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado:</label>
                                <select id="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Pagada">Pagada</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha-desde" class="form-label">Desde:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" id="fecha-desde" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de facturas -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Listado de Facturas</h3>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover check-table">
                                <thead>
                                    <tr>
                                        <th>Nº Factura</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-facturas">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        <div id="paginacion-facturas" class="mt-3 text-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Factura -->
    <div class="modal fade" id="modalVerFactura" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleFactura">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="btnImprimir" class="btn btn-primary">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/facturas.js"></script>
</body>

</html>