// reportes_avanzados.js
function cargarReporte() {
    const year = document.getElementById('yearInput').value;
    const url = `../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}`;
  
    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          alert(data.error);
          return;
        }
        const ingresos = Array.isArray(data) ? data : (data.data || []);
        const tbody = document.getElementById('tablaIngresos');
        if (tbody && Array.isArray(ingresos)) {
          tbody.innerHTML = '';
          let labels = [];
          let valores = [];
          ingresos.forEach(obj => {
            labels.push('Mes ' + obj.mes);
            valores.push(obj.total_mes);
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${obj.mes}</td><td>${obj.total_mes}</td>`;
            tbody.appendChild(tr);
          });
          actualizarChart(labels, valores, year);
        } else {
          console.error("cargarReporte: Formato inesperado", data);
        }
      })
      .catch(err => console.error('Error en cargarReporte:', err));
  }
  
  function actualizarChart(labels, valores, year) {
    const ctx = document.getElementById('chartIngresos').getContext('2d');
    if (window.chartIngresos instanceof Chart) {
      window.chartIngresos.destroy();
    }
    window.chartIngresos = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: `Ingresos (Año ${year})`,
          data: valores,
          backgroundColor: 'rgba(59, 130, 246, 0.5)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });
  }
  
  function exportarCSV() {
    const year = document.getElementById('yearInput').value;
    window.open(`../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}&export=csv`, '_blank');
  }
  
  function exportarPDF() {
    const year = document.getElementById('yearInput').value;
    window.open(`../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}&export=pdf`, '_blank');
  }
  
  function exportarXLSX() {
    const year = document.getElementById('yearInput').value;
    window.open(`../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}&export=xlsx`, '_blank');
  }
  