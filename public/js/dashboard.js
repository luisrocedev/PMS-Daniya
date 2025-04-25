// Inicialización de contadores animados
let occupancyCounter, revenueCounter, bookingsCounter, checkinsCounter;
let occupancyChart, revenueChart;

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar contadores
    initializeCounters();
    
    // Cargar datos iniciales
    loadDashboardData();
    
    // Actualizar datos cada 5 minutos
    setInterval(loadDashboardData, 300000);
});

function initializeCounters() {
    const options = {
        duration: 2,
        useEasing: true,
        useGrouping: true,
        decimal: ',',
        prefix: '',
        suffix: ''
    };

    // Usar la versión UMD de CountUp correctamente
    occupancyCounter = new countUp.CountUp('occupancyRate', 0, {...options, suffix: '%'});
    revenueCounter = new countUp.CountUp('revenue', 0, {...options, prefix: '€'});
    bookingsCounter = new countUp.CountUp('bookings', 0, options);
    checkinsCounter = new countUp.CountUp('checkins', 0, options);

    // Iniciar los contadores
    occupancyCounter.start();
    revenueCounter.start();
    bookingsCounter.start();
    checkinsCounter.start();
}

function loadDashboardData() {
    // Cargar datos de ocupación
    fetch('../api/ocupacion.php')
        .then(res => res.json())
        .then(data => {
            updateOccupancyStats(data);
            updateOccupancyChart(data);
        });

    // Cargar datos de ingresos diarios
    fetch('../api/reportes_avanzados.php?action=ingresos_diarios')
        .then(res => res.json())
        .then(data => {
            updateRevenueStats(data);
            updateRevenueChart(data);
        });

    // Cargar datos de reservas activas
    fetch('../api/reservas.php?estado=activas')
        .then(res => res.json())
        .then(data => {
            updateBookingStats(data);
        });

    // Cargar próximos check-ins
    fetch('../api/checkins_hoy.php')
        .then(res => res.json())
        .then(data => {
            updateCheckinStats(data);
            updateUpcomingCheckins(data);
        });
}

function updateOccupancyStats(data) {
    const total = data.ocupadas + data.mantenimiento + data.disponibles;
    const rate = ((data.ocupadas / total) * 100).toFixed(1);
    occupancyCounter.update(rate);
    
    // Actualizar tendencia
    const trend = document.getElementById('occupancyTrend');
    if (data.tendencia > 0) {
        trend.className = 'trend-indicator trend-up';
        trend.innerHTML = `<i class="fas fa-arrow-up"></i> ${data.tendencia}%`;
    } else if (data.tendencia < 0) {
        trend.className = 'trend-indicator trend-down';
        trend.innerHTML = `<i class="fas fa-arrow-down"></i> ${Math.abs(data.tendencia)}%`;
    }
}

function updateOccupancyChart(data) {
    const ctx = document.getElementById('occupancyChart').getContext('2d');
    
    if (occupancyChart) {
        occupancyChart.destroy();
    }

    occupancyChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Ocupadas', 'En Mantenimiento', 'Disponibles'],
            datasets: [{
                data: [data.ocupadas, data.mantenimiento, data.disponibles],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(16, 185, 129, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateRevenueChart(data) {
    // Validar que los datos existan y sean arrays
    if (!data || !Array.isArray(data.valores) || !Array.isArray(data.labels)) {
        console.error('Datos de ingresos no válidos para el gráfico');
        return;
    }

    // Asegurarse de que tenemos datos válidos
    const valores = data.valores.map(v => Number(v) || 0);
    const fechas = data.labels || [];

    const options = {
        series: [{
            name: 'Ingresos',
            data: valores
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: fechas,
            type: 'category' // Cambiado de 'datetime' a 'category'
        },
        yaxis: {
            labels: {
                formatter: (val) => `€${val}`
            }
        },
        tooltip: {
            y: {
                formatter: (val) => `€${val}`
            }
        },
        theme: {
            mode: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'
        }
    };

    try {
        if (!revenueChart) {
            revenueChart = new ApexCharts(document.querySelector("#revenueChart"), options);
            revenueChart.render();
        } else {
            revenueChart.updateOptions(options);
        }
    } catch (error) {
        console.error('Error al actualizar el gráfico de ingresos:', error);
    }
}

function updateRevenueStats(data) {
    // Asegurarnos de que data y data.valores existen
    if (!data || !data.valores) {
        console.error('No se recibieron datos de ingresos válidos');
        return;
    }

    const total = Array.isArray(data.valores) ? data.valores.reduce((a, b) => a + (Number(b) || 0), 0) : 0;
    revenueCounter.update(total);
    
    const trend = document.getElementById('revenueTrend');
    if (Array.isArray(data.valores) && data.valores.length >= 2) {
        const lastDayChange = ((data.valores[data.valores.length - 1] - data.valores[data.valores.length - 2]) / data.valores[data.valores.length - 2] * 100).toFixed(1);
        
        if (lastDayChange > 0) {
            trend.className = 'trend-indicator trend-up';
            trend.innerHTML = `<i class="fas fa-arrow-up"></i> ${lastDayChange}%`;
        } else if (lastDayChange < 0) {
            trend.className = 'trend-indicator trend-down';
            trend.innerHTML = `<i class="fas fa-arrow-down"></i> ${Math.abs(lastDayChange)}%`;
        }
    } else {
        trend.innerHTML = '';
    }
}

function updateBookingStats(data) {
    bookingsCounter.update(data.total || 0);
    
    const trend = document.getElementById('bookingsTrend');
    if (data.tendencia > 0) {
        trend.className = 'trend-indicator trend-up';
        trend.innerHTML = `<i class="fas fa-arrow-up"></i> ${data.tendencia}`;
    } else if (data.tendencia < 0) {
        trend.className = 'trend-indicator trend-down';
        trend.innerHTML = `<i class="fas fa-arrow-down"></i> ${Math.abs(data.tendencia)}`;
    }
}

function updateCheckinStats(data) {
    checkinsCounter.update(data.length || 0);
}

function updateUpcomingCheckins(data) {
    const tbody = document.getElementById('upcomingCheckins');
    tbody.innerHTML = '';
    
    // Usar el array de proximasLlegadas del objeto data
    if (!data || !data.proximasLlegadas) {
        console.error('No se recibieron datos de check-ins válidos');
        return;
    }

    data.proximasLlegadas.forEach(checkin => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${new Date(checkin.fecha_entrada).toLocaleTimeString('es', {hour: '2-digit', minute:'2-digit'})}</td>
            <td>${checkin.nombre} ${checkin.apellidos}</td>
            <td>${checkin.numero_habitacion}</td>
            <td><span class="badge bg-warning">Pendiente</span></td>
        `;
        tbody.appendChild(tr);
    });
}