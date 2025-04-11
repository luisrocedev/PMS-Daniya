// habitaciones.js
function listarHabitaciones() {
    fetch('../api/habitaciones.php')
      .then(response => response.json())
      .then(data => {
        const habitaciones = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tabla-habitaciones');
        if (!tbody) return;
        tbody.innerHTML = '';
        habitaciones.forEach(hab => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${hab.id_habitacion}</td>
            <td>${hab.numero_habitacion}</td>
            <td>${hab.tipo_habitacion}</td>
            <td>${hab.capacidad}</td>
            <td>${hab.piso}</td>
            <td>${hab.estado}</td>
            <td>
              <button class="btn" onclick="eliminarHabitacion(${hab.id_habitacion})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error('Error en listarHabitaciones:', err));
  }
  
  function crearHabitacion() {
    const numero_habitacion = document.getElementById('numero_habitacion').value;
    const tipo_habitacion = document.getElementById('tipo_habitacion').value;
    const capacidad = document.getElementById('capacidad').value;
    const piso = document.getElementById('piso').value;
    const estado = document.getElementById('estado').value;
  
    fetch('../api/habitaciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          numero_habitacion,
          tipo_habitacion,
          capacidad,
          piso,
          estado
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarHabitaciones();
          document.getElementById('numero_habitacion').value = '';
          document.getElementById('tipo_habitacion').value = '';
          document.getElementById('capacidad').value = '';
          document.getElementById('piso').value = '';
          document.getElementById('estado').value = 'Disponible';
        } else {
          alert(data.error || 'No se pudo crear la habitación');
        }
      })
      .catch(err => console.error('Error en crearHabitacion:', err));
  }
  
  function eliminarHabitacion(idHab) {
    if (!confirm('¿Seguro que deseas eliminar esta habitación?')) return;
    fetch(`../api/habitaciones.php?id=${idHab}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              alert(data.msg);
              listarHabitaciones();  // función que recarga la lista
          } else {
              // Se muestra el mensaje de error recibido de la API
              alert(data.error || 'No se pudo eliminar la habitación');
          }
      })
      .catch(err => console.error('Error en eliminarHabitacion:', err));
}
  