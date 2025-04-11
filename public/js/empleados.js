// empleados.js
function listarEmpleados() {
    fetch('../api/empleados.php')
      .then(response => response.json())
      .then(data => {
        const empleados = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tabla-empleados');
        if (!tbody) return;
        tbody.innerHTML = '';
        empleados.forEach(emp => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${emp.id_empleado}</td>
            <td>${emp.nombre}</td>
            <td>${emp.apellidos}</td>
            <td>${emp.dni}</td>
            <td>${emp.telefono || ''}</td>
            <td>${emp.email || ''}</td>
            <td>${emp.id_rol}</td>
            <td>${emp.id_departamento}</td>
            <td>
              <button class="btn" onclick="eliminarEmpleado(${emp.id_empleado})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error('Error en listarEmpleados:', err));
  }
  
  function crearEmpleado() {
    const nombre = document.getElementById('nombre').value;
    const apellidos = document.getElementById('apellidos').value;
    const dni = document.getElementById('dni').value;
    const telefono = document.getElementById('telefono').value;
    const email = document.getElementById('email').value;
    const direccion = document.getElementById('direccion').value;
    const fecha_contrat = document.getElementById('fecha_contrat').value;
    const id_rol = document.getElementById('id_rol').value;
    const id_departamento = document.getElementById('id_departamento').value;
  
    fetch('../api/empleados.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ nombre, apellidos, dni, telefono, email, direccion, fecha_contrat, id_rol, id_departamento })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarEmpleados();
          document.getElementById('nombre').value = '';
          document.getElementById('apellidos').value = '';
          document.getElementById('dni').value = '';
          document.getElementById('telefono').value = '';
          document.getElementById('email').value = '';
          document.getElementById('direccion').value = '';
          document.getElementById('fecha_contrat').value = '';
          document.getElementById('id_rol').value = '1';
          document.getElementById('id_departamento').value = '1';
        } else {
          alert(data.error || 'No se pudo crear el empleado');
        }
      })
      .catch(err => console.error('Error en crearEmpleado:', err));
  }
  
  function eliminarEmpleado(idEmpleado) {
    if (!confirm('¿Seguro que deseas eliminar este empleado?')) return;
    fetch(`../api/empleados.php?id=${idEmpleado}`, { method: 'DELETE' })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.msg);
          listarEmpleados();
        } else {
          alert(data.error || 'No se pudo eliminar el empleado');
        }
      })
      .catch(err => console.error('Error en eliminarEmpleado:', err));
  }
  