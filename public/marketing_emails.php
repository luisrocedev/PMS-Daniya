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
    <title>Email Marketing - PMS Daniya Denia</title>
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
                <h2 class="page-title">Email Marketing</h2>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="crearNuevaCampana()">
                        <i class="fas fa-plus me-2"></i>Nueva Campaña
                    </button>
                </div>
            </div>

            <!-- Contenido principal con scroll -->
            <div class="content-wrapper">
                <!-- Resumen de emails -->
                <div class="grid-container">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-paper-plane fa-2x text-primary mb-3"></i>
                            <div class="stat-value" id="emailsEnviados">0</div>
                            <div class="stat-label">Emails Enviados</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-envelope-open fa-2x text-success mb-3"></i>
                            <div class="stat-value" id="tasaApertura">0%</div>
                            <div class="stat-label">Tasa de Apertura</div>
                        </div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-info mb-3"></i>
                            <div class="stat-value" id="conversionRate">0%</div>
                            <div class="stat-label">Tasa de Conversión</div>
                        </div>
                    </div>
                </div>

                <!-- Formulario y Vista Previa -->
                <div class="row mt-4">
                    <!-- Formulario de Email -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-4">Configuración de Campaña</h3>
                                <form id="emailForm" onsubmit="event.preventDefault(); sendMarketingEmail();">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Campaña</label>
                                        <select id="emailType" class="form-select" required>
                                            <option value="">Selecciona el tipo de campaña</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Destinatarios</label>
                                        <select id="clientSelection" class="form-select" required onchange="toggleClientList()">
                                            <option value="all">Todos los clientes</option>
                                            <option value="selected">Clientes específicos</option>
                                            <option value="segment">Por segmento</option>
                                        </select>
                                    </div>

                                    <div id="clientList" class="mb-3" style="display:none;">
                                        <label class="form-label">Seleccionar Clientes</label>
                                        <select id="selectedClients" class="form-select" multiple size="6">
                                        </select>
                                        <div class="form-text">Mantén presionado Ctrl para seleccionar múltiples clientes</div>
                                    </div>

                                    <div id="segmentOptions" class="mb-3" style="display:none;">
                                        <label class="form-label">Segmento</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="vip" id="segmentVIP">
                                            <label class="form-check-label" for="segmentVIP">Clientes VIP</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="frequent" id="segmentFrequent">
                                            <label class="form-check-label" for="segmentFrequent">Clientes Frecuentes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="inactive" id="segmentInactive">
                                            <label class="form-check-label" for="segmentInactive">Clientes Inactivos</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Programar Envío</label>
                                        <select id="scheduleType" class="form-select" onchange="toggleSchedule()">
                                            <option value="now">Enviar ahora</option>
                                            <option value="scheduled">Programar envío</option>
                                        </select>
                                    </div>

                                    <div id="scheduleDateTime" class="mb-3" style="display:none;">
                                        <label class="form-label">Fecha y Hora de Envío</label>
                                        <input type="datetime-local" id="scheduledDateTime" class="form-control">
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Enviar Campaña
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Vista Previa -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title h5 mb-4">Vista Previa</h3>
                                <div class="email-preview border rounded p-3" style="height: 400px; overflow-y: auto;">
                                    <div id="emailPreview">
                                        Selecciona un tipo de campaña para ver la vista previa
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Campañas -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-4">Historial de Campañas</h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Campaña</th>
                                        <th>Destinatarios</th>
                                        <th>Enviados</th>
                                        <th>Aperturas</th>
                                        <th>Clics</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="campaignHistory">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100" style="display:none; background: rgba(0,0,0,0.7); z-index: 9999;">
        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-white">
            <div class="spinner-border text-light mb-3" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="h5" id="loadingMessage">Enviando campaña...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- CAMPANAS MARKETING DINÁMICAS ---
        const API_CAMPANAS = '../api/campanas_marketing.php';
        // Cargar campañas al iniciar
        function cargarCampanas() {
            fetch(API_CAMPANAS)
                .then(r => r.json())
                .then(campanas => {
                    const select = document.getElementById('emailType');
                    select.innerHTML = '<option value="">Selecciona campaña</option>';
                    campanas.forEach(c => {
                        const option = document.createElement('option');
                        option.value = c.id_campana;
                        option.textContent = c.nombre;
                        select.appendChild(option);
                    });
                });
        }
        // Mostrar vista previa dinámica
        document.addEventListener('DOMContentLoaded', () => {
            cargarCampanas();
            cargarClientes();
            cargarEstadisticas();
            cargarHistorial();
        });
        document.getElementById('emailType').addEventListener('change', function() {
            const id = this.value;
            if (!id) {
                document.getElementById('emailPreview').innerHTML = 'Selecciona una campaña para ver la vista previa';
                return;
            }
            fetch(`${API_CAMPANAS}?id=${id}`)
                .then(r => r.json())
                .then(campana => {
                    document.getElementById('emailPreview').innerHTML = campana.contenido_html;
                });
        });
        // Crear nueva campaña (prompt simple, puedes mejorar con modal)
        function crearNuevaCampana() {
            const nombre = prompt('Nombre de la campaña:');
            if (!nombre) return;
            const asunto = prompt('Asunto del email:');
            if (!asunto) return;
            const contenido_html = prompt('Contenido HTML del email:');
            if (!contenido_html) return;
            fetch(API_CAMPANAS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre, asunto, contenido_html, estado: 'borrador' })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert('Campaña creada');
                    cargarCampanas();
                } else {
                    alert('Error al crear campaña');
                }
            });
        }
        // --- RESTO DE FUNCIONES (clientes, envío, etc) ---
        // Variables globales
        const API_URL = '../api/send_marketing_email.php';
        let emailTemplates = {};

        // Inicialización
        document.addEventListener('DOMContentLoaded', () => {
            cargarClientes();
            cargarEstadisticas();
            cargarHistorial();
        });

        // Cargar clientes
        function cargarClientes() {
            fetch('../api/clientes.php')
                .then(r => r.json())
                .then(data => {
                    const select = document.getElementById('selectedClients');
                    select.innerHTML = '';

                    const clientes = Array.isArray(data) ? data : (data.data || []);
                    clientes.forEach(cliente => {
                        const option = document.createElement('option');
                        option.value = cliente.id_cliente;
                        option.textContent = `${cliente.nombre} ${cliente.apellidos}`;
                        select.appendChild(option);
                    });
                })
                .catch(err => mostrarAlerta('Error al cargar clientes', 'danger'));
        }

        // Toggle lista de clientes
        function toggleClientList() {
            const selection = document.getElementById('clientSelection').value;
            document.getElementById('clientList').style.display =
                selection === 'selected' ? 'block' : 'none';
            document.getElementById('segmentOptions').style.display =
                selection === 'segment' ? 'block' : 'none';
        }

        // Toggle programación
        function toggleSchedule() {
            const scheduleType = document.getElementById('scheduleType').value;
            document.getElementById('scheduleDateTime').style.display =
                scheduleType === 'scheduled' ? 'block' : 'none';
        }

        // Enviar campaña
        function sendMarketingEmail() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';

            const formData = new FormData();
            formData.append('emailType', document.getElementById('emailType').value);
            formData.append('clientSelection', document.getElementById('clientSelection').value);

            const selectedClients = document.getElementById('selectedClients');
            if (selectedClients.style.display !== 'none') {
                const clientIds = Array.from(selectedClients.selectedOptions).map(opt => opt.value);
                formData.append('clientIds', JSON.stringify(clientIds));
            }

            fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    loadingOverlay.style.display = 'none';
                    if (data.success) {
                        mostrarAlerta(`Campaña enviada exitosamente a ${data.details.successfulSends} destinatarios`, 'success');
                        cargarEstadisticas();
                        cargarHistorial();
                    } else {
                        mostrarAlerta(data.error || 'Error al enviar la campaña', 'danger');
                    }
                })
                .catch(err => {
                    loadingOverlay.style.display = 'none';
                    mostrarAlerta('Error de conexión', 'danger');
                });
        }

        // Cargar estadísticas
        function cargarEstadisticas() {
            // Simulación de estadísticas - Conectar con API real
            document.getElementById('emailsEnviados').textContent = '1,234';
            document.getElementById('tasaApertura').textContent = '45%';
            document.getElementById('conversionRate').textContent = '12%';
        }

        // Cargar historial
        function cargarHistorial() {
            // Simulación de historial - Conectar con API real
            const tbody = document.getElementById('campaignHistory');
            tbody.innerHTML = `
                <tr>
                    <td>2025-05-08</td>
                    <td>Promoción de Verano</td>
                    <td>234</td>
                    <td>230</td>
                    <td>115</td>
                    <td>45</td>
                    <td><span class="badge bg-success">Completada</span></td>
                </tr>
                <tr>
                    <td>2025-05-01</td>
                    <td>Descuento en Spa</td>
                    <td>500</td>
                    <td>495</td>
                    <td>200</td>
                    <td>80</td>
                    <td><span class="badge bg-success">Completada</span></td>
                </tr>
            `;
        }

        // Mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>

</html>