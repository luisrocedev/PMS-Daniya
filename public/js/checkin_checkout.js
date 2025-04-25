// public/js/checkin_checkout.js

// Variables globales
let refreshInterval;
const REFRESH_INTERVAL = 30000; // 30 segundos

document.addEventListener('DOMContentLoaded', () => {
  initializeApp();
});

function initializeApp() {
  cargarCheckInOut();
  setupAutoRefresh();
  initializeEventListeners();
}

// Configurar auto-refresh
function setupAutoRefresh() {
  const autoRefreshToggle = document.getElementById('auto-refresh');
  autoRefreshToggle.addEventListener('change', () => {
    if (autoRefreshToggle.checked) {
      refreshInterval = setInterval(cargarCheckInOut, REFRESH_INTERVAL);
    } else {
      clearInterval(refreshInterval);
    }
  });
}

// Inicializar event listeners
function initializeEventListeners() {
  document.getElementById('formCheckinModal').addEventListener('submit', handleCheckinSubmit);
  document.getElementById('btn-add-cargo').addEventListener('click', handleNewCargo);
  document.querySelector('a#tab-cargos').addEventListener('shown.bs.tab', () => cargarCargos(window.reservaId));
}

// Cargar datos de check-in/check-out
async function cargarCheckInOut() {
  try {
    const response = await fetch('../api/checkinout.php');
    const data = await response.json();
    
    updateStats(data);
    renderCheckInTable(data.pendientesCheckIn || []);
    renderCheckOutTable(data.pendientesCheckOut || []);
    
    // Añadir animación a las nuevas filas
    document.querySelectorAll('tbody tr').forEach(row => {
      row.classList.add('animate-fade-in');
    });
  } catch (error) {
    console.error('Error al cargar datos:', error);
  }
}

// Actualizar estadísticas
function updateStats(data) {
  const stats = {
    'pending-checkins': data.pendientesCheckIn?.length || 0,
    'pending-checkouts': data.pendientesCheckOut?.length || 0,
    'completed-today': (data.completadosHoy?.checkins || 0) + (data.completadosHoy?.checkouts || 0)
  };

  // Animar cambios en las estadísticas
  Object.entries(stats).forEach(([id, value]) => {
    const element = document.getElementById(id);
    animateValue(element, parseInt(element.textContent), value, 500);
  });
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
function renderCheckInTable(checkins) {
  const tbody = document.getElementById('tabla-checkin');
  tbody.innerHTML = checkins.length ? checkins.map(r => `
    <tr>
      <td>${r.id_reserva}</td>
      <td>${r.nombre_cliente} ${r.apellidos_cliente}</td>
      <td>${r.id_habitacion}</td>
      <td>
        <button class="btn check-action-btn btn-checkin" onclick="abrirModalCheckin(${r.id_reserva})">
          <i class="fas fa-sign-in-alt me-2"></i>Check-in
        </button>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="4" class="text-center">No hay check-ins pendientes</td></tr>';
}

// Renderizar tabla de check-outs
function renderCheckOutTable(checkouts) {
  const tbody = document.getElementById('tabla-checkout');
  tbody.innerHTML = checkouts.length ? checkouts.map(r => `
    <tr>
      <td>${r.id_reserva}</td>
      <td>${r.nombre_cliente} ${r.apellidos_cliente}</td>
      <td>${r.id_habitacion}</td>
      <td>
        <button class="btn check-action-btn btn-checkout" onclick="hacerCheckOut(${r.id_reserva})">
          <i class="fas fa-sign-out-alt me-2"></i>Check-out
        </button>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="4" class="text-center">No hay check-outs pendientes</td></tr>';
}

// Modal de Check-in
function abrirModalCheckin(id) {
  document.getElementById('modalCheckin').style.display = 'block';
  document.getElementById('id_reserva_modal').value = id;
}

function cerrarModalCheckin() {
  document.getElementById('modalCheckin').style.display = 'none';
  document.getElementById('formCheckinModal').reset();
}

// Manejar envío del formulario de check-in
async function handleCheckinSubmit(e) {
  e.preventDefault();
  const form = new FormData(e.target);
  
  try {
    const uploadResponse = await fetch('../api/checkin_uploads.php', {
      method: 'POST',
      body: form
    });
    const uploadData = await uploadResponse.json();
    
    if (uploadData.success) {
      await hacerCheckIn(form.get('id_reserva'));
    } else {
      alert(uploadData.error);
    }
  } catch (error) {
    console.error('Error en el proceso de check-in:', error);
    alert('Error al procesar el check-in');
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
      cerrarModalCheckin();
      await cargarCheckInOut();
      window.location.href = `cliente_detalle.php?id_reserva=${id}`;
    } else {
      alert(data.error);
    }
  } catch (error) {
    console.error('Error al realizar check-in:', error);
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
      alert(data.msg);
      cargarCheckInOut();
    } else {
      alert(data.error);
    }
  } catch (error) {
    console.error('Error al realizar check-out:', error);
  }
}

// Gestión de cargos
async function cargarCargos(id) {
  if (!id) return;
  
  try {
    const response = await fetch(`../api/cargos.php?reserva=${id}`);
    const data = await response.json();
    
    const container = document.getElementById('lista-cargos');
    container.innerHTML = data.length ? data.map(cargo => `
      <div class="cargo-item animate-fade-in">
        <div>
          <strong>${cargo.descripcion}</strong>
          <small class="text-muted">(${new Date(cargo.fecha).toLocaleDateString()})</small>
        </div>
        <div class="text-end">
          <span class="badge bg-primary">${cargo.importe.toFixed(2)} €</span>
        </div>
      </div>
    `).join('') : '<p class="text-center text-muted">No hay cargos registrados</p>';
  } catch (error) {
    console.error('Error al cargar cargos:', error);
  }
}

// Manejar nuevo cargo
async function handleNewCargo() {
  const descripcion = document.getElementById('desc-cargo').value;
  const importe = document.getElementById('imp-cargo').value;
  const id_reserva = window.reservaId;

  if (!id_reserva) {
    alert('Debe seleccionar una reserva primero');
    return;
  }

  try {
    const response = await fetch('../api/cargos.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `id_reserva=${id_reserva}&descripcion=${encodeURIComponent(descripcion)}&importe=${importe}`
    });
    const data = await response.json();
    
    if (data.success) {
      document.getElementById('form-nuevo-cargo').reset();
      cargarCargos(id_reserva);
    } else {
      alert('Error al añadir el cargo');
    }
  } catch (error) {
    console.error('Error al añadir cargo:', error);
  }
}
