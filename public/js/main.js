// public/js/main.js

/*********************************************************
 * 1. Check-In / Check-Out
 *********************************************************/
function cargarCheckInOut() {
  // GET => listar pendientes
  fetch('../api/checkinout.php')
    .then(response => response.json())
    .then(data => {
      const pendientesCheckIn = data.pendientesCheckIn || [];
      const pendientesCheckOut = data.pendientesCheckOut || [];

      // Asumimos que en checkin_checkout.html tenemos:
      // <tbody id="tabla-checkin"></tbody>
      // <tbody id="tabla-checkout"></tbody>
      const tbodyCheckin = document.getElementById('tabla-checkin');
      const tbodyCheckout = document.getElementById('tabla-checkout');

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
    })
    .catch(err => console.error('Error en cargarCheckInOut:', err));
}

function hacerCheckIn(idReserva) {
  // POST => action=checkin, id_reserva
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
      cargarCheckInOut(); // recargar la tabla
    } else {
      alert(data.error || 'Error en check-in');
    }
  })
  .catch(err => console.error(err));
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
  .catch(err => console.error(err));
}


/*********************************************************
 * 2. Ocupación
 *********************************************************/
function actualizarOcupacion() {
  // GET => api/ocupacion.php
  fetch('../api/ocupacion.php')
    .then(res => res.json())
    .then(data => {
      // { total, ocupadas, mantenimiento, disponibles }
      document.getElementById('ocupadas').textContent = data.ocupadas;
      document.getElementById('disponibles').textContent = data.disponibles;
      // etc. si tienes más spans
    })
    .catch(err => console.error('Error en actualizarOcupacion:', err));
}


/*********************************************************
 * 3. CRUD de reservas
 *********************************************************/
// 3.1 Obtener todas las reservas
function listarReservas() {
  fetch('../api/reservas.php') // GET sin id => todas
    .then(response => response.json())
    .then(data => {
      // data es un array con todas las reservas
      // Ejemplo de render en tabla con id="tabla-reservas"
      const tbody = document.getElementById('tabla-reservas');
      tbody.innerHTML = '';
      data.forEach((r) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.id_reserva}</td>
          <td>${r.id_cliente}</td>
          <td>${r.id_habitacion}</td>
          <td>${r.fecha_entrada}</td>
          <td>${r.fecha_salida}</td>
          <td>${r.estado_reserva}</td>
          <td>
             <button onclick="eliminarReserva(${r.id_reserva})">Eliminar</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error(err));
}

// 3.2 Crear una reserva (ejemplo)
function crearReserva() {
  // Supongamos que tienes inputs en tu HTML con IDs:
  // #cliente, #habitacion, #fecha_entrada, #fecha_salida
  const idCliente = document.getElementById('cliente').value;
  const idHabitacion = document.getElementById('habitacion').value;
  const fEntrada = document.getElementById('fecha_entrada').value;
  const fSalida = document.getElementById('fecha_salida').value;

  fetch('../api/reservas.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      id_cliente: idCliente,
      id_habitacion: idHabitacion,
      fecha_entrada: fEntrada,
      fecha_salida: fSalida,
      estado_reserva: 'Pendiente'
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarReservas();
    } else {
      alert(data.error || 'No se pudo crear la reserva');
    }
  })
  .catch(err => console.error(err));
}

// 3.3 Eliminar una reserva
function eliminarReserva(idReserva) {
  // Usamos método DELETE => /reservas.php?id=XX
  fetch(`../api/reservas.php?id=${idReserva}`, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarReservas();
    } else {
      alert(data.error || 'Error al eliminar reserva');
    }
  })
  .catch(err => console.error(err));
}
/*********************************************************
 * 4. CRUD de Clientes
 *********************************************************/

// 4.1 Listar clientes
function listarClientes() {
  // GET => api/clientes.php (sin id => todos)
  fetch('../api/clientes.php')
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('tabla-clientes');
      if (!tbody) return;

      tbody.innerHTML = '';
      data.forEach(cliente => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${cliente.id_cliente}</td>
          <td>${cliente.nombre}</td>
          <td>${cliente.apellidos}</td>
          <td>${cliente.telefono || ''}</td>
          <td>${cliente.email || ''}</td>
          <td>
            <button class="btn" onclick="eliminarCliente(${cliente.id_cliente})">Eliminar</button>
            <!-- Podrías crear un botón para 'Editar' también -->
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error(err));
}

// 4.2 Crear un cliente
function crearCliente() {
  const nombre = document.getElementById('nombre').value;
  const apellidos = document.getElementById('apellidos').value;
  const telefono = document.getElementById('telefono').value;
  const email = document.getElementById('email').value;

  fetch('../api/clientes.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      nombre,
      apellidos,
      telefono,
      email
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      // Recargar tabla
      listarClientes();
      // Limpiar formulario
      document.getElementById('nombre').value = '';
      document.getElementById('apellidos').value = '';
      document.getElementById('telefono').value = '';
      document.getElementById('email').value = '';
    } else {
      alert(data.error || 'No se pudo crear el cliente');
    }
  })
  .catch(err => console.error(err));
}

// 4.3 Eliminar un cliente
function eliminarCliente(idCliente) {
  if (!confirm('¿Seguro que deseas eliminar este cliente?')) {
    return;
  }
  fetch(`../api/clientes.php?id=${idCliente}`, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarClientes();
    } else {
      alert(data.error || 'Error al eliminar cliente');
    }
  })
  .catch(err => console.error(err));
}
/*********************************************************
 * 5. CRUD de Empleados
 *********************************************************/

// 5.1 Listar empleados
function listarEmpleados() {
  fetch('../api/empleados.php') // GET (sin id => todos)
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('tabla-empleados');
      if (!tbody) return;

      tbody.innerHTML = '';
      data.forEach(emp => {
        // Muestra en la tabla
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
            <!-- Para editar, podrías hacer un botón similar: editarEmpleado(emp.id_empleado) -->
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error(err));
}

// 5.2 Crear empleado
function crearEmpleado() {
  const nombre          = document.getElementById('nombre').value;
  const apellidos       = document.getElementById('apellidos').value;
  const dni             = document.getElementById('dni').value;
  const telefono        = document.getElementById('telefono').value;
  const email           = document.getElementById('email').value;
  const direccion       = document.getElementById('direccion').value;
  const fecha_contrat   = document.getElementById('fecha_contrat').value;
  const id_rol          = document.getElementById('id_rol').value;
  const id_departamento = document.getElementById('id_departamento').value;

  fetch('../api/empleados.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      nombre,
      apellidos,
      dni,
      telefono,
      email,
      direccion,
      fecha_contrat,
      id_rol,
      id_departamento
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      // Recargar la tabla
      listarEmpleados();
      // Limpiar formulario
      document.getElementById('nombre').value        = '';
      document.getElementById('apellidos').value     = '';
      document.getElementById('dni').value           = '';
      document.getElementById('telefono').value      = '';
      document.getElementById('email').value         = '';
      document.getElementById('direccion').value     = '';
      document.getElementById('fecha_contrat').value = '';
      document.getElementById('id_rol').value        = '1';
      document.getElementById('id_departamento').value = '1';
    } else {
      alert(data.error || 'No se pudo crear el empleado');
    }
  })
  .catch(err => console.error(err));
}

// 5.3 Eliminar empleado
function eliminarEmpleado(idEmpleado) {
  if (!confirm('¿Seguro que deseas eliminar este empleado?')) return;

  fetch(`../api/empleados.php?id=${idEmpleado}`, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarEmpleados();
    } else {
      alert(data.error || 'No se pudo eliminar el empleado');
    }
  })
  .catch(err => console.error(err));
}
/*********************************************************
 * CRUD de Habitaciones
 *********************************************************/

// Listar habitaciones
function listarHabitaciones() {
  fetch('../api/habitaciones.php') // GET sin id => todas
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('tabla-habitaciones');
      if (!tbody) return;
      tbody.innerHTML = '';

      data.forEach(hab => {
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
              <!-- Si quieres editar, creas otra función -->
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error(err));
}

// Crear habitación
function crearHabitacion() {
  const numero_habitacion = document.getElementById('numero_habitacion').value;
  const tipo_habitacion   = document.getElementById('tipo_habitacion').value;
  const capacidad         = document.getElementById('capacidad').value;
  const piso              = document.getElementById('piso').value;
  const estado            = document.getElementById('estado').value;

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

      // Limpiar formulario
      document.getElementById('numero_habitacion').value = '';
      document.getElementById('tipo_habitacion').value   = '';
      document.getElementById('capacidad').value         = '';
      document.getElementById('piso').value              = '';
      document.getElementById('estado').value            = 'Disponible';
    } else {
      alert(data.error || 'No se pudo crear la habitación');
    }
  })
  .catch(err => console.error(err));
}

// Eliminar habitación
function eliminarHabitacion(idHab) {
  if (!confirm('¿Seguro que deseas eliminar esta habitación?')) return;

  fetch(`../api/habitaciones.php?id=${idHab}`, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarHabitaciones();
    } else {
      alert(data.error || 'No se pudo eliminar la habitación');
    }
  })
  .catch(err => console.error(err));
}
/*********************************************************
 * CRUD de Mantenimiento
 *********************************************************/

// Listar incidencias
function listarIncidencias() {
  fetch('../api/mantenimiento.php')
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('tabla-mantenimiento');
      if (!tbody) return;

      tbody.innerHTML = '';
      data.forEach(mant => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${mant.id_incidencia}</td>
          <td>${mant.id_habitacion}</td>
          <td>${mant.id_empleado}</td>
          <td>${mant.descripcion}</td>
          <td>${mant.fecha_reporte}</td>
          <td>${mant.fecha_resolucion || ''}</td>
          <td>${mant.estado}</td>
          <td>
            <button class="btn" onclick="eliminarIncidencia(${mant.id_incidencia})">Eliminar</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error(err));
}

// Crear incidencia
function crearIncidencia() {
  const id_habitacion    = document.getElementById('id_habitacion').value;
  const id_empleado      = document.getElementById('id_empleado').value;
  const descripcion      = document.getElementById('descripcion').value;
  const fecha_reporte    = document.getElementById('fecha_reporte').value;
  const fecha_resolucion = document.getElementById('fecha_resolucion').value;
  const estado           = document.getElementById('estado_mant').value;

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

      // Limpieza de formulario
      document.getElementById('id_habitacion').value    = '';
      document.getElementById('id_empleado').value      = '';
      document.getElementById('descripcion').value      = '';
      document.getElementById('fecha_reporte').value    = '';
      document.getElementById('fecha_resolucion').value = '';
      document.getElementById('estado_mant').value      = 'Pendiente';
    } else {
      alert(data.error || 'No se pudo crear la incidencia');
    }
  })
  .catch(err => console.error(err));
}

// Eliminar incidencia
function eliminarIncidencia(idIncidencia) {
  if (!confirm('¿Seguro que deseas eliminar esta incidencia?')) return;

  fetch(`../api/mantenimiento.php?id=${idIncidencia}`, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarIncidencias();
    } else {
      alert(data.error || 'No se pudo eliminar la incidencia');
    }
  })
  .catch(err => console.error(err));
}
/*********************************************************
 * CRUD de Facturas
 *********************************************************/

// Listar facturas
function listarFacturas() {
  fetch('../api/facturas.php')
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('tabla-facturas');
      if (!tbody) return;

      tbody.innerHTML = '';
      data.forEach(f => {
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
    .catch(err => console.error(err));
}

// Crear factura
function crearFactura() {
  const id_reserva    = document.getElementById('id_reserva').value;
  const fecha_emision = document.getElementById('fecha_emision').value;
  const total         = document.getElementById('total_factura').value;
  const metodo_pago   = document.getElementById('metodo_pago').value;

  fetch('../api/facturas.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      id_reserva,
      fecha_emision,
      total,
      metodo_pago
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarFacturas();

      // Limpiar formulario
      document.getElementById('id_reserva').value    = '';
      document.getElementById('fecha_emision').value = '';
      document.getElementById('total_factura').value = '';
      document.getElementById('metodo_pago').value   = 'Efectivo';
    } else {
      alert(data.error || 'No se pudo crear la factura');
    }
  })
  .catch(err => console.error(err));
}

// Eliminar factura
function eliminarFactura(idFact) {
  if (!confirm('¿Seguro que deseas eliminar esta factura?')) return;

  fetch(`../api/facturas.php?id=${idFact}`, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.msg);
      listarFacturas();
    } else {
      alert(data.error || 'No se pudo eliminar');
    }
  })
  .catch(err => console.error(err));
}
