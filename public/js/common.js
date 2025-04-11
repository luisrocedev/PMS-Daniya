// common.js
document.addEventListener('DOMContentLoaded', () => {
    // Si se detectan elementos con id específicos, se ejecutan las funciones correspondientes.
    if (document.getElementById("tabla-empleados")) {
      listarEmpleados();
    }
    if (document.getElementById("tabla-habitaciones")) {
      listarHabitaciones();
    }
    if (document.getElementById("total_habs")) {
      actualizarOcupacion();
    }
    if (document.getElementById("tabla-reservas")) {
      listarReservas();
    }
    if (document.getElementById("tabla-clientes")) {
      listarClientes();
    }
    if (document.getElementById("tabla-fact")) {
      listarFacturas();
    }
    if (document.getElementById("chartIngresos")) {
      cargarReporte();
    }
  });
  