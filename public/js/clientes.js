// clientes.js
function listarClientes() {
    fetch('../api/clientes.php')
      .then(response => response.json())
      .then(data => {
        const clientes = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tabla-clientes');
        if (!tbody) return;
        tbody.innerHTML = '';
        clientes.forEach(cliente => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${cliente.id_cliente}</td>
            <td>${cliente.nombre}</td>
            <td>${cliente.apellidos}</td>
            <td>${cliente.dni}</td>
            <td>${cliente.email || ''}</td>
            <td>${cliente.telefono || ''}</td>
            <td>${cliente.direccion || ''}</td>
            <td>
              <button class="btn" onclick="eliminarCliente(${cliente.id_cliente})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error('Error en listarClientes:', err));
  }
  
  function crearCliente() {
    const nombre = document.getElementById('nombreCli').value;
    const apellidos = document.getElementById('apellidosCli').value;
    const dni = document.getElementById('dniCli').value;
    const email = document.getElementById('emailCli').value;
    const telefono = document.getElementById('telCli').value;
    const direccion = document.getElementById('dirCli').value;
  
    fetch('../api/clientes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ nombre, apellidos, dni, email, telefono, direccion })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarClientes();
          document.getElementById('nombreCli').value = '';
          document.getElementById('apellidosCli').value = '';
          document.getElementById('dniCli').value = '';
          document.getElementById('emailCli').value = '';
          document.getElementById('telCli').value = '';
          document.getElementById('dirCli').value = '';
        } else {
          alert(data.error || 'No se pudo crear el cliente');
        }
      })
      .catch(err => console.error('Error en crearCliente:', err));
  }
  
  function eliminarCliente(idCliente) {
    if (!confirm('¿Seguro que deseas eliminar este cliente?')) return;
    fetch(`../api/clientes.php?id=${idCliente}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarClientes();
        } else {
          alert(data.error || 'Error al eliminar cliente');
        }
      })
      .catch(err => console.error('Error en eliminarCliente:', err));
  }
  