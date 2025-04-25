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

    occupancyCounter = new CountUp('occupancyRate', 0, 0, 1, 2, {...options, suffix: '%'});
    revenueCounter = new CountUp('revenue', 0, 0, 2, 2, {...options, prefix: '€'});
    bookingsCounter = new CountUp('bookings', 0, 0, 0, 2, options);
    checkinsCounter = new CountUp('checkins', 0, 0, 0, 2, options);
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
    const options = {
        series: [{
            name: 'Ingresos',
            data: data.valores
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
            categories: data.fechas,
            type: 'datetime'
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

    if (!revenueChart) {
        revenueChart = new ApexCharts(document.querySelector("#revenueChart"), options);
        revenueChart.render();
    } else {
        revenueChart.updateOptions(options);
    }
}

function updateRevenueStats(data) {
    const total = data.valores.reduce((a, b) => a + b, 0);
    revenueCounter.update(total);
    
    const trend = document.getElementById('revenueTrend');
    const lastDayChange = ((data.valores[data.valores.length - 1] - data.valores[data.valores.length - 2]) / data.valores[data.valores.length - 2] * 100).toFixed(1);
    
    if (lastDayChange > 0) {
        trend.className = 'trend-indicator trend-up';
        trend.innerHTML = `<i class="fas fa-arrow-up"></i> ${lastDayChange}%`;
    } else if (lastDayChange < 0) {
        trend.className = 'trend-indicator trend-down';
        trend.innerHTML = `<i class="fas fa-arrow-down"></i> ${Math.abs(lastDayChange)}%`;
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
    
    data.slice(0, 5).forEach(checkin => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${checkin.hora}</td>
            <td>${checkin.cliente}</td>
            <td>${checkin.habitacion}</td>
            <td><span class="badge ${checkin.estado === 'Pendiente' ? 'bg-warning' : 'bg-success'}">${checkin.estado}</span></td>
        `;
        tbody.appendChild(tr);
    });
}