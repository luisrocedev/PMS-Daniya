// public/js/cliente_detalle.js
const API_CARGOS = '../api/cargos.php';
const API_TIPOS  = '../api/tipos_cargo.php';

document.addEventListener('DOMContentLoaded', () => {
  renderCatalogo();
  cargarCargos();
  document.getElementById('formCargo').addEventListener('submit', guardarCargo);
});

function renderCatalogo() {
  fetch(API_TIPOS)
    .then(res => res.json())
    .then(tipos => {
      const cont = document.getElementById('catalogo-cargos');
      cont.innerHTML = tipos.map(t => `
        <button class="btn btn-outline-primary m-1"
          onclick="addCargo('${t.nombre}', ${t.precio})">
          ${t.nombre} (${parseFloat(t.precio).toFixed(2)} €)
        </button>`).join('');
    })
    .catch(err => console.error('Error al cargar catálogo:', err));
}

function addCargo(descripcion, importe) {
  const body = `id_reserva=${idReserva}&descripcion=${encodeURIComponent(descripcion)}&importe=${importe}`;
  fetch(API_CARGOS, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) cargarCargos();
    else alert(data.error || 'Error al añadir cargo');
  })
  .catch(err => console.error('Error al añadir cargo:', err));
}

function cargarCargos() {
  fetch(`${API_CARGOS}?reserva=${idReserva}`)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('tabla-cargos');
      tbody.innerHTML = data.map(c => `
        <tr>
          <td>${c.id_cargo}</td>
          <td>${c.descripcion}</td>
          <td>${parseFloat(c.importe).toFixed(2)} €</td>
          <td>${c.fecha}</td>
          <td>${c.pagado ? 'Sí' : 'No'}</td>
          <td>
            <button class="btn btn-sm btn-warning" onclick="editarCargo(${c.id_cargo})">✎</button>
            <button class="btn btn-sm btn-danger" onclick="borrarCargo(${c.id_cargo})">🗑️</button>
          </td>
        </tr>
      `).join('') || '<tr><td colspan="6">No hay cargos.</td></tr>';
    })
    .catch(err => console.error('Error al cargar cargos:', err));
}

function editarCargo(id) {
  fetch(`${API_CARGOS}?id=${id}`)
    .then(res => res.json())
    .then(c => {
      document.getElementById('modalTitle').innerText = 'Editar Cargo';
      document.getElementById('cargo_id').value   = c.id_cargo;
      document.getElementById('cargo_desc').value = c.descripcion;
      document.getElementById('cargo_imp').value  = c.importe;
      document.getElementById('modalCargo').style.display = 'block';
    });
}

function cerrarModal() {
  document.getElementById('modalCargo').style.display = 'none';
}

function guardarCargo(e) {
  e.preventDefault();
  const id   = document.getElementById('cargo_id').value;
  const desc = document.getElementById('cargo_desc').value;
  const imp  = document.getElementById('cargo_imp').value;
  const url  = id ? `${API_CARGOS}?id=${id}` : API_CARGOS;
  const opts = {
    method: id ? 'PUT' : 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `id_reserva=${idReserva}&descripcion=${encodeURIComponent(desc)}&importe=${imp}`
  };
  fetch(url, opts)
    .then(res => res.json())
    .then(_ => {
      cerrarModal();
      cargarCargos();
    });
}

function borrarCargo(id) {
  if (!confirm('¿Eliminar este cargo?')) return;
  fetch(`${API_CARGOS}?id=${id}`, { method: 'DELETE' })
    .then(res => res.json())
    .then(_ => cargarCargos());
}
