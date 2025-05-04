<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asistente Virtual del Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .asistente-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 24px;
            background: var(--bg-card);
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }

        .asistente-mensaje {
            margin-bottom: 16px;
        }

        .asistente-respuesta {
            margin-top: 16px;
            font-weight: 500;
            min-height: 100px;
            padding: 15px;
            border-radius: 8px;
            background: var(--bg-main);
            border: 1px solid var(--border-color);
        }

        .asistente-contacto {
            margin-top: 24px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-main);
        }

        button.asistente-btn {
            padding: 8px 16px;
            border: none;
            background: var(--primary-color);
            color: var(--text-light);
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button.asistente-btn:hover {
            background: var(--primary-hover);
        }

        .asistente-page {
            display: none;
            height: calc(100vh - 150px);
        }

        .asistente-page.active {
            display: block;
        }

        .historial-item {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .pregunta {
            font-weight: 500;
            color: var(--primary-color);
        }

        .respuesta {
            margin-top: 5px;
            padding-left: 10px;
            border-left: 3px solid var(--primary-color);
        }

        .faq-item {
            margin-bottom: 15px;
            padding: 10px;
            background: var(--bg-main);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .faq-question {
            font-weight: 500;
            color: var(--primary-color);
        }

        .faq-answer {
            display: none;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <div class="d-flex" style="margin-top:1rem;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content p-4 w-100">
            <h2 class="page-title mb-4">Asistente Virtual del Hotel</h2>

            <div id="asistente-pages">
                <div class="asistente-page active" data-page="1">
                    <!-- Formulario principal del asistente -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-4">¿En qué podemos ayudarte?</h3>
                            <form id="formAsistente" class="mb-4">
                                <div class="input-group mb-3">
                                    <input type="text" id="mensaje" name="mensaje" class="form-control" placeholder="Escribe tu pregunta aquí..." required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Preguntar
                                    </button>
                                </div>
                            </form>

                            <div id="respuesta" class="asistente-respuesta">
                                <em>Aquí aparecerá la respuesta a tu pregunta...</em>
                            </div>

                            <div id="contacto" class="asistente-contacto mt-4" style="display:none;">
                                <p class="mb-2">¿Prefieres hablar con un recepcionista?</p>
                                <button class="btn btn-secondary" onclick="window.location.href='tel:+34966123456'">
                                    <i class="fas fa-phone-alt me-2"></i>Llamar a recepción
                                </button>
                                <p class="mt-2">Teléfono: <strong>+34 966 123 456</strong> (24h)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="asistente-page" data-page="2">
                    <!-- Historial de consultas -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Historial de consultas</h3>

                            <div id="historial-container">
                                <!-- El historial se llena dinámicamente -->
                                <div class="text-center text-muted">
                                    <em>No hay consultas previas</em>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="asistente-page" data-page="3">
                    <!-- Preguntas frecuentes -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Preguntas frecuentes</h3>

                            <div id="faqs">
                                <div class="faq-item" onclick="toggleFaq(this)">
                                    <div class="faq-question">¿Cuál es la hora de check-in y check-out?</div>
                                    <div class="faq-answer">El check-in es a partir de las 14:00h y el check-out hasta las 12:00h. Consulta en recepción si necesitas modificar estos horarios.</div>
                                </div>

                                <div class="faq-item" onclick="toggleFaq(this)">
                                    <div class="faq-question">¿El hotel tiene wifi?</div>
                                    <div class="faq-answer">Sí, disponemos de wifi gratuito en todas las áreas del hotel. La contraseña se proporciona en recepción durante el check-in.</div>
                                </div>

                                <div class="faq-item" onclick="toggleFaq(this)">
                                    <div class="faq-question">¿Hay servicio de habitaciones?</div>
                                    <div class="faq-answer">Sí, el servicio de habitaciones está disponible de 7:00 a 23:00h. Puedes consultar el menú en la carpeta de información de tu habitación.</div>
                                </div>

                                <div class="faq-item" onclick="toggleFaq(this)">
                                    <div class="faq-question">¿Se permiten mascotas?</div>
                                    <div class="faq-answer">Aceptamos mascotas con un suplemento de 15€ por noche. Por favor, infórmanos al hacer la reserva.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles de navegación de páginas -->
                <div class="page-nav text-center mt-4">
                    <button id="prevAst" class="btn btn-secondary me-2">Anterior</button>
                    <span class="page-indicator">Página <span id="currentAstPage">1</span> de <span id="totalAstPages">3</span></span>
                    <button id="nextAst" class="btn btn-secondary">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Historial de consultas
        let historial = JSON.parse(localStorage.getItem('asistente_historial') || '[]');

        // Gestión del formulario
        const form = document.getElementById('formAsistente');
        const respuestaDiv = document.getElementById('respuesta');
        const contactoDiv = document.getElementById('contacto');
        const historialContainer = document.getElementById('historial-container');

        // Cargar historial al iniciar
        cargarHistorial();

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            respuestaDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Consultando...</span></div> Consultando...';
            contactoDiv.style.display = 'none';
            const mensaje = document.getElementById('mensaje').value;
            try {
                const res = await fetch('http://localhost:3000/analizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mensaje
                    })
                });
                if (!res.ok) {
                    respuestaDiv.textContent = `Error HTTP: ${res.status}`;
                    console.error('Respuesta HTTP no OK:', res);
                    return;
                }
                const data = await res.json();
                console.log('Respuesta recibida:', data);
                let mostrar = data.respuesta || data.sugerencia || 'No se recibió respuesta.';
                respuestaDiv.textContent = mostrar;

                // Guardar en historial
                guardarEnHistorial(mensaje, mostrar);

                if (
                    data.intencion_detectada === 'no_entendido' ||
                    (mostrar && mostrar.toLowerCase().includes('no entiendo'))
                ) {
                    contactoDiv.style.display = 'block';
                }
            } catch (err) {
                respuestaDiv.textContent = 'Error al conectar con el asistente.';
                console.error('Error en fetch:', err);
            }
        });

        function guardarEnHistorial(pregunta, respuesta) {
            const item = {
                fecha: new Date().toISOString(),
                pregunta,
                respuesta
            };
            historial.unshift(item); // Añadir al inicio
            if (historial.length > 10) historial.pop(); // Mantener un máximo de 10 elementos
            localStorage.setItem('asistente_historial', JSON.stringify(historial));
            cargarHistorial();
        }

        function cargarHistorial() {
            if (historial.length === 0) {
                historialContainer.innerHTML = '<div class="text-center text-muted"><em>No hay consultas previas</em></div>';
                return;
            }

            historialContainer.innerHTML = '';
            historial.forEach(item => {
                const fecha = new Date(item.fecha).toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const divItem = document.createElement('div');
                divItem.className = 'historial-item';
                divItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="pregunta">${item.pregunta}</div>
                        <small class="text-muted">${fecha}</small>
                    </div>
                    <div class="respuesta">${item.respuesta}</div>
                `;
                historialContainer.appendChild(divItem);
            });
        }

        function toggleFaq(element) {
            const answer = element.querySelector('.faq-answer');
            const isVisible = answer.style.display === 'block';

            // Ocultar todas las respuestas
            document.querySelectorAll('.faq-answer').forEach(a => {
                a.style.display = 'none';
            });

            // Mostrar u ocultar la respuesta actual
            if (!isVisible) {
                answer.style.display = 'block';
            }
        }

        // Función de paginación interna para el asistente
        function initializeAsistentePageNav() {
            const pages = document.querySelectorAll('#asistente-pages .asistente-page');
            const prevBtn = document.getElementById('prevAst');
            const nextBtn = document.getElementById('nextAst');
            const currentPageEl = document.getElementById('currentAstPage');
            const totalPagesEl = document.getElementById('totalAstPages');
            let current = 0;

            // Establecer el total de páginas
            if (totalPagesEl) totalPagesEl.textContent = pages.length;

            function updateButtons() {
                if (prevBtn) prevBtn.disabled = current === 0;
                if (nextBtn) nextBtn.disabled = current === pages.length - 1;
                if (currentPageEl) currentPageEl.textContent = current + 1;
            }

            function showPage(index) {
                pages[current].classList.remove('active');
                current = index;
                pages[current].classList.add('active');
                updateButtons();
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (current > 0) showPage(current - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (current < pages.length - 1) showPage(current + 1);
                });
            }

            updateButtons();
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            initializeAsistentePageNav();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>