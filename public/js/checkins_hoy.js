// public/js/checkins_hoy.js
// Variables globales
let refreshInterval;
const REFRESH_INTERVAL = 30000; // 30 segundos

document.addEventListener('DOMContentLoaded', () => {
  initializeApp();
});

function initializeApp() {
  cargarCheckIns();
  setupAutoRefresh();
}

// Configurar auto-refresh
function setupAutoRefresh() {
  const autoRefreshToggle = document.getElementById('auto-refresh');
  if (autoRefreshToggle) {
    autoRefreshToggle.addEventListener('change', () => {
      if (autoRefreshToggle.checked) {
        refreshInterval = setInterval(cargarCheckIns, REFRESH_INTERVAL);
        mostrarMensaje('Auto-actualización activada', 'info');
      } else {
        clearInterval(refreshInterval);
        mostrarMensaje('Auto-actualización desactivada', 'info');
      }
    });
  }
}

// Cargar datos de check-ins
async function cargarCheckIns() {
  try {
    const response = await fetch('../api/checkins_hoy.php');
    const data = await response.json();
    
    updateStats(data);
    renderCheckInsTable(data);
    renderUpcomingArrivals(data.proximasLlegadas || []);
    
    // Añadir animación a las nuevas filas
    document.querySelectorAll('tbody tr, .timeline-item').forEach(el => {
      el.classList.add('animate-fade-in');
    });
  } catch (error) {
    console.error('Error al cargar datos:', error);
    document.getElementById('tabla-checkins-hoy').innerHTML = 
      '<tr><td colspan="6" class="text-center">Error al cargar datos</td></tr>';
  }
}

// Actualizar estadísticas
function updateStats(data) {
  const stats = {
    'pending-today': data.pendientes?.length || 0,
    'completed-today': data.completados?.length || 0,
    'next-hour': data.proximaHora?.length || 0
  };

  // Animar cambios en las estadísticas
  Object.entries(stats).forEach(([id, value]) => {
    const element = document.getElementById(id);
    if (element) {
      animateValue(element, parseInt(element.textContent) || 0, value, 500);
    }
  });

  // Actualizar hora próxima
  const nextHourTime = document.getElementById('next-hour-time');
  if (nextHourTime) {
    const now = new Date();
    const nextHour = new Date(now.setHours(now.getHours() + 1));
    nextHourTime.textContent = `hasta ${nextHour.getHours()}:00h`;
  }
}

// Función para animar valores numéricos
function animateValue(element, start, end, duration) {
  if (start === end) return;
  const range = end - start;
  const increment = end > start ? 1 : -1;
  const stepTime = Math.abs(Math.floor(duration / range));
  let current = start;
  
  const timer = setInterval(() => {
    current += increment;
    element.textContent = current;
    if (current === end) {
      clearInterval(timer);
    }
  }, stepTime);
}

// Renderizar tabla de check-ins
function renderCheckInsTable(data) {
  const tbody = document.getElementById('tabla-checkins-hoy');
  if (!tbody) return;
  
  const checkins = data.checkins || [];
  
  tbody.innerHTML = checkins.length ? checkins.map(ci => `
    <tr>
      <td>${formatTime(ci.fecha_checkin)}</td>
      <td>${ci.id_reserva}</td>
      <td>
        <div class="d-flex align-items-center">
          <div>
            <div class="fw-bold">${ci.nombre} ${ci.apellidos}</div>
            <small class="text-muted">${ci.email || ''}</small>
          </div>
        </div>
      </td>
      <td>${ci.numero_habitacion}</td>
      <td>
        <span class="badge ${getStatusClass(ci.estado)}">
          ${ci.estado}
        </span>
      </td>
      <td>
        <a href="cliente_detalle.php?id_reserva=${ci.id_reserva}" 
           class="btn btn-sm btn-primary">
          <i class="fas fa-eye me-1"></i>Ver ficha
        </a>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="6" class="text-center">No hay check-ins registrados hoy</td></tr>';
}

// Renderizar próximas llegadas
function renderUpcomingArrivals(arrivals) {
  const container = document.getElementById('upcoming-arrivals');
  if (!container) return;
  
  container.innerHTML = arrivals.length ? arrivals.map(arrival => `
    <div class="timeline-item">
      <div class="time">${formatTime(arrival.fecha_entrada)}</div>
      <div class="content">
        <div class="guest">${arrival.nombre} ${arrival.apellidos}</div>
        <div class="room">Habitación ${arrival.numero_habitacion}</div>
        <div class="mt-2">
          <span class="badge bg-warning">
            <i class="fas fa-clock me-1"></i>Pendiente
          </span>
        </div>
      </div>
    </div>
  `).join('') : '<p class="text-center text-muted">No hay llegadas programadas próximamente</p>';
}

// Mostrar mensajes al usuario
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

// Función auxiliar para formatear hora
function formatTime(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Función auxiliar para determinar clase de estado
function getStatusClass(estado) {
  return {
    'pendiente': 'bg-warning',
    'completado': 'bg-success',
    'retrasado': 'bg-danger'
  }[estado.toLowerCase()] || 'bg-secondary';
}
