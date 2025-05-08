// Variables globales
let occupancyCounter, revenueCounter, bookingsCounter, checkinsCounter;
let occupancyChart, revenueChart;

// Función que se ejecuta cuando el documento está listo
document.addEventListener('DOMContentLoaded', () => {
    initDashboard();
    initializeCounters();
    actualizarDashboard();
    setInterval(actualizarDashboard, 300000);
});

// Inicializar el dashboard
function initDashboard() {
    // Inicializar el gráfico de ocupación
    const ctx = document.getElementById('occupancyChart').getContext('2d');
    occupancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Ocupación %',
                data: [],
                borderColor: '#3f8efc',
                backgroundColor: 'rgba(63, 142, 252, 0.1)',
                tension: 0.4,
                fill: true
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
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: value => value + '%'
                    }
                }
            }
        }
    });

    // Agregar listeners a los elementos interactivos
    document.querySelector('.task-list').addEventListener('click', (e) => {
        if (e.target.classList.contains('task-checkbox')) {
            toggleTask(e.target);
        }
    });
}

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

// Actualizar datos del dashboard
async function actualizarDashboard() {
    try {
        const response = await fetch(`${window.location.origin}/GitHub/PMS-Daniya/api/dashboard_data.php`);
        const data = await response.json();

        if (data.success) {
            actualizarEstadisticas(data.stats);
            actualizarOcupacion(data.occupancy);
            actualizarKPIs(data.kpis);
            actualizarActividad(data.activity);
            actualizarTareas(data.tasks);
        }
    } catch (error) {
        console.error('Error al actualizar el dashboard:', error);
    }
}

// Actualizar estadísticas generales
function actualizarEstadisticas(stats) {
    document.getElementById('habitacionesDisponibles').textContent = stats.habitaciones_disponibles;
    document.getElementById('reservasHoy').textContent = stats.reservas_hoy;
    document.getElementById('ocupacion').textContent = stats.ocupacion + '%';
    document.getElementById('ingresosDiarios').textContent = stats.ingresos_diarios + '€';
    document.getElementById('trendHabitaciones').textContent = stats.trend_habitaciones;
}

// Actualizar datos de ocupación para el gráfico
function actualizarOcupacion(occupancyData) {
    occupancyChart.data.datasets[0].data = occupancyData;
    occupancyChart.update();
}

// Actualizar KPIs
function actualizarKPIs(kpis) {
    document.getElementById('revpar').textContent = kpis.revpar + '€';
    document.getElementById('adr').textContent = kpis.adr + '€';
    document.getElementById('avgStay').textContent = kpis.avg_stay + ' días';
}

// Actualizar lista de actividad reciente
function actualizarActividad(activities) {
    const activityList = document.getElementById('activityList');
    activityList.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="fas ${activity.icon}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">${activity.title}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        </div>
    `).join('');
}

// Actualizar lista de tareas
function actualizarTareas(tasks) {
    const taskList = document.getElementById('taskList');
    taskList.innerHTML = tasks.map(task => `
        <div class="task-item">
            <div class="task-checkbox" data-task-id="${task.id}" ${task.completed ? 'checked' : ''}></div>
            <div class="task-content">
                <div class="task-title">${task.title}</div>
                <div class="task-due">Vence: ${task.due_date}</div>
            </div>
        </div>
    `).join('');
}

// Toggle estado de una tarea
async function toggleTask(checkbox) {
    const taskId = checkbox.dataset.taskId;
    checkbox.classList.toggle('checked');

    try {
        await fetch('../api/toggle_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ taskId })
        });
    } catch (error) {
        console.error('Error al actualizar la tarea:', error);
        checkbox.classList.toggle('checked'); // Revertir el cambio visual
    }
}

function initializePageNav() {
    const pages = document.querySelectorAll('.dashboard-page');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const currentPageEl = document.getElementById('currentPage');
    const totalPagesEl = document.getElementById('totalPages');
    
    // Verificar que los elementos existan antes de proceder
    if (!prevBtn || !nextBtn || pages.length === 0) {
        // Si no existen los elementos necesarios, salir de la función
        return;
    }
    
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