// ocupacion.js
function actualizarOcupacion() {
    fetch('../api/ocupacion.php')
      .then(res => res.json())
      .then(data => {
        if (document.getElementById('total_habs')) {
          document.getElementById('total_habs').textContent = data.total;
        }
        if (document.getElementById('ocupadas_habs')) {
          document.getElementById('ocupadas_habs').textContent = data.ocupadas;
        }
        if (document.getElementById('mantenimiento_habs')) {
          document.getElementById('mantenimiento_habs').textContent = data.mantenimiento;
        }
        if (document.getElementById('disponibles_habs')) {
          document.getElementById('disponibles_habs').textContent = data.disponibles;
        }
      })
      .catch(err => console.error('Error en actualizarOcupacion:', err));
  }
  