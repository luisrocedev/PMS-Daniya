// reservas.js
function listarReservas() {
    fetch('../api/reservas.php')
      .then(response => response.json())
      .then(data => {
        const reservas = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tabla-reservas');
        if (!tbody) return;
        tbody.innerHTML = '';
        reservas.forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${r.id_reserva}</td>
            <td>${r.id_cliente}</td>
            <td>${r.id_habitacion}</td>
            <td>${r.fecha_entrada}</td>
            <td>${r.fecha_salida}</td>
            <td>${r.estado_reserva}</td>
            <td>
              <button class="btn" onclick="eliminarReserva(${r.id_reserva})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error('Error en listarReservas:', err));
  }
  
  function crearReserva() {
    const idCliente = document.getElementById('id_cliente_nueva').value;
    const idHabitacion = document.getElementById('id_habitacion_nueva').value;
    const fEntrada = document.getElementById('fecha_entrada_nueva').value;
    const fSalida = document.getElementById('fecha_salida_nueva').value;
    const estadoReserva = document.getElementById('estado_nueva').value;
  
    fetch('../api/reservas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          id_cliente: idCliente,
          id_habitacion: idHabitacion,
          fecha_entrada: fEntrada,
          fecha_salida: fSalida,
          estado_reserva: estadoReserva
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarReservas();
          document.getElementById('id_cliente_nueva').value = '';
          document.getElementById('id_habitacion_nueva').value = '';
          document.getElementById('fecha_entrada_nueva').value = '';
          document.getElementById('fecha_salida_nueva').value = '';
          document.getElementById('estado_nueva').value = 'Pendiente';
        } else {
          alert(data.error || 'No se pudo crear la reserva');
        }
      })
      .catch(err => console.error('Error en crearReserva:', err));
  }
  
  function eliminarReserva(idReserva) {
    fetch(`../api/reservas.php?id=${idReserva}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarReservas();
        } else {
          alert(data.error || 'Error al eliminar reserva');
        }
      })
      .catch(err => console.error('Error en eliminarReserva:', err));
  }
  