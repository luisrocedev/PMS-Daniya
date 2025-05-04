// reportes_avanzados.js
// Variables globales para el tipo de reporte activo
let reporteActual = 'ingresos';
let chartInstance = null;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    cargarReporte('ingresos');
    inicializarNavegacion();
});

// Función para gestionar la navegación entre páginas
function inicializarNavegacion() {
    // Esta función está diseñada para ser compatible con la navegación
    // No necesitamos botones prev/next porque ahora usamos tabs y botones directos
    console.log('Sistema de navegación de reportes inicializado');
}

// Función para mostrar una página específica
function showReportPage(pageNum) {
    const pages = document.querySelectorAll('#reportes-avanzados-pages .content-page');
    pages.forEach(page => page.classList.remove('active'));
    
    const targetPage = document.querySelector(`#reportes-avanzados-pages .content-page[data-page="${pageNum}"]`);
    if (targetPage) {
        targetPage.classList.add('active');
    }
}

// Carga un tipo de reporte específico
function cargarReporte(tipo) {
    reporteActual = tipo || 'ingresos';
    
    // Actualizar el título según el tipo de reporte
    const titulos = {
        'ingresos': 'Ingresos Mensuales',
        'ocupacion': 'Ocupación por Tipo de Habitación',
        'clientes': 'Análisis de Perfil de Clientes'
    };
    
    const tituloEl = document.getElementById('reporteTitulo');
    if (tituloEl) {
        tituloEl.textContent = titulos[reporteActual] || 'Reporte';
    }
    
    // Cargar los datos según el tipo seleccionado
    const yearInput = document.getElementById('yearInput');
    if (!yearInput.value) {
        yearInput.value = new Date().getFullYear();
    }
    
    aplicarFiltros();
}

// Aplica los filtros actuales y recarga datos
function aplicarFiltros() {
    const year = document.getElementById('yearInput').value;
    const periodo = document.getElementById('periodoSelect').value;
    
    let url = `../api/reportes_avanzados.php?action=${reporteActual}&year=${year}&periodo=${periodo}`;
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                alert('Error: ' + data.error);
                return;
            }
            
            procesarDatosReporte(data, reporteActual);
        })
        .catch(err => {
            console.error('Error en cargarReporte:', err);
            alert('Error al cargar los datos del reporte. Por favor, revisa la consola para más detalles.');
        });
}

// Procesa los datos según el tipo de reporte
function procesarDatosReporte(data, tipo) {
    const tbody = document.getElementById('tablaIngresos');
    if (!tbody) {
        console.error('No se encontró el elemento tablaIngresos');
        return;
    }

    tbody.innerHTML = '';
    let labels = [];
    let valores = [];
    let variaciones = [];
    
    // Asegurarnos de que data es un array
    const ingresos = Array.isArray(data) ? data : [];
    let valorAnterior = null;
    
    ingresos.forEach((obj, index) => {
        const periodo = obj.mes || obj.periodo || index + 1;
        const total = parseFloat(obj.total_mes || obj.total || 0);
        
        // Calcular variación porcentual
        let variacion = 0;
        if (valorAnterior !== null && valorAnterior !== 0) {
            variacion = ((total - valorAnterior) / valorAnterior) * 100;
        }
        valorAnterior = total;
        
        // Convertir número de mes/periodo a nombre según el tipo
        let nombrePeriodo = '';
        if (tipo === 'ingresos') {
            nombrePeriodo = new Date(2025, periodo - 1, 1).toLocaleDateString('es-ES', { month: 'long' });
        } else {
            nombrePeriodo = `Periodo ${periodo}`;
        }
        
        labels.push(nombrePeriodo);
        valores.push(total);
        variaciones.push(variacion);
        
        // Crear fila en la tabla
        const tr = document.createElement('tr');
        let claseVariacion = variacion > 0 ? 'text-success' : (variacion < 0 ? 'text-danger' : '');
        let iconoVariacion = variacion > 0 ? '<i class="fas fa-arrow-up"></i>' : (variacion < 0 ? '<i class="fas fa-arrow-down"></i>' : '');
        
        tr.innerHTML = `
            <td>${nombrePeriodo}</td>
            <td class="text-end">${total.toFixed(2)} €</td>
            <td class="text-end ${claseVariacion}">
                ${index > 0 ? iconoVariacion + ' ' + Math.abs(variacion).toFixed(2) + '%' : '-'}
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    actualizarChart(labels, valores, tipo);
}

// Actualiza o crea el gráfico con los datos cargados
function actualizarChart(labels, valores, tipo) {
    const ctx = document.getElementById('chartIngresos');
    if (!ctx) {
        console.error('No se encontró el elemento chartIngresos');
        return;
    }
    
    // Configurar opciones según tipo de reporte
    const configuraciones = {
        'ingresos': {
            titulo: `Ingresos Mensuales (${document.getElementById('yearInput').value})`,
            color: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgba(59, 130, 246, 1)',
            tipo: 'bar'
        },
        'ocupacion': {
            titulo: 'Ocupación por Tipo de Habitación',
            color: 'rgba(16, 185, 129, 0.5)',
            borderColor: 'rgba(16, 185, 129, 1)',
            tipo: 'line'
        },
        'clientes': {
            titulo: 'Análisis de Clientes',
            color: 'rgba(236, 72, 153, 0.5)',
            borderColor: 'rgba(236, 72, 153, 1)',
            tipo: 'bar'
        }
    };
    
    const config = configuraciones[tipo] || configuraciones.ingresos;
    
    // Si ya existe un gráfico, destruirlo
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    // Crear nuevo gráfico
    chartInstance = new Chart(ctx, {
        type: config.tipo,
        data: {
            labels: labels,
            datasets: [{
                label: config.titulo,
                data: valores,
                backgroundColor: config.color,
                borderColor: config.borderColor,
                borderWidth: 1,
                tension: config.tipo === 'line' ? 0.3 : 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (tipo === 'ocupacion') {
                                return value + '%';
                            } else {
                                return value.toFixed(2) + ' €';
                            }
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (tipo === 'ocupacion') {
                                return context.parsed.y.toFixed(2) + '%';
                            } else {
                                return context.parsed.y.toFixed(2) + ' €';
                            }
                        }
                    }
                }
            }
        }
    });
}

// Funciones para exportar reportes
function exportarCSV() {
    const year = document.getElementById('yearInput').value;
    const periodo = document.getElementById('periodoSelect').value;
    window.open(`../api/reportes_avanzados.php?action=${reporteActual}&year=${year}&periodo=${periodo}&export=csv`, '_blank');
}
  
function exportarPDF() {
    const year = document.getElementById('yearInput').value;
    const periodo = document.getElementById('periodoSelect').value;
    window.open(`../api/reportes_avanzados.php?action=${reporteActual}&year=${year}&periodo=${periodo}&export=pdf`, '_blank');
}
  
function exportarXLSX() {
    const year = document.getElementById('yearInput').value;
    const periodo = document.getElementById('periodoSelect').value;
    window.open(`../api/reportes_avanzados.php?action=${reporteActual}&year=${year}&periodo=${periodo}&export=xlsx`, '_blank');
}
