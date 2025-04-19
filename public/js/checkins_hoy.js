// public/js/checkins_hoy.js
document.addEventListener('DOMContentLoaded', () => {
    fetch('../api/checkins_hoy.php')
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then(data => {
        const tbody = document.getElementById('tabla-checkins-hoy');
        tbody.innerHTML = data.length
          ? data.map(ci => `
            <tr>
              <td>${new Date(ci.fecha_checkin).toLocaleTimeString()}</td>
              <td>${ci.id_reserva}</td>
              <td>${ci.nombre} ${ci.apellidos}</td>
              <td>${ci.numero_habitacion}</td>
              <td>
                <a href="cliente_detalle.php?id_reserva=${ci.id_reserva}" class="btn btn-sm btn-primary">
                  Ver ficha
                </a>
              </td>
            </tr>
          `).join('')
          : `<tr><td colspan="5">No hay check‑ins hoy.</td></tr>`;
      })
      .catch(err => {
        console.error('Error al cargar check‑ins de hoy:', err);
        document.getElementById('tabla-checkins-hoy').innerHTML =
          `<tr><td colspan="5">Error al cargar datos.</td></tr>`;
      });
  });
  