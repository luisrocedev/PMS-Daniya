// Variables globales
let financialChart, occupancyChart, reservationsChart, bookingTrendsChart, avgStayChart;

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    loadInitialData();
    initializeReportsPageNav(); // Paginación interna de reportes
}

// Configurar event listeners
function setupEventListeners() {
    // Periodo financiero
    document.getElementById('financial-period').addEventListener('change', updateFinancialChart);
    
    // Mes de ocupación
    document.getElementById('occupancy-month').addEventListener('change', updateOccupancyData);
    
    // Métrica de reservas
    document.getElementById('booking-metric').addEventListener('change', updateReservationsChart);

    // Inicializar valor del mes actual
    const currentDate = new Date();
    document.getElementById('occupancy-month').value = 
        `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}`;
}

// Cargar datos iniciales
async function loadInitialData() {
    try {
        await Promise.all([
            loadStats(),
            updateFinancialChart(),
            updateOccupancyData(),
            updateReservationsChart()
        ]);
    } catch (error) {
        console.error('Error al cargar datos iniciales:', error);
    }
}

// Cargar estadísticas generales
async function loadStats() {
    try {
        const response = await fetch('../api/reportes_avanzados.php?action=stats_generales');
        const data = await response.json();
        console.log('Respuesta de stats_generales:', data); // Log para depuración
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
    if (!element) return;

    let start = parseInt(element.textContent);
    let end = isCurrency ? parseFloat(value) : parseInt(value);
    // Validar que los valores sean finitos y no negativos
    if (!isFinite(start) || start < 0) start = 0;
    if (!isFinite(end) || end < 0) end = 0;
    animateValue(element, start, end, 1000, suffix, isCurrency);
}

// Animación de valores
function animateValue(element, start, end, duration, suffix = '', isCurrency = false) {
    // Validar que los valores sean finitos y no negativos
    if (!isFinite(start) || start < 0) start = 0;
    if (!isFinite(end) || end < 0) end = 0;
    if (start === end) {
        element.textContent = isCurrency
            ? end.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + suffix
            : end + suffix;
        return;
    }
    const range = end - start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / Math.max(Math.abs(range), 1)));
    let current = start;
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current > end) || (increment < 0 && current < end)) current = end;
        if (isCurrency) {
            element.textContent = current.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + suffix;
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
        
        // Validar que data.valores sea un array
        const valores = Array.isArray(data.valores) ? data.valores.map(v => (isFinite(v) && v >= 0 ? Number(v) : 0)) : [];
        const labels = Array.isArray(data.labels) ? data.labels : [];
        
        financialChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos',
                    data: valores,
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
    // Validar que data.ocupacion y data.dates sean arrays
    const ocupacion = Array.isArray(data.ocupacion) ? data.ocupacion.map(v => (isFinite(v) && v >= 0 ? Number(v) : 0)) : [];
    const dates = Array.isArray(data.dates) ? data.dates : [];
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
            data: ocupacion
        }],
        xaxis: {
            categories: dates
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
    if (!Array.isArray(data)) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Sin datos disponibles</td></tr>';
        return;
    }
    tbody.innerHTML = data.map(type => `
        <tr class="animate-fade-in">
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
    if (!data || !Array.isArray(data.labels) || !Array.isArray(data.valores)) {
        if (typeof bookingTrendsChart !== 'undefined' && bookingTrendsChart) bookingTrendsChart.destroy();
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        return;
    }
    // Validar datos para evitar valores infinitos o negativos
    const safeLabels = data.labels;
    const safeValores = data.valores.map(v => (isFinite(v) && v >= 0 ? v : 0));
    if (bookingTrendsChart) bookingTrendsChart.destroy();
    bookingTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: safeLabels,
            datasets: [{
                label: 'Tendencia de Reservas',
                data: safeValores,
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
    if (!data || !Array.isArray(data.labels) || !Array.isArray(data.valores)) {
        if (typeof avgStayChart !== 'undefined' && avgStayChart) avgStayChart.destroy();
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        return;
    }
    if (avgStayChart) avgStayChart.destroy();
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
        case 'all':
            // No necesita parámetros adicionales
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

// Función de paginación interna para la sección de reportes
function initializeReportsPageNav() {
    const pages = document.querySelectorAll('#reports-pages .report-page');
    const prevBtn = document.getElementById('prevRep');
    const nextBtn = document.getElementById('nextRep');
    const currentPageEl = document.getElementById('currentRepPage');
    const totalPagesEl = document.getElementById('totalRepPages');
    let current = 0;

    // Establecer el total de páginas
    if (totalPagesEl) totalPagesEl.textContent = pages.length;

    function updateButtons() {
        prevBtn.disabled = current === 0;
        nextBtn.disabled = current === pages.length - 1;
        if (currentPageEl) currentPageEl.textContent = current + 1;
    }

    function showPage(index) {
        pages[current].classList.remove('active');
        current = index;
        pages[current].classList.add('active');
        updateButtons();
    }

    prevBtn.addEventListener('click', () => { if (current > 0) showPage(current - 1); });
    nextBtn.addEventListener('click', () => { if (current < pages.length - 1) showPage(current + 1); });

    updateButtons();
}