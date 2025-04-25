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
                    <h2 class="page-title">Gestión de Facturas</h2>
                </div>
            </div>

            <!-- Resumen de Facturas -->
            <div class="row g-4 mb-4">
                <!-- Total Facturas -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice stat-icon text-primary"></i>
                            <div id="totalFacturas" class="stat-value">0</div>
                            <div class="stat-label">Total Facturas</div>
                        </div>
                    </div>
                </div>

                <!-- Total Importe -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-euro-sign stat-icon text-success"></i>
                            <div id="totalImporte" class="stat-value">0€</div>
                            <div class="stat-label">Total Facturado</div>
                        </div>
                    </div>
                </div>

                <!-- Promedio por Factura -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-calculator stat-icon text-info"></i>
                            <div id="promedioFactura" class="stat-value">0€</div>
                            <div class="stat-label">Promedio por Factura</div>
                        </div>
                    </div>
                </div>

                <!-- Facturas Pendientes -->
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="fas fa-clock stat-icon text-warning"></i>
                            <div id="facturasPendientes" class="stat-value">0</div>
                            <div class="stat-label">Pendientes de Cobro</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros de búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">Buscar Facturas</h3>
                    <form onsubmit="event.preventDefault(); listarFacturasPaginado(1);" class="row g-3">
                        <div class="col-md-3">
                            <label for="reservaF" class="form-label">Reserva:</label>
                            <select id="reservaF" class="form-select">
                                <option value="">Todas las reservas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fechaInicio" class="form-label">Fecha Inicio:</label>
                            <input type="date" id="fechaInicio" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="fechaFin" class="form-label">Fecha Fin:</label>
                            <input type="date" id="fechaFin" class="form-control">
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
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="card-title mb-0">Listado de Facturas</h3>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaFactura">
                            <i class="fas fa-plus me-2"></i>Nueva Factura
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Reserva</th>
                                    <th>Fecha Emisión</th>
                                    <th class="text-end">Total</th>
                                    <th>Método Pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-fact">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    <div id="paginacionFact" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Factura -->
    <div class="modal fade" id="modalNuevaFactura" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearFactura" onsubmit="crearFactura(event)">
                        <div class="mb-3">
                            <label for="idResF" class="form-label">Reserva:</label>
                            <select id="idResF" class="form-select" required>
                                <option value="">Seleccione una reserva</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fechaF" class="form-label">Fecha Emisión:</label>
                            <input type="date" id="fechaF" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="totalF" class="form-label">Total:</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" step="0.01" id="totalF" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="metodoF" class="form-label">Método Pago:</label>
                            <select id="metodoF" class="form-select" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formCrearFactura" class="btn btn-primary">Crear Factura</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Factura -->
    <div class="modal fade" id="modalDetalleFactura" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-4">Factura #</dt>
                        <dd class="col-sm-8" id="detalleFacturaId"></dd>

                        <dt class="col-sm-4">Reserva #</dt>
                        <dd class="col-sm-8" id="detalleReservaId"></dd>

                        <dt class="col-sm-4">Fecha Emisión</dt>
                        <dd class="col-sm-8" id="detalleFechaEmision"></dd>

                        <dt class="col-sm-4">Total</dt>
                        <dd class="col-sm-8" id="detalleTotal"></dd>

                        <dt class="col-sm-4">Método Pago</dt>
                        <dd class="col-sm-8" id="detalleMetodoPago"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirFactura(document.getElementById('detalleFacturaId').textContent)">
                        <i class="fas fa-print me-2"></i>Imprimir
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