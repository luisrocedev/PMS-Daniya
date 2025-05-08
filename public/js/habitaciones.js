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
        .catch(err => {
            console.error('Error al cargar estadísticas:', err);
            mostrarAlerta('Error al cargar las estadísticas', 'error');
        });
}

// Función para animar los contadores
function animateValue(id, end) {
    const obj = document.getElementById(id);
    if (!obj) return;
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

// Función para listar habitaciones con filtros (sin paginación)
function listarHabitaciones() {
    const search = document.getElementById('searchHab')?.value || '';
    const estado = document.getElementById('estadoHab')?.value || '';
    
    let url = '../api/habitaciones.php?';
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (estado) url += `estado=${encodeURIComponent(estado)}&`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const habitaciones = Array.isArray(data) ? data : (data.data || []);
            renderizarTablaHabitaciones(habitaciones);
        })
        .catch(err => {
            console.error('Error en listarHabitaciones:', err);
            mostrarAlerta('Error al cargar las habitaciones', 'error');
        });
}

// Renderizar tabla de habitaciones
function renderizarTablaHabitaciones(habitaciones) {
    const tbody = document.getElementById('tabla-habitaciones');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (habitaciones.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="7" class="text-center">No se encontraron habitaciones</td>';
        tbody.appendChild(tr);
        return;
    }

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
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-primary" onclick="editarHabitacion(${hab.id_habitacion})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmarEliminarHabitacion(${hab.id_habitacion})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
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

// Función para crear una nueva habitación desde el modal
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
            cerrarModal('modalNuevaHabitacion');
            mostrarAlerta('Habitación creada con éxito', 'success');
            document.getElementById('formNuevaHabitacion').reset();
            listarHabitaciones();
            actualizarEstadisticas();
        } else {
            mostrarAlerta(data.error || 'Error al crear la habitación', 'error');
        }
    })
    .catch(err => {
        console.error('Error en crearHabitacion:', err);
        mostrarAlerta('Error al crear la habitación', 'error');
    });
}

// Función para crear una nueva habitación desde el formulario en página
function crearHabitacionEnPagina() {
    const formData = {
        numero_habitacion: document.getElementById('numHabInPage').value,
        tipo_habitacion: document.getElementById('tipoHabInPage').value,
        capacidad: document.getElementById('capHabInPage').value,
        piso: document.getElementById('pisoHabInPage').value,
        estado: document.getElementById('estHabInPage').value
    };

    fetch('../api/habitaciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Habitación creada con éxito', 'success');
            document.getElementById('formNuevaHabitacionInPage').reset();
            listarHabitaciones();
            actualizarEstadisticas();
        } else {
            mostrarAlerta(data.error || 'Error al crear la habitación', 'error');
        }
    })
    .catch(err => {
        console.error('Error en crearHabitacionEnPagina:', err);
        mostrarAlerta('Error al crear la habitación', 'error');
    });
}

// Función para editar una habitación
function editarHabitacion(idHab) {
    fetch(`../api/habitaciones.php?id=${idHab}`)
        .then(res => res.json())
        .then(hab => {
            if (hab) {
                document.getElementById('idHabEdit').value = hab.id_habitacion;
                document.getElementById('numHabEdit').value = hab.numero_habitacion;
                document.getElementById('tipoHabEdit').value = hab.tipo_habitacion;
                document.getElementById('capHabEdit').value = hab.capacidad;
                document.getElementById('pisoHabEdit').value = hab.piso;
                document.getElementById('estHabEdit').value = hab.estado;
                
                const modal = new bootstrap.Modal(document.getElementById('modalEditarHabitacion'));
                modal.show();
            } else {
                mostrarAlerta('No se pudo cargar la información de la habitación', 'error');
            }
        })
        .catch(err => {
            console.error('Error en editarHabitacion:', err);
            mostrarAlerta('Error al cargar los datos de la habitación', 'error');
        });
}

// Función para actualizar una habitación
function actualizarHabitacion() {
    const idHab = document.getElementById('idHabEdit').value;
    const formData = {
        numero_habitacion: document.getElementById('numHabEdit').value,
        tipo_habitacion: document.getElementById('tipoHabEdit').value,
        capacidad: document.getElementById('capHabEdit').value,
        piso: document.getElementById('pisoHabEdit').value,
        estado: document.getElementById('estHabEdit').value
    };

    fetch(`../api/habitaciones.php?id=${idHab}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cerrarModal('modalEditarHabitacion');
            mostrarAlerta('Habitación actualizada con éxito', 'success');
            listarHabitaciones();
            actualizarEstadisticas();
        } else {
            mostrarAlerta(data.error || 'Error al actualizar la habitación', 'error');
        }
    })
    .catch(err => {
        console.error('Error en actualizarHabitacion:', err);
        mostrarAlerta('Error al actualizar la habitación', 'error');
    });
}

// Confirmar eliminación de habitación
function confirmarEliminarHabitacion(idHab) {
    if (confirm('¿Está seguro de que desea eliminar esta habitación?')) {
        eliminarHabitacion(idHab);
    }
}

// Función para eliminar una habitación
function eliminarHabitacion(idHab) {
    fetch(`../api/habitaciones.php?id=${idHab}`, { method: 'DELETE' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('Habitación eliminada con éxito', 'success');
                listarHabitaciones();
                actualizarEstadisticas();
            } else {
                mostrarAlerta(data.error || 'Error al eliminar la habitación', 'error');
            }
        })
        .catch(err => {
            console.error('Error en eliminarHabitacion:', err);
            mostrarAlerta('Error al eliminar la habitación', 'error');
        });
}

// Función para cerrar un modal
function cerrarModal(modalId) {
    const modalEl = document.getElementById(modalId);
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo === 'error' ? 'danger' : tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
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
