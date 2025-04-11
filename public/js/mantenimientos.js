// mantenimientos.js
function listarIncidencias() {
    fetch('../api/mantenimiento.php')
      .then(response => response.json())
      .then(data => {
        const incidencias = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tabla-mant');
        if (!tbody) return;
        tbody.innerHTML = '';
        incidencias.forEach(m => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${m.id_incidencia}</td>
            <td>${m.id_habitacion}</td>
            <td>${m.id_empleado}</td>
            <td>${m.descripcion}</td>
            <td>${m.fecha_reporte}</td>
            <td>${m.fecha_resolucion || ''}</td>
            <td>${m.estado}</td>
            <td>
              <button class="btn" onclick="eliminarIncidencia(${m.id_incidencia})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error('Error en listarIncidencias:', err));
  }
  
  function crearIncidencia() {
    const id_habitacion = document.getElementById('idHab').value;
    const id_empleado = document.getElementById('idEmp').value;
    const descripcion = document.getElementById('descMant').value;
    const fecha_reporte = document.getElementById('fRep').value;
    const fecha_resolucion = document.getElementById('fRes').value;
    const estado = document.getElementById('estMant').value;
  
    fetch('../api/mantenimiento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          id_habitacion,
          id_empleado,
          descripcion,
          fecha_reporte,
          fecha_resolucion,
          estado
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarIncidencias();
          document.getElementById('idHab').value = '';
          document.getElementById('idEmp').value = '';
          document.getElementById('descMant').value = '';
          document.getElementById('fRep').value = '';
          document.getElementById('fRes').value = '';
          document.getElementById('estMant').value = 'Pendiente';
        } else {
          alert(data.error || 'No se pudo crear la incidencia');
        }
      })
      .catch(err => console.error('Error en crearIncidencia:', err));
  }
  
  function eliminarIncidencia(idI) {
    if (!confirm('¿Seguro que deseas eliminar esta incidencia?')) return;
    fetch(`../api/mantenimiento.php?id=${idI}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarIncidencias();
        } else {
          alert(data.error || 'No se pudo eliminar la incidencia');
        }
      })
      .catch(err => console.error('Error en eliminarIncidencia:', err));
  }
  