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
    <title>Enviar Emails de Marketing - PMS Daniya Denia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div style="display:flex; margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="main-content">
            <h2 class="page-title">Enviar Emails de Marketing</h2>

            <!-- Formulario para seleccionar el tipo de email y enviar -->
            <div class="card">
                <h3>Seleccionar Email de Marketing</h3>
                <form id="emailForm" onsubmit="event.preventDefault(); sendMarketingEmail();">
                    <label for="emailType">Tipo de Email:</label>
                    <select id="emailType" class="form-select" required>
                        <option value="">Selecciona un tipo de email</option>
                        <option value="promocion_verano">Promoción de Verano</option>
                        <!-- Añade más opciones según los tipos de emails que tengas -->
                    </select>

                    <label for="clientSelection">Seleccionar Clientes:</label>
                    <select id="clientSelection" class="form-select" required>
                        <option value="all">Todos los clientes</option>
                        <option value="selected">Seleccionar clientes específicos</option>
                    </select>

                    <div id="clientList" style="display:none;">
                        <label for="selectedClients">Clientes Específicos:</label>
                        <select id="selectedClients" class="form-select" multiple>
                            <!-- Opciones de clientes se cargarán dinámicamente -->
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2">Enviar Email</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Spinner de Carga -->
    <div id="loadingOverlay" style="display: none;">
        <div class="spinner"></div>
        <p>Enviando emails... Por favor, espera.</p>
    </div>

    <!-- Estilos para el Spinner -->
    <style>
        /* Fondo oscurecido */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            font-size: 1.2rem;
            z-index: 9999;
        }

        /* Spinner animado */
        .spinner {
            border: 6px solid #f3f3f3;
            /* Borde ligero */
            border-top: 6px solid #3498db;
            /* Borde azul */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        /* Animación del spinner */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        document.getElementById('clientSelection').addEventListener('change', function() {
            const clientList = document.getElementById('clientList');
            if (this.value === 'selected') {
                clientList.style.display = 'block';
                loadClients();
            } else {
                clientList.style.display = 'none';
            }
        });

        function loadClients() {
            fetch('../api/clientes.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('selectedClients');
                    select.innerHTML = ''; // Limpiar opciones previas

                    // Verificar si la respuesta tiene una propiedad "data"
                    const clientes = data.data || data;

                    // Verificar que sea un array antes de iterar
                    if (!Array.isArray(clientes)) {
                        console.error('La respuesta del servidor no es un array:', data);
                        alert('Error al cargar clientes. Respuesta inesperada del servidor.');
                        return;
                    }

                    // Iterar sobre los clientes y agregar opciones al select
                    clientes.forEach(cliente => {
                        const option = document.createElement('option');
                        option.value = cliente.id_cliente;
                        option.textContent = `${cliente.nombre} ${cliente.apellidos}`;
                        select.appendChild(option);
                    });
                })
                .catch(err => {
                    console.error('Error al cargar clientes:', err);
                    alert('Error al cargar clientes. Por favor, verifica tu conexión.');
                });
        }

        function sendMarketingEmail() {
            // Mostrar el spinner
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';

            // Obtener datos del formulario
            const emailType = document.getElementById('emailType').value;
            const clientSelection = document.getElementById('clientSelection').value;
            let clientIds = [];

            if (clientSelection === 'selected') {
                const selectedClients = document.getElementById('selectedClients');
                for (let option of selectedClients.options) {
                    if (option.selected) {
                        clientIds.push(option.value);
                    }
                }
            }

            const formData = new FormData();
            formData.append('emailType', emailType);
            formData.append('clientSelection', clientSelection);
            if (clientIds.length > 0) {
                formData.append('clientIds', JSON.stringify(clientIds));
            }

            // Enviar la solicitud al backend
            fetch('../api/send_marketing_email.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Ocultar el spinner
                    loadingOverlay.style.display = 'none';

                    // Mostrar el resultado
                    if (data.success) {
                        let message = `Proceso completado:\n`;
                        message += `- Total de clientes: ${data.details.totalClients}\n`;
                        message += `- Emails enviados correctamente: ${data.details.successfulSends}\n`;
                        if (data.details.failedSends.length > 0) {
                            message += `- Emails fallidos: ${data.details.failedSends.join(', ')}\n`;
                        }
                        alert(message);
                    } else {
                        alert('Error al enviar emails: ' + data.error);
                    }
                })
                .catch(err => {
                    // Ocultar el spinner en caso de error
                    loadingOverlay.style.display = 'none';
                    console.error('Error al enviar emails:', err);
                    alert('Error al enviar emails. Por favor, intenta nuevamente.');
                });
        }
    </script>
</body>

</html>