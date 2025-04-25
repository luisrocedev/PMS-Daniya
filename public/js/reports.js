// Variables globales
let financialChart, occupancyChart, reservationsChart, bookingTrendsChart, avgStayChart;

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    loadInitialData();
}

// Configurar event listeners
function setupEventListeners() {
    // Periodo financiero
    document.getElementById('financial-period').addEventListener('change', updateFinancialChart);
    
    // Mes de ocupación
    document.getElementById('occupancy-month').addEventListener('change', updateOccupancyData);
    
    // Métrica de reservas
    document.getElementById('booking-metric').addEventListener('change', updateReservationsChart);
}

// Cargar datos iniciales
async function loadInitialData() {
    await Promise.all([
        loadStats(),
        updateFinancialChart(),
        updateOccupancyData(),
        updateReservationsChart()
    ]);
}

// Cargar estadísticas generales
async function loadStats() {
    try {
        const response = await fetch('../api/reportes_avanzados.php?action=stats_generales');
        const data = await response.json();
        
        updateStatCard('total-revenue', data.ingresos_mes, '€', true);
        updateStatCard('avg-occupancy', data.ocupacion_media, '%');
        updateStatCard('total-bookings', data.reservas_mes);
        
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// Actualizar tarjeta de estadística con animación
function updateStatCard(id, value, suffix = '', isCurrency = false) {
    const element = document.getElementById(id);
    const start = parseInt(element.textContent);
    const end = isCurrency ? parseFloat(value) : parseInt(value);
    
    animateValue(element, start, end, 1000, suffix, isCurrency);
}

// Animación de valores
function animateValue(element, start, end, duration, suffix = '', isCurrency = false) {
    const range = end - start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / range));
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (isCurrency) {
            element.textContent = current.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + suffix;
        } else {
            element.textContent = current + suffix;
        }
        
        if (current === end) {
            clearInterval(timer);
        }
    }, stepTime);
}

// Actualizar gráfico financiero
async function updateFinancialChart() {
    const period = document.getElementById('financial-period').value;
    try {
        const response = await fetch(`../api/reportes_avanzados.php?action=ingresos_${period}`);
        const data = await response.json();
        
        const ctx = document.getElementById('financialChart').getContext('2d');
        
        if (financialChart) {
            financialChart.destroy();
        }
        
        financialChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Ingresos',
                    data: data.valores,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Evolución de Ingresos'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value + '€'
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    } catch (error) {
        console.error('Error al actualizar gráfico financiero:', error);
    }
}

// Actualizar datos de ocupación
async function updateOccupancyData() {
    const month = document.getElementById('occupancy-month').value;
    try {
        const response = await fetch(`../api/reportes_avanzados.php?action=ocupacion&month=${month}`);
        const data = await response.json();
        
        updateOccupancyChart(data);
        updateRoomTypeStats(data.roomTypes);
        
    } catch (error) {
        console.error('Error al actualizar datos de ocupación:', error);
    }
}

// Actualizar gráfico de ocupación
function updateOccupancyChart(data) {
    const options = {
        chart: {
            type: 'area',
            height: 350,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [{
            name: 'Ocupación',
            data: data.ocupacion
        }],
        xaxis: {
            categories: data.dates
        },
        yaxis: {
            labels: {
                formatter: value => value + '%'
            }
        },
        colors: ['#2196F3'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2
            }
        }
    };

    if (occupancyChart) {
        occupancyChart.updateOptions(options);
    } else {
        occupancyChart = new ApexCharts(document.getElementById('occupancyChart'), options);
        occupancyChart.render();
    }
}

// Actualizar estadísticas por tipo de habitación
function updateRoomTypeStats(data) {
    const tbody = document.getElementById('room-type-stats');
    tbody.innerHTML = data.map(type => `
        <tr>
            <td>${type.nombre}</td>
            <td>${type.disponibles}</td>
            <td>${type.ocupadas}</td>
            <td>
                <div class="metric-badge ${getOccupancyClass(type.porcentaje)}">
                    ${type.porcentaje}%
                </div>
            </td>
            <td>${type.ingreso_medio}€</td>
        </tr>
    `).join('');
}

// Actualizar gráfico de reservas
async function updateReservationsChart() {
    const metric = document.getElementById('booking-metric').value;
    try {
        const response = await fetch(`../api/reportes_avanzados.php?action=reservas&metric=${metric}`);
        const data = await response.json();
        
        const options = {
            chart: {
                type: 'donut',
                height: 350
            },
            series: data.valores,
            labels: data.labels,
            colors: ['#4CAF50', '#FFC107', '#F44336', '#2196F3'],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        if (reservationsChart) {
            reservationsChart.updateOptions(options);
        } else {
            reservationsChart = new ApexCharts(document.getElementById('reservationsChart'), options);
            reservationsChart.render();
        }
        
        updateBookingTrends(data.trends);
        updateAverageStay(data.avgStay);
        
    } catch (error) {
        console.error('Error al actualizar gráfico de reservas:', error);
    }
}

// Actualizar tendencias de reservas
function updateBookingTrends(data) {
    const ctx = document.getElementById('bookingTrendsChart').getContext('2d');
    
    if (bookingTrendsChart) {
        bookingTrendsChart.destroy();
    }
    
    bookingTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Tendencia de Reservas',
                data: data.valores,
                borderColor: '#673AB7',
                backgroundColor: 'rgba(103, 58, 183, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Actualizar gráfico de estancia media
function updateAverageStay(data) {
    const ctx = document.getElementById('avgStayChart').getContext('2d');
    
    if (avgStayChart) {
        avgStayChart.destroy();
    }
    
    avgStayChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Días de Estancia',
                data: data.valores,
                backgroundColor: '#FF9800',
                borderColor: '#F57C00',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Exportar reportes
function exportReport(type, format) {
    const params = new URLSearchParams();
    
    switch (type) {
        case 'financial':
            params.set('period', document.getElementById('financial-period').value);
            break;
        case 'occupancy':
            params.set('month', document.getElementById('occupancy-month').value);
            break;
        case 'reservations':
            params.set('metric', document.getElementById('booking-metric').value);
            break;
    }
    
    window.open(`../api/reportes_avanzados.php?action=export_${type}&format=${format}&${params.toString()}`, '_blank');
}

// Función auxiliar para determinar clase de ocupación
function getOccupancyClass(percentage) {
    if (percentage >= 80) return 'positive';
    if (percentage >= 50) return 'neutral';
    return 'negative';
}