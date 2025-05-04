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
    <title>Gestión de Facturas - PMS Daniya Denia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
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

        /* Ajustes específicos para la tabla */
        .table-container {
            max-height: calc(100% - 20px);
            overflow-y: auto;
            margin-bottom: 0;
        }

        .card-body {
            padding: 1rem;
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
                    <h2 class="page-title mb-0">Gestión de Facturas</h2>
                    <div>
                        <a href="generar_factura.php" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Nueva Factura
                        </a>
                    </div>
                </div>

                <!-- Resumen financiero - Fixed Height -->
                <div class="row g-3 cards-row">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-file-invoice-dollar stat-icon text-primary"></i>
                                <div id="total-facturas" class="stat-value">0</div>
                                <div class="stat-label">Facturas emitidas</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-money-bill-wave stat-icon text-success"></i>
                                <div id="total-ingresos" class="stat-value">0€</div>
                                <div class="stat-label">Ingresos totales</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-exclamation-triangle stat-icon text-warning"></i>
                                <div id="facturas-pendientes" class="stat-value">0</div>
                                <div class="stat-label">Pendientes de pago</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-chart-line stat-icon text-info"></i>
                                <div id="promedio-factura" class="stat-value">0€</div>
                                <div class="stat-label">Valor promedio</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros de búsqueda - Fixed Height -->
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <h5 class="card-title mb-3">Filtrar Facturas</h5>
                        <form onsubmit="event.preventDefault(); filtrarFacturas(1);" class="row g-2">
                            <div class="col-md-3">
                                <label for="busqueda" class="form-label small mb-1">Buscar:</label>
                                <input type="text" id="busqueda" class="form-control form-control-sm" placeholder="Cliente, número...">
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label small mb-1">Estado:</label>
                                <select id="estado" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="Pagada">Pagada</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha-desde" class="form-label small mb-1">Desde:</label>
                                <input type="date" id="fecha-desde" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-search me-1"></i>Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de facturas - Scrollable -->
                <div class="card mb-0 flex-grow-1">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0">Listado de Facturas</h5>
                    </div>
                    <div class="card-body p-0 d-flex flex-column h-100">
                        <div class="table-container">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light sticky-top">
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
                        <div id="paginacion-facturas" class="mt-2 p-2 border-top"></div>
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