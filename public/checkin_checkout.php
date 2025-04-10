<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Check-in/Check-out - Daniya Denia</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <!-- Incluir navbar -->
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div style="display:flex; margin-top:1rem;">
    <!-- Incluir sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content">
      <h2 class="page-title">Check-in / Check-out</h2>

      <!-- Sección de RESERVAS PENDIENTES DE CHECK-IN (estado_reserva = Confirmada) -->
      <div class="card">
        <h3>Reservas pendientes de Check-in</h3>
        <table>
          <thead>
            <tr>
              <th>ID Reserva</th>
              <th>Cliente</th>
              <th>Habitación</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tabla-checkin">
            <!-- Se llenará mediante JS con los datos de la BD (reservas confirmadas) -->
          </tbody>
        </table>
      </div>

      <!-- Sección de RESERVAS PENDIENTES DE CHECK-OUT (estado_reserva = CheckIn) -->
      <div class="card">
        <h3>Reservas pendientes de Check-out</h3>
        <table>
          <thead>
            <tr>
              <th>ID Reserva</th>
              <th>Cliente</th>
              <th>Habitación</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tabla-checkout">
            <!-- Se llenará mediante JS con los datos de la BD (reservas en CheckIn) -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- MODAL para realizar Check-in con DNI y firma -->
  <div id="modalCheckin" class="modal" style="display:none;">
    <div class="modal-content">
      <h3>Check-in: Datos de Documento y Firma</h3>
      <form id="formCheckinModal">
        <!-- Campo oculto para la reserva en la que estamos haciendo check-in -->
        <input type="hidden" name="id_reserva" id="id_reserva_modal" value="">

        <label for="dni_modal">Nº Documento (DNI/Pasaporte):</label>
        <input type="text" name="dni" id="dni_modal" required>

        <label for="firma_modal">Firma (texto):</label>
        <input type="text" name="firma" id="firma_modal" required>

        <div style="margin-top:1rem;">
          <button class="btn" type="submit">Realizar Check-in</button>
          <button class="btn" type="button" onclick="cerrarModalCheckin()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // =========================================================
    // 1. Cargar reservas pendientes de check-in/check-out
    // =========================================================
    document.addEventListener('DOMContentLoaded', () => {
      cargarCheckInOut();
    });

    // Función que pide datos a "../api/checkinout.php" (GET) 
    // y rellena tabla-checkin y tabla-checkout
    function cargarCheckInOut() {
      fetch('../api/checkinout.php')
        .then(response => response.json())
        .then(data => {
          // data.pendientesCheckIn, data.pendientesCheckOut => 
          // (depende de cómo implementaste la respuesta en checkinout.php)
          // Suponiendo que devuelva un objeto con arrays:
          // { pendientesCheckIn: [...], pendientesCheckOut: [...] }

          const pendientesCheckIn = data.pendientesCheckIn || [];
          const pendientesCheckOut = data.pendientesCheckOut || [];

          // Llenar tabla-checkin
          const tbodyCheckin = document.getElementById('tabla-checkin');
          tbodyCheckin.innerHTML = '';
          pendientesCheckIn.forEach(res => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td>${res.id_reserva}</td>
          <td>${res.nombre_cliente} ${res.apellidos_cliente}</td>
          <td>${res.id_habitacion}</td>
          <td>
            <button class="btn" onclick="abrirModalCheckin(${res.id_reserva})">Check-in</button>
          </td>
        `;
            tbodyCheckin.appendChild(tr);
          });

          // Llenar tabla-checkout
          const tbodyCheckout = document.getElementById('tabla-checkout');
          tbodyCheckout.innerHTML = '';
          pendientesCheckOut.forEach(res => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td>${res.id_reserva}</td>
          <td>${res.nombre_cliente} ${res.apellidos_cliente}</td>
          <td>${res.id_habitacion}</td>
          <td>
            <button class="btn" onclick="hacerCheckOut(${res.id_reserva})">Check-out</button>
          </td>
        `;
            tbodyCheckout.appendChild(tr);
          });
        })
        .catch(err => console.error('Error al cargar checkInOut:', err));
    }

    // =========================================================
    // 2. Abrir/Cerrar el modal de Check-in
    // =========================================================
    function abrirModalCheckin(idReserva) {
      const modal = document.getElementById('modalCheckin');
      modal.style.display = 'block';
      // Asignamos el id_reserva al hidden del formulario
      document.getElementById('id_reserva_modal').value = idReserva;
    }

    function cerrarModalCheckin() {
      document.getElementById('modalCheckin').style.display = 'none';
      document.getElementById('id_reserva_modal').value = '';
      document.getElementById('dni_modal').value = '';
      document.getElementById('firma_modal').value = '';
    }

    // =========================================================
    // 3. Al enviar el formulario del modal => 
    //    Subir DNI y Firma a checkin_uploads, luego hacer checkin
    // =========================================================
    const formCheckinModal = document.getElementById('formCheckinModal');
    formCheckinModal.addEventListener('submit', (e) => {
      e.preventDefault();
      const id_reserva = document.getElementById('id_reserva_modal').value;
      const dni = document.getElementById('dni_modal').value;
      const firma = document.getElementById('firma_modal').value;

      // 3.1. Primero, enviamos a checkin_uploads.php
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
            // 3.2. Luego, actualizamos la reserva a estado=CheckIn (en checkinout.php)
            hacerCheckIn(id_reserva);
          } else {
            alert(data.error || 'Error al subir datos de check-in');
          }
        })
        .catch(err => console.error(err));
    });

    // =========================================================
    // 4. Hacer CheckIn => se llama a checkinout con action=checkin
    // =========================================================
    function hacerCheckIn(idReserva) {
      const form = new FormData();
      form.append('action', 'checkin');
      form.append('id_reserva', idReserva);

      fetch('../api/checkinout.php', {
          method: 'POST',
          body: form
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert(data.msg || 'Check-in realizado');
            // Cerrar modal
            cerrarModalCheckin();
            // Recargar tablas
            cargarCheckInOut();
          } else {
            alert(data.error || 'Error en check-in');
          }
        })
        .catch(err => console.error(err));
    }

    // =========================================================
    // 5. Hacer CheckOut => ya existente
    // =========================================================
    function hacerCheckOut(idReserva) {
      const form = new FormData();
      form.append('action', 'checkout');
      form.append('id_reserva', idReserva);

      fetch('../api/checkinout.php', {
          method: 'POST',
          body: form
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert(data.msg || 'Check-out realizado');
            cargarCheckInOut();
          } else {
            alert(data.error || 'Error al hacer check-out');
          }
        })
        .catch(err => console.error(err));
    }
  </script>

  <!-- Estilos para el modal, ajústalo a tu style.css si prefieres -->
  <style>
    .modal {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 9999;
    }

    .modal-content {
      background: #fff;
      width: 400px;
      margin: 10% auto;
      padding: 1.5rem;
      border-radius: 8px;
      position: relative;
    }
  </style>

</body>

</html>