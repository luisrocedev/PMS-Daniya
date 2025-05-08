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
    <title>RRHH - PMS Daniya Denia</title>
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
                <h2 class="page-title">Recursos Humanos</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="location.href='empleados.php'">
                        <i class="fas fa-users me-2"></i>Gestionar Empleados
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen estadístico -->
                <div class="grid-container animate-fadeInUp">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-user-clock fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="empleadosActivos">0</div>
                            <div class="stat-label">Empleados Activos</div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i> 5% vs mes anterior
                            </div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-clipboard-check fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="asistenciaHoy">0%</div>
                            <div class="stat-label">Asistencia Hoy</div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i> 98% cumplimiento
                            </div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-calendar-alt fa-2x text-warning mb-3"></i>
                            <div class="stat-value" id="turnosActivos">0</div>
                            <div class="stat-label">Turnos Activos</div>
                            <div class="stat-trend neutral">
                                <i class="fas fa-equals"></i> Sin cambios
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulos de RRHH -->
                <div class="grid-container mt-4">
                    <!-- Módulo de Turnos -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-clock fa-2x text-primary me-3"></i>
                                <div>
                                    <h3 class="card-title mb-1">Gestión de Turnos</h3>
                                    <p class="text-muted mb-0">Configuración de turnos estándar</p>
                                </div>
                            </div>
                            <p class="card-text">Define y gestiona los diferentes turnos de trabajo: mañana, tarde, noche y especiales.</p>
                            <div class="d-flex justify-content-end">
                                <a href="turnos.php" class="btn btn-outline-primary">
                                    <i class="fas fa-cog me-2"></i>Configurar Turnos
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Módulo de Horarios -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-calendar-week fa-2x text-success me-3"></i>
                                <div>
                                    <h3 class="card-title mb-1">Planificación de Horarios</h3>
                                    <p class="text-muted mb-0">Asignación de turnos por empleado</p>
                                </div>
                            </div>
                            <p class="card-text">Planifica los horarios semanales y mensuales del personal. Vista calendario y exportación.</p>
                            <div class="d-flex justify-content-end">
                                <a href="horarios.php" class="btn btn-outline-success">
                                    <i class="fas fa-calendar-plus me-2"></i>Gestionar Horarios
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Módulo de Asistencia -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-fingerprint fa-2x text-info me-3"></i>
                                <div>
                                    <h3 class="card-title mb-1">Control de Asistencia</h3>
                                    <p class="text-muted mb-0">Registro y monitoreo de fichajes</p>
                                </div>
                            </div>
                            <p class="card-text">Seguimiento de entradas, salidas, ausencias y generación de informes de asistencia.</p>
                            <div class="d-flex justify-content-end">
                                <a href="asistencia.php" class="btn btn-outline-info">
                                    <i class="fas fa-user-clock me-2"></i>Ver Asistencia
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Actividad Reciente -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-history me-2"></i>Actividad Reciente
                        </h3>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="time">Hoy 9:00</div>
                                <div class="content">
                                    <div class="guest">María López</div>
                                    <div class="room">Entrada registrada - Turno mañana</div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">Ayer 18:30</div>
                                <div class="content">
                                    <div class="guest">Juan Pérez</div>
                                    <div class="room">Cambio de turno solicitado</div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="time">Ayer 15:00</div>
                                <div class="content">
                                    <div class="guest">Ana García</div>
                                    <div class="room">Ausencia justificada registrada</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para actualizar las estadísticas
        function actualizarEstadisticas() {
            // Aquí se implementaría la lógica para obtener datos reales
            document.getElementById('empleadosActivos').textContent = '24';
            document.getElementById('asistenciaHoy').textContent = '95%';
            document.getElementById('turnosActivos').textContent = '3';
        }

        // Actualizar estadísticas al cargar la página
        document.addEventListener('DOMContentLoaded', actualizarEstadisticas);
    </script>
</body>

</html>