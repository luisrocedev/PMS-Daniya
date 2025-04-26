// reportes_avanzados.js
function cargarReporte() {
    const yearInput = document.getElementById('yearInput');
    // Establecer año actual por defecto si no hay valor
    if (!yearInput.value) {
        yearInput.value = new Date().getFullYear();
    }
    const year = yearInput.value;
    const url = `../api/reportes_avanzados.php?action=ingresos_mensuales&year=${year}`;
  
    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          console.error('Error:', data.error);
          alert('Error: ' + data.error);
          return;
        }
        
        const tbody = document.getElementById('tablaIngresos');
        if (!tbody) {
            console.error('No se encontró el elemento tablaIngresos');
            return;
        }

        tbody.innerHTML = '';
        let labels = [];
        let valores = [];

        // Asegurarnos de que data es un array
        const ingresos = Array.isArray(data) ? data : [];
        
        ingresos.forEach(obj => {
            const mes = obj.mes;
            const total = parseFloat(obj.total_mes) || 0;
            
            // Convertir número de mes a nombre
            const nombreMes = new Date(2025, mes - 1, 1).toLocaleDateString('es-ES', { month: 'long' });
            
            labels.push(nombreMes);
            valores.push(total);
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${nombreMes}</td>
                <td class="text-end">${total.toFixed(2)} €</td>
            `;
            tbody.appendChild(tr);
        });

        actualizarChart(labels, valores, year);
      })
      .catch(err => {
        console.error('Error en cargarReporte:', err);
        alert('Error al cargar los datos del reporte. Por favor, revisa la consola para más detalles.');
      });
}

function actualizarChart(labels, valores, year) {
    const ctx = document.getElementById('chartIngresos');
    if (!ctx) {
        console.error('No se encontró el elemento chartIngresos');
        return;
    }

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
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toFixed(2) + ' €';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(2) + ' €';
                        }
                    }
                }
            }
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
