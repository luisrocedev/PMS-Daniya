// public/js/checkin_checkout.js

document.addEventListener('DOMContentLoaded', () => {
  cargarCheckInOut();
});

// 1. Cargar reservas pendientes
function cargarCheckInOut() {
  fetch('../api/checkinout.php')
    .then(r => r.json())
    .then(data => {
      const inArr  = data.pendientesCheckIn || [];
      const outArr = data.pendientesCheckOut || [];

      const tIn = document.getElementById('tabla-checkin');
      tIn.innerHTML = '';
      inArr.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.id_reserva}</td>
          <td>${r.nombre_cliente} ${r.apellidos_cliente}</td>
          <td>${r.id_habitacion}</td>
          <td><button class="btn btn-primary" onclick="abrirModalCheckin(${r.id_reserva})">Check-in</button></td>
        `;
        tIn.appendChild(tr);
      });

      const tOut = document.getElementById('tabla-checkout');
      tOut.innerHTML = '';
      outArr.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.id_reserva}</td>
          <td>${r.nombre_cliente} ${r.apellidos_cliente}</td>
          <td>${r.id_habitacion}</td>
          <td><button class="btn btn-warning" onclick="hacerCheckOut(${r.id_reserva})">Check-out</button></td>
        `;
        tOut.appendChild(tr);
      });
    })
    .catch(console.error);
}

// 2. Modal Check-in
function abrirModalCheckin(id) {
  document.getElementById('modalCheckin').style.display = 'block';
  document.getElementById('id_reserva_modal').value = id;
}
function cerrarModalCheckin() {
  document.getElementById('modalCheckin').style.display = 'none';
  ['id_reserva_modal','dni_modal','firma_modal'].forEach(id=>document.getElementById(id).value='');
}

// 3. Enviar DNI/firma y hacer checkin
document.getElementById('formCheckinModal').addEventListener('submit', e => {
  e.preventDefault();
  const form = new FormData(e.target);
  fetch('../api/checkin_uploads.php', { method:'POST', body:form })
    .then(r=>r.json())
    .then(data=>{
      if (data.success) hacerCheckIn(form.get('id_reserva'));
      else alert(data.error);
    })
    .catch(console.error);
});

function hacerCheckIn(id) {
  const form = new FormData();
  form.append('action','checkin');
  form.append('id_reserva',id);
  fetch('../api/checkinout.php',{ method:'POST', body:form })
    .then(r=>r.json())
    .then(d=>{
      if (d.success) {
        alert(d.msg);
        cerrarModalCheckin();
        cargarCheckInOut();
      } else alert(d.error);
      if (d.success) {
        // Tras hacer check‑in, vamos a la ficha de la reserva/cliente
        window.location.href = `cliente_detalle.php?id_reserva=${id}`;
      } else {
        alert(d.error);
      }
    }).catch(console.error);
}

// 5. Hacer Check-out
function hacerCheckOut(id) {
  const form = new FormData();
  form.append('action','checkout');
  form.append('id_reserva',id);
  fetch('../api/checkinout.php',{ method:'POST', body:form })
    .then(r=>r.json())
    .then(d=>{
      if (d.success) {
        alert(d.msg);
        cargarCheckInOut();
      } else alert(d.error);
    }).catch(console.error);
}

// 6. Cargar y añadir cargos
function cargarCargos(id) {
  fetch(`/api/cargos.php?reserva=${id}`)
    .then(r=>r.json())
    .then(data=>{
      const cont = document.getElementById('lista-cargos');
      cont.innerHTML = data.length
        ? data.map(c=>`<div class="d-flex justify-content-between border-bottom py-1">
            <span>${c.descripcion} (${c.fecha})</span>
            <strong>${c.importe.toFixed(2)} €</strong>
          </div>`).join('')
        : '<p>No hay cargos.</p>';
    });
}
document.getElementById('btn-add-cargo').addEventListener('click',()=>{
  const desc = document.getElementById('desc-cargo').value;
  const imp  = document.getElementById('imp-cargo').value;
  const id   = document.getElementById('id_reserva_modal').value; // o window.reservaId
  fetch('/api/cargos.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`id_reserva=${id}&descripcion=${encodeURIComponent(desc)}&importe=${imp}`
  }).then(()=> {
    document.getElementById('desc-cargo').value = '';
    document.getElementById('imp-cargo').value = '';
    cargarCargos(id);
  });
});
document.querySelector('a#tab-cargos').addEventListener('shown.bs.tab', ()=> cargarCargos(window.reservaId));
