// checkin_checkout.js
function cargarCheckInOut() {
    fetch('../api/checkinout.php')
      .then(response => response.json())
      .then(data => {
        const pendientesCheckIn = data.pendientesCheckIn || [];
        const pendientesCheckOut = data.pendientesCheckOut || [];
        const tbodyCheckin = document.getElementById('tabla-checkin');
        const tbodyCheckout = document.getElementById('tabla-checkout');
  
        if (tbodyCheckin) {
          tbodyCheckin.innerHTML = '';
          pendientesCheckIn.forEach(res => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${res.id_reserva}</td>
              <td>${res.nombre_cliente} ${res.apellidos_cliente}</td>
              <td>${res.id_habitacion}</td>
              <td><button class="btn" onclick="hacerCheckIn(${res.id_reserva})">Check-in</button></td>
            `;
            tbodyCheckin.appendChild(tr);
          });
        }
        if (tbodyCheckout) {
          tbodyCheckout.innerHTML = '';
          pendientesCheckOut.forEach(res => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${res.id_reserva}</td>
              <td>${res.nombre_cliente} ${res.apellidos_cliente}</td>
              <td>${res.id_habitacion}</td>
              <td><button class="btn" onclick="hacerCheckOut(${res.id_reserva})">Check-out</button></td>
            `;
            tbodyCheckout.appendChild(tr);
          });
        }
      })
      .catch(err => console.error('Error en cargarCheckInOut:', err));
  }
  
  function hacerCheckIn(idReserva) {
    fetch('../api/checkinout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'checkin',
          id_reserva: idReserva
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          cargarCheckInOut();
        } else {
          alert(data.error || 'Error en check-in');
        }
      })
      .catch(err => console.error('Error en hacerCheckIn:', err));
  }
  
  function hacerCheckOut(idReserva) {
    fetch('../api/checkinout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'checkout',
          id_reserva: idReserva
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          cargarCheckInOut();
        } else {
          alert(data.error || 'Error en check-out');
        }
      })
      .catch(err => console.error('Error en hacerCheckOut:', err));
  }
  
  function abrirModalCheckin(idReserva) {
    const modal = document.getElementById('modalCheckin');
    if (modal) {
      modal.style.display = 'block';
      document.getElementById('id_reserva_modal').value = idReserva;
    }
  }
  
  function cerrarModalCheckin() {
    const modal = document.getElementById('modalCheckin');
    if (modal) {
      modal.style.display = 'none';
      document.getElementById('id_reserva_modal').value = '';
      document.getElementById('dni_modal').value = '';
      document.getElementById('firma_modal').value = '';
    }
  }
  
  const formCheckinModal = document.getElementById('formCheckinModal');
  if (formCheckinModal) {
    formCheckinModal.addEventListener('submit', (e) => {
      e.preventDefault();
      const id_reserva = document.getElementById('id_reserva_modal').value;
      const dni = document.getElementById('dni_modal').value;
      const firma = document.getElementById('firma_modal').value;
      const formData = new FormData();
      formData.append('id_reserva', id_reserva);
      formData.append('dni', dni);
      formData.append('firma', firma);
  
      fetch('../api/checkin_uploads.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            hacerCheckIn(id_reserva);
            cerrarModalCheckin();
          } else {
            alert(data.error || 'Error al subir datos de check-in');
          }
        })
        .catch(err => console.error('Error en enviar datos de check-in:', err));
    });
  }
  