// habitaciones.js
// Cargar los datos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    listarHabitaciones();
    actualizarEstadisticas();
});

// Función para actualizar las estadísticas en las tarjetas
function actualizarEstadisticas() {
    fetch('../api/habitaciones.php')
        .then(response => response.json())
        .then(data => {
            const habitaciones = Array.isArray(data) ? data : (data.data || []);
            const stats = {
                disponibles: 0,
                ocupadas: 0,
                mantenimiento: 0,
                total: habitaciones.length
            };

            habitaciones.forEach(hab => {
                switch(hab.estado) {
                    case 'Disponible':
                        stats.disponibles++;
                        break;
                    case 'Ocupada':
                        stats.ocupadas++;
                        break;
                    case 'Mantenimiento':
                        stats.mantenimiento++;
                        break;
                }
            });

            // Actualizar los contadores con animación
            animateValue('disponibles', stats.disponibles);
            animateValue('ocupadas', stats.ocupadas);
            animateValue('mantenimiento', stats.mantenimiento);
            animateValue('total', stats.total);
        })
        .catch(err => console.error('Error al cargar estadísticas:', err));
}

// Función para animar los contadores
function animateValue(id, end) {
    const obj = document.getElementById(id);
    const start = parseInt(obj.innerHTML) || 0;
    const duration = 1000;
    const step = (end - start) / (duration / 16);
    
    let current = start;
    const animate = () => {
        current += step;
        if ((step > 0 && current >= end) || (step < 0 && current <= end)) {
            obj.innerHTML = end;
        } else {
            obj.innerHTML = Math.round(current);
            requestAnimationFrame(animate);
        }
    };
    animate();
}

// Función para listar habitaciones con filtros
function listarHabitaciones() {
    const search = document.getElementById('searchHab').value;
    const estado = document.getElementById('estadoHab').value;
    
    let url = '../api/habitaciones.php?';
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (estado) url += `estado=${encodeURIComponent(estado)}&`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const habitaciones = Array.isArray(data) ? data : (data.data || []);
            const tbody = document.getElementById('tabla-habitaciones');
            tbody.innerHTML = '';
            
            habitaciones.forEach(hab => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${hab.id_habitacion}</td>
                    <td>${hab.numero_habitacion}</td>
                    <td>${hab.tipo_habitacion}</td>
                    <td>${hab.capacidad}</td>
                    <td>${hab.piso}</td>
                    <td>
                        <span class="badge bg-${getEstadoColor(hab.estado)}">${hab.estado}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1" onclick="editarHabitacion(${hab.id_habitacion})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarHabitacion(${hab.id_habitacion})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error('Error en listarHabitaciones:', err));
}

// Función para obtener el color del badge según el estado
function getEstadoColor(estado) {
    switch(estado) {
        case 'Disponible': return 'success';
        case 'Ocupada': return 'primary';
        case 'Mantenimiento': return 'warning';
        default: return 'secondary';
    }
}

// Función para crear una nueva habitación
function crearHabitacion() {
    const formData = {
        numero_habitacion: document.getElementById('numHab').value,
        tipo_habitacion: document.getElementById('tipoHab').value,
        capacidad: document.getElementById('capHab').value,
        piso: document.getElementById('pisoHab').value,
        estado: document.getElementById('estHab').value
    };

    fetch('../api/habitaciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalNuevaHabitacion')).hide();
            mostrarAlerta('Habitación creada con éxito', 'success');
            document.getElementById('formNuevaHabitacion').reset();
            listarHabitaciones();
            actualizarEstadisticas();
        } else {
            mostrarAlerta(data.error || 'Error al crear la habitación', 'danger');
        }
    })
    .catch(err => {
        console.error('Error en crearHabitacion:', err);
        mostrarAlerta('Error al crear la habitación', 'danger');
    });
}

// Función para eliminar una habitación
function eliminarHabitacion(idHab) {
    if (!confirm('¿Está seguro de que desea eliminar esta habitación?')) return;

    fetch(`../api/habitaciones.php?id=${idHab}`, { method: 'DELETE' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('Habitación eliminada con éxito', 'success');
                listarHabitaciones();
                actualizarEstadisticas();
            } else {
                mostrarAlerta(data.error || 'Error al eliminar la habitación', 'danger');
            }
        })
        .catch(err => {
            console.error('Error en eliminarHabitacion:', err);
            mostrarAlerta('Error al eliminar la habitación', 'danger');
        });
}

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
