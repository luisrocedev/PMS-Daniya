// facturas.js
function listarFacturas() {
    fetch('../api/facturas.php')
      .then(response => response.json())
      .then(data => {
        const facturas = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tabla-fact');
        if (!tbody) return;
        tbody.innerHTML = '';
        facturas.forEach(f => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${f.id_factura}</td>
            <td>${f.id_reserva}</td>
            <td>${f.fecha_emision}</td>
            <td>${f.total}</td>
            <td>${f.metodo_pago}</td>
            <td>
              <button class="btn" onclick="eliminarFactura(${f.id_factura})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error('Error en listarFacturas:', err));
  }
  
  function crearFactura() {
    const id_reserva = document.getElementById('idResF').value;
    const fecha_emision = document.getElementById('fechaF').value;
    const total = document.getElementById('totalF').value;
    const metodo_pago = document.getElementById('metodoF').value;
  
    fetch('../api/facturas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id_reserva, fecha_emision, total, metodo_pago })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarFacturas();
          document.getElementById('idResF').value = '';
          document.getElementById('fechaF').value = '';
          document.getElementById('totalF').value = '';
          document.getElementById('metodoF').value = 'Efectivo';
        } else {
          alert(data.error || 'No se pudo crear la factura');
        }
      })
      .catch(err => console.error('Error en crearFactura:', err));
  }
  
  function eliminarFactura(idF) {
    if (!confirm('¿Seguro que deseas eliminar esta factura?')) return;
    fetch(`../api/facturas.php?id=${idF}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarFacturas();
        } else {
          alert(data.error || 'No se pudo eliminar la factura');
        }
      })
      .catch(err => console.error('Error en eliminarFactura:', err));
  }
  