// public/js/checkin_checkout.js

// Variables globales
let refreshInterval;
const REFRESH_INTERVAL = 30000; // 30 segundos

// Inicialización cuando el documento está listo
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

// Inicializar la aplicación
function initializeApp() {
    cargarCheckInOut();
    setupAutoRefresh();
    initializeEventListeners();
}

// Configurar auto-refresh
function setupAutoRefresh() {
    const autoRefreshToggle = document.getElementById('auto-refresh');
    if (autoRefreshToggle) {
        autoRefreshToggle.addEventListener('change', () => {
            if (autoRefreshToggle.checked) {
                refreshInterval = setInterval(cargarCheckInOut, REFRESH_INTERVAL);
                mostrarMensaje('Auto-actualización activada', 'info');
            } else {
                clearInterval(refreshInterval);
                mostrarMensaje('Auto-actualización desactivada', 'info');
            }
        });
    }
}

// Inicializar event listeners
function initializeEventListeners() {
    const formCheckinModal = document.getElementById('formCheckinModal');
    if (formCheckinModal) {
        formCheckinModal.addEventListener('submit', handleCheckinSubmit);
    }

    const btnAddCargo = document.getElementById('btn-add-cargo');
    if (btnAddCargo) {
        btnAddCargo.addEventListener('click', handleNewCargo);
    }
}

// Modificación para permitir filtro dinámico "HOY" sin recargar la página
function filtrarPorFechaHoy() {
    if (typeof cargarCheckInOut === 'function') {
        const hoy = new Date().toISOString().split('T')[0];
        cargarCheckInOut(hoy);
    }
}

// Cargar datos de check-in/check-out
async function cargarCheckInOut(fecha = null) {
    try {
        let url = '../api/checkinout.php';
        if (fecha) {
            url += '?fecha=' + encodeURIComponent(fecha);
        }
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            updateStats(data.stats);
            renderCheckInTable(Array.isArray(data.pendientesCheckIn) ? data.pendientesCheckIn : []);
            renderCheckOutTable(Array.isArray(data.pendientesCheckOut) ? data.pendientesCheckOut : []);
            renderUpcomingArrivals(data.proximasLlegadas ? data.proximasLlegadas : []);
            updateCounters(
                Array.isArray(data.pendientesCheckIn) ? data.pendientesCheckIn.length : 0,
                Array.isArray(data.pendientesCheckOut) ? data.pendientesCheckOut.length : 0
            );
            animateValues();
        } else {
            throw new Error(data.error || 'Error al cargar datos');
        }
    } catch (error) {
        console.error('Error al cargar datos:', error);
        mostrarError('Error al cargar los datos: ' + error.message);
    }
}

// Actualizar estadísticas
function updateStats(stats) {
    document.getElementById('pending-checkins').textContent = stats.pendingCheckins;
    document.getElementById('pending-checkouts').textContent = stats.pendingCheckouts;
    document.getElementById('completed-today').textContent = stats.completedToday;
    document.getElementById('occupancy-rate').textContent = stats.occupancyRate + '%';
}

// Actualizar contadores en badges
function updateCounters(checkinCount, checkoutCount) {
    document.getElementById('checkin-count').textContent = checkinCount;
    document.getElementById('checkout-count').textContent = checkoutCount;
}

// Renderizar tabla de check-ins
function renderCheckInTable(checkins) {
    const tbody = document.getElementById('tabla-checkin');
    if (!tbody) return;

    tbody.innerHTML = checkins.length ? checkins.map(r => `
        <tr class="animate-fade-in">
            <td>${r.id_reserva}</td>
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <div class="fw-bold">${r.nombre_cliente} ${r.apellidos_cliente}</div>
                        <small class="text-muted">${r.email || ''}</small>
                    </div>
                </div>
            </td>
            <td>${r.id_habitacion}</td>
            <td>${formatTime(r.hora_estimada)}</td>
            <td>
                <span class="badge ${getStatusClass(r.estado)}">${r.estado}</span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="abrirModalCheckin(${r.id_reserva})">
                    <i class="fas fa-sign-in-alt me-1"></i>Check-in
                </button>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="6" class="text-center">No hay check-ins pendientes</td></tr>';
}

// Renderizar tabla de check-outs
function renderCheckOutTable(checkouts) {
    const tbody = document.getElementById('tabla-checkout');
    if (!tbody) return;

    tbody.innerHTML = checkouts.length ? checkouts.map(r => `
        <tr class="animate-fade-in">
            <td>${r.id_reserva}</td>
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <div class="fw-bold">${r.nombre_cliente} ${r.apellidos_cliente}</div>
                        <small class="text-muted">${r.email || ''}</small>
                    </div>
                </div>
            </td>
            <td>${r.id_habitacion}</td>
            <td>${formatTime(r.hora_limite)}</td>
            <td>
                <span class="badge ${getStatusClass(r.estado)}">${r.estado}</span>
            </td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="hacerCheckOut(${r.id_reserva})">
                    <i class="fas fa-sign-out-alt me-1"></i>Check-out
                </button>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="6" class="text-center">No hay check-outs pendientes</td></tr>';
}

// Renderizar próximas llegadas
function renderUpcomingArrivals(arrivals) {
    const container = document.getElementById('upcoming-arrivals');
    if (!container) return;

    container.innerHTML = arrivals.length ? arrivals.map(a => `
        <div class="timeline-item animate-fade-in">
            <div class="timeline-point"></div>
            <div class="timeline-content">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-1">${a.nombre_cliente} ${a.apellidos_cliente}</h4>
                    <span class="badge bg-info">${formatTime(a.hora_estimada)}</span>
                </div>
                <p class="mb-0">Habitación ${a.id_habitacion}</p>
            </div>
        </div>
    `).join('') : '<div class="text-center text-muted">No hay llegadas programadas</div>';
}

// Funciones de utilidad
function formatTime(time) {
    return new Date(time).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusClass(status) {
    return {
        'Pendiente': 'bg-warning',
        'Confirmado': 'bg-primary',
        'Completado': 'bg-success',
        'Retrasado': 'bg-danger'
    }[status] || 'bg-secondary';
}

// Modal de Check-in
async function abrirModalCheckin(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalCheckin'));
    document.getElementById('id_reserva_modal').value = id;
    document.getElementById('dni_modal').value = '';
    document.getElementById('firma_modal').value = '';
    document.getElementById('terminos').checked = false;
    modal.show();
}

// Manejar envío del formulario de check-in
async function handleCheckinSubmit(e) {
    e.preventDefault();
    const form = new FormData(e.target);

    try {
        // Subir documentos
        const uploadResponse = await fetch('../api/checkin_uploads.php', {
            method: 'POST',
            body: form
        });
        
        const uploadData = await uploadResponse.json();
        
        if (uploadData.success) {
            // Realizar check-in
            await hacerCheckIn(form.get('id_reserva'));
            bootstrap.Modal.getInstance(document.getElementById('modalCheckin')).hide();
        } else {
            throw new Error(uploadData.error || 'Error al procesar los documentos');
        }
    } catch (error) {
        console.error('Error en el proceso de check-in:', error);
        mostrarError(error.message);
    }
}

// Realizar check-in
async function hacerCheckIn(id) {
    try {
        const response = await fetch('../api/checkinout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=checkin&id_reserva=${id}`
        });
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Check-in realizado correctamente', 'success');
            cargarCheckInOut();
            setTimeout(() => {
                window.location.href = `cliente_detalle.php?id_reserva=${id}`;
            }, 1500);
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error al realizar check-in:', error);
        mostrarError(error.message);
    }
}

// Realizar check-out
async function hacerCheckOut(id) {
    if (!confirm('¿Está seguro de realizar el check-out? Se generará la factura final.')) return;

    try {
        const response = await fetch('../api/checkinout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=checkout&id_reserva=${id}`
        });
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Check-out realizado correctamente', 'success');
            cargarCheckInOut();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error al realizar check-out:', error);
        mostrarError(error.message);
    }
}

// Gestión de cargos
async function handleNewCargo() {
    const descripcion = document.getElementById('desc-cargo').value;
    const importe = document.getElementById('imp-cargo').value;
    const id_reserva = document.getElementById('id_reserva_modal').value;

    if (!descripcion || !importe || !id_reserva) {
        mostrarError('Todos los campos son requeridos');
        return;
    }

    try {
        const response = await fetch('../api/cargos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_reserva,
                descripcion,
                importe: parseFloat(importe)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('form-nuevo-cargo').reset();
            cargarCargos(id_reserva);
            mostrarMensaje('Cargo añadido correctamente', 'success');
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error al añadir cargo:', error);
        mostrarError(error.message);
    }
}

// Mostrar mensajes al usuario
function mostrarError(mensaje) {
    mostrarMensaje(mensaje, 'danger');
}

function mostrarMensaje(mensaje, tipo = 'info') {
    const container = document.createElement('div');
    container.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    container.style.zIndex = '1050';
    container.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(container);
    
    setTimeout(() => {
        container.remove();
    }, 3000);
}

// Animación de valores
function animateValues() {
    document.querySelectorAll('.stat-value').forEach(el => {
        const value = parseInt(el.textContent);
        if (!isNaN(value)) {
            animateValue(el, 0, value, 1000);
        }
    });
}

function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const update = () => {
        current += increment;
        element.textContent = Math.round(current);
        
        if ((increment > 0 && current < end) || (increment < 0 && current > end)) {
            requestAnimationFrame(update);
        } else {
            element.textContent = end;
        }
    };
    
    requestAnimationFrame(update);
}
